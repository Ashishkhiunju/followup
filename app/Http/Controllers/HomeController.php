<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

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
}
