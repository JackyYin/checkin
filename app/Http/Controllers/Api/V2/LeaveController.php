<?php

namespace App\Http\Controllers\Api\V2;

use App\Events\LeaveCreated;
use App\Events\LeaveUpdated;
use App\Helpers\LeaveHelper;
use App\Http\Controllers\Controller;
use App\Models\Check;
use App\Models\LeaveReason;
use App\Models\Staff;
use App\Transformers\CheckTransformer;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;

class LeaveController extends Controller
{

    private $checkTransformer;

    public function __construct()
    {
        $this->checkTransformer = new CheckTransformer();
    }

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
        $data = [];

        foreach (array_except(Check::getEnum('type'), Check::TYPE_NORMAL) as $key => $value) {
            $data[] = [
                'id' => $key,
                'name' => $value
            ];
        };

        return $this->response(200, $data);
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
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="type",
     *       in="formData",
     *       type="number",
     *   ),
     *   @SWG\Parameter(
     *       name="reason",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkin_at",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkout_at",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function store(\App\Http\Requests\Api\V2\Leave\StoreRequest $request)
    {
        return $this->LeaveHandler($request, $request->type);
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
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="reason",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkin_at",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkout_at",
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
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="reason",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkin_at",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkout_at",
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

    private function LeaveHandler($request, $type)
    {
        $staff = Auth::guard('api')->user();

        $check = Check::create([
            'staff_id'    => $staff->id,
            'checkin_at'  => $request->checkin_at,
            'checkout_at' => $request->checkout_at,
            'type'        => $type,
        ]);

        $reason = LeaveReason::create([
            'check_id' => $check->id,
            'reason'   => $request->reason,
        ]);

        event(new LeaveCreated($check));

        Log::info('A Leave is Created.', $this->checkTransformer->transform($check));

        if ($request->header('Accept') == 'text/plain') {
            $reply_message = $check->checkin_at." 至 ".$check->checkout_at." 請假成功,\n"
                    ."姓名： ".$staff->name."\n"
                    ."假別： ".$this->CHECK_TYPE[$type]."\n"
                    ."原因： ".$reason->reason."\n"
                    ."編號： ".$check->id;

            return response($reply_message, 200);
        }

        return response()->json([
            'reply_message' => fractal($check, $this->checkTransformer, new \League\Fractal\Serializer\ArraySerializer()),
            'subscribers'   => $this->getSubscribersExcept($staff->id),
        ], 200);
    }
    /**
     *
     * @SWG\Put(path="/api/v2/leave/{id}",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="編輯請假",
     *   operationId="update-leave",
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="id",
     *       in="path",
     *       type="number",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="type",
     *       in="formData",
     *       type="number",
     *   ),
     *   @SWG\Parameter(
     *       name="reason",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkin_at",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkout_at",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function update(\App\Http\Requests\Api\V2\Leave\UpdateRequest $request)
    {
        $staff = Auth::guard('api')->user();
        $leave = Check::where('id', $request->route('id'))->where('staff_id', $staff->id)->isLeave()->first();

        if (!$leave) {
            if ($request->header('Accept') == 'text/plain') {
                return response("沒有權限更新此假單", 403);
            }

            return $this->response(403, [
                'permission' => [
                    '沒有權限更新此假單'
                ]
            ]);
        }

        if (!LeaveHelper::CheckRepeat($staff->id, $request->checkin_at, $request->checkout_at, $request->route('id'))) {
            if ($request->header('Accept') == 'text/plain') {
                return response("已存在重複的請假時間", 400);
            }

            return $this->response(400, [
                'repeat' => [
                    '已存在重複的請假時間'
                ]
            ]);
        }

        $leave->update([
            'type'        => $request->input('type', $leave->type),
            'checkin_at'  => $request->checkin_at,
            'checkout_at' => $request->checkout_at,
        ]);

        if ($request->filled('reason')) {
            LeaveReason::updateOrCreate([
                'check_id' => $leave->id
            ], [
                'reason' => $request->reason
            ]);
        }

        event(new LeaveUpdated($leave));

        Log::info('A Leave is Updated.', $this->checkTransformer->transform($leave));

        if ($request->header('Accept') == 'text/plain') {
            $reply_message =
                "編號: ".$leave->id." 編輯成功\n"
                ."時間: ".date("Y-m-d", strtotime($leave->checkin_at))." (".$this->WEEK_DAY[date("l", strtotime($leave->checkin_at))].") ".date("H:i", strtotime($leave->checkin_at))." ~ ".date("H:i", strtotime($leave->checkout_at))."\n"
                ."姓名: ".$leave->staff->name."\n"
                ."假別: ".$this->CHECK_TYPE[$leave->type]."\n"
                ."原因: ".$leave->leave_reason->reason."\n";

            return response($reply_message, 200);
        }

        return response()->json([
            'reply_message' => fractal($leave, $this->checkTransformer, new \League\Fractal\Serializer\ArraySerializer()),
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
     * @SWG\Get(path="/api/v2/leave/{id}",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="查看請假資訊",
     *   operationId="get-specific-leave",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="id",
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

        $leave = Check::where('id', $request->route('id'))->where('staff_id', $staff->id)->first();

        if (!$leave) {
            return $this->response(403, [
                'permission' => [
                    '沒有權限查看此假單'
                ]
            ]);
        }

        return $this->response(200, fractal($leave, $this->checkTransformer, new \League\Fractal\Serializer\ArraySerializer()));
    }
    /**
     *
     * @SWG\Delete(path="/api/v2/leave/{id}",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="刪除假單",
     *   operationId="delete-specific-leave",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="id",
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

        $leave = Check::where('id', $request->route('id'))->where('staff_id', $staff->id)->first();

        if (!$leave) {
            return $this->response(403, [
                'permission' => [
                    '沒有權限刪除此假單'
                ]
            ]);
        }

        \App\Jobs\Stride\DeleteNotification::dispatch($leave);
        \App\Jobs\Discord\DeleteNotification::dispatch($leave);
        \App\Jobs\Line\DeleteNotification::dispatch($leave);

        $leave->delete();

        return $this->response(200, "刪除假單成功");
    }
}
