<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Transformers\BotTransformer;
use App\Models\Bot;

class BotController extends Controller
{
    /**
     * @SWG\Tag(name="Bot", description="機器人")
     */
    /**
     *
     * @SWG\Get(path="/api/v2/bot/me",
     *   tags={"Bot", "V2"},
     *   security={
     *      {"bot": {}}
     *   },
     *   summary="取得機器人資訊",
     *   operationId="get-personal-staff-information",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function me(Request $request)
    {
        return $this->response(200,
            fractal($request->user(), new BotTransformer, new \League\Fractal\Serializer\ArraySerializer())
        );
    }
    /**
     *
     * @SWG\Patch(path="/api/v2/bot/{id}",
     *   tags={"Bot", "V2"},
     *   security={
     *      {"bot": {}}
     *   },
     *   summary="更新機器人資訊",
     *   operationId="get-personal-staff-information",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="id",
     *       in="path",
     *       type="integer",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="auth_hook_url",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Parameter(
     *       name="notify_hook_url",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function update(\App\Http\Requests\Api\V2\Bot\UpdateRequest $request)
    {
        $bot = $request->user();

        $bot->update([
            'auth_hook_url' => $request->filled('auth_hook_url') ? $request->auth_hook_url : $bot->auth_hook_url,
            'notify_hook_url' => $request->filled('notify_hook_url') ? $request->notify_hook_url : $bot->notify_hook_url,
        ]);

        return $this->response(200,
            fractal($bot, new BotTransformer, new \League\Fractal\Serializer\ArraySerializer())
        );
    }
}
