<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
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
     *   	{"bot": {}},
     *   	{"api-user": {}}
     *   },
     *   summary="取得假別列表",
     *   operationId="get-leave-types",
     *   produces={"text/plain"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getLeaveType(Request $request)
    {
        $staff =  Auth::guard('api')->user();

        if ($staff->active == Staff::NON_ACTIVE) {
            return response()->json([
                'reply_message' => "帳號未驗證",
                'subscribers'   => [],
            ], 200);
        }

        return response()->json([
            array(
                'id'   => Check::TYPE_ANNUAL_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_ANNUAL_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_PERSONAL_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_PERSONAL_LEAVE],
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
                'id'   => Check::TYPE_MOURNING_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_MOURNING_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_MARRIAGE_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_MARRIAGE_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_MATERNITY_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_MATERNITY_LEAVE],
            ),
            array(
                'id'   => Check::TYPE_PATERNITY_LEAVE, 
                'name' => $this->CHECK_TYPE[Check::TYPE_PATERNITY_LEAVE],
            ),
        ], 200);
    }
}
