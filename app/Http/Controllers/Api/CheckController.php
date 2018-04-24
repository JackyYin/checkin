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
    /**
     *
     * @SWG\Get(path="/api/checkin",
     *   tags={"project"},
     *   summary="打卡上班",
     *   operationId="register",
     *   produces={"application/json"},
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
                Check::create([
                    'staff_id'   => $staff->id,
                    'checkin_at' => Carbon::now(),
                    'type'       => Check::TYPE_NORMAL, 
                ]);
                $response = "打卡上班成功！";
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
}
