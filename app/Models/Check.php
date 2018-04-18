<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'checkin_at', 'checkout_at', 'hours', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    public $timestamps = false;
}
