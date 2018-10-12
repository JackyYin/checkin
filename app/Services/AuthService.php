<?php

namespace App\Services;

use Laravel\Socialite\Contracts\User as ProviderUser;
use DB;
use GuzzleHttp\Client;
use App\Models\Staff;
use App\Models\Bot;

class AuthService
{

    public function __construct()
    {
    }

    public function getToken(Staff $staff, Bot $bot, $email_auth_token)
    {
        $client = new Client();
        $oauth_client = DB::table('oauth_clients')->where('name', $bot->name." User")->first();

        try {
            $response = $client->post(url('/oauth/token'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $oauth_client->id,
                    'client_secret' => $oauth_client->secret,
                    'username' => $staff->email,
                    'password' => $email_auth_token,
                    'scope' => '',
                ],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return json_decode((string) $response->getBody());
    }

    public function sendToken(Staff $staff, Bot $bot, $object)
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
                        'expires_in' => $object->expires_in,
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
}
