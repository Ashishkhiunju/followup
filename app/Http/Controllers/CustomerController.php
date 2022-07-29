<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerLoanDetailResource;

class CustomerController extends Controller
{
    public const PER_PAGE           = 10;
    public const DEFAULT_SORT_FIELD = 'created_at';
    public const DEFAULT_SORT_ORDER = 'asc';

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

        $customers = Customer::select('id','name','phone')->get();
        $array = [];
        foreach($customers as $k=>$customer){
            $array[$k]['value']=$customer->id;
            $array[$k]['label']=$customer->name.'/'.$customer->phone;
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
        if($request->hasFile('image')){
            $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('customer', $request->image,$imageName);
            $imageName = "customer/".$imageName;
        }
        Customer::create($request->post()+['image'=>$imageName]);
        return response()->json([
            'message'=>'Customer Created Successfully!!'
        ]);
    }

    public function customerLoanDetail(Request $request, $id){
        // return Loan::with('loan_type')->where('customer_id',$id)->where('user_id',Auth::user()->id)->get();
        $sortFields = ['name', 'address', 'email','citizen_ship_no','company_name','phone'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField      = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder      = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput    = $request->input('search');
        $query          = Loan::with('loan_type')->where('customer_id',$id)->where('user_id',Auth::user()->id)->orderBy("created_at", 'desc');
        $perPage        = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query       = $query->where('loan_amount', 'like', $searchQuery);
        }
        $customers = $query->paginate((int)$perPage);
    return CustomerLoanDetailResource::collection($customers);
    }

    public function allcustomers(Request $request){
        $sortFields = ['name', 'address', 'email','citizen_ship_no','company_name','phone'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField      = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder      = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput    = $request->input('search');
        $query          = Customer::orderBy("created_at", 'desc');
        $perPage        = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query       = $query->where('name', 'like', $searchQuery)->orWhere('email', 'like', $searchQuery)->orWhere(
                'address',
                'like',
                $searchQuery
            );
        }
        $customers = $query->paginate((int)$perPage);
    return CustomerResource::collection($customers);
    }
}
