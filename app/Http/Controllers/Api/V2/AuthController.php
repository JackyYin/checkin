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
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use App\Mail\AuthenticationEmail;
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
     *   summary="寄發驗證email",
     *   operationId="auth",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="email",
     *       in="formData",
     *       type="string",
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */

    public function auth(\App\Http\Requests\Api\V2\Auth\AuthRequest $request)
    {
        $new_staff = Staff::where('email', $request->email)->first();
        $registration_token = Uuid::uuid4();
        //驗證url
        $new_staff->update([
            'registration_token' => Hash::make($registration_token),
        ]);
        $confirmation_url = route('api.bot.auth.verify', ['bot_name' => $request->user()->name, 'registration_token' => $registration_token]);

        Mail::send(new AuthenticationEmail($new_staff, $confirmation_url));

        return $this->response(200, "請至信箱確認驗證信件.");
    }
    /**
     *
     * @SWG\Get(path="/api/v2/bot/{bot_name}/auth/verify/{registration_token}",
     *   tags={"Auth", "V2"},
     *   summary="email產生並送token到bot endpoint",
     *   operationId="verify",
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
    public function verify($bot_name, $registration_token)
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
        $client = new Client([
            'headers' => [
                'Content-Type'  => 'application/json',
            ]
        ]);

        $json = [
            'action' => 'User Authorized',
            'reply_message' => [
                'email' => $staff->email,
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
            ]
        ];

        try {
            $response = $client->request('POST', $bot->auth_hook_url, [
                'json' => $json
            ]);
        }
        catch (ClientException $e) {
            return $e->getResponse();
        }

        if ($response->getStatusCode() == 200) {
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
     *   summary="token重發手續",
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
            return $this->response(400, [
                'refresh_token' => [
                    '請填入refresh_token'
                ]
            ]);
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
    /**
     *
     * @SWG\Post(path="/api/v2/bot/auth/login",
     *   tags={"Auth", "V2"},
     *   summary="App登入",
     *   operationId="app-login",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="email",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="password",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function login(\App\Http\Requests\Api\V2\Auth\LoginRequest $request)
    {
        $http = new Client;
        $oauth_client = DB::table('oauth_clients')->where('name', 'App User')->first();

        $form_params = [
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '',
        ];

        try {
            $response = $http->request('POST', url('/oauth/token'), [
                'form_params' => $form_params
            ]);
        }
        catch (ClientException $e) {
            return $this->response(401, '帳號或密碼錯誤');
        }

        $response = json_decode((string) $response->getBody());

        return $this->response(200, [
            'access_token' => $response->access_token,
        ]);
    }
}
