<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\LoanInstallationDate;
use App\Models\LoanSentReminder;


use Illuminate\Console\Command;

class SendSmsCorn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendSms:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Sms';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $todaydate = date('Y-m-d');
        $dateafterWeek = date("Y-m-d", strtotime('+7 days', strtotime($todaydate)));
        $datas = LoanInstallationDate::with('loan.customer')->whereBetween('next_installation_eng_date', [$todaydate, $dateafterWeek])->get();
        $checkreminderAlreadySendForToday = LoanSentReminder::where('reminder_date', date('Y-m-d'))->where('reminder_type', 'email')->count();
        if ($checkreminderAlreadySendForToday == 0) {

            foreach ($datas as $data) {
                LoanSentReminder::create([
                    'loan_id'=>$data->loan_id,
                    'reminder_date'=>date('Y-m-d'),
                    'reminder_type'=>'email',
                ]);
                dispatch(new SendEmailJob($data->loan->customer->email, $data));
            }
        }
    }
}
