<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class LoanController extends Controller
{

    public function index(){
        return Loan::select('id','name','loan_amount','image')->get();
    }

    public function store(Request $request){
        try{
            $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
            
            Storage::disk('public')->putFileAs('loan', $request->image,$imageName);
            $imageName = "loan/".$imageName;
            Loan::create($request->post()+['image'=>$imageName]);

            return response()->json([
                'message'=>'Loan Created Successfully!!'
            ]);
        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while creating a product!!'
            ],500);
        }
    }

    public function show(loan $loan)
    {
       
        return response()->json([
            'loan'=>$loan
        ]);

        
    }


    public function update(Request $request, Loan $loan){
        // try{

            $loan->fill($request->post())->update();

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
}
