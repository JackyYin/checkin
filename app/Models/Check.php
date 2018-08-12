<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Enums;

class Check extends Model
{
    use Enums;

    const TYPE_NORMAL          = 0;
    const TYPE_PERSONAL_LEAVE  = 1;  //事假
    const TYPE_ANNUAL_LEAVE    = 2;  //特休
    const TYPE_OFFICIAL_LEAVE  = 3;  //公假
    const TYPE_SICK_LEAVE      = 4;  //病假
    const TYPE_ONLINE          = 5;  //Online
    const TYPE_LATE            = 6;  //晚到
    const TYPE_MOURNING_LEAVE  = 7;  //喪假
    const TYPE_MATERNITY_LEAVE = 8;  //產假
    const TYPE_PATERNITY_LEAVE = 9;  //陪產假
    const TYPE_MARRIAGE_LEAVE  = 10; //婚假

    protected $enumTypes = [
        self::TYPE_NORMAL           => "正常打卡",
        self::TYPE_PERSONAL_LEAVE   => "事假",
        self::TYPE_ANNUAL_LEAVE     => "特休",
        self::TYPE_OFFICIAL_LEAVE   => "出差",
        self::TYPE_SICK_LEAVE       => '病假',
        self::TYPE_ONLINE           => 'Online',
        self::TYPE_LATE             => '晚到',
        self::TYPE_MOURNING_LEAVE   => '喪假',
        self::TYPE_MATERNITY_LEAVE  => '產假',
        self::TYPE_PATERNITY_LEAVE  => '陪產假',
        self::TYPE_MARRIAGE_LEAVE   => '婚假',
    ];

    protected $casts = [
        'type' => 'integer',
    ];

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

    public function leave_reason()
    {
        return $this->hasOne(LeaveReason::class, 'check_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    public function scopeIsLeave($query)
    {
        return $query->where('type', '!=', self::TYPE_NORMAL);
    }

    public function isLeave()
    {
        return $this->type != Check::TYPE_NORMAL;
    }

    public function isSimple()
    {
        return $this->isLeave() && $this->type != Check::TYPE_ONLINE && $this->type != Check::TYPE_OFFICIAL_LEAVE;
    }
}
