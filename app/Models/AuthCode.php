<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model
{
    protected $table = 'staff_auth';
    /**
     * the attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id', 'auth_code'
    ];

    /**
     * the attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    public $timestamps = false;
}
