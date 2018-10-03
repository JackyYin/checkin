<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use App\Service\SocialService;
use App\Transformers\StaffTransformer;
use App\Models\Social;

class LoginController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider, SocialService $service, StaffTransformer $transformer)
    {
        $staff = $service->createOrGetStaff(Socialite::driver($provider)->user(), $provider);

        if (!$staff) {
            return "您尚未成為本公司的員工";
        }

        return fractal($staff, $transformer, new \League\Fractal\Serializer\ArraySerializer());
    }
}
