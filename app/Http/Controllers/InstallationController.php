<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanContact;
use App\Models\LoanInstallationDate;

class InstallationController extends Controller
{
    //

    public function installationContactForToday(){
        $todaydate = date("Y-m-d");

        $loancontacts = LoanContact::whereDate('installation_date',$todaydate)->first();
        if(empty($loancontacts)){
            $loaninstallations = LoanInstallationDate::whereDate('next_installation_eng_date',$todaydate)->get();
            foreach($loaninstallations as $installation){
                LoanContact::create([
                    'loan_id'=>$installation->loan_id,
                    'installation_date'=>$installation->next_installation_eng_date,
                    'contacted'=>0,
                    'paid'=>0,
                ]);
            }

        }

    }
}
