<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{

    const TYPE_NORMAL          = 0;
    const TYPE_PERSONAL_LEAVE  = 1; //事假
    const TYPE_ANNUAL_LEAVE    = 2; //特休
    const TYPE_OFFICIAL_LEAVE  = 3; //公假

    protected $table = 'checks';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id', 'checkin_at', 'checkout_at', 'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    public $timestamps = false;

    public function scopeTwo_days_ago($query)
    {
        return $query->where('checkin_at', '<=', date('Y-m-d', strtotime('-1 day')))
            ->where('checkin_at', '>=', date('Y-m-d', strtotime('-2 days')));
    }

    public function scopeNot_checked_out($query)
    {
        return $query->whereNull('checkout_at');
    }
}
