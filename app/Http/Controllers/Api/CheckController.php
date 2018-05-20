<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use SVGGraph;
use App\Models\Staff;
use App\Models\Line;
use App\Models\Check;


class CheckController extends Controller
{
    public function __construct()
    {
    }
    /**
     *
     * @SWG\Post(path="/api/checkin",
     *   tags={"project"},
     *   summary="打卡上班",
     *   operationId="checkin",
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
    public function checkIn(Request $request)
    {
        $messages = [
            'line_id.required' => '請填入line_id',
            'line_id.exists'     => '不存在的line_id'
        ];
        $validator = Validator::make($request->all(), [
            'line_id'  => 'required|exists:staff_line,line_id'
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return "帳號未驗證";
        }

        return $this->checkInProcess($staff);
    }

    private function checkInProcess(Staff $staff)
    {
        switch ($staff->count_check_diff_today()) {

            case 0:
                $check = Check::create([
                    'staff_id'   => $staff->id,
                    'checkin_at' => Carbon::now(),
                    'type'       => Check::TYPE_NORMAL, 
                ]);
                $response = $check->checkin_at." 打卡上班成功！";
                break; 

            case 1:
                $response = "母湯喔,請先確認已打卡下班";
                break;

            default:
                $response = "母湯喔,請聯絡系統管理員";
                break;
        }

        return $response;
    }

    /**
     *
     * @SWG\Post(path="/api/checkout",
     *   tags={"project"},
     *   summary="打卡下班",
     *   operationId="checkout",
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
    public function checkOut(Request $request)
    {
        $messages = [
            'line_id.required' => '請填入line_id',
            'line_id.exists'     => '不存在的line_id'
        ];
        $validator = Validator::make($request->all(), [
            'line_id'  => 'required|exists:staff_line,line_id'
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return "帳號未驗證";
        }

        return $this->checkOutProcess($staff);
    }

    private function checkOutProcess($staff)
    {
        switch ($staff->count_check_diff_today()) {

            case 0:
                $response = "母湯喔,沒上班就想下班？";
                break; 

            case 1:
                $check = $staff->get_check_list
                    ->where('checkin_at', '>=', date('Y-m-d').' 00:00:00')
                    ->where('checkout_at', null)
                    ->first();
                $check->update([
                    'checkout_at' => Carbon::now()
                ]);
                $datetime1 = date_create($check->checkin_at);
                $datetime2 = date_create($check->checkout_at);
                $interval  = date_diff($datetime1, $datetime2);
                $response = $check->checkout_at." 打卡下班成功！\n"
                    ."已上班時數： ".$interval->format('%H:%I');
                break;

            default:
                $response = "母湯喔,請聯絡系統管理員";
                break;
        }

        return $response;
    }
    /**
     *
     * @SWG\Post(path="/api/get-check-type",
     *   tags={"project"},
     *   summary="取得請假與打卡列表",
     *   operationId="get-check-list",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="line_id",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getCheckType(Request $request)
    {

        $messages = [
            'line_id.required'    => '請填入line_id',
            'line_id.exists'      => '不存在的line_id',
        ];
        $validator = Validator::make($request->all(), [
            'line_id'    => 'required|exists:staff_line,line_id',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return "帳號未驗證";
        }

        return response()->json([
            Check::TYPE_NORMAL          => '打卡',
            Check::TYPE_PERSONAL_LEAVE  => '事假',
            Check::TYPE_ANNUAL_LEAVE    => '特休',
            Check::TYPE_OFFICIAL_LEAVE  => '公假',
            99                          => '所有',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * @SWG\Post(path="/api/get-check-list",
     *   tags={"project"},
     *   summary="取得請假或打卡紀錄",
     *   operationId="get-check-list",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="line_id",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="start_date",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="end_date",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getCheckList(Request $request)
    {
        $messages = [
            'line_id.required'       => '請填入line_id',
            'line_id.exists'         => '不存在的line_id',
            'start_date.required'    => '請填入起始日期',
            'start_date.date_format' => '請填入格式： YYYY-MM-DD',
            'start_date.before'      => '起始日期必須在結束時間之前',
            'end_date.required'      => '請填入結束日期',
            'end_date.date_format'   => '請填入格式： YYYY-MM-DD',
            'end_date.after'         => '結束時間必須在起始時間之後',
        ];
        $validator = Validator::make($request->all(), [
            'line_id'    => 'required|exists:staff_line,line_id',
            'start_date' => 'required|date_format:Y-m-d|before:end_date',
            'end_date'   => 'required|date_format:Y-m-d|after:start_date',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return implode(",",$array);
        }

        $staff = Line::where('line_id', $request->input('line_id'))->first()->staff;

        if ($staff->active == Staff::NON_ACTIVE) {
            return "帳號未驗證";
        }

        //mysql query
        $from = $request->input('start_date');
        $to   = $request->input('end_date');
        $mysql =
            "SELECT
            SUM(IF(c.type = 0, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as work_time,
            SUM(IF(c.type = 1, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as personal_leave_time,
            SUM(IF(c.type = 2, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as annual_leave_time,
            SUM(IF(c.type = 3, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as official_leave_time,
            SUM(IF(c.type = 4, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as sick_leave_time
            FROM checks c left join  staffs s on s.id = c.staff_id
            WHERE checkin_at >= '".$from." 00:00:00'"
            ." AND checkout_at <= '".date('Y-m-d', strtotime('+1 day', strtotime($to)))." 00:00:00'"
            ." AND c.staff_id = ".$staff->id;
        $row = DB::select($mysql);

        $salt = $this->saveSVGGraph($row);
        return response(Storage::get('/public/'.$salt.".png"))
            ->header('Content-Type', 'image/png');
    }

    private function saveSVGGraph($row)
    {
        //make svg graph
        $settings = array(
            'label_x' => 'types',
            'label_y' => 'hours',
        );
        $graph = new SVGGraph(500, 500, $settings);
        $colours = array('yellow');
        $values = array(
            'work'     => $row[0]['work_time'],
            'personal' => $row[0]['personal_leave_time'],
            'annual'   => $row[0]['annual_leave_time'],
            'official' => $row[0]['official_leave_time'],
            'sick'     => $row[0]['sick_leave_time']);
        $graph->colours = $colours;
        $graph->Values($values);
        $svg = $graph->FETCH('BarGraph',FALSE, FALSE);
        
        //save graph
        $salt = str_random(30);
        file_put_contents(storage_path('/app/public/').$salt.".svg", $svg);
        $command = "inkscape ".storage_path('app/public/').$salt.".svg -e ".storage_path('app/public/'.$salt.".png");
        exec($command);
    
        return $salt;
    }
}
