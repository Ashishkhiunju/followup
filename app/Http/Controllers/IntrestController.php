<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanIntrest;
use App\Models\Loan;
use App\Models\Customer;

class IntrestController extends Controller
{
    public function loanintrest(){
        // $count=3000;
        // for($i=0;$i<$count;$i++){
        //     Customer::create([
        //         'name'=>'Ashish',
        //         'email'=>'Ashish@gmail.com',
        //         'address'=>'Ashi',
        //         'phone'=>'1234567',
        //         'citizen_ship_no'=>'1232',
        //     ]);
        // }

        $todaysdate = date('Y-m-d');
        $todayloanIntrest = loanIntrest::whereDate('date',date('Y-m-d'))->first();
        if(empty($todayloanIntrest)){
            $loans = Loan::where('remaining_amount',">","0")->get();
            foreach($loans as $loan){
                $intrest = ($loan->intrest_rate * $loan->loan_amount) / (100*365);
                LoanIntrest::create([
                    'loan_id'=>$loan->id,
                    'date'=>$todaysdate,
                    'intrest_amount'=>$intrest,
                ]);
                $loan->increment('intrest_amount',$intrest);
            }
        }




    }
}
