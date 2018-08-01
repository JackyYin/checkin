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
    public function store(\App\Http\Requests\Api\V2\Leave\StoreRequest $request)
    {
        return $this->LeaveHandler($request, $request->leave_type);
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
    public function requestLate(\App\Http\Requests\Api\V2\Leave\RequestLateRequest $request)
    {
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
    public function requestOnline(\App\Http\Requests\Api\V2\Leave\RequestOnlineRequest $request)
    {
        return $this->LeaveHandler($request, Check::TYPE_ONLINE);
    }

    private function LeaveHandler($request, $leave_type)
    {
        $staff = Auth::guard('api')->user();

        if(!LeaveHelper::CheckRepeat($staff->id, $request->start_time.":00", $request->end_time.":00")) {
            return $this->response(400, [
                'repeat' => [
                    '已存在重複的請假時間'
                ]
            ]);
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

        StrideHelper::roomNotification($check, "Create");
        StrideHelper::personalNotification($check, "Create");

        $reply_message = $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                ."姓名： ".$staff->name."\n"
                ."假別： ".$this->CHECK_TYPE[$leave_type]."\n"
                ."原因： ".$reason->reason."\n"
                ."編號： ".$check->id;

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
    public function update(\App\Http\Requests\Api\V2\Leave\UpdateRequest $request)
    {
        $staff = Auth::guard('api')->user();
        $leave = Check::where('id', $request->route('leaveId'))->where('staff_id', $staff->id)->isLeave()->first();

        if (!$leave) {
            return $this->response(400, [
                'permission' => [
                    '沒有權限更新此假單'
                ]
            ]);
        }

        if (!LeaveHelper::CheckRepeat($staff->id, $request->start_time.":00", $request->end_time.":00", $request->route('leaveId'))) {
            return $this->response(400, [
                'repeat' => [
                    '已存在重複的請假時間'
                ]
            ]);
        }

        $leave->update([
            'type'        => $request->input('leave_type', $leave->type),
            'checkin_at'  => $request->start_time.":00",
            'checkout_at' => $request->end_time.":00",
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

    private function getSubscribersExcept($staff_id)
    {
        return Staff::where('id', '!=', $staff_id)
            ->subscribed()
            ->active()
            ->get()->pluck('email');
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
                'permission' => [
                    '沒有權限查看此假單'
                ]
            ]);
        }

        return $this->response(200, [
            'id'           => $leave->id,
            'leave_type'   => $leave->type,
            'leave_reason' => $leave->leave_reason ? $leave->leave_reason->reason : '',
            'start_time'   => $leave->checkin_at,
            'end_time'     => $leave->checkout_at,
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
                'permission' => [
                    '沒有權限刪除此假單'
                ]
            ]);
        }

        $leave->delete();

        return $this->response(200, "刪除假單成功");
    }
}
