<?php

namespace App\Models;

use App\Traits\Enums;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use Enums;

    const NON_ACTIVE = 0;
    const ACTIVE     = 1;

    protected $table = 'staffs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'active', 'staff_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function line()
    {
        return $this->hasOne(Line::class,'staff_id','id');
    }

    public function authcode()
    {
        return $this->hasOne(AuthCode::class,'staff_id','id');
    }

    public function get_check_list()
    {
        return $this->hasMany(Check::class,'staff_id','id');
    }

    public function get_check_today()
    {
        return $this->get_check_list->where('checkin_at', '>=', date('Y-m-d').' 00:00:00')->first();
    }

    public function checked_in_today()
    {
        $this->get_check_today() ? $checked = true : $checked = false;
        return $checked;
    }

    public function checked_out_today()
    {
        $list = $this->get_check_list
            ->where('checkin_at', '>=', date('Y-m-d').' 00:00:00')
            ->where('checkout_at', '>=', date('Y-m-d').' 00:00:00')->first();
        $list ? $checked = true : $checked = false;
        return $checked;
    }
}
