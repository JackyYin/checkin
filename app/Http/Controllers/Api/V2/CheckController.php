<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Helpers\LeaveHelper;
use App\Models\Check;
use App\Transformers\CheckTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    private $checkTransformer;

    public function __construct()
    {
        $this->checkTransformer = new CheckTransformer($simple = true);
    }

    /**
     * @SWG\Tag(name="Check", description="上下班")
     */
    /**
     *
     * @SWG\Post(path="/api/v2/check/in/location",
     *   tags={"Check", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="用位置打卡上班",
     *   operationId="checkin",
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="latitude",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="longitude",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function locationCheckIn(\App\Http\Requests\Api\V2\Check\LocationCheckinRequest $request)
    {
        $check = Check::create([
            'staff_id'    => $request->user()->id,
            'checkin_at'  => Carbon::now(),
            'type'        => Check::TYPE_NORMAL,
        ]);

        return response()->json([
            'reply_message' => fractal($check, $this->checkTransformer, new \League\Fractal\Serializer\ArraySerializer()),
        ], 200);
    }
    /**
     *
     * @SWG\Post(path="/api/v2/check/out/location",
     *   tags={"Check", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="用位置打卡下班",
     *   operationId="checkin",
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="latitude",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="longitude",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */

    public function locationCheckOut(\App\Http\Requests\Api\V2\Check\LocationCheckoutRequest $request)
    {
        // 不正常的情況下,有可能存在兩筆以上有上班沒下班的卡
        $check = $request->user()->checks()->readyForCheckoutToday()->latest('id')->first();

        if(!LeaveHelper::CheckRepeat($request->user(), $check->checkin_at, Carbon::now())) {
            return $this->response(400, [
                'repeat' => [
                    '已存在重複的請或打卡時間'
                ]
            ]);
        }

        $check->update([
            'checkout_at' => Carbon::now()
        ]);

        return response()->json([
            'reply_message' => fractal($check, $this->checkTransformer, new \League\Fractal\Serializer\ArraySerializer()),
        ], 200);
    }
}
