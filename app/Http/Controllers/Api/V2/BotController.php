<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Artisan;
use DB;
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
     *   operationId="get-bot-information",
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
     * @SWG\Patch(path="/api/v2/bot/me",
     *   tags={"Bot", "V2"},
     *   security={
     *      {"bot": {}}
     *   },
     *   summary="更新機器人資訊",
     *   operationId="update-bot-information",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="name",
     *       in="formData",
     *       type="string",
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

        if ($request->filled('name')) {
            DB::update('update oauth_clients set name= ? where name= ?', [$request->name." User", $bot->name." User"]);
        }

        $bot->update([
            'name' => $request->filled('name') ? $request->name : $bot->name,
            'auth_hook_url' => $request->filled('auth_hook_url') ? $request->auth_hook_url : $bot->auth_hook_url,
            'notify_hook_url' => $request->filled('notify_hook_url') ? $request->notify_hook_url : $bot->notify_hook_url,
        ]);

        return $this->response(200,
            fractal($bot, new BotTransformer, new \League\Fractal\Serializer\ArraySerializer())
        );
    }
    /**
     *
     * @SWG\Post(path="/api/v2/bot",
     *   tags={"Bot", "V2"},
     *   summary="新增機器人",
     *   operationId="create-bot",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="name",
     *       in="formData",
     *       type="string",
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
    public function store(\App\Http\Requests\Api\V2\Bot\StoreRequest $request)
    {
        $bot = Bot::create($request->only(['name', 'auth_hook_url', 'notify_hook_url']));

        Artisan::call('passport:client', [
            '--personal'  => true,
            '--name'      => $bot->name." User",
        ]);

        return $this->response(200,
            fractal($bot, new BotTransformer, new \League\Fractal\Serializer\ArraySerializer())
        );
    }
}
