<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Http\Resources\LoanCollection;
use DB;

class LoanController extends Controller
{

    public function index(){
        // return new LoanCollection(Loan::with('customer')->get());
        return Loan::with('customer')->get();
        
    }

    public function store(Request $request){
        $request->validate([
            'name'=>'required',
            'email'=>'nullable|unique:customers',
            'address'=>'required',
            'phone'=>'required|unique:customers',
            'loan_type'=>'required',
            'loan_amount'=>'required',
        ]);
        DB::beginTransaction();
        try{
            $imageName = "";
            if($request->image){
                $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
            
                Storage::disk('public')->putFileAs('customer', $request->image,$imageName);
                $imageName = "customer/".$imageName;
            }
            $customer = Customer::create([
                'name'=>$request->name,
                'address'=>$request->address,
                'phone'=>$request->phone,
                'email'=>$request->email,
                'company_name'=>$request->company_name,
                'citizen_ship_no'=>$request->citizen_ship_no,
                'image'=>$imageName
            ]);
           
            Loan::create($request->post()+['customer_id'=>$customer->id]);
            DB::commit();
            return response()->json([
                'message'=>'Loan Created Successfully!!'
            ]);
        }catch(\Exception $e){
            DB::rollback();
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>$e->getMessage()
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
