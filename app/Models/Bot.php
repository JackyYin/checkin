<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Bot extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'bots';
    /**
     * the attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * the attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    public $timestamps = false;

    public function staffs()
    {
        return $this->belongsToMany(Staff::class)->using(BotStaff::class)->withPivot('email_auth_token');
    }
}
