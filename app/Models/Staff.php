<?php

namespace App\Models;

use App\Traits\Enums;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Staff extends Model
{
    use Enums;

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
        'name', 'email', 'active', 'staff_code', 'subscribed'
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

    public function get_check_list()
    {
        return $this->hasMany(Check::class,'staff_id','id');
    }

    public function count_checkin_today()
    {
        return $this->get_check_list
            ->where('type', Check::TYPE_NORMAL)
            ->where('checkin_at', '>=', Carbon::today())->count();
    }

    public function count_checkout_today()
    {
        return $this->get_check_list
            ->where('type', Check::TYPE_NORMAL)
            ->where('checkin_at', '>=', Carbon::today())
            ->where('checkout_at', '>=', Carbon::today())
            ->count();
    }

    public function count_check_diff_today()
    {
        return $this->count_checkin_today() - $this->count_checkout_today();
    }

    public function range_one_day($from)
    {
        return $this->get_check_list->where('checkin_at', '>=', $from)->where('checkin_at', '<=', date('Y-m-d', strtotime($from.'+ 1 day' )));
    }
}
