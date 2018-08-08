<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Helpers\StrideHelper;
use App\Models\Check;

class StrideNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stride:notify 
        {check*? : The IDs of the Check}
        {--scope=}
        {--action=}
        {--panel} : Display the Content Panel
        {--daily} : Push daily Notification without other arguments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notification For All Leaves Today To Stride';

    protected $StrideHelper;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->StrideHelper = new StrideHelper();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('daily')) {
            $generalChecks = Check::where('checkout_at', '>=', Carbon::today())
                ->where('checkin_at', '<=', Carbon::tomorrow())
                ->isLeave()
                ->get();

            $this->StrideHelper->sendPanel();

            foreach ($generalChecks as $check) {
                $this->StrideHelper->roomNotification($check, "Create");
            }
            return;
        }

        $checks = Check::whereIn('id', $this->argument('check'))->get();

        if (!$checks) {
            $this->error('Check Not Found!');
            return;
        }

        $scope = $this->choice('What is The Scope?', ['Room', 'Personal'], 0);
        $action = $this->choice('What is The Action?', ['Create', 'Edit'], 0);

        if ($this->option('panel')) {
            $this->StrideHelper->sendPanel();
        }

        if ($scope == 'Room') {
            foreach ($checks as $check) {
                $this->StrideHelper->roomNotification($check, $action);
            }
        }
        elseif ($scope == 'Personal') {
            foreach ($checks as $check) {
                $this->StrideHelper->personalNotification($check, $action);
            }
        }
    }
}
