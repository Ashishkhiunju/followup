<?php
namespace App\Jobs;

use App\Mail\SendEmailTest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    public function __construct($details, $datas)
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

        $mail = Mail::to($this->details)->send($email);

    }
}
