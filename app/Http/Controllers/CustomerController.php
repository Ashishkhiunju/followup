<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(){
       return Customer::get();
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
}
