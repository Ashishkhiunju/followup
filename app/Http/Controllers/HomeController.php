<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\LoanInstallationDate;
use App\Models\LoanContact;
use App\Models\Saving;
use DateTime;
use Mail;



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
        // $data['to']='sunitabagale95@gmail.com';
        //     Mail::raw('Hi sunita Maam', function ($message) use($data) {
        //         $message->to($data['to'])
        //         ->subject('Registration Otp');
        //     });

        //     return;
        $datetime1 = new DateTime('2022-8-3');
        $datetime2 = new DateTime('2022-08-6');
        $interval = $datetime1->diff($datetime2);
        dd($interval->d .' days');

        $todaydate = date('Y-m-d');
       $dateafterWeek =  date("Y-m-d", strtotime('+7 days' , strtotime($todaydate)));
        $data = LoanInstallationDate::whereBetween('next_installation_eng_date',[$todaydate,$dateafterWeek])->get();
        dd($data);

        // return view('home');
        // return 'success';
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
