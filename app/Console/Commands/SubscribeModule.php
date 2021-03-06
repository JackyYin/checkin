<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\Profile;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SubscribeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:subscribe
        {module} : module to join';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add staffs to module';

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
        $staffs = Staff::whereHas('profile', function ($query) {
                $query->whereIn('identity', [Profile::ID_FULL_TIME, Profile::ID_PART_TIME]);
            })->get();

        if ($staffs->isEmpty()) {
            $this->error('Staff Not Existed');
            return;
        }

        $bar = $this->output->createProgressBar(count($staffs));

        $module = Module::where('name', $this->argument('module'))->first();

        if (!$module) {
            $this->error('Module Not Existed');
            return;
        }

        foreach ($staffs as $staff) {

            $staff->modules()->attach($module->id);

            $bar->advance();
        }

        $bar->finish();
    }
}
