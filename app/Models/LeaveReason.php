<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveReason extends Model
{
    protected $table = 'leave_reason';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'check_id', 'reason'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    public $timestamps = false;

}
