<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Line;
use App\Models\Check;
use App\Models\LeaveReason;

class LeaveController extends Controller
{
    private $CHECK_TYPE = [
        1 => "事假",
        2 => "特休",
        3 => "公假",
        4 => '病假',
    ];
    /**
     *
     * @SWG\Post(path="/api/get-leave-type",
     *   tags={"project"},
     *   summary="取得假別列表",
     *   operationId="get-leave-type",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *       name="line_id",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
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
            Check::TYPE_OFFICIAL_LEAVE  => '公假',
            Check::TYPE_SICK_LEAVE      => '病假',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * @SWG\Post(path="/api/request-leave",
     *   tags={"project"},
     *   summary="申請請假",
     *   operationId="request-leave",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="line_id",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="leave_type",
     *       in="formData",
     *       type="number",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="leave_reason",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="start_time",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="end_time",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function requestLeave(Request $request)
    {
        $messages = [
            'line_id.required'       => '請填入line_id',
            'line_id.exists'         => '不存在的line_id',
            'leave_type.required'    => '請填入假別',
            'start_time.required'    => '請填入起始時間',
            'start_time.date_format' => '請輸入格式： YYYY-MM-DD hh:mm',
            'start_time.before'      => '起始時間必須在結束時間之前',
            'end_time.required'      => '請填入結束時間',
            'end_time.date_format'   => '請輸入格式： YYYY-MM-DD hh:mm',
            'end_time.after'         => '結束時間必須在起始時間之後',
            'leave_reason.required'  => '請填入請假原因',
        ];
        $validator = Validator::make($request->all(), [
            'line_id'      => 'required|exists:staff_line,line_id',
            'leave_type'   => 'required|numeric',
            'start_time'   => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'     => 'required|date_format:Y-m-d H:i|after:start_time',
            'leave_reason' => 'required',
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
            'checkin_at'  => $request->input('start_time').":00",
            'checkout_at' => $request->input('end_time').":00",
            'type'        => $request->input('leave_type'),
        ]);

        $reason = LeaveReason::create([
            'check_id' => $check->id,
            'reason'   => $request->input('leave_reason'),
        ]);

        return $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                ."假別： ".$this->CHECK_TYPE[$check->type].",\n"
                ."原因： ".$reason->reason.",\n"
                ."編號： ".$check->id;
    }
}
