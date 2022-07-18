<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Customer;
use App\Models\LoanContact;
use App\Models\LoanDetail;
use App\Models\LoanReminder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Bsdate;//nepali to english
use App\Models\LoanInstallationDate;
use App\Http\Resources\LoanCollection;
use Illuminate\Support\Facades\Storage;
use Krishnahimself\DateConverter\DateConverter;//eng to nepali
use App\Helper\Nepali_Calendar;

class LoanController extends Controller
{

    public function index(Request $request){

        // dd(date("Y-m-d"));
        // return new LoanCollection(Loan::with('customer')->get());
        $loan = Loan::with('customer','loan_details')->where('user_id',Auth::user()->id);
        if($request->search){
            $search = $request->search;
            $loan = $loan->whereHas('customer',function($q)use($search){
                $q->where('name',"LIKE","%".$search."%")
                  ->orWhere('phone',"LIKE","%".$search."%");

            });
            // $loan = $loan->where('loan_amount',"LIKE","%".$search."%");

        }
        $filter = $request->filter;

        return $loan->orderBy('id',$filter)->paginate(10);

    }

    public function todayfollowup(){
        $todaydate = date("Y-m-d");
        return LoanInstallationDate::with('loan','loan.customer','loan.loan_details')->whereHas('loan',function($q){
            $q->where('user_id',Auth::user()->id);
        })->where('next_installation_eng_date',$todaydate)->paginate(10);
        // return Loan::with('customer','loan_details')->where('installation_type','daily')->paginate(10);
    }

    public function notContacted(Request $request){
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

        $query = LoanContact::with('loan','loan.customer','loan.loan_details')->whereHas('loan',function($q){
                $q->where('user_id',Auth::user()->id);
            })->where('contacted','0');

        if($request->search){
            $search = $request->search;
            $query = $query->whereHas('loan.customer',function($q)use($search){
                $q->where('name',"LIKE","%".$search."%")
                  ->orWhere('phone',"LIKE","%".$search."%");
            });

        }
        $query = $query->orderBy('id',$request->filter)->paginate(10);
     return $query;
    }

    public function makeconnected(Request $request){

        $loancontacts = LoanContact::updateOrCreate(
                [
                    'loan_id'=>$request->loan_id,
                    'installation_date'=>$request->installation_date
                ],
                [
                    'contacted'=>1
                ]
            );
        if($loancontacts){
            return response()->json([
                'status'=>'200',
                'message'=>'Contacted make successfully'
            ],200);
        }else{
            return response()->json([
                'status'=>'401',
                'message'=>'Something went wrong'
            ],401);
        }
        return $request->loan_id;
    }

    public function makeReminder(Request $request){
        $reminderEngdate = eng_date($request->reminderDate);
        // return $request->reminderDetail;
        $loanreminder = LoanReminder::updateOrCreate([
            'loan_id'=>$request->loan_id,
            'installation_date'=>$request->installation_date,
        ],
        [
            'reminder_date_eng'=>$reminderEngdate,
            'reminder_date_nep'=>$request->reminderDate,
            'reminder_detail'=>$request->reminderDetail,
        ]);
        if($loanreminder){
            return response()->json([
                'status'=>"200",
                'message'=>"Reminder Update Successfully",
            ]);
        }else{
            return response()->json([
                'status'=>"401",
                'message'=>"Something went Wrong Please try again later",
            ]);
        }


    }

    public function reminder(){
        $todaydate = date("Y-m-d");

        return LoanReminder::with('loan','loan.customer','loan.loan_details')->whereHas('loan',function($q){
            $q->where('user_id',Auth::user()->id);
        })->whereDate('reminder_date_eng',$todaydate)->paginate(10);
    }

    public function store(Request $request){

        if($request->has('customer_id')){
            $request->validate([
                'customer_id'=>'required',
                'loan_type'=>'required',
                'loan_amount'=>'required',
                'intrest_rate'=>'required',
            ]);
        }else{
            $request->validate([
                'name'=>'required',
                'email'=>'nullable|unique:customers',
                'address'=>'required',
                'phone'=>'required|unique:customers',
                'loan_type'=>'required',
                'loan_amount'=>'required',
                'intrest_rate'=>'required',
            ]);
        }

        DB::beginTransaction();
        try{
            $imageName = "";
            if($request->hasFile('image')){
                $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();

                Storage::disk('public')->putFileAs('files', $request->image,$imageName);
                $imageName = "files/".$imageName;
            }
            if(!$request->customer_id){
                $customer = Customer::create([
                    'name'=>$request->name,
                    'address'=>$request->address,
                    'phone'=>$request->phone,
                    'email'=>$request->email,
                    'company_name'=>$request->company_name,
                    'citizen_ship_no'=>$request->citizen_ship_no,
                    'image'=>$imageName
                ]);
                $customerid = $customer->id;
            }else{
                $customerid = $request->customer_id;
            }

            $issue_date_eng = eng_date($request->issue_date);
            $due_date_eng = eng_date($request->due_date);

            // $intrest_amount = $this->calculateIntrestAmount($request->loan_amount,$request->intrest_rate);
            $postarray = [
                'customer_id'=>$customerid,
                'remaining_amount'=>$request->loan_amount,
                'issue_date_eng'=>$issue_date_eng,
                'issue_date_nep'=>$request->issue_date,
                'due_date_eng'=>$due_date_eng,
                'due_date_nep'=>$request->due_date,
                'user_id'=>Auth::user()->id,
                'intrest_rate'=>$request->intrest_rate,
                'intrest_amount'=>"0",
            ];

            $loan = Loan::create($request->post()+$postarray);
            $this->createLoanInstallationDate($loan->id,$loan->installation_type,$loan->issue_date_eng);

            DB::commit();
            return response()->json([
                'message'=>'Loan Created Successfully!!'
            ]);
        }catch(\Exception $e){
            DB::rollback();
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function createLoanInstallationDate($loan_id,$installation_type,$startdate){



        $startdate = Carbon::createFromFormat('Y-m-d', $startdate);


        if($installation_type == "daily"){
            $nextInstallationEngDate = $startdate->addDays(1);
        }
        if($installation_type == 'weekely'){
            $nextInstallationEngDate = $startdate->addDays(7);
        }
        if($installation_type == 'monthly'){
            $nextInstallationEngDate = $startdate->addDays(30);
        }
        if($installation_type == 'yearly'){
            $nextInstallationEngDate = $startdate->addDays(365);
        }

        $nextInstallationEngDate = date('Y-m-d',strtotime($nextInstallationEngDate));
        $installationDates = LoanInstallationDate::updateOrCreate(
            ['loan_id'=>$loan_id],
            ['next_installation_eng_date'=>$nextInstallationEngDate],

        );
        $installation_eng_date = $installationDates->next_installation_eng_date;

        $nextInstallationNepDate = nep_date($installation_eng_date);
        $installationDates->next_installation_nep_date = $nextInstallationNepDate;
        $installationDates->save();

    }

    public function calculateIntrestAmount($loanamount,$intrestAmount){
        $amount = ($intrestAmount * $loanamount) / (100*365);
        return $amount;
    }

    public function show($id)
    {
        $loan = Loan::with('customer')->where('id',$id)->first();
        return response()->json([
            'loan'=>$loan
        ]);


    }


    public function update(Request $request, Loan $loan){
        // try{
            $issue_date_eng = eng_date($request->issue_date);
            $due_date_eng = eng_date($request->due_date);
            $postarray = [

                'issue_date_eng'=>$issue_date_eng,
                'issue_date_nep'=>$request->issue_date,
                'due_date_eng'=>$due_date_eng,
                'due_date_nep'=>$request->due_date,
            ];

            $loan->fill($request->post())->update();
            $loan->update($postarray);

            if($request->hasFile('image')){

                // remove old image
                if($loan->image){
                    $exists = Storage::disk('public')->exists("loan/{$loan->image}");
                    if($exists){
                        Storage::disk('public')->delete("loan/{$loan->image}");
                    }
                }

                $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('loan', $request->image,$imageName);
                $imageName = 'loan/'.$imageName;
                $loan->image = $imageName;
                $loan->save();
            }

            return response()->json([
                'message'=>'Loan Updated Successfully!!'
            ]);

        // }catch(\Exception $e){
        //     \Log::error($e->getMessage());
        //     return response()->json([
        //         'message'=>'Something goes wrong while updating a product!!'
        //     ],500);
        // }
    }


    public function destroy(Loan $loan){
        try {

            if($loan->image){
                $exists = Storage::disk('public')->exists("loan/{$loan->image}");
                if($exists){
                    Storage::disk('public')->delete("loan/{$loan->image}");
                }
            }

            $loan->delete();

            return response()->json([
                'message'=>'Loan Deleted Successfully!!'
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while deleting a product!!'
            ]);
        }
    }


    public function loandetail($id){

        return Loan::with('customer','loan_details')->where('id',$id)->first();

    }

    public function saveloandetail(Request $request){
        DB::beginTransaction();
        try{
            LoanDetail::create([
                'loan_id'=>$request->loan_id,
                'paid_amount'=>$request->paid_amount,
                'paid_date'=>date("Y-m-d"),
            ]);
            $loan = Loan::find($request->loan_id);
            $loan->decrement('remaining_amount',$request->paid_amount);
            $loan->increment('paid_amount',$request->paid_amount);

            //update loan installation date
            $loaninstallationdate = LoanInstallationDate::where('loan_id',$request->loan_id)->first();
            $this->createLoanInstallationDate($loan->id,$loan->installation_type,$loaninstallationdate->next_installation_eng_date);

            //update loan contact if exist
            $loancontact = LoanContact::where('loan_id',$request->loan_id)->where('installation_date',$loaninstallationdate->next_installation_eng_date)->first();
            if($loancontact){
                $loancontact->update([
                    'contacted'=>'1',
                    'paid'=>'1',
                ]);
            }
            DB::commit();
            return response()->json([
                'message'=>'Loan Installment Paid'
            ]);
        }catch(\Exception $e){
            DB::rollback();
            $message = $e->getMessage();
            return response()->json([
                'message'=>$message
            ]);
        }

    }

    public function loanAllDetails($id){

        $loandetails = Loan::with('customer','loan_details','loan_type','loan_contacts','loan_reminders')->where('id',$id)->first();
        if(Auth::user()->id != $loandetails->user_id){
            return response()->json([
                'status'=>"401",
                'message'=>"You Dont have Access For this user",
            ]);
        }
        return $loandetails;
    }


}
