<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
       
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

       $token = $user->createToken($user->email.'_Token')->plainTextToken;

        return response()->json([
            'status'=>200,
            'username'=>$user->name,
            'token'=>$token,
            'message'=>"Registered Successfully",
        ]);
    }

    public function login(Request $request){
        $user = User::where('email',$request->email)->first();
        $data = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($data)) {
            $token = auth()->user()->createToken('apiToken')->plainTextToken;
            return response()->json([
                'status'=>200,
                'username'=>$user->name,
                'role'=>$user->role_id,
                'token'=>$token,
                'message'=>"Logged In Successfully",
            ]);
        }else{
            return response()->json([ 
                'status'=>401,
                'message'=>'Invalid Credentials',
            ]);
        }
        // if(! $user || ! Hash::check($request->password,$user->password)){
        //     return response()->json([ 
        //         'status'=>401,
        //         'message'=>'Invalid Credentials',
        //     ]);
        // }else{
        //     $token = $user->createToken($user->email.'_Token')->plainTextToken;
        //     return response()->json([
        //         'status'=>200,
        //         'username'=>$user->name,
        //         'role'=>$user->role_id,
        //         'token'=>$token,
        //         'message'=>"Logged In Successfully",
        //     ]);
        // }
    }

    public function logout(){
        
        auth()->user()->tokens()->delete();
        return response()->json([
            'status'=>200,
            'message'=>"Loggged Out Successfully",
        ]);
    }

    public function isAuthenticate(){
        $auth = auth()->user();
        if($auth){
            return "true";
        }else{
            return "false";
        }
    }
}
