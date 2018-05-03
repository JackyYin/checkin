<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Line;
use App\Models\Check;

class LeaveController extends Controller
{
    /**
     *
     * @SWG\Get(path="/api/get-leave-list",
     *   tags={"project"},
     *   summary="取得假別列表",
     *   operationId="get-leave-list",
     *   produces={"application/jsom"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function getLeaveType()
    {
        return json_encode([
            Check::TYPE_PERSONAL_LEAVE  => '事假',
            Check::TYPE_ANNUAL_LEAVE    => '特休',
            Check::TYPE_OFFICIAL_LEAVE  => '公假'
        ], JSON_UNESCAPED_UNICODE);

    }

}
