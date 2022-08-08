<?php


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\LoanContact;
use App\Models\LoanInstallationDate;
use App\Models\LoanSentReminder;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

// Route::get('/', function () {
//     return view('app');
// });
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();


Route::get('email-test', function () {
    // LoanSentReminder::create([
    //     'loan_id'=>1,
    //     'reminder_date'=>date('Y-m-d'),
    //     'reminder_type'=>'email',
    // ]);

    $todaydate = date('Y-m-d');
    $dateafterWeek =  date("Y-m-d", strtotime('+7 days' , strtotime($todaydate)));
    $data = LoanInstallationDate::with('loan.customer')->whereBetween('next_installation_eng_date',[$todaydate,$dateafterWeek])->get();
    dd($data);
    // $details['email'] = 'ashishkhinju123456789@gmail.com';
    // $emails = ['chandanee48@gmail.com', 'sunitabagale95@gmail.com', 'ashishkhinju123456789@gmail.com'];
    // foreach ($emails as $email) {
    //     dispatch(new App\Jobs\SendEmailJob($email));
    // }
    // dispatch(new App\Jobs\SendEmailJob($details));
    // dd('done');
});

Route::get('installation-contact-today', function () {

    Artisan::call('installationLoanToday:cron');
    // DB::beginTransaction();
    // try {
    //     $todaydate = date("Y-m-d");

    //     $loancontacts = LoanContact::whereDate('installation_date', $todaydate)->first();
    //     if (empty($loancontacts)) {
    //         $loaninstallations = LoanInstallationDate::whereDate('next_installation_eng_date', $todaydate)->get();
    //         foreach ($loaninstallations as $detail) {
    //             dispatch(new App\Jobs\InstallationContactTodayJob($detail));
    //         }
    //     }
    //     DB::commit();
    //     dd('done');
    // } catch (\Exception $e) {
    //     DB::rollback();
    //     dd($e->getMessage());
    // }

})->name('installationContactToday');


Route::get('runsedular', function () {


    Artisan::call('schedule:work');
    Artisan::call('queue:work');

});
