<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffModule extends Model
{
    protected $table = 'staff_module';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    public $timestamps = false;
}
