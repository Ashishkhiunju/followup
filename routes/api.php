<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[AuthController::class,'register']); // Singup URL
Route::post('/login',[AuthController::class,'login']); // lOGIN url
Route::get('authenticate',[AuthController::class,'isAuthenticate']);
// Route::get('loan',[LoanController::class,'index']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);

    Route::resource('loan-type',LoanTypeController::class);
    Route::resource('user',UserController::class);
    Route::post('updatepassword/{id}',[UserController::class,'updatepassword']);
    Route::resource('loan',LoanController::class);
    Route::get('loans/todayfollowup',[LoanController::class,'todayfollowup']);
    Route::get('loans/notContacted',[LoanController::class,'notContacted']);
    Route::get('loandetail/{id}',[LoanController::class,'loandetail']);
    Route::post('saveloandetail',[LoanController::class,'saveloandetail']);
    Route::resource('customers',CustomerController::class);
    Route::get('customer/list',[CustomerController::class,'customerlist']);
    Route::get('customerloandetail/{id}',[CustomerController::class,'customerLoanDetail']);
});
