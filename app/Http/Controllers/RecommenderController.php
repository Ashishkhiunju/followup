<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recommender;
use App\Http\Resources\RecommenderResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RecommenderController extends Controller
{
    public const PER_PAGE           = 10;
    public const DEFAULT_SORT_FIELD = 'created_at';
    public const DEFAULT_SORT_ORDER = 'asc';

    public function index(Request $request){
        $sortFields = ['name', 'address', 'email','citizen_ship_no','company_name','phone'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField      = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder      = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput    = $request->input('search');
        $query          = Recommender::orderBy("created_at", 'desc');
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
        return RecommenderResource::collection($customers);
    }


    public function store(Request $request){

        $request->validate([
            'name'=>'required',
            'email'=>'nullable|unique:recommenders',
            'address'=>'required',
            'phone'=>'required|unique:recommenders',

        ]);
        $imageName ="";
        if($request->hasFile('image')){
            $imageName = Str::random().strtotime(date('Y-m-d H:i:s')).'.'.$request->image->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('recommender', $request->image,$imageName);
            $imageName = "recommender/".$imageName;
        }
        Recommender::create($request->post()+['image'=>$imageName]);
        return response()->json([
            'message'=>'Recommender Created Successfully!!'
        ]);

    }

    public function show($id){
        return Recommender::find($id);
    }

    public function update(Request $request,$id){
        $recommender = Recommender::Find($id);
        $request->validate([
            'name'=>'required',
            'email'=>'nullable|unique:recommenders,email,'.$id,
            'address'=>'required',
            'phone'=>'required|unique:recommenders,phone,'.$id,

        ]);


        $imageName =$recommender->image;
        if($request->hasFile('image')){
            $imageName = Str::random().strtotime(date('Y-m-d H:i:s')).'.'.$request->image->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('recommender', $request->image,$imageName);
            $imageName = "recommender/".$imageName;
        }

        $recommender->update([
            'name'=>$request->name,
            'email'=>$request->email,
            'address'=>$request->address,
            'company_name'=>$request->company_name,
            'citizen_ship_no'=>$request->citizen_ship_no,
            'company_address'=>$request->company_address,
            'image'=>$imageName,
        ]);

        return response()->json([
            'message'=>'Recommender Update Successfully!!'
        ]);
    }

    public function destroy(Request $request,$id){
        Recommender::where('id',$id)->delete();
        return response()->json([
            'message'=>"Deleted SuccessFully",
        ]);
    }

    public function list(){
        $recommenders = Recommender::select('id','name','phone')->get();
        $array = [];
        foreach($recommenders as $k=>$recommender){
            $array[$k]['value']=$recommender->id;
            $array[$k]['label']=$recommender->name.'/'.$recommender->phone;
        }
        return $array;
    }


}
