<?php

namespace App\Http\Controllers\Api\V2\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Validator;
use Carbon\Carbon;
use Auth;
use App\Transformers\CheckTransformer;
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
     * @SWG\Get(path="/api/v2/leave/record",
     *   tags={"Leave", "V2", "Record"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得所有請假紀錄",
     *   operationId="get-leave-records",
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
     *   @SWG\Parameter(
     *       name="staff_ids[]",
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
    public function index(\App\Http\Requests\Api\V2\Leave\Record\IndexRequest $request)
    {
        $leaves = Check::with(['leave_reason', 'staff'])
            ->whereHas('staff', function ($query) use ($request) {
                if ($request->filled('staff_ids')) {
                    $query->whereIn('id', $request->staff_ids);
                }
            })
            ->where(function ($query) use ($request) {
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

        return $this->response(200, fractal()->collection($leaves, new CheckTransformer));
    }
    /**
     *
     * @SWG\Get(path="/api/v2/leave/record/search",
     *   tags={"Leave", "V2", "Record"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="搜尋請假紀錄",
     *   operationId="search-leave-records",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="checkin_at",
     *       in="query",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="checkout_at",
     *       in="query",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="keyword",
     *       in="query",
     *       type="string",
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function search(\App\Http\Requests\Api\V2\Leave\Record\SearchRequest $request)
    {
        $leaves = Check::with(['leave_reason', 'staff.profile'])
            ->whereHas('staff', function ($query) use ($request) {
                if ($request->filled('keyword')) {
                    $query->where('name', 'like', "%{$request->keyword}%")
                        ->orWhere('email', 'like', "%{$request->keyword}%")
                        ->orWhereHas('profile', function ($query) use ($request) {
                            $query->where('gender', 'like', "%{$request->keyword}%")
                                ->orWhere('group', 'like', "%{$request->keyword}%");
                        });
                }
            })
            ->where(function ($query) use ($request) {
                if ($request->filled('checkin_at')) {
                    $query->where('checkin_at', ">=", $request->checkin_at);
                }

                if ($request->filled('checkout_at')) {
                    $query->where('checkout_at', "<=", $request->checkout_at);
                }
        })->isLeave()->get();

        return $this->response(200, fractal()->collection($leaves, new CheckTransformer(true)));
    }
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
        $staff = Auth::guard('api')->user();

        $leaves = $staff->checks()->with(['leave_reason', 'staff'])->where(function ($query) use ($request) {
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

        return $this->response(200, fractal()->collection($leaves, new CheckTransformer));
    }
}
