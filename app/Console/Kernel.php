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
        //刪掉api:取得請假圖表儲存的檔案
        $command = "rm -rf ".base_path('storage/app/chart/')."*";
        $schedule->exec($command)->daily();

        //自動打上下班卡
        $schedule->call(function () {
            //六日不補上下班
            if (Carbon::now()->isWeekday()) {
                $this->autoCheck();
            }
        })->dailyAt('23:59');
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

    private function autoCheck()
    {
        //$fakeTime = Carbon::create(2018,6,1,8);
        //Carbon::setTestNow($fakeTime);
        $staffs = Staff::with(['get_check_list'])
            ->whereHas('profile', function ($query) {
                $query->where('identity', Profile::ID_FULL_TIME);
            })->get();
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
        $noon_end       = strtotime(Carbon::today()->addHours(13));
        foreach ($staffs as $staff) {
            $checkin_random_time =  date("Y-m-d H:i:s",$checkin_start + mt_rand(0,$checkin_diff));
            $checkout_random_time =  date("Y-m-d H:i:s",$checkout_start + mt_rand(0,$checkout_diff));
            //11:50~12:10
            $noon_start_random_time =  date("Y-m-d H:i:s",strtotime('-10 minutes', $noon_start) + mt_rand(0,1200));
            //12:50~13:10
            $noon_end_random_time =  date("Y-m-d H:i:s",strtotime('-10 minutes', $noon_end) + mt_rand(0,1200));
            $leaves = $staff->get_check_list
                ->where('type', '!=', 0)
                ->where('checkin_at', '>=', Carbon::today()->addHours(9))
                ->where('checkin_at', '<=', Carbon::today()->addHours(19))
                ->where('checkout_at', '<=', Carbon::today()->addHours(19));
            if ($leaves->isEmpty()) {
                    Check::create([
                            'staff_id'    => $staff->id,
                            'checkin_at'  => $checkin_random_time,
                            'checkout_at' => $checkout_random_time,
                            'type'        => 0,
                    ]);
            }
            else {
                if ($leaves->count() == 1) {
                    $leave = $leaves->first();
                    $leave_from = strtotime($leave->checkin_at);
                    $leave_to   = strtotime($leave->checkout_at);
                    //正負10分鐘
                    $leave_to_random_time = date("Y-m-d H:i:s", strtotime('-10 minutes', $leave_to) + mt_rand(0,1200));
                    $leave_from_random_time = date("Y-m-d H:i:s", strtotime('-10 minutes', $leave_from) + mt_rand(0,1200));
                    //不用打開頭
                    if ($checkin_start <= $leave_from && $leave_from <= $checkin_end) {
                        //不用打結尾
                        if ($checkout_start <= $leave_to && $leave_to <= $checkout_end) {
                        }
                        //要打結尾
                        else {
                            //請假請到中午
                            if ($noon_start <= $leave_to && $leave_to <= $noon_end) {
                                Check::create([
                                    'staff_id'    => $staff->id,
                                    'checkin_at'  => $noon_end_random_time,
                                    'checkout_at' => $checkout_random_time,
                                    'type'        => 0,
                                ]);
                            }
                            else {
                                Check::create([
                                    'staff_id'    => $staff->id,
                                    'checkin_at'  => $leave_to_random_time,
                                    'checkout_at' => $checkout_random_time,
                                    'type'        => 0,
                                ]);
                            }
                        }
                    }
                    //要打開頭
                    else {
                        //不用打結尾
                        if ($checkout_start <= $leave_to && $leave_to <= $checkout_end) {
                            //從中午的區間開始請
                            if ($noon_start <= $leave_from && $leave_from <= $noon_end) {
                                Check::create([
                                    'staff_id'    => $staff->id,
                                    'checkin_at'  => $checkin_random_time,
                                    'checkout_at' => $noon_start_random_time,
                                    'type'        => 0,
                                ]);
                            }
                            else {
                                Check::create([
                                    'staff_id'    => $staff->id,
                                    'checkin_at'  => $checkin_random_time,
                                    'checkout_at' => $leave_from_random_time,
                                    'type'        => 0,
                                ]);
                            }
                        }
                        //要打結尾
                        else {
                            //從中午的區間開始請
                            if ($noon_start <= $leave_from && $leave_from <= $noon_end) {
                                //請到中午的區間
                                if ($noon_start <= $leave_to && $leave_to <= $noon_end) {
                                    //視為沒請
                                    Check::create([
                                        'staff_id'    => $staff->id,
                                        'checkin_at'  => $checkin_random_time,
                                        'checkout_at' => $checkout_random_time,
                                        'type'        => 0,
                                    ]);
                                    $leave->delete();
                                }
                                else {
                                    Check::create([
                                        'staff_id'    => $staff->id,
                                        'checkin_at'  => $checkin_random_time,
                                        'checkout_at' => $noon_start_random_time,
                                        'type'        => 0,
                                    ]);
                                    Check::create([
                                        'staff_id'    => $staff->id,
                                        'checkin_at'  => $leave_to_random_time,
                                        'checkout_at' => $checkout_random_time,
                                        'type'        => 0,
                                    ]);
                                }
                            }
                            //從非中午開始請
                            else {
                                //請到中午的區間
                                if ($noon_start <= $leave_to && $leave_to <= $noon_end) {
                                    Check::create([
                                        'staff_id'    => $staff->id,
                                        'checkin_at'  => $checkin_random_time,
                                        'checkout_at' => $leave_from_random_time,
                                        'type'        => 0,
                                    ]);
                                    Check::create([
                                        'staff_id'    => $staff->id,
                                        'checkin_at'  => $noon_end_random_time,
                                        'checkout_at' => $checkout_random_time,
                                        'type'        => 0,
                                    ]);
                                }
                                else {
                                    Check::create([
                                        'staff_id'    => $staff->id,
                                        'checkin_at'  => $checkin_random_time,
                                        'checkout_at' => $leave_from_random_time,
                                        'type'        => 0,
                                    ]);
                                    Check::create([
                                        'staff_id'    => $staff->id,
                                        'checkin_at'  => $leave_to_random_time,
                                        'checkout_at' => $checkout_random_time,
                                        'type'        => 0,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function autoNotify()
    {
        $checks = Check::where('checkin_at', ">=", Carbon::today())
            ->where('checkin_at', "<=", Carbon::tomorrow())
            ->get();

        foreach ($checks as $check) {
            StrideHelper::create_notify($check);
        }
    }
}
