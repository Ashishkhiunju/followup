<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerLoanDetailResource;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\CustomerSignature;
use App\Models\Loan;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public const PER_PAGE = 10;
    public const DEFAULT_SORT_FIELD = 'created_at';
    public const DEFAULT_SORT_ORDER = 'asc';

    public function index()
    {
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

    public function customerlist()
    {

        $customers = Customer::select('id', 'name', 'phone')->get();
        $array = [];
        foreach ($customers as $k => $customer) {
            $array[$k]['value'] = $customer->id;
            $array[$k]['label'] = $customer->name . '/' . $customer->phone;
        }
        return $array;
        // $customers->map(function($data){
        //     return [
        //         'value'=>$data->id,
        //         'label'=>$data->name,
        //     ];
        // });
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'nullable|unique:customers',
            'address' => 'required',
            'phone' => 'required|unique:customers',

        ]);
        $imageName = "";
        if ($request->hasFile('image')) {
            $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('customer/', $request->image, $imageName);
            $imageName = "customer/" . $imageName;
        }

        $customer = Customer::create($request->post() + ['image' => $imageName]);
        if (!empty($request->multiple_signatures)) {
            $collection = collect($request->multiple_signatures);
            $collection->map(function ($data) use($customer){
                $imageName = strtotime(date('Y-m-d')).Str::random() .'.' . $data->getClientOriginalExtension();

                Storage::disk('public')->putFileAs('customer/signatures/', $data, $imageName);
                $imageName = "customer/signatures/" . $imageName;
                CustomerSignature::create([

                    'customer_id' => $customer->id,
                    'image' => $imageName,
                ]);
            });
        }

        return response()->json([
            'message' => 'Customer Created Successfully!!',
        ]);
    }

    public function customerLoanDetail(Request $request, $id)
    {
        // return Loan::with('loan_type')->where('customer_id',$id)->where('user_id',Auth::user()->id)->get();
        $sortFields = ['name', 'address', 'email', 'citizen_ship_no', 'company_name', 'phone'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput = $request->input('search');
        $query = Loan::with('loan_type')->where('customer_id', $id)->where('user_id', Auth::user()->id)->orderBy("created_at", 'desc');
        $perPage = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query = $query->where('loan_amount', 'like', $searchQuery);
        }
        $customers = $query->paginate((int) $perPage);
        return CustomerLoanDetailResource::collection($customers);
    }

    public function allcustomers(Request $request)
    {
        $sortFields = ['name', 'address', 'email', 'citizen_ship_no', 'company_name', 'phone'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput = $request->input('search');
        $query = Customer::orderBy("created_at", 'desc');
        $perPage = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query = $query->where('name', 'like', $searchQuery)->orWhere('email', 'like', $searchQuery)->orWhere(
                'address',
                'like',
                $searchQuery
            );
        }
        $customers = $query->paginate((int) $perPage);
        return CustomerResource::collection($customers);
    }

    public function show($id){

      return Customer::with('customer_signatures')->find($id);

    }

    public function update(Request $request,$id){
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|unique:customers,email,'.$id,
            'address' => 'required',
            'phone' => 'required|unique:customers,phone,'.$id,

        ]);
        $customer = Customer::find($id);
        $imageName = $customer->image;
        if ($request->hasFile('image')) {
            $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('customer/', $request->image, $imageName);
            $imageName = "customer/" . $imageName;
        }

        $updatearray = [
            'image'=>$imageName,
        ];
        $customer->fill($request->post());
        $customer->update($updatearray);

        if (!empty($request->multiple_signatures)) {
            $collection = collect($request->multiple_signatures);
            $collection->map(function ($data) use($id){
                $imageName = strtotime(date('Y-m-d')).Str::random() .'.' . $data->getClientOriginalExtension();

                Storage::disk('public')->putFileAs('customer/signatures/', $data, $imageName);
                $imageName = "customer/signatures/" . $imageName;
                CustomerSignature::create([

                    'customer_id' => $id,
                    'image' => $imageName,
                ]);
            });
        }

        return response()->json([
            'icon'=>'success',
            'message' => 'Customer Update Successfully!!',
        ]);
    }
}
