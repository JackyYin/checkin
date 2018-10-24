<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PredictionNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prediction:notify 
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
            $staffs = Staff::with('checks')->whereHas('profile', function ($query) {
                    $query->whereIn('identity', [Profile::ID_FULL_TIME, Profile::ID_PART_TIME])
                        ->whereNotNull('birth');
            })->whereHas('modules', function ($query) {
                $query->where('name', 'prediction');
            })->get();
        } else {
            $staffs = Staff::with('checks')->whereIn('email', $this->argument('emails'))
                ->whereHas('profile', function ($query) {
                    $query->whereIn('identity', [Profile::ID_FULL_TIME, Profile::ID_PART_TIME])
                        ->whereNotNull('birth');
                })->whereHas('modules', function ($query) {
                    $query->where('name', 'prediction');
                })->get();
        }

        if ($staffs->isEmpty()) {
            $this->error('Staff Not Existed');
            return;
        }

        $bar = $this->output->createProgressBar(count($staffs));

        foreach ($staffs as $staff) {
            \App\Jobs\Line\PredictionNotification::dispatch($staff);
            $bar->advance();
        }

        $bar->finish();
    }
}
