<?php

namespace App\Service;

use Laravel\Socialite\Contracts\User as ProviderUser;
use DB;
use GuzzleHttp\Client;
use App\Models\Staff;
use App\Models\Social;

class SocialService
{
    public function createOrGetStaff(ProviderUser $providerUser, $provider)
    {
        $account = Social::whereProvider($provider)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->staff;
        } else {
            $staff = Staff::whereEmail($providerUser->getEmail())->first();

            if (!$staff) {
                return null;
            }

            Social::create([
                'staff_id' => $staff->id,
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'facebook'
            ]);

            return $staff;
        }
    }

    public function issueToken($provider, $bot_name, $social_access_token)
    {
        $oauth_client = DB::table('oauth_clients')->where('name', $bot_name.' User')->first();

        if (!$oauth_client) {
            return [
                'success' => false,
                'message' => "不存在的 oauth client (Bot User)",
            ];
        }

        $form_params = [
            'grant_type' => 'social',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'accessToken' => $social_access_token,
            'provider' => $provider,
        ];

        try {
            $http = new Client;
            $response = $http->request('POST', url('/oauth/token'), [
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
}
