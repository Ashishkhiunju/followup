<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Models\Saving;
use Illuminate\Support\Str;
use Redirect,Response,File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserLoanResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public const PER_PAGE           = 10;
    public const DEFAULT_SORT_FIELD = 'created_at';
    public const DEFAULT_SORT_ORDER = 'asc';

    public function __construct( User $user)
    {
        $this->user = $user;
    }

    public function index(Request $request){
        // return User::all()->except(1);
        $sortFields = ['name', 'address', 'email','citizen_ship_no','company_name','phone'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField      = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder      = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput    = $request->input('search');
        $query          = $this->user->orderBy($sortField, $sortOrder);
        $perPage        = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query       = $query->where('name', 'like', $searchQuery)->orWhere('email', 'like', $searchQuery)->orWhere(
                'address',
                'like',
                $searchQuery
            );
        }
        $users = $query->where('id','!=',1)->paginate((int)$perPage);
    return UserResource::collection($users);
    }

    public function store(Request $request){
        $request->validate([
            'name'=>'required',
            'email'=>'nullable|unique:users',
            'address'=>'required',
            'phone'=>'required|unique:users',
            'password'=>'required|min:8',
            'confirm_password'=>'required|same:password',
            'role_id'=>'required',
        ]);
        DB::beginTransaction();
        try{
            $imageName = "";
            if($request->image){
                $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();

                Storage::disk('public')->putFileAs('user', $request->image,$imageName);
                $imageName = "user/".$imageName;
            }
            User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'address'=>$request->address,
                'phone'=>$request->phone,
                'image'=>$imageName,
                'role_id'=>$request->role_id,
            ]);
            DB::commit();
            return response()->json([
                'message'=>'Successfully Added'
            ]);
        }catch(\Exception $e){
            DB::rollback();
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function show($id){

        return User::where('id',$id)->first();

    }

    public function update(Request $request, User $user){
        $request->validate([
            'name'=>'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'address'=>'required',
            'phone'=>'required|unique:users,phone,'.$user->id,

            'role_id'=>'required',
        ]);
        DB::beginTransaction();
        try{
            if($request->hasFile('image')){
                $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();

                Storage::disk('public')->putFileAs('user', $request->image,$imageName);
                $imageName = "user/".$imageName;
            }

            $user->name=$request->name;
            $user->email=$request->email;
            $user->address=$request->address;
            $user->phone=$request->phone;
            $user->role_id=$request->role_id;
            $user->image = $imageName ?? $user->image;
            $user->save();
            DB::commit();
            return response()->json([
                'message'=>'Successfully Updated'
            ]);

        }catch(\Exception $e){
            DB::rollback();
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function updatepassword(Request $request,$id){
        $request->validate([
            'password'=>'required|min:8',
            'confirm_password'=>'required|same:password',
        ]);

        $user = User::find($id);
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json([
            'message'=>"Password Changed Successfully"
        ]);
    }

    public function destroy($id){
        $loan = Loan::where('user_id',$id)->count();
        $saving = Saving::where('user_id',$id)->count();
        if($loan > 0 || $saving > 0){
            return response()->json([
                'icon'=>"error",
                'message'=>"User Had already assigned Loan or Saving , So cannot delete"
            ]);
        }
        $user=User::where('id',$id)->delete();
        return response()->json([
            'message'=>"Deleted Successfully"
        ]);
    }

    public function userlist(){

        $users=User::get();
        $array = [];
        foreach($users as $k=>$user){
            $array[$k]['value']=$user->id;
            $array[$k]['label']=$user->role_id == 1 ? $user->name."(Admin)" : $user->name;
        }
        return $array;

    }

    public function userAssignedLoanlist(Request $request){
        $loans = Loan::with('customer')->where('user_id',$request->user_id)->get();
        $array = [];
        foreach($loans as $k=>$loan){
            $array[$k]['value']=$loan->id;
            $array[$k]['label']=$loan->customer->name.'('.$loan->loan_purpose.')';
        }
        return $array;

    }

    public function transferLoan(Request $request){
        $loan = Loan::where('id',$request->loan_id)->first();
        $loan->user_id = $request->transfer_to;
        $loan->save();
        return response()->json([
            'message'=>'Transfer Successfully'
        ]);
    }

    public function allusers(Request $request)
    {
        $sortFields = ['name', 'address', 'email','citizen_ship_no','company_name','phone'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField      = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder      = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput    = $request->input('search');
        $query          = $this->user->orderBy($sortField, $sortOrder);
        $perPage        = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query       = $query->where('name', 'like', $searchQuery)->orWhere('email', 'like', $searchQuery)->orWhere(
                'address',
                'like',
                $searchQuery
            );
        }
        $users = $query->paginate((int)$perPage);
    return UserResource::collection($users);
    }

    public function userLoan(Request $request,$id){
        $sortFields = [ 'installation_type', 'loan_amount'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField      = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder      = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput    = $request->input('search');
        $query          = Loan::with('customer')->where('user_id',$id)->orderBy($sortField, $sortOrder);
        $perPage        = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";

            $query       = $query->whereHas('customer',function($q) use ($searchQuery){
                                $q->where('name','Like',$searchQuery);
                            })->orWhere('installation_type', 'like', $searchQuery)->orWhere('loan_amount', 'like', $searchQuery);
        }
        $users = $query->paginate((int)$perPage);
    return UserLoanResource::collection($users);
    }
}
