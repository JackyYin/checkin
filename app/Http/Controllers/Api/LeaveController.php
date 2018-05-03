<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Line;
use App\Models\Check;

class LeaveController extends Controller
{
    private $CHECK_TYPE = [
        1 => "事假",
        2 => "特休",
        3 => "公假",
    ];
    public function getLeaveType(Request $request)
    {
        $messages = [
            'line_id.required'       => '請填入line_id',
            'line_id.exists'         => '不存在的line_id',
        ];
        $validator = Validator::make($request->all(), [
            'line_id'    => 'required|exists:staff_line,line_id',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return "帳號未驗證";
        }

        return response()->json([
            Check::TYPE_PERSONAL_LEAVE  => '事假',
            Check::TYPE_ANNUAL_LEAVE    => '特休',
            Check::TYPE_OFFICIAL_LEAVE  => '公假'
        ], JSON_UNESCAPED_UNICODE);
    }

    public function requestLeave(Request $request)
    {
        $messages = [
            'line_id.required'       => '請填入line_id',
            'line_id.exists'         => '不存在的line_id',
            'leave_type.required'    => '請填入假別',
            'start_time.required'    => '請填入起始時間',
            'start_time.date_format' => '請輸入格式： YYYY-MM-DD',
            'end_time.required'      => '請填入結束時間',
            'end_time.date_format'   => '請輸入格式： YYYY-MM-DD',
        ];
        $validator = Validator::make($request->all(), [
            'line_id'    => 'required|exists:staff_line,line_id',
            'leave_type' => 'required|numeric',
            'start_time' => 'required|date_format:Y-m-d',
            'end_time'   => 'required|date_format:Y-m-d',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return "帳號未驗證";
        }

        $check = Check::create([
            'staff_id'    => $staff->id,
            'checkin_at'  => $request->input('start_time'),
            'checkout_at' => $request->input('end_time'),
            'type'        => $request->input('leave_type'),
        ]);

        return $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                ."假別： ".$this->CHECK_TYPE[$check->type].",\n"
                ."編號： ".$check->id;
    }
}
