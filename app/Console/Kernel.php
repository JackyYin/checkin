<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Helpers\StrideHelper;
use GuzzleHttp\Client;
use Carbon\Carbon;
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
        \Laravel\Passport\Console\ClientCommand::class,
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
        $schedule->command('stride:notify --daily')->dailyat('09:00');

        //星座運勢每日推播
        $schedule->command('fortune:notify --daily')->dailyAt('08:30');

        //星座運勢每日推播
        $schedule->command('prediction:notify --daily')->dailyAt('08:45');
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
}
