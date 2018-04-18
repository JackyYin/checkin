<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function get_check_list()
    {
        return $this->hasMany(Check::class,'user_id','id');
    }

    public function checked_today()
    {
        $this->get_check_list->where('checkin_at', '>=', date('Y-m-d').' 00:00:00') ? $checked = true : $checked = false;
        return $checked;
    }
}
