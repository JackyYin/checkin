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
     * @SWG\Get(path="/api/checkin",
     *   tags={"project"},
     *   summary="打卡上班",
     *   operationId="checkin",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *     in="query",
     *     name="line_id",
     *     type="string",
     *     required=true,
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
     * @SWG\Get(path="/api/checkout",
     *   tags={"project"},
     *   summary="打卡下班",
     *   operationId="checkout",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *     in="query",
     *     name="line_id",
     *     type="string",
     *     required=true,
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
                $response = $check->checkout_at." 打卡下班成功！";
                break;

            default:
                $response = "母湯喔,請聯絡系統管理員";
                break;
        }

        return $response;
    }
}
