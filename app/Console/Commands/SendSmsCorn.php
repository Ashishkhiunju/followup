<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendEmailJob;
use App\Models\LoanInstallationDate;

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
        $dateafterWeek =  date("Y-m-d", strtotime('+7 days' , strtotime($todaydate)));
        $datas = LoanInstallationDate::with('loan.customer')->whereBetween('next_installation_eng_date',[$todaydate,$dateafterWeek])->get();
        // $emails = ['chandanee48@gmail.com', 'sunitabagale95@gmail.com', 'ashishkhinju123456789@gmail.com'];
        foreach ($datas as $data) {
            dispatch(new SendEmailJob($data->loan->customer->email,$data));
        }
    }
}
