<?php

namespace App\Console\Commands;

use App\Jobs\InstallationContactTodayJob;
use App\Models\LoanContact;
use App\Models\LoanInstallationDate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InstallationLoanTodayCorn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installationLoanToday:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan Installation For Today';

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
        // \Log::info("Cron is working fine!");
        // return redirect()->route('installationContactToday');
        DB::beginTransaction();
        try {
            $todaydate = date("Y-m-d");

            $loancontacts = LoanContact::whereDate('installation_date', $todaydate)->first();
            if (empty($loancontacts)) {
                $loaninstallations = LoanInstallationDate::whereDate('next_installation_eng_date', $todaydate)->get();

                foreach ($loaninstallations as $detail) {
                    dispatch(new InstallationContactTodayJob($detail));
                }
            }
            DB::commit();
            // dd('done');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
        }
    }
}
