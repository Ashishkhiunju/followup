<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(){
       return Customer::all();
    //    $array = array();
    //    foreach($customers as $k=>$customer){
    //     $array[$k]['name']=$customer->name;
    //     $array[$k]['address']=$customer->address;
    //     $array[$k]['phone']=$customer->phone;
    //     $array[$k]['email']=$customer->email;
    //     $array[$k]['company_name']=$customer->company_name;
    //     $array[$k]['citizen_ship_no']=$customer->citizen_ship_no;
    //     $btn = '';
    //     $array[$k]['action'] = $btn;
    //    }

    //    return response()->json([
    //     'customer'=>$array
    // ]);

    }

    public function customerlist(){

        $customers = Customer::get();
        $array = [];
        foreach($customers as $k=>$customer){
            $array[$k]['value']=$customer->id;
            $array[$k]['label']=$customer->name;
        }
        return $array;
        // $customers->map(function($data){
        //     return [
        //         'value'=>$data->id,
        //         'label'=>$data->name,
        //     ];
        // });
    }

    public function store(Request $request){
        $request->validate([
            'name'=>'required',
            'email'=>'nullable|unique:customers',
            'address'=>'required',
            'phone'=>'required|unique:customers',

        ]);
        $imageName ="";
        if($request->image){
            $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('customer', $request->image,$imageName);
            $imageName = "customer/".$imageName;
        }
        Customer::create($request->post()+['image'=>$imageName]);
        return response()->json([
            'message'=>'Customer Created Successfully!!'
        ]);
    }

    public function customerLoanDetail($id){
        return Loan::with('loan_type')->where('customer_id',$id)->get();
    }
}
