<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\IntrestController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoanTypeController;
use App\Http\Controllers\RecommenderController;
use App\Http\Controllers\InstallationController;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register',[AuthController::class,'register']); // Singup URL
Route::post('/login',[AuthController::class,'login']); // lOGIN url
Route::get('allusers',[UserController::class,'allusers']);

// Route::get('loan',[LoanController::class,'index']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('runsedular', function () {

        Artisan::call('installationLoanToday:cron');

    });
    Route::get('authenticate',[AuthController::class,'isAuthenticate']);
    Route::get('dashboard-datas',[HomeController::class,'dashboardDatas']);
    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/convertdate',[HomeController::class,'convertdate']);
    Route::resource('loan-type',LoanTypeController::class);
    Route::get('user/list',[UserController::class,'userlist']);
    Route::get('user-related/loan',[UserController::class,'userAssignedLoanlist']);
    Route::post('user/transferLoan',[UserController::class,'transferLoan']);
    Route::get('users-loan/{id}',[UserController::class,'userLoan']);
    Route::resource('user',UserController::class);
    Route::get('allusers',[UserController::class,'allusers']);


    Route::post('updatepassword/{id}',[UserController::class,'updatepassword']);
    Route::resource('loan',LoanController::class);
    Route::get('loans/todayfollowup',[LoanController::class,'todayfollowup']);
    Route::get('loans/notContacted',[LoanController::class,'notContacted']);
    Route::post('loan/makeconnected',[LoanController::class,'makeconnected']);
    Route::get('installationContactForToday',[InstallationController::class,'installationContactForToday']);
    Route::post('loan/makereminder',[LoanController::class,'makeReminder']);
    Route::get('loans/reminder',[LoanController::class,'reminder']);
    Route::get('loandetail/{id}',[LoanController::class,'loandetail']);
    Route::post('delete-loan-image',[LoanController::class,'deleteLoanImage']);
    //intrest
    Route::post('loanintrest',[IntrestController::class,'loanintrest']);
    Route::get('view-loan-alldetails/{id}',[LoanController::class,'loanAllDetails']);
    Route::post('saveloandetail',[LoanController::class,'saveloandetail']);
    Route::resource('customers',CustomerController::class);
    Route::get('customer/list',[CustomerController::class,'customerlist']);
    Route::get('allcustomers',[CustomerController::class,'allcustomers']);
    Route::get('customerloandetail/{id}',[CustomerController::class,'customerLoanDetail']);

    //Saving
    Route::get('all-saving',[SavingController::class,'allSavings']);
    Route::resource('saving',SavingController::class);

    Route::post('create-saving-detail',[SavingController::class,'createSavingDetail']);
    Route::post('create-withdraw-detail',[SavingController::class,'createWithdrawDetail']);

    Route::resource('recommender',RecommenderController::class);
    Route::get('allrecommenders',[RecommenderController::class,'index']);
    Route::get('recommender-list',[RecommenderController::class,'list']);

});
