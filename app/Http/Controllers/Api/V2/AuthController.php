<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Ramsey\Uuid\Uuid;
use Validator;
use Mail;
use DB;
use Auth;
use GuzzleHttp\Client;
use App\Models\Staff;
use App\Models\Line;
use App\Models\Bot;

class AuthController extends Controller
{
    /**
     * @SWG\Tag(name="Auth", description="使用者認證")
     */
    /**
     *
     * @SWG\Post(path="/api/v2/bot/auth",
     *   tags={"Auth", "V2"},
     *   security={
     *      {"bot": {}},
     *   },
     *   summary="註冊手續",
     *   operationId="auth",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="email",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */

    public function auth(Request $request)
    {
        $messages = [
            'email.required'   => '請填入email',
            'email'            => '請填入有效的email',
            'email.exists'     => '不存在的email,請先登錄員工個人資料',
        ];
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:staffs,email',
        ], $messages);

        if ($validator->fails()) {
            $array = $validator->errors()->all();
            return response()->json([
                'reply_message' => implode(",",$array),
            ], 400);
        }

        $new_staff = Staff::where('email', $request->input('email'))->first();
        $bot = Auth::guard('bot')->user();
        //驗證url
        $registration_token = Uuid::uuid4();
        $new_staff->update([
            'registration_token' => Hash::make($registration_token),
        ]);

        $confirmation_url = route('api.bot.auth.active', ['bot_name' => $bot->name, 'registration_token' => $registration_token]);
        $this->sendRegistrationEmail($new_staff, $confirmation_url);

        return response()->json([
            'reply_message' => "請至信箱確認驗證信件.",
        ], 200);
    }

    private function sendRegistrationEmail(Staff $staff, $confirmation_url)
    {
        Mail::send('emails.registration',['staff' => $staff, 'url' => $confirmation_url], function ($message) use($staff) {
            $message->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'))
            ->to($staff->email, $staff->name)
            ->subject("您好,請點擊連結以啟用帳號");
        } );
    }

    /**
     *
     * @SWG\Get(path="/api/v2/bot/{bot_name}/auth/active/{registration_token}",
     *   tags={"Auth", "V2"},
     *   summary="註冊驗證手續",
     *   operationId="active",
     *   produces={"text/plain"},
     *   @SWG\Parameter(
     *       name="bot_name",
     *       in="path",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="registration_token",
     *       in="path",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function active($bot_name, $registration_token)
    {
        $staff = Staff::all()->filter(function ($item) use ($registration_token) {
            return Hash::check($registration_token, $item->registration_token);
        })->first();

        if (!$staff) {
            return "帳號驗證失敗";
        }

        $bot = Bot::where('name', $bot_name)->first();

        $object = $this->getToken($staff, $bot, $registration_token);

        if (App::environment('local')) {
            return json_decode(json_encode($object), true);
        }

        return $this->sendToken($staff, $bot, $object->access_token, $object->refresh_token);
    }

    private function getToken(Staff $staff, Bot $bot, $registration_token)
    {
        $http = new Client;
        $oauth_client = DB::table('oauth_clients')->where('name', $bot->name." User")->first();
        $response = $http->post(url('/oauth/token'), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oauth_client->id,
                'client_secret' => $oauth_client->secret,
                'username' => $staff->email,
                'password' => $registration_token,
                'scope' => '',
            ],
        ]);

        return json_decode((string) $response->getBody());
    }

    private function sendToken(Staff $staff, Bot $bot, $access_token, $refresh_token)
    {
        //送token給line-bot
        $client = new Client();
        $response = $client->request('POST', $bot->auth_hook_url, [
            'json' => [
                'action' => 'User Authorized',
                'reply_message' => [
                    'email' => $staff->email,
                    'access_token' => $access_token,
                    'refresh_token' => $refresh_token,
                ]
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $staff->update([
                'active' => Staff::ACTIVE,
            ]);

            return "帳號驗證成功";
        }
    }
    /**
     *
     * @SWG\Post(path="/api/v2/bot/auth/refresh",
     *   tags={"Auth", "V2"},
     *   security={
     *      {"bot": {}},
     *   },
     *   summary="憑證重發手續",
     *   operationId="refresh",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="refresh_token",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function refresh(Request $request)
    {
        if (!$request->filled('refresh_token')) {
            return response()->json([
                'reply_message' => "請填入refresh_token",
            ], 400);
        }

        $bot = Auth::guard('bot')->user();

        $object = $this->getRefreshToken($bot, $request->refresh_token);

        return response()->json([
            'action' => 'User Token Refreshed',
            'reply_message' => [
                'access_token' => $object->access_token,
                'refresh_token' => $object->refresh_token,
            ]
        ], 200);
    }

    private function getRefreshToken(Bot $bot, $refresh_token)
    {
        $http = new Client;
        $oauth_client = DB::table('oauth_clients')->where('name', $bot->name." User")->first();
        $response = $http->post(url('/oauth/token'), [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
                'client_id' => $oauth_client->id,
                'client_secret' => $oauth_client->secret,
                'scope' => '',
            ],
        ]);

        return json_decode((string) $response->getBody());
    }
}
