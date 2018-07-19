<?php

namespace App\Http\Controllers\Api\V2;

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

class LeaveController extends Controller
{
    private $CHECK_TYPE = [
        1  => "事假",
        2  => "特休",
        3  => "出差",
        4  => '病假',
        5  => 'Online',
        6  => '晚到',
        7  => '喪假',
        8  => '產假',
        9  => '陪產假',
        10 => '婚假',
    ];
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
     * @SWG\Get(path="/api/v2/leave/annual",
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

        StrideHelper::createNotification($check);
        StrideHelper::personalNotification($check);

        return response()->json([
            'reply_message' => $reply_message,
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
     *   operationId="edit-leave",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="id",
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
    public function update($leave_id, Request $request)
    {
        $leave = Check::find($leave_id);

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

        if (!$this->CheckRepeat($staff->id, $request->input('start_time', $leave->checkin_at), $request->input('end_time', $leave->checkout_at), $leave_id)) {
            return response()->json([
                'reply_message' => "已存在重複的請假時間",
            ], 400);
        }

        $leave->update([
            'type'        => $request->input('type', $leave->type),
            'checkin_at'  => $request->input('start_time', $leave->checkin_at),
            'checkout_at' => $request->input('end_time', $leave->checkout_at),
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

        StrideHelper::editNotification($leave);
        StrideHelper::personalNotification($check);

        $reply_message = 
            "編號: ".$leave->id."編輯成功\n"
            ."時間: ".$leave->checkin_at."至".$leave->checkout_at."\n"
            ."姓名: ".$staff->name."\n"
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
            ->where('type', '!=', 0)
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
     * @SWG\Get(path="/api/v2/leave",
     *   tags={"Leave", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得請假圖表",
     *   operationId="get-leave-chart",
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

        $salt = $this->saveSVGGraph($EnumTypes, $row);
        return response()->file(storage_path("app/chart/".$salt.".png"));
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
