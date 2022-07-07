<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
        if(! $user || ! Hash::check($request->password,$user->password)){
            return response()->json([ 
                'status'=>401,
                'message'=>'Invalid Credentials',
            ]);
        }else{
            $token = $user->createToken($user->email.'_Token')->plainTextToken;
            return response()->json([
                'status'=>200,
                'username'=>$user->name,
                'token'=>$token,
                'message'=>"Logged In Successfully",
            ]);
        }
    }

    public function logout(){
        
        auth()->user()->tokens()->delete();
        return response()->json([
            'status'=>200,
            'message'=>"Loggged Out Successfully",
        ]);
    }
}
