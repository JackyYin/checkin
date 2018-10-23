<?php

namespace App\Http\Controllers\Api\V2\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Staff\Module\OnRequest;
use App\Http\Requests\Api\V2\Staff\Module\OffRequest;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     *
     * @SWG\Get(path="/api/v2/staff/module",
     *   tags={"Staff", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="個人模組列表",
     *   operationId="module-index",
     *   produces={"application/json", "text/plain"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function index(Request $request, \App\Transformers\StaffModuleTransformer $transformer)
    {
        return $this->response(200, fractal(Module::all(), $transformer));
    }
    /**
     *
     * @SWG\Post(path="/api/v2/staff/module/on",
     *   tags={"Staff", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="啟用模組",
     *   operationId="subscribe-module",
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="module_name",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function on(OnRequest $request)
    {
        $module = Module::where('name', $request->module_name)->first();

        $request->user()->modules()->attach($module);

        return $this->response(200, $request->module_name."模組已啟用");
    }
    /**
     *
     * @SWG\Post(path="/api/v2/staff/module/off",
     *   tags={"Staff", "V2"},
     *   security={
     *      {"api-user": {}}
     *   },
     *   summary="停用模組",
     *   operationId="stop-subscribe-module",
     *   produces={"application/json", "text/plain"},
     *   @SWG\Parameter(
     *       name="module_name",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function off(OffRequest $request)
    {
        $module = Module::where('name', $request->module_name)->first();

        $request->user()->modules()->detach($module);

        return $this->response(200, $request->module_name."模組已停用");
    }
}
