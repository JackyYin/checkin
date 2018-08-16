<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Check;
use App\Models\Profile;

class AutoCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Check In and Check Out';

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
        //$fakeTime = Carbon::create(2018,8,14);
        //Carbon::setTestNow($fakeTime);
        if (Carbon::now()->isWeekend()) {
            return;
        }
        $staffs = Staff::with(['checks'])
            ->whereHas('profile', function ($query) {
                $query->where('identity', Profile::ID_FULL_TIME);
            })->get();
        $bar = $this->output->createProgressBar(count($staffs));
        //random checkin time
        $date = Carbon::today()->toDateString();
        $checkin_start = Carbon::createFromFormat('Y-m-d H:i:s', $date." ".config('check.checkin.start'));
        $checkin_end   = Carbon::createFromFormat('Y-m-d H:i:s', $date." ".config('check.checkin.end'));
        //random checkout time
        $checkout_start = Carbon::createFromFormat('Y-m-d H:i:s', $date." ".config('check.checkout.start'));
        $checkout_end   = Carbon::createFromFormat('Y-m-d H:i:s', $date." ".config('check.checkout.end'));
        //noon
        $noon_start = Carbon::createFromFormat('Y-m-d H:i:s', $date." ".config('check.noon.start'));
        $noon_end   = Carbon::createFromFormat('Y-m-d H:i:s', $date." ".config('check.noon.end'));

        foreach ($staffs as $staff) {
            $checkin_random_time =  $checkin_start->copy()->addMinutes(rand(0, $checkin_start->diffInMinutes($checkin_end)))->addSeconds(rand(0,60));
            $checkout_random_time =  $checkout_start->copy()->addMinutes(rand(0, $checkout_start->diffInMinutes($checkout_end)))->addSeconds(rand(0,60));
            //11:50~12:10
            $noon_start_random_time = $noon_start->copy()->subMinutes(10)->addMinutes(rand(0,20))->addSeconds(rand(0,60));
            //12:50~13:10
            $noon_end_random_time = $noon_end->copy()->subMinutes(10)->addMinutes(rand(0,20))->addSeconds(rand(0,60));
            $leaves = $staff->checks()->isLeave()
                ->where('checkin_at', '>=', $checkin_start)
                ->where('checkin_at', '<=', $checkout_end)
                ->where('checkout_at', '<=', $checkout_end)->get();
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
                    $leave_from = $leave->checkin_at;
                    $leave_to   = $leave->checkout_at;
                    //正負10分鐘
                    $leave_from_random_time = $leave_from->copy()->subMinutes(10)->addMinutes(rand(0,20));
                    $leave_to_random_time = $leave_to->copy()->subMinutes(10)->addMinutes(rand(0,20));
                    //不用打開頭
                    if ($leave_from->between($checkin_start, $checkin_end)) {
                        //不用打結尾
                        if ($leave_to->between($checkout_start, $checkout_end)) {
                        }
                        //要打結尾
                        else {
                            //請假請到中午
                            if ($leave_to->between($noon_start, $noon_end)) {
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
                        if ($leave_to->between($checkout_start, $checkout_end)) {
                            //從中午的區間開始請
                            if ($leave_from->between($noon_start, $noon_end)) {
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
                            if ($leave_from->between($noon_start, $noon_end)) {
                                //請到中午的區間
                                if ($leave_to->between($noon_start, $noon_end)) {
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
                                if ($leave_to->between($noon_start, $noon_end)) {
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
            $bar->advance();
        }
        $bar->finish();
    }
}
