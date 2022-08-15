<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanContact;
use App\Models\LoanDetail;
use App\Models\LoanImage;
use App\Models\LoanInstallationDate;
use App\Models\LoanReminder;
use Auth;
use Carbon\Carbon; //nepali to english
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; //eng to nepali

class LoanController extends Controller
{

    public function index(Request $request)
    {

        // dd(date("Y-m-d"));
        // return new LoanCollection(Loan::with('customer')->get());
        $loan = Loan::with('customer', 'loan_details', 'loan_installation_date')->where('user_id', Auth::user()->id);
        if ($request->filter == 'week') {
            $todaydate = date('Y-m-d');
            $dateafterWeek = date("Y-m-d", strtotime('+7 days', strtotime($todaydate)));
            $loan->whereHas('loan_installation_date', function ($q) use ($todaydate, $dateafterWeek) {
                $q->whereBetween('next_installation_eng_date', [$todaydate, $dateafterWeek]);
            });
        }
        if (strlen($request->search) > 1) {
            $search = $request->search;
            $loan = $loan->whereHas('customer', function ($q) use ($search) {
                $q->where('name', "LIKE", "%" . $search . "%")
                    ->orWhere('phone', "LIKE", "%" . $search . "%");

            });
            // $loan = $loan->where('loan_amount',"LIKE","%".$search."%");

        }
        if ($request->filter == 'week') {
            $filter = "DESC";
        } else {
            $filter = $request->filter;
        }

        return $loan->orderBy('id', $filter)->paginate(10);

    }

    public function todayfollowup()
    {
        $todaydate = date("Y-m-d");
        return LoanInstallationDate::with('loan', 'loan.customer', 'loan.loan_details')->whereHas('loan', function ($q) {
            $q->where('user_id', Auth::user()->id);
        })->where('next_installation_eng_date', $todaydate)->paginate(10);
        // return Loan::with('customer','loan_details')->where('installation_type','daily')->paginate(10);
    }

    public function notContacted(Request $request)
    {
        $todaydate = date("Y-m-d");
        // $LoanInstallationDate = LoanInstallationDate::with('loan','loan.customer','loan.loan_details')->whereHas('loan',function($q){
        //     $q->where('user_id',Auth::user()->id);
        // })->where('next_installation_eng_date',$todaydate)
        // ->whereNotExists(function($query)
        //         {
        //             $query->select(DB::raw(1))
        //                   ->from('loans_contacts')
        //                   ->whereRaw('loan_installation_dates.loan_id = loans_contacts.loan_id')
        //                   ->whereRaw('loan_installation_dates.next_installation_eng_date = loans_contacts.installation_date')
        //                   ->whereRaw('loans_contacts.contacted = 1');
        //         })

        // ->paginate(10);

        $query = LoanContact::with('loan', 'loan.customer', 'loan.loan_details')->whereHas('loan', function ($q) {
            $q->where('user_id', Auth::user()->id);
        })->where('contacted', '0');

        if ($request->search) {
            $search = $request->search;
            $query = $query->whereHas('loan.customer', function ($q) use ($search) {
                $q->where('name', "LIKE", "%" . $search . "%")
                    ->orWhere('phone', "LIKE", "%" . $search . "%");
            });

        }
        $query = $query->orderBy('id', $request->filter)->paginate(10);
        return $query;
    }

    public function makeconnected(Request $request)
    {

        $loancontacts = LoanContact::updateOrCreate(
            [
                'loan_id' => $request->loan_id,
                'installation_date' => $request->installation_date,
            ],
            [
                'contacted' => 1,
            ]
        );
        if ($loancontacts) {
            return response()->json([
                'status' => '200',
                'message' => 'Contacted make successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => '401',
                'message' => 'Something went wrong',
            ], 401);
        }
        return $request->loan_id;
    }

    public function makeReminder(Request $request)
    {
        $reminderEngdate = eng_date($request->reminderDate);
        // return $request->reminderDetail;
        $loanreminder = LoanReminder::updateOrCreate([
            'loan_id' => $request->loan_id,
            'installation_date' => $request->installation_date,
        ],
            [
                'reminder_date_eng' => $reminderEngdate,
                'reminder_date_nep' => $request->reminderDate,
                'reminder_detail' => $request->reminderDetail,
            ]);
        if ($loanreminder) {
            return response()->json([
                'status' => "200",
                'message' => "Reminder Update Successfully",
            ]);
        } else {
            return response()->json([
                'status' => "401",
                'message' => "Something went Wrong Please try again later",
            ]);
        }

    }

    public function reminder()
    {
        $todaydate = date("Y-m-d");

        return LoanReminder::with('loan', 'loan.customer', 'loan.loan_details')->whereHas('loan', function ($q) {
            $q->where('user_id', Auth::user()->id);
        })->whereDate('reminder_date_eng', $todaydate)->paginate(10);
    }

    public function store(Request $request)
    {
// return gettype($request->multiple_files);
        if ($request->has('customer_id')) {
            $request->validate([
                'customer_id' => 'required',
                'loan_type' => 'required',
                'loan_amount' => 'required',
                'intrest_rate' => 'required',
            ]);
        } else {
            $request->validate([
                'name' => 'required',
                'email' => 'nullable|unique:customers',
                'address' => 'required',
                'phone' => 'required|unique:customers',
                'loan_type' => 'required',
                'loan_amount' => 'required',
                'intrest_rate' => 'required',
            ]);
        }

        DB::beginTransaction();
        try {
            $imageName = "";
            if ($request->hasFile('image')) {
                $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();

                Storage::disk('public')->putFileAs('files', $request->image, $imageName);
                $imageName = "files/" . $imageName;
            }
            if (!$request->customer_id) {
                $customer = Customer::create([
                    'name' => $request->name,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'company_name' => $request->company_name,
                    'citizen_ship_no' => $request->citizen_ship_no,
                    'image' => $imageName,
                ]);
                $customerid = $customer->id;
            } else {
                $customerid = $request->customer_id;
            }

            $issue_date_eng = eng_date($request->issue_date);
            $due_date_eng = eng_date($request->due_date);

            // $intrest_amount = $this->calculateIntrestAmount($request->loan_amount,$request->intrest_rate);
            $emiInMonth = ($request->loan_duration > 1) ? PMT($request->intrest_rate, $request->loan_duration, $request->loan_amount) : $request->loan_amount;
            $remaining_duration = $request->loan_duration;
            if ($request->installation_type == "daily") {
                $remaining_duration = $remaining_duration * 30;
            }
            if ($request->installation_type == "weekly") {
                $remaining_duration = $remaining_duration * 4;
            }
            if ($request->installation_type == "monthly") {
                $remaining_duration = $remaining_duration;
            }
            if ($request->installation_type == "yearly") {
                $remaining_duration = $remaining_duration / 12;
            }
            $emi = 0;
            $total_loan_amount = $request->loan_amount;
            $remainig_amount = $request->loan_amount;
            if ($request->loan_based == 'emi') {
                $emi = $request->emi;
                $total_loan_amount = $emiInMonth * $request->loan_duration;
                $remainig_amount = $emiInMonth * $request->loan_duration;
            }

            $postarray = [
                'customer_id' => $customerid,
                'remaining_amount' => $remainig_amount,
                'remaining_duration' => $remaining_duration,
                'total_loan_amount' => $total_loan_amount,
                'emi' => $emi,
                'issue_date_eng' => $issue_date_eng,
                'issue_date_nep' => $request->issue_date,
                'due_date_eng' => $due_date_eng,
                'due_date_nep' => $request->due_date,
                'user_id' => Auth::user()->id,
                'intrest_rate' => $request->intrest_rate,
                'intrest_amount' => "0",
                'recommender_id' => $request->recommender_id,
                'loan_type' => $request->loan_type,
                'loan_amount' => $request->loan_amount,
                'loan_duration' => $request->loan_duration,
                'loan_purpose' => $request->loan_purpose,
                'installation_type' => $request->installation_type,
                'citizen_ship_no' => $request->citizen_ship_no,
                'status' => $request->status,

            ];

            $loan = Loan::create($postarray);

            if (!empty($request->multiple_files)) {
                foreach ($request->multiple_files as $files) {

                    $imageName = Str::random() . '.' . $files->getClientOriginalExtension();

                    Storage::disk('public')->putFileAs('files', $files, $imageName);
                    $imageName = "files/" . $imageName;
                    LoanImage::create([
                        'loan_id' => $loan->id,
                        'image' => $imageName,
                    ]);
                }

            }
            $this->createLoanInstallationDate($loan->id, $loan->installation_type, $loan->issue_date_eng);

            DB::commit();
            return response()->json([
                'message' => 'Loan Created Successfully!!',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createLoanInstallationDate($loan_id, $installation_type, $startdate)
    {

        $startdate = Carbon::createFromFormat('Y-m-d', $startdate);

        if ($installation_type == "daily") {
            $nextInstallationEngDate = $startdate->addDays(1);
        }
        if ($installation_type == 'weekly') {
            $nextInstallationEngDate = $startdate->addDays(7);
        }
        if ($installation_type == 'monthly') {
            $nextInstallationEngDate = $startdate->addDays(30);
        }
        if ($installation_type == 'yearly') {
            $nextInstallationEngDate = $startdate->addDays(365);
        }

        $nextInstallationEngDate = date('Y-m-d', strtotime($nextInstallationEngDate));
        $installationDates = LoanInstallationDate::updateOrCreate(
            ['loan_id' => $loan_id],
            ['next_installation_eng_date' => $nextInstallationEngDate],

        );
        $installation_eng_date = $installationDates->next_installation_eng_date;

        $nextInstallationNepDate = nep_date($installation_eng_date);
        $installationDates->next_installation_nep_date = $nextInstallationNepDate;
        $installationDates->save();

    }

    public function calculateIntrestAmount($loanamount, $intrestAmount)
    {
        $amount = ($intrestAmount * $loanamount) / (100 * 365);
        return $amount;
    }

    public function show($id)
    {
        $loan = Loan::with('customer', 'recommender', 'loan_images')->where('id', $id)->first();
        return response()->json([
            'loan' => $loan,
        ]);

    }

    public function update(Request $request, Loan $loan)
    {
        DB::beginTransaction();
        try {
            $issue_date_eng = eng_date($request->issue_date);
            $due_date_eng = eng_date($request->due_date);
            $postarray = [

                'issue_date_eng' => $issue_date_eng,
                'issue_date_nep' => $request->issue_date,
                'due_date_eng' => $due_date_eng,
                'due_date_nep' => $request->due_date,
            ];

            $loan->fill($request->post())->update();
            $loan->update($postarray);

            if ($request->hasFile('image')) {

                // remove old image
                if ($loan->image) {
                    $exists = Storage::disk('public')->exists("loan/{$loan->image}");
                    if ($exists) {
                        Storage::disk('public')->delete("loan/{$loan->image}");
                    }
                }

                $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('loan', $request->image, $imageName);
                $imageName = 'loan/' . $imageName;
                $loan->image = $imageName;
                $loan->save();

            }

            if (!empty($request->multiple_files)) {
                foreach ($request->multiple_files as $files) {

                    $imageName = Str::random() . '.' . $files->getClientOriginalExtension();

                    Storage::disk('public')->putFileAs('files', $files, $imageName);
                    $imageName = "files/" . $imageName;
                    LoanImage::create([
                        'loan_id' => $loan->id,
                        'image' => $imageName,
                    ]);
                }

            }
            DB::commit();
            return response()->json([
                'message' => 'Loan Updated Successfully!!',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something goes wrong while updating a product!!',
            ], 500);
        }
    }

    public function destroy(Loan $loan)
    {
        try {

            if ($loan->image) {
                $exists = Storage::disk('public')->exists("loan/{$loan->image}");
                if ($exists) {
                    Storage::disk('public')->delete("loan/{$loan->image}");
                }
            }

            $loan->delete();

            return response()->json([
                'message' => 'Loan Deleted Successfully!!',
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something goes wrong while deleting a product!!',
            ]);
        }
    }

    public function loandetail($id)
    {

        return Loan::with('customer', 'loan_details', 'loan_installation_date', 'loan_type')->where('user_id', Auth::user()->id)->where('id', $id)->first();

    }

    public function saveloandetail(Request $request)
    {
        DB::beginTransaction();
        try {
            LoanDetail::create([
                'loan_id' => $request->loan_id,
                'paid_amount' => $request->paid_amount,
                'paid_date' => date("Y-m-d"),
            ]);
            $loan = Loan::find($request->loan_id);
            $loan->decrement('remaining_amount', $request->paid_amount);
            $loan->increment('paid_amount', $request->paid_amount);

            $loan->decrement('remaining_duration', 1);
            $emi = 0;
            if ($request->loanbased == "emi") {
                $emiInMonth = PMT($loan->intrest_rate, $loan->loan_duration, $loan->loan_amount);

                if ($loan->installation_type == 'daily') {
                    $emi = $emiInMonth / 30;
                    // $remaining_duration = $loan->remaining_duration != 0 ? $loan->remaining_duration: 1;
                    // $emi = ($emiInMonth*$loan->loan_duration)/$remaining_duration;

                    // if(($loan->loan_duration - 1) * 30 == $loan->remaining_duration){
                    //     $loan->decrement('loan_duration',1);
                    // }
                }
                if ($loan->installation_type == 'weekly') {
                    // $remaining_duration = $loan->remaining_duration != 0 ? $loan->remaining_duration: 1;
                    // $emi = ($emiInMonth*$loan->loan_duration)/$remaining_duration;
                    $emi = $emiInMonth / 4;

                    // if(($loan->loan_duration - 1) * 4 == $loan->remaining_duration){
                    //     $loan->decrement('loan_duration',1);
                    // }
                }
                if ($loan->installation_type == 'monthly') {
                    // $loan->decrement('loan_duration',1);
                    // $emiInMonth = ($loan->loan_duration > 1) ? PMT($loan->intrest_rate,$loan->loan_duration,$loan->remaining_amount) : $loan->remaining_amount;
                    $emi = $emiInMonth;

                }
                if ($loan->installation_type == 'yearly') {
                    $emi = $emiInMonth * 12;
                    // $loan->decrement('loan_duration',12);
                    // $emiInMonth = ($loan->loan_duration > 12) ? PMT($loan->intrest_rate,$loan->loan_duration,$loan->remaining_amount) : $loan->remaining_amount;
                    // $emi = ($loan->loan_duration > 12) ? $emiInMonth * 12 :$loan->remaining_amount ;
                }
            }

            $loan->emi = $emi;

            $loan->save();
            //update loan installation date
            $loaninstallationdate = LoanInstallationDate::where('loan_id', $request->loan_id)->first();

            $this->createLoanInstallationDate($loan->id, $loan->installation_type, $loaninstallationdate->next_installation_eng_date);

            //update loan contact if exist
            $loancontact = LoanContact::where('loan_id', $request->loan_id)->where('installation_date', $loaninstallationdate->next_installation_eng_date)->first();
            if ($loancontact) {
                $loancontact->update([
                    'contacted' => '1',
                    'paid' => '1',
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => 'Loan Installment Paid',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return response()->json([
                'message' => $message,
            ]);
        }

    }

    public function loanAllDetails($id)
    {

        $loandetails = Loan::with('customer', 'loan_details', 'loan_type', 'loan_contacts', 'loan_reminders', 'loan_images')->where('id', $id)->first();
        if (Auth::user()->id != $loandetails->user_id) {
            return response()->json([
                'status' => "401",
                'message' => "You Dont have Access For this user",
            ]);
        }
        return $loandetails;
    }
    public function deleteLoanImage(Request $request)
    {
        $image = LoanImage::find($request->image_id);
        $image->delete();
        return response()->json([
            'message' => "Deleted Image SuccessFully",
        ]);

    }

}
