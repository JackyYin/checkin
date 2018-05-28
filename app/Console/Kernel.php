<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Models\Check;
use App\Models\Staff;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->call(function () {
//            $checks = Check::two_days_ago()->not_checked_out()->get();
//            foreach ($checks as $check) {
//                $check->update([
//                    'checkout_at' => Carbon::now()->subDays(1)->subHours(5)->subMinutes(30),
//                ]);
//            }
//        })->dailyAt('0:01');

        //刪掉api:get-check-list儲存的檔案
        $command = "rm -rf ".base_path('storage/app/chart/')."*";
        $schedule->exec($command)->everyMinute();

        //自動打上下班卡
        $schedule->call(function () {
            $this->autoCheck();
        })->dailyAt('23:59');
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

    private function autoCheck()
    {
        $staffs = Staff::all();
        //random checkin time
        $checkin_start = strtotime(Carbon::today()->addHours(9));
        $checkin_end   = strtotime(Carbon::today()->addHours(9)->addMinutes(30));
        $checkin_diff  = $checkin_end - $checkin_start;
        //random checkout time
        $checkout_start = strtotime(Carbon::today()->addHours(18)->addMinutes(30));
        $checkout_end   = strtotime(Carbon::today()->addHours(19));
        $checkout_diff  = $checkout_end - $checkout_start;
        //noon
        $noon_start     = strtotime(Carbon::today()->addHours(12));
        $noon_end       = strtotime(Carbon::today()->addHours(13)->addminutes(30));
        foreach ($staffs as $staff) {
            $checkin_time =  date("Y-m-d H:i:s",$checkin_start + mt_rand(0,$checkin_diff));
            $checkout_time =  date("Y-m-d H:i:s",$checkout_start + mt_rand(0,$checkout_diff));
            $leaves = $staff->get_check_list
                ->where('type', '!=', 0)
                ->where('checkin_at', '>=', Carbon::today()->addHours(9))
                ->where('checkin_at', '<=', Carbon::today()->addHours(19))
                ->where('checkout_at', '<=', Carbon::today()->addHours(19));
            if ($leaves->isEmpty()) {
                    Check::create([
                            'staff_id'    => $staff->id,
                            'checkin_at'  => $checkin_time,
                            'checkout_at' => $checkout_time,
                            'type'        => 0,
                    ]);

            }
            else {
                if ($leaves->count() == 1) {
                    $leave = $leaves->first();
                    $leave_from =     $leave->checkin_at;
                    $leave_to   =     $leave->checkout_at;
                    $int_leave_from = strtotime($leave_from);
                    $int_leave_to   = strtotime($leave_to);

                    //不用請開頭
                    if ($checkin_start <= $int_leave_from && $int_leave_from <= $checkin_end) {
                        //不用請結尾
                        if ($checkout_start <= $int_leave_to && $int_leave_to <= $checkout_end) {
                                return;
                        }
                        else {
                            Check::create([
                                'staff_id'    => $staff->id,
                                'checkin_at'  => $leave_to,
                                'checkout_at' => $checkout_time,
                                'type'        => 0,
                            ]);
                        }
                    }
                    //要請開頭
                    else {
                        //不用請結尾
                        if ($checkout_start <= $int_leave_to && $int_leave_to <= $checkout_end) {
                            Check::create([
                                'staff_id'    => $staff->id,
                                'checkin_at'  => $checkin_time,
                                'checkout_at' => $leave_from,
                                'type'        => 0,
                            ]);
                        }
                        else {
                            Check::create([
                                'staff_id'    => $staff->id,
                                'checkin_at'  => $checkin_time,
                                'checkout_at' => $leave_from,
                                'type'        => 0,
                            ]);
                            Check::create([
                                'staff_id'    => $staff->id,
                                'checkin_at'  => $leave_to,
                                'checkout_at' => $checkout_time,
                                'type'        => 0,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
