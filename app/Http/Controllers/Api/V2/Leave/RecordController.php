<?php

namespace App\Http\Controllers\Api\V2\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Validator;
use Carbon\Carbon;
use Auth;
use App\Transformer\CheckTransformer;
use App\Helpers\LeaveHelper;
use App\Models\Staff;
use App\Models\Check;
use App\Models\LeaveReason;

class RecordController extends Controller
{
    /**
     * @SWG\Tag(name="Record", description="紀錄")
     */
    /**
     *
     * @SWG\Get(path="/api/v2/leave/record/me",
     *   tags={"Leave", "V2", "Record"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得個人請假紀錄",
     *   operationId="get-my-leave-records",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="start_date",
     *       in="query",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="end_date",
     *       in="query",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="types[]",
     *       in="query",
     *       type="array",
     *       collectionFormat="multi",
     *       @SWG\Items(
     *          type="integer",
     *       ),
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function me(\App\Http\Requests\Api\V2\Leave\Record\GetMeRequest $request)
    {
        if ($request->filled('start_date') && $request->filled('end_date')
            && strtotime($request->end_date." 00:00:00") <= strtotime($request->start_date." 00:00:00")) {
            return response()->json([
                'reply_message' => "起始時間需在結束時間之前",
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $staff = Auth::guard('api')->user();

        $checks = $staff->get_check_list()->with(['leave_reason', 'staff'])->where(function ($query) use ($request) {
                if ($request->filled('start_date')) {
                    $query->where('checkin_at', ">=", $request->start_date);
                }

                if ($request->filled('end_date')) {
                    $query->where('checkout_at', "<=", $request->end_date);
                }

                if ($request->filled('types')) {
                    $query->whereIn('type', $request->types);
                }
        } )->isLeave()->get();

        return $this->response(200, fractal()->collection($checks, new CheckTransformer));
    }
}
