<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        {check* : The IDs of the Check}
        {--scope=}
        {--action=}
        {--panel} : Display the Content Panel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notification For All Leaves Today To Stride';

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
     * @return mixed
     */
    public function handle()
    {
        $checks = Check::whereIn('id', $this->argument('check'))->get();

        if (!$checks) {
            $this->error('Check Not Found!');
            return;
        }

        $scope = $this->choice('What is The Scope?', ['Room', 'Personal'], 0);
        $action = $this->choice('What is The Action?', ['Create', 'Edit'], 0);

        if ($this->option('panel')) {
            StrideHelper::sendPanel();
        }

        if ($scope == 'Room') {
            foreach ($checks as $check) {
                StrideHelper::roomNotification($check, $action);
            }
        }
        elseif ($scope == 'Personal') {
            foreach ($checks as $check) {
                StrideHelper::personalNotification($check, $action);
            }
        }
    }
}
