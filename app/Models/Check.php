<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{

    const TYPE_NORMAL          = 0;
    const TYPE_PERSONAL_LEAVE  = 1;
    const TYPE_ANNUAL_LEAVE    = 2;
    const TYPE_OFFICIAL_LEAVE  = 3;

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
}
