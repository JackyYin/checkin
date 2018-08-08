<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BotStaff extends Pivot
{
    protected $table = 'bot_staff';
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
}
