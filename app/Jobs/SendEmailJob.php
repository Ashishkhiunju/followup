<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SendEmailTest;
use Mail;
class SendEmailJob implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
protected $details;
protected $datas;
/**
* Create a new job instance.
*
* @return void
*/
public function __construct($details,$datas)
{
$this->details = $details;
$this->datas = $datas;
}
/**
* Execute the job.
*
* @return void
*/
public function handle()
{
$email = new SendEmailTest($this->datas);
// $emails = ['chandanee48@gmail.com','sunitabagale95@gmail.com','ashishkhinju123456789@gmail.com'];
// foreach($emails as $email){
//     // dd($email);
//     Mail::to($email)->send($email);
// }
// Mail::to($this->details['email'])->send($email);
Mail::to($this->details)->send($email);
}
}
