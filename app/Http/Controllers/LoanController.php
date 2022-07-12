<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Customer;
use App\Models\LoanDetail;
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

    public function index(){
        // dd(date("Y-m-d"));
        // return new LoanCollection(Loan::with('customer')->get());

        return Loan::with('customer','loan_details')->paginate(10);

    }

    public function todayfollowup(){
        $todaydate = date("Y-m-d");
        return LoanInstallationDate::with('loan','loan.customer','loan.loan_details')->where('next_installation_eng_date',$todaydate)->paginate(10);
        // return Loan::with('customer','loan_details')->where('installation_type','daily')->paginate(10);
    }

    public function notContacted(){
        $todaydate = date("Y-m-d");
        $LoanInstallationDate = LoanInstallationDate::with('loan','loan.customer','loan.loan_details')->where('next_installation_eng_date',$todaydate)
        ->whereNotExists(function($query)
                {
                    $query->select(DB::raw(1))
                          ->from('loans_contacts')
                          ->whereRaw('loan_installation_dates.loan_id = loans_contacts.loan_id')
                          ->whereRaw('loan_installation_dates.next_installation_eng_date = loans_contacts.installation_date')
                          ->whereRaw('loans_contacts.contacted = 1');
                })

        ->paginate(10);

     return $LoanInstallationDate;
    }

    public function store(Request $request){

        if($request->has('customer_id')){
            $request->validate([
                'customer_id'=>'required',
                'loan_type'=>'required',
                'loan_amount'=>'required',
            ]);
        }else{
            $request->validate([
                'name'=>'required',
                'email'=>'nullable|unique:customers',
                'address'=>'required',
                'phone'=>'required|unique:customers',
                'loan_type'=>'required',
                'loan_amount'=>'required',
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
            $postarray = [
                'customer_id'=>$customerid,
                'issue_date_eng'=>$issue_date_eng,
                'issue_date_nep'=>$request->issue_date,
                'due_date_eng'=>$due_date_eng,
                'due_date_nep'=>$request->due_date,
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
        LoanDetail::create($request->post());
        return response()->json([
            'message'=>'Loan Installment Paid'
        ]);
    }


}
