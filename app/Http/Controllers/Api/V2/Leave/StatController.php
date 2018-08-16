<?php

namespace App\Http\Controllers\Api\V2\Leave;

use App\Http\Controllers\Controller;
use App\Helpers\LeaveHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use App\Models\Staff;
use App\Models\Line;
use App\Models\Check;
use App\Models\LeaveReason;

class StatController extends Controller
{
    /**
     *
     * @SWG\Get(path="/api/v2/leave/stat/annual",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="特休狀況統計",
     *   operationId="get-annual-stat",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getAnnualStat(Request $request)
    {
        $staff = Auth::guard('api')->user();

        $on_board_date = Carbon::createFromFormat('Y-m-d', $staff->profile->on_board_date);
        $on_board_months = $on_board_date->diffInMonths(Carbon::now());

        if ( $on_board_months < 6) {
            $annual_hours = 0;
            $used_hours = $this->getUsedHours($staff, 0);
        }
        elseif ( 6 <= $on_board_months && $on_board_months < 12) {
            $annual_hours = 24;
            $used_hours = $this->getUsedHours($staff, 6);
        }
        elseif ( 12 <= $on_board_months && $on_board_months < 24) {
            $annual_hours = 56;
            $used_hours = $this->getUsedHours($staff, 12);
        }
        elseif ( 24 <= $on_board_months && $on_board_months < 36) {
            $annual_hours = 80;
            $used_hours = $this->getUsedHours($staff, 24);
        }
        elseif ( 36 <= $on_board_months && $on_board_months < 60) {
            $annual_hours = 112;
            $which_year = floor(($on_board_months - 36) / 12);
            $used_hours = $this->getUsedHours($staff, 36 + $which_year * 12);
        }
        elseif ( 60 <= $on_board_months && $on_board_months < 120) {
            $annual_hours = 120;
            $which_year = floor(($on_board_months - 60) / 12);
            $used_hours = $this->getUsedHours($staff, 60 + $which_year * 12);
        }
        elseif ( 120 <= $on_board_months) {
            $annual_hours = 128 + (floor($on_board_months / 12) - 10) * 8;
            if ($annual_hours >= 240) {
                $annual_hours = 240;
            }
            $which_year = floor(($on_board_months - 120) / 12);
            $used_hours = $this->getUsedHours($staff, 120 + $which_year * 12);
        }

        $remained_hours = $annual_hours - $used_hours > 0 ? $annual_hours - $used_hours : 0;

        $body = "可用特休時數: ".$annual_hours."\n"
               ."已用特休時數: ".$used_hours."\n"
               ."剩下特休時數: ".$remained_hours;

        return response()->json([
            'reply_message' => $body,
        ], 200);
    }

    private function getUsedHours($staff, $added_months)
    {
        $on_board_date = Carbon::createFromFormat('Y-m-d', $staff->profile->on_board_date);

        $checks = $staff->checks
            ->where('type', Check::TYPE_ANNUAL_LEAVE)
            ->where('checkin_at', ">=", $on_board_date->addMonths($added_months));

        return LeaveHelper::countHours($checks);
    }
    /**
     *
     * @SWG\Get(path="/api/v2/leave/stat/me",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得個人請假統計時數",
     *   operationId="get-my-leave-stat",
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
    public function me(\App\Http\Requests\Api\V2\Leave\Stat\MeRequest $request)
    {
        $staff = Auth::guard('api')->user();

        $EnumTypes = array_except(Check::getEnum('engType'), Check::TYPE_NORMAL);

        if ($request->filled('types')) {
            $EnumTypes = array_only($EnumTypes, $request->types);
        }

        $noon_start = explode(":", config('check.noon.start'))[0];
        $noon_end = explode(":", config('check.noon.end'))[0];
        $select_string = "";

        foreach( $EnumTypes as $key => $value) {
            $select_string .= "SUM(IF(type = ".$key.",  IF(checkin_at <= DATE_ADD(DATE(checkin_at), INTERVAL ".$noon_start."  HOUR) && checkout_at >= DATE_ADD(DATE(checkin_at), INTERVAL ".$noon_end." HOUR), TIMESTAMPDIFF(MINUTE,checkin_at,checkout_at) - 60, TIMESTAMPDIFF(MINUTE,checkin_at,checkout_at)), 0) / 60) as ".$value.",";
        }
        $select_string = substr($select_string, 0, -1);

        $data = $staff->checks()->isLeave()->where(function ($query) use ($request) {
                if ($request->filled('start_date')) {
                    $query->where('checkin_at', ">=", $request->start_date);
                }

                if ($request->filled('end_date')) {
                    $query->where('checkout_at', "<=", $request->end_date);
                }
        } )->selectRaw($select_string)->first();

        $array = json_decode(json_encode($data), true);

        foreach ($array as $key => $value) {
            if (is_null($value)) {
                $array{$key} = 0;
            }
        }

        return response()->json([
            'reply_message' => $array
        ]);
    }
    /**
     *
     * @SWG\Get(path="/api/v2/leave/stat",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得請假統計時數總覽",
     *   operationId="get-all-leave-stat",
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
     *       name="staff_ids[]",
     *       in="query",
     *       type="array",
     *       collectionFormat="multi",
     *       @SWG\Items(
     *          type="integer",
     *       ),
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
    public function index(\App\Http\Requests\Api\V2\Leave\Stat\IndexRequest $request)
    {
        $EnumTypes = array_except(Check::getEnum('engType'), Check::TYPE_NORMAL);

        if ($request->filled('types')) {
            $EnumTypes = array_only($EnumTypes, $request->types);
        }

        $checks = Check::with('staff')->whereHas('staff', function ($query) use ($request) {
            if ($request->filled('staff_ids')) {
                $query->whereIn('id', $request->staff_ids);
            }
        })->isLeave()->where(function ($query) use ($request) {
                if ($request->filled('start_date')) {
                    $query->where('checkin_at', ">=", $request->start_date);
                }

                if ($request->filled('end_date')) {
                    $query->where('checkout_at', "<=", $request->end_date);
                }

                if ($request->filled('types')) {
                    $query->whereIn('type', $request->types);
                }
        })->whereNotNull('checkin_at')->whereNotNull('checkout_at')->get();

        return $this->response(200, $this->statisticHelper($checks, $EnumTypes, $request->filled('staff_ids') ? $request->staff_ids : []));
    }

    private function statisticHelper($checks, $EnumTypes, $staff_ids = [])
    {
        $results = $checks->groupBy('staff_id')->map(function ($collection) use ($EnumTypes) {
            $result = (object) [
                'name' => '',
                'stat' => (object)[],
            ];
            $result->name = $collection->first()->staff->name;
            foreach ($EnumTypes as $type) {
                $result->stat->$type = 0;
            }

            $collection->each(function ($check) use ($EnumTypes, $result) {
                $result->stat->{$EnumTypes[$check->type]} += ($check->minutes / 60);
            });

            return $result;
        })->values();

        foreach(array_diff($staff_ids, $checks->pluck('staff_id')->toArray()) as $id) {
            $new = (object) [
                'name' => Staff::find($id)->name,
                'stat' => (object)[],
            ];

            foreach ($EnumTypes as $type) {
                $new->stat->$type = 0;
            }
            $results[] = $new;
        }
        return $results;
    }
}
