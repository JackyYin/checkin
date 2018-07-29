<?php

namespace App\Http\Controllers\Api\V2\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use Auth;
use App\Transformer\CheckTransformer;
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
     * @SWG\Get(path="/api/v2/leave/record/me",
     *   tags={"Leave", "V2", "Record"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得個人請假紀錄",
     *   operationId="get-my-leave-records",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function me(Request $request)
    {
        $staff = Auth::guard('api')->user();
        $checks = $staff->get_check_list()->isLeave()->get();
        return $this->response(200, fractal()->collection($checks, new CheckTransformer));
    }
}
