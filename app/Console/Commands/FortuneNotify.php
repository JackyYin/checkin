<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Staff;

class FortuneNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fortune:notify 
        {emails?* : The email to notify}
        {--daily} : Push daily Notification without other arguments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notification For Todays Fortune Information';

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
        if ($this->option('daily')) {
            $staffs = Staff::all();
    
            $bar = $this->output->createProgressBar(count($staffs));

            foreach ($staffs as $staff) {
                if ($staff->profile && $staff->profile->birth) {
                    \App\Jobs\Line\FortuneNotification::dispatch($staff);
                }
                $bar->advance();
            }

            $bar->finish();
        } else {
            $staffs = Staff::whereIn('email', $this->argument('emails'))->get();

            if ($staffs->isEmpty()) {
                $this->error('Check Not Found!');
                return;
            }

            $bar = $this->output->createProgressBar(count($staffs));

            foreach ($staffs as $staff) {
                if ($staff->profile && $staff->profile->birth) {
                    \App\Jobs\Line\FortuneNotification::dispatch($staff);
                }
                $bar->advance();
            }

            $bar->finish();
            
        }
    }
}
