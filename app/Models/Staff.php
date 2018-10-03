<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Schedula\Laravel\PassportSocialite\User\UserSocialAccount;
use App\Traits\Enums;
use App\Models\Social;

class Staff extends Authenticatable implements UserSocialAccount
{
    use HasApiTokens;

    const NON_ACTIVE = 0;
    const ACTIVE     = 1;

    const NOT_SUBSCRIBED = 0;
    const SUBSCRIBED     = 1;

    protected $table = 'staffs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'active', 'staff_code', 'subscribed', 'registration_token', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
    * Find user using social provider's id
    *
    * @param string $provider Provider name as requested from oauth e.g. facebook
    * @param string $id User id of social provider
    *
    * @return User
    */
    public static function findForPassportSocialite($provider, $id) {
        $account = Social::where('provider', $provider)->where('provider_user_id', $id)->first();
        if($account) {
            if($account->staff){
                return $account->staff;
            }
        }
        return;
    }

    public function line()
    {
        return $this->hasOne(Line::class,'staff_id','id');
    }

    public function authcode()
    {
        return $this->hasMany(AuthCode::class,'staff_id','id');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class,'staff_id','id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class,'staff_id','id');
    }

    public function manager()
    {
        return $this->hasOne(Manager::class,'staff_id','id');
    }

    public function registration_token()
    {
        return $this->hasMany(RegistrationToken::class,'staff_id','id');
    }

    public function checks()
    {
        return $this->hasMany(Check::class,'staff_id','id');
    }

    public function socials()
    {
        return $this->hasMany(Social::class,'staff_id','id');
    }

    public function bots()
    {
        return $this->belongsToMany(Bot::class)->using(BotStaff::class)->withPivot('email_auth_token');
    }

    public function findForPassport($username)
    {
        return $this->where('email', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        return  Hash::check($password, $this->password) || $this->bots->filter(function ($bot) use ($password) {
            return Hash::check($password, $bot->pivot->auth_email_token);
        });
    }

    public function scopeSubscribed($query)
    {
        return $query->where('subscribed', self::SUBSCRIBED);
    }

    public function scopeActive($query)
    {
        return $query->where('active', self::ACTIVE);
    }
}
