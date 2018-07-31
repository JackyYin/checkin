<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Helpers\StrideHelper;
use App\Helpers\LeaveHelper;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use Auth;
use App\Models\Staff;
use App\Models\Check;
use App\Models\LeaveReason;

class LeaveController extends Controller
{
    /**
     * @SWG\Tag(name="Leave", description="請假")
     */
    /**
     *
     * @SWG\Get(path="/api/v2/leave/types",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得假別列表",
     *   operationId="get-leave-types",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getLeaveType(Request $request)
    {
        return response()->json([
            array(
                'id'   => Check::TYPE_PERSONAL_LEAVE,
                'name' => $this->CHECK_TYPE[Check::TYPE_PERSONAL_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_ANNUAL_LEAVE,
                'name' => $this->CHECK_TYPE[Check::TYPE_ANNUAL_LEAVE],
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
                'id'   => Check::TYPE_ONLINE,
                'name' => $this->CHECK_TYPE[Check::TYPE_ONLINE],
            ),
            array(
                'id'   => Check::TYPE_LATE,
                'name' => $this->CHECK_TYPE[Check::TYPE_LATE],
            ),
            array(
                'id'   => Check::TYPE_MOURNING_LEAVE,
                'name' => $this->CHECK_TYPE[Check::TYPE_MOURNING_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_MATERNITY_LEAVE,
                'name' => $this->CHECK_TYPE[Check::TYPE_MATERNITY_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_PATERNITY_LEAVE,
                'name' => $this->CHECK_TYPE[Check::TYPE_PATERNITY_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_MARRIAGE_LEAVE,
                'name' => $this->CHECK_TYPE[Check::TYPE_MARRIAGE_LEAVE],
            ),
        ], 200);
    }
    /**
     *
     * @SWG\Post(path="/api/v2/leave",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="請假申請",
     *   operationId="request-leave",
     *   produces={"application/json"},
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
    public function store(Request $request)
    {
        $messages = [
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
            'leave_type'   => 'required|numeric',
            'start_time'   => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'     => 'required|date_format:Y-m-d H:i|after:start_time',
            'leave_reason' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",", $array),
            ], 400);
        }

        return $this->LeaveHandler($request, $request->input('leave_type'));
    }
    /**
     *
     * @SWG\Post(path="/api/v2/leave/late",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="申請晚到",
     *   operationId="request-late",
     *   produces={"application/json"},
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
            'start_time.required'    => '請填入起始時間',
            'start_time.date_format' => '請輸入格式： YYYY-MM-DD hh:mm',
            'start_time.before'      => '起始時間必須在結束時間之前',
            'end_time.required'      => '請填入結束時間',
            'end_time.date_format'   => '請輸入格式： YYYY-MM-DD hh:mm',
            'end_time.after'         => '結束時間必須在起始時間之後',
            'leave_reason.required'  => '請填入請假原因',
        ];
        $validator = Validator::make($request->all(), [
            'start_time'   => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'     => 'required|date_format:Y-m-d H:i|after:start_time',
            'leave_reason' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",", $array),
            ], 400);
        }

        return $this->LeaveHandler($request, Check::TYPE_LATE);
    }
    /**
     *
     * @SWG\Post(path="/api/v2/leave/online",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="申請online",
     *   operationId="request-online",
     *   produces={"application/json"},
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
            'start_time.required'    => '請填入起始時間',
            'start_time.date_format' => '請輸入格式： YYYY-MM-DD hh:mm',
            'start_time.before'      => '起始時間必須在結束時間之前',
            'end_time.required'      => '請填入結束時間',
            'end_time.date_format'   => '請輸入格式： YYYY-MM-DD hh:mm',
            'end_time.after'         => '結束時間必須在起始時間之後',
            'leave_reason.required'  => '請填入請假原因',
        ];
        $validator = Validator::make($request->all(), [
            'start_time'   => 'required|date_format:Y-m-d H:i|before:end_time',
            'end_time'     => 'required|date_format:Y-m-d H:i|after:start_time',
            'leave_reason' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",", $array),
            ], 400);
        }

        return $this->LeaveHandler($request, Check::TYPE_ONLINE);
    }

    private function LeaveHandler(Request $request, $leave_type) 
    {
        $staff = Auth::guard('api')->user();

        if(!$this->CheckRepeat($staff->id, $request->input('start_time'), $request->input('end_time'))) {
            return response()->json([
                'reply_message' => "已存在重複的請假時間",
            ], 400);
        }

        $check = Check::create([
            'staff_id'    => $staff->id,
            'checkin_at'  => $request->input('start_time').":00",
            'checkout_at' => $request->input('end_time').":00",
            'type'        => $leave_type,
        ]);

        $reason = LeaveReason::create([
            'check_id' => $check->id,
            'reason'   => $request->input('leave_reason'),
        ]);

        $reply_message = $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                ."姓名： ".$staff->name."\n"
                ."假別： ".$this->CHECK_TYPE[$leave_type]."\n"
                ."原因： ".$reason->reason."\n"
                ."編號： ".$check->id;

        StrideHelper::roomNotification($check, "Create");
        StrideHelper::personalNotification($check, "Create");

        return response()->json([
            'reply_message' => $reply_message,
            'subscribers'   => $this->getSubscribersExcept($staff->id),
        ], 200);
    }
    /**
     *
     * @SWG\Put(path="/api/v2/leave/{leaveId}",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="編輯請假",
     *   operationId="edit-leave",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="leaveId",
     *       in="path",
     *       type="number",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="leave_type",
     *       in="formData",
     *       type="number",
     *   ),
     *   @SWG\Parameter(
     *       name="leave_reason",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="start_time",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="end_time",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function update($leaveId, Request $request)
    {
        $leave = Check::find($leaveId);

        if (!$leave) {
            return response()->json([
                'reply_message' => "請輸入正確的請假id",
            ], 400);
        }

        $staff = Auth::guard('api')->user();

        if ($leave->staff_id != $staff->id) {
            return response()->json([
                'reply_message' => "沒有權限編輯此筆假單",
            ], 400);
        } 

        $messages = [
            'start_time.date_format' => '請輸入格式： YYYY-MM-DD hh:mm',
            'end_time.date_format'   => '請輸入格式： YYYY-MM-DD hh:mm',
            'required_without_all'   => '請至少填入一個要修改的參數:開始時間、結束時間、請假原因、請假類別',
        ];
        $validator = Validator::make($request->all(), [
            'leave_type'        => 'numeric',
            'start_time'        => 'date_format:Y-m-d H:i',
            'end_time'          => 'date_format:Y-m-d H:i',
            'attribute_ensurer' => 'required_without_all:leave_type,start_time,end_time,leave_reason',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",", $array),
            ], 400);
        }

        if ($request->filled('start_time') && !$request->filled('end_time') 
            && strtotime($request->start_time.":00") >= strtotime($leave->checkout_at)) {
            return response()->json([
                'reply_message' => "起始時間需在結束時間之前",
            ], 400);
        }

        if (!$request->filled('start_time') && $request->filled('end_time') 
            && strtotime($request->end_time.":00") <= strtotime($leave->checkin_at)) {
            return response()->json([
                'reply_message' => "結束時間需在起始時間之後",
            ], 400);
        }

        if ($request->filled('start_time') && $request->filled('end_time') 
            && strtotime($request->end_time.":00") <= strtotime($request->start_time.":00")) {
            return response()->json([
                'reply_message' => "起始時間需在結束時間之前",
            ], 400);
        }

        if (!$this->CheckRepeat($staff->id, $request->input('start_time', $leave->checkin_at), $request->input('end_time', $leave->checkout_at), $leaveId)) {
            return response()->json([
                'reply_message' => "已存在重複的請假時間",
            ], 400);
        }

        $checkin_at  = $request->filled('start_time') ? $request->start_time.":00" : $leave->checkin_at;
        $checkout_at = $request->filled('end_time') ? $request->end_time.":00" : $leave->checkout_at;

        $leave->update([
            'type'        => $request->input('leave_type', $leave->type),
            'checkin_at'  => $checkin_at,
            'checkout_at' => $checkout_at,
        ]);

        if ($request->filled('leave_reason')) {
            if ($leave->leave_reason) {
                $leave->leave_reason->update([
                    'reason' => $request->leave_reason,
                ]);
            }
            else {
                LeaveReason::create([
                    'check_id' => $leave->id,
                    'reason'   => $request->leave_reason,
                ]);
            }
        }

        StrideHelper::roomNotification($leave, "Edit");
        StrideHelper::personalNotification($leave, "Edit");

        $reply_message = 
            "編號: ".$leave->id." 編輯成功\n"
            ."時間: ".date("Y-m-d", strtotime($leave->checkin_at))." (".$this->WEEK_DAY[date("l", strtotime($leave->checkin_at))].") ".date("H:i", strtotime($leave->checkin_at))." ~ ".date("H:i", strtotime($leave->checkout_at))."\n"
            ."姓名: ".$leave->staff->name."\n"
            ."假別: ".$this->CHECK_TYPE[$leave->type]."\n"
            ."原因: ".$leave->leave_reason->reason."\n";

        return response()->json([
            'reply_message' => $reply_message,
            'subscribers'   => $this->getSubscribersExcept($staff->id),
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

    private function getSubscribersExcept($staff_id)
    {
        $subscribers = Staff::where('subscribed', STAFF::SUBSCRIBED)
            ->where('active', STAFF::ACTIVE)
            ->where('id', '!=', $staff_id)->get()->pluck('email');

        return $subscribers;
    }
    /**
     *
     * @SWG\Get(path="/api/v2/leave/{leaveId}",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="查看請假資訊",
     *   operationId="get-specific-leave",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="leaveId",
     *       in="path",
     *       type="integer",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function show(\App\Http\Requests\Api\V2\Leave\ShowRequest $request)
    {
        $staff = Auth::guard('api')->user();

        $leave = Check::where('id', $request->route('leaveId'))->where('staff_id', $staff->id)->first();

        if (!$leave) {
            return $this->response(400, [
                'auth' => [
                    '沒有權限刪除此假單'
                ]
            ]);
        }

        return response()->json([
            'reply_message' => [
                'id'           => $leave->id,
                'leave_type'   => $leave->type,
                'leave_reason' => $leave->leave_reason ? $leave->leave_reason->reason : '',
                'start_time'   => $leave->checkin_at,
                'end_time'     => $leave->checkout_at,
            ]
        ]);
    }
    /**
     *
     * @SWG\Delete(path="/api/v2/leave/{leaveId}",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="刪除假單",
     *   operationId="delete-specific-leave",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="leaveId",
     *       in="path",
     *       type="integer",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function destroy(\App\Http\Requests\Api\V2\Leave\DestroyRequest $request)
    {
        $staff = Auth::guard('api')->user();

        $leave = Check::where('id', $request->route('leaveId'))->where('staff_id', $staff->id)->first();

        if (!$leave) {
            return $this->response(400, [
                'auth' => [
                    '沒有權限刪除此假單'
                ]
            ]);
        }

        $leave->delete();

        return response()->json([
            'reply_message' => "假單刪除成功",
        ]);
    }
}
