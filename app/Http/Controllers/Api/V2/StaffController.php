<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Transformers\StaffTransformer;
use App\Models\Staff;

class StaffController extends Controller
{
    /**
     * @SWG\Tag(name="Staff", description="員工")
     */
    /**
     *
     * @SWG\Get(path="/api/v2/staff/me",
     *   tags={"Staff", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="取得個人資訊",
     *   operationId="get-personal-staff-information",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function me(Request $request)
    {
        return $this->response(200,
            fractal($request->user(), new StaffTransformer, new \League\Fractal\Serializer\ArraySerializer())->includeProfile()
        );
    }
}
