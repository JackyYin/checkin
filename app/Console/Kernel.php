<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Helpers\StrideHelper;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Artisan;
use App\Models\Check;
use App\Models\Staff;
use App\Models\Profile;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\AutoCheck::class,
        \App\Console\Commands\StrideNotify::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //刪掉api:取得請假圖表儲存的檔案
        $command = "rm -rf ".base_path('storage/app/chart/')."*";
        $schedule->exec($command)->daily();

        //自動打上下班卡
        $schedule->command('auto:check')->dailyAt('23:59');

        //自動發通知到stride
        $schedule->call(function () {
            $this->autoNotify();
        })->dailyAt('09:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    private function autoNotify()
    {
        $generalChecks = Check::where('checkout_at', '>=', Carbon::today())
            ->where('checkin_at', '<=', Carbon::tomorrow())
            ->isLeave()
            ->get()->pluck('id');

        Artisan::call('stride:notify', [
            'check'    => $generalChecks,
            '--scope'  => 'Room',
            '--action' => 'Create',
            '--panel'  =>  true,
        ]);

    }
}
