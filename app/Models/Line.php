<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    protected $table = 'staff_line';
    /**
     * the attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id', 'line_id'
    ];

    /**
     * the attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    public $timestamps = false;
}
