<?php

namespace App\Http\Controllers\Api\V2\Leave;

use App\Http\Controllers\Controller;
use App\Helpers\StrideHelper;
use App\Helpers\LeaveHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use Carbon\Carbon;
use SVGGraph;
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

        $checks = $staff->get_check_list
            ->where('type', Check::TYPE_ANNUAL_LEAVE)
            ->where('checkin_at', ">=", $on_board_date->addMonths($added_months));

        return LeaveHelper::countHours($checks);
    }
    /**
     *
     * @SWG\Get(path="/api/v2/leave/stat",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得請假統計時數",
     *   operationId="get-leave-stat",
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
    public function index(Request $request)
    {
        $messages = [
            'start_date.date_format' => '請填入格式： YYYY-MM-DD',
            'end_date.date_format'   => '請填入格式： YYYY-MM-DD',
        ];
        $validator = Validator::make($request->all(), [
            'start_date' => 'date_format:Y-m-d',
            'end_date'   => 'date_format:Y-m-d',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",", $array),
            ], 400);
        }

        if ($request->filled('start_date') && $request->filled('end_date')
            && strtotime($request->end_date." 00:00:00") <= strtotime($request->start_date." 00:00:00")) {
            return response()->json([
                'reply_message' => "起始時間需在結束時間之前",
            ], 400);
        }
        $staff = Auth::guard('api')->user();

        $select_string = "";
        $EnumTypes = array (
            Check::TYPE_PERSONAL_LEAVE  => "personal",
            Check::TYPE_ANNUAL_LEAVE    => "annual",
            Check::TYPE_OFFICIAL_LEAVE  => "official",
            Check::TYPE_SICK_LEAVE      => "sick",
            Check::TYPE_ONLINE          => "online",
            Check::TYPE_LATE            => "late",
            Check::TYPE_MOURNING_LEAVE  => "mourning",
            Check::TYPE_MATERNITY_LEAVE => "maternity",
            Check::TYPE_PATERNITY_LEAVE => "paternity",
            Check::TYPE_MARRIAGE_LEAVE  => "marriage",
        );

        if ($request->filled('types')) {
            $EnumTypes = array_only($EnumTypes, $request->types);
        }

        $noon_start = explode(":", Check::NOON_START)[0];
        $noon_end = explode(":", Check::NOON_END)[0];

        foreach( $EnumTypes as $key => $value) {
            $select_string .= "SUM(IF(type = ".$key.",  IF(checkin_at <= DATE_ADD(DATE(checkin_at), INTERVAL ".$noon_start."  HOUR) && checkout_at >= DATE_ADD(DATE(checkin_at), INTERVAL ".$noon_end." HOUR), TIMESTAMPDIFF(MINUTE,checkin_at,checkout_at) - 60, TIMESTAMPDIFF(MINUTE,checkin_at,checkout_at)), 0) / 60) as ".$value.",";
        }
        $select_string = substr($select_string, 0, -1);

        $row = Check::where('staff_id', $staff->id)
            ->where(function ($query) use ($request) {
                if ($request->filled('start_date')) {
                    $from = Carbon::createFromFormat('Y-m-d', $request->start_date);
                    $query->where('checkin_at', ">=", $from);
                }

                if ($request->filled('end_date')) {
                    $to = Carbon::createFromFormat('Y-m-d', $request->end_date);
                    $query->where('checkin_at', "<=", $to->addDay());
                }
            })
            ->selectRaw($select_string)->first();

        return response()->json([
            'reply_message' => $row
        ]);
        //$salt = $this->saveSVGGraph($EnumTypes, $row);
        //return response()->file(storage_path("app/chart/".$salt.".png"));
    }

    private function saveSVGGraph($EnumTypes, $row)
    {
        //make svg graph
        $settings = array(
            'label_x' => 'types',
            'label_y' => 'hours',
        );
        $graph = new SVGGraph(1000, 600, $settings);
        $colours = array(array('red', 'yellow'));
        $values = array();
        foreach($EnumTypes as $type) {
            $values[$type] = $row->{$type};
        }
        $graph->colours = $colours;
        $graph->Values($values);
        $svg = $graph->FETCH('BarGraph', FALSE, FALSE);

        //save graph
        $salt = str_random(30);
        file_put_contents(storage_path('app/chart/').$salt.".svg", $svg);
        $command = "inkscape ".storage_path('app/chart/').$salt.".svg -e ".storage_path('app/chart/'.$salt.".png");
        exec($command);

        return $salt;
    }
}
