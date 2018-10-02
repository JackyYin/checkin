<?php

namespace App\Service;

use Laravel\Socialite\Contracts\User as ProviderUser;
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
}
