<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Auth;
use DB;
use Mail;
use Socialite;
use Validator;
use App\Services\SocialService;
use App\Mail\AuthenticationEmail;
use App\Models\Bot;
use App\Models\Line;
use App\Models\Staff;
use App\Models\Social;

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
        $staff = Staff::where('email', $request->email)->first();

        $token = Uuid::uuid4();
        //驗證url
        $staff->bots()->save($request->user(), ['email_auth_token' => Hash::make($token)]);
        $confirmation_url = route('api.bot.auth.verify', ['bot_name' => $request->user()->name, 'registration_token' => $token]);

        Mail::send(new AuthenticationEmail($staff, $confirmation_url));

        return $this->response(200, "請至信箱確認驗證信件.");
    }
    /**
     *
     * @SWG\Get(path="/api/v2/bot/{bot_name}/auth/verify/{token}",
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
     *       name="token",
     *       in="path",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function verify($bot_name, $token)
    {
        $bot = Bot::where('name', $bot_name)->first();

        $staff = $bot->staffs->filter(function ($staff) use ($token) {
            return Hash::check($token, $staff->pivot->email_auth_token);
        })->first();

        if (!$staff) {
            return "帳號驗證失敗";
        }

        $object = $this->getToken($staff, $bot, $token);

        if (App::environment('local')) {
            $staff->bots()->detach($bot->id);
            return json_decode(json_encode($object), true);
        }

        return $this->sendToken($staff, $bot, $object);
    }

    private function getToken(Staff $staff, Bot $bot, $email_auth_token)
    {
        $http = new Client;
        $oauth_client = DB::table('oauth_clients')->where('name', $bot->name." User")->first();
        $response = $http->post(url('/oauth/token'), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oauth_client->id,
                'client_secret' => $oauth_client->secret,
                'username' => $staff->email,
                'password' => $email_auth_token,
                'scope' => '',
            ],
        ]);

        return json_decode((string) $response->getBody());
    }

    private function sendToken(Staff $staff, Bot $bot, $object)
    {
        //送token給line-bot
        $client = new Client([
            'headers' => [
                'Content-Type'  => 'application/json',
            ]
        ]);

        try {
            $response = $client->request('POST', $bot->auth_hook_url, [
                'json' => [
                    'action' => 'User Authorized',
                    'reply_message' => [
                        'email' => $staff->email,
                        'access_token' => $object->access_token,
                        'refresh_token' => $object->refresh_token,
                        'expired_in' => $object->expired_in,
                    ]
                ]
            ]);
        }
        catch (ClientException $e) {
            return "無法與機器人: ".$bot->name." 連結";
        }

        if ($response->getStatusCode() == 200) {
            $staff->bots()->detach($bot->id);
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

        if (!$object['success']) {
            return response()->json([
                'action' => 'User Token Refresh Failed',
                'reply_message' => [
                    'error' => $object['message']
                ]
            ], 401);
        }

        return response()->json([
            'action' => 'User Token Refreshed',
            'reply_message' => [
                'access_token'  => $object['message']->access_token,
                'refresh_token' => $object['message']->refresh_token,
                'expired_in'    => $object['message']->expired_in,
            ]
        ], 200);
    }

    private function getRefreshToken(Bot $bot, $refresh_token)
    {
        $oauth_client = DB::table('oauth_clients')->where('name', $bot->name." User")->first();

        $form_params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'scope' => '',
        ];

        try {
            $http = new Client;
            $response = $http->post(url('/oauth/token'), [
                'form_params' => $form_params
            ]);
        }
        catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => json_decode((string) $response->getBody()),
        ];
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
            $http = new Client;
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
    /**
     *
     * @SWG\Post(path="/api/v2/bot/auth/login/{provider}",
     *   tags={"Auth", "V2"},
     *   security={
     *      {"bot": {}},
     *   },
     *   summary="測試功能-以機器人使用者身份做社群登入",
     *   operationId="app-login",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *       name="provider",
     *       in="path",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Parameter(
     *       name="social_access_token",
     *       in="formData",
     *       type="string",
     *       required=true,
     *   ),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function loginSocial(
        $provider,
        \App\Http\Requests\Api\V2\Auth\LoginSocialRequest $request,
        SocialService $service
    )
    {
        $staff = $service->createOrGetStaff(
            Socialite::driver($provider)->userFromToken($request->social_access_token),
            $provider
        );

        if (!$staff) {
            return $this->response(400, [
                'account' => '您尚未成為本公司的成員'
            ]);
        }

        $object = $service->issueToken($provider, $request->user()->name, $request->social_access_token);

        if ($object['success']) {
            return $this->response(200, [
                'access_token' => $object['message']->access_token,
                'refresh_token' => $object['message']->refresh_token,
            ]);
        } else {
            return $this->response(400, [
                'error' => $object['message']
            ]);
        }
    }
}
