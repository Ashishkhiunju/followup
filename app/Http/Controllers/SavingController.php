<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Saving;
use App\Models\SavingImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class SavingController extends Controller
{
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
                'intrest_rate'=>$request->intrest_rate,
                'issue_date_eng'=>eng_date($request->issue_date),
                'issue_date_nep'=>$request->issue_date,
                'user_id'=>Auth::user()->id,
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
}
