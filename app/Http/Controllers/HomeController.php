<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\LoanInstallationDate;
use App\Models\LoanContact;
use App\Models\Saving;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {


        // return view('home');
        return 'success';
    }

    public function convertdate(Request $request){
        if($request->convertto == 'nepali'){
            return [
                'nep_date_inNepali' => nep_date($request->date),
                'nep_date_inEnglish' => nep_date_inEng($request->date),
            ];
        }

    }

    public function dashboardDatas(){
        $data = [];
        $data['loan_installation_today'] = LoanInstallationDate::whereDate('next_installation_eng_date',date('Y-m-d'))->count();
        $data['loan_contacts_today'] = LoanContact::whereDate('installation_date',date('Y-m-d'))->count();
        $data['not_contacts_today'] = LoanContact::whereDate('installation_date',date('Y-m-d'))->where('contacted',0)->count();
        $data['saving_today'] = Saving ::whereDate('issue_date_eng',date('Y-m-d'))->sum('saving_amount');

        return $data;
    }
}
