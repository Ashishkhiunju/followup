<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Saving;
use App\Models\SavingDetail;
use App\Models\SavingImage;
use App\Models\SavingWithdraw;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class SavingController extends Controller
{

    public function index(){
        return Saving::with('customer')->latest()->get();
    }

    public function store(Request $request){
        $request->validate([
            'customer_id'=>'required',
            'saving_type'=>'required',
            'saving_amount'=>'required',
            'intrest_rate'=>'required',
            'issue_date'=>'required',
        ]);

        DB::beginTransaction();
        try{
            $saving = Saving::create([
                'customer_id'=>$request->customer_id,
                'saving_type'=>$request->saving_type,
                'saving_amount'=>$request->saving_amount,
                'intrest_rate'=>$request->intrest_rate,
                'issue_date_eng'=>eng_date($request->issue_date),
                'issue_date_nep'=>$request->issue_date,
                'user_id'=>Auth::user()->id,
            ]);
            $savingDetail = SavingDetail::create([
                'saving_id'=>$saving->id,
                'amount'=>$request->saving_amount,
                'date'=>$saving->issue_date_eng,
            ]);

            if(!empty($request->multiple_files)){
                foreach($request->multiple_files as $files ){

                    $imageName = Str::random().strtotime(date('Y-m-d H:i:s')).'.'.$files->getClientOriginalExtension();

                    Storage::disk('public')->putFileAs('saving', $files,$imageName);
                    $imageName = "saving/".$imageName;
                    SavingImage::create([
                        'saving_id'=>$saving->id,
                        'image'=>$imageName,
                    ]);
                }


            }
            DB::commit();

            return response()->json([
                'message'=>"Sucessfully Created"
            ]);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'message'=>$e->getMessage()
            ]);
        }
    }

    public function show($id){
        $saving = Saving::with('customer','saving_details','saving_withdraws')->where('id',$id)->first();
        return $saving;
    }

    public function createSavingDetail(Request $request){
        DB::beginTransaction();
        try{
            $savingDetail = SavingDetail::create([
                'saving_id'=>$request->saving_id,
                'amount'=>$request->amount,
                'date'=>date('Y-m-d'),
            ]);
            $saving = Saving::find($request->saving_id);
            $saving->increment('saving_amount',$request->amount);
            DB::commit();
            return response()->json([
                'message'=>"Saving Created Successfuly"
            ]);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'message'=>$e->getMessage()
            ]);
        }

    }

    public function createWithdrawDetail(Request $request){
        $request->validate([
            'amount'=>'required',
        ]);
        $saving = Saving::find($request->saving_id);


        DB::beginTransaction();
        try{
            SavingWithdraw::create([
                'amount'=>$request->amount,
                'saving_id'=>$request->saving_id,
                'date'=>date('Y-m-d'),
            ]);
            $saving = Saving::find($request->saving_id);
            $saving->decrement('saving_amount',$request->amount);
            DB::commit();
            return response()->json([
                'message'=>"Saving Withdraw Successfuly"
            ]);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'message'=>$e->getMessage()
            ]);
        }
    }


}
