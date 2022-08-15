<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanType;

class LoanTypeController extends Controller
{
    //
    public function index(){
        return LoanType::all();
    }

    public function store(Request $request){
        $checkexist = LoanType::where('type',$request->title)->first();
        if($checkexist){
            return response()->json([
                'message'=>'loan type already exist'
            ]);
        }
        LoanType::create([
            'type'=>$request->title,
            'based'=>$request->based,
            'intrest_rate'=>$request->intrest_rate,
        ]);
        return response()->json([
            'message'=>'Loan type created successfully'
        ]);
    }
}
