<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\StrideHelper;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Line;
use App\Models\Check;
use App\Models\LeaveReason;

class LeaveController extends Controller
{
    /**
     * @SWG\Tag(name="Leave", description="請假")
     */
    /**
     *
     * @SWG\Post(path="/api/get-leave-type",
     *   tags={"Leave"},
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
            return response()->json([
                'reply_message' => implode(",", $array),
                'subscribers'   => [],
            ], 400);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return response()->json([
                'reply_message' => "帳號未驗證",
                'subscribers'   => [],
            ], 200);
        }

        return response()->json([
            array(
                'id'   => Check::TYPE_ANNUAL_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_ANNUAL_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_PERSONAL_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_PERSONAL_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_OFFICIAL_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_OFFICIAL_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_SICK_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_SICK_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_MOURNING_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_MOURNING_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_MARRIAGE_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_MARRIAGE_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_MATERNITY_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_MATERNITY_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_PATERNITY_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_PATERNITY_LEAVE],
            ),
        ], 200);
    }

    /**
     *
     * @SWG\Post(path="/api/request-leave",
     *   tags={"Leave"},
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
            return response()->json([
                'reply_message' => implode(",", $array),
                'subscribers'   => [],
            ], 400);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return response()->json([
                'reply_message' => "帳號未驗證",
                'subscribers'   => [],
            ], 200);
        }

        if(!$this->CheckRepeat($staff->id, $request->input('start_time'), $request->input('end_time'))) {
            return response()->json([
                'reply_message' => "已存在重複的請假時間",
                'subscribers'   => [],
            ], 200);
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

        StrideHelper::roomNotification($check, "Create");
        StrideHelper::personalNotification($check, "Create");

        $reply_message = $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                ."姓名： ".$staff->name.",\n"
                ."假別： ".$this->CHECK_TYPE[$check->type].",\n"
                ."原因： ".$reason->reason.",\n"
                ."編號： ".$check->id;

        $subscribers = Staff::where('subscribed', STAFF::SUBSCRIBED)
            ->where('active', STAFF::ACTIVE)
            ->where('id', '!=', $staff->id)->get()->map(function ($item) {
           return  $item->line;
        })->pluck('line_id');

        return response()->json([
            'reply_message' => $reply_message,
            'subscribers'   => $subscribers,
        ]);
    }

    private function CheckRepeat($staff_id, $from, $to, $without = null)
    {
        $date = explode(" ", $from)[0];
        $date_start = $date." 00:00:00";
        $date_end   = $date." 23:59:59";

        $past_checks = Check::where('staff_id', $staff_id)
            ->isLeave()
            ->where('checkin_at', '>=', $date_start)
            ->where('checkin_at', '<=', $date_end)
            ->where('checkout_at', '<=', $date_end)
            ->where('id', '!=', $without)
            ->get();

        foreach ($past_checks as $check) {
            $check_start = strtotime($check->checkin_at);
            $check_end   = strtotime($check->checkout_at);
            if (strtotime($from) <= $check_start) {
                if (strtotime($to) <= $check_start) {
                    continue;
                }
                elseif ($check_start < strtotime($to) && strtotime($to) < $check_end) {
                    return false;
                }
                elseif ($check_end <= strtotime($to)) {
                    $check->delete();
                }
            }
            elseif ($check_start < strtotime($from) && strtotime($from) < $check_end) {
                return false;
            }
            elseif ($check_end <= strtotime($from)) {
                continue;
            }
        }

        return true;
    }
    /**
     *
     * @SWG\Post(path="/api/request-late",
     *   tags={"Leave"},
     *   summary="申請晚到",
     *   operationId="request-late",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="line_id",
     *       in="formData",
     *       type="string",
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
    public function requestLate(Request $request)
    {
        $messages = [
            'line_id.required'       => '請填入line_id',
            'line_id.exists'         => '不存在的line_id',
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
            'start_time'   => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'     => 'required|date_format:Y-m-d H:i|after:start_time',
            'leave_reason' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",", $array),
                'subscribers'   => [],
            ], 400);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return response()->json([
                'reply_message' => "帳號未驗證",
                'subscribers'   => [],
            ], 200);
        }

        if(!$this->CheckRepeat($staff->id, $request->input('start_time'), $request->input('end_time'))) {
            return response()->json([
                'reply_message' => "已存在重複的請假時間",
                'subscribers'   => [],
            ], 200);
        }

        $check = Check::create([
            'staff_id'    => $staff->id,
            'checkin_at'  => $request->input('start_time').":00",
            'checkout_at' => $request->input('end_time').":00",
            'type'        => Check::TYPE_LATE,
        ]);

        $reason = LeaveReason::create([
            'check_id' => $check->id,
            'reason'   => $request->input('leave_reason'),
        ]);

        $reply_message = $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                ."姓名： ".$staff->name.",\n"
                ."假別： ".$this->CHECK_TYPE[$check->type].",\n"
                ."原因： ".$reason->reason.",\n"
                ."編號： ".$check->id;

        $subscribers = Staff::where('subscribed', STAFF::SUBSCRIBED)
            ->where('active', STAFF::ACTIVE)
            ->where('id', '!=', $staff->id)->get()->map(function ($item) {
           return  $item->line;
        })->pluck('line_id');

        return response()->json([
            'reply_message' => $reply_message,
            'subscribers'   => $subscribers,
        ]);
    }
    /**
     *
     * @SWG\Post(path="/api/request-online",
     *   tags={"Leave"},
     *   summary="申請online",
     *   operationId="request-online",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="line_id",
     *       in="formData",
     *       type="string",
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
    public function requestOnline(Request $request)
    {
        $messages = [
            'line_id.required'       => '請填入line_id',
            'line_id.exists'         => '不存在的line_id',
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
            'start_time'   => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'     => 'required|date_format:Y-m-d H:i|after:start_time',
            'leave_reason' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",", $array),
                'subscribers'   => [],
            ], 400);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return response()->json([
                'reply_message' => "帳號未驗證",
                'subscribers'   => [],
            ], 200);
        }

        if(!$this->CheckRepeat($staff->id, $request->input('start_time'), $request->input('end_time'))) {
            return response()->json([
                'reply_message' => "已存在重複的請假時間",
                'subscribers'   => [],
            ], 200);
        }

        $check = Check::create([
            'staff_id'    => $staff->id,
            'checkin_at'  => $request->input('start_time').":00",
            'checkout_at' => $request->input('end_time').":00",
            'type'        => Check::TYPE_ONLINE,
        ]);

        $reason = LeaveReason::create([
            'check_id' => $check->id,
            'reason'   => $request->input('leave_reason'),
        ]);

        StrideHelper::roomNotification($check, "Create");
        StrideHelper::personalNotification($check, "Create");

        $reply_message = $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                ."姓名： ".$staff->name.",\n"
                ."假別： ".$this->CHECK_TYPE[$check->type].",\n"
                ."原因： ".$reason->reason.",\n"
                ."編號： ".$check->id;

        $subscribers = Staff::where('subscribed', STAFF::SUBSCRIBED)
            ->where('active', STAFF::ACTIVE)
            ->where('id', '!=', $staff->id)->get()->map(function ($item) {
           return  $item->line;
        })->pluck('line_id');

        return response()->json([
            'reply_message' => $reply_message,
            'subscribers'   => $subscribers,
        ]);

    }
}
