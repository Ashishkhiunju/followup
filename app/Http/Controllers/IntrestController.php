<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanIntrest;
use App\Models\Loan;

class IntrestController extends Controller
{
    public function loanintrest(){
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
