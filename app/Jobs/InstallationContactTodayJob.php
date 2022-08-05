<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\LoanContact;
use App\Models\LoanInstallationDate;

class InstallationContactTodayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $detail;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($detail)
    {

        $this->detail = $detail;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // $todaydate = date("Y-m-d");

        // $loancontacts = LoanContact::whereDate('installation_date',$todaydate)->first();
        // if(empty($loancontacts)){
            // $loaninstallations = LoanInstallationDate::whereDate('next_installation_eng_date',$todaydate)->get();

                LoanContact::create([
                    'loan_id'=>$this->detail->loan_id,
                    'installation_date'=>$this->detail->next_installation_eng_date,
                    'contacted'=>0,
                    'paid'=>0,
                ]);


        // }
    }
}
