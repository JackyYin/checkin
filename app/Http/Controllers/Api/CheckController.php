<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
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
     *       name="check_type",
     *       in="formData",
     *       type="number",
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
            'check_type.required'    => '請填入類別',
            'start_date.required'    => '請填入起始日期',
            'start_date.date_format' => '請填入格式： YYYY-MM-DD',
            'start_date.before'      => '起始日期必須在結束時間之前',
            'end_date.required'      => '請填入結束日期',
            'end_date.date_format'   => '請填入格式： YYYY-MM-DD',
            'end_date.after'         => '結束時間必須在起始時間之後',
        ];
        $validator = Validator::make($request->all(), [
            'line_id'    => 'required|exists:staff_line,line_id',
            'check_type' => 'required|numeric',
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

        $lists = $staff->get_check_list
            ->filter(function($item) use ($request) {
                if ($request->input('check_type') != 99) {
                    return $item->type == $request->input('check_type');
                }
                return $item;
            })
            ->where('checkin_at', '>=', $request->input('start_date')." 00:00:00")
            ->where('checkin_at', '<=', $request->input('end_date')." 23:59:59")->values()->toArray();

        return $lists;
    }
}
