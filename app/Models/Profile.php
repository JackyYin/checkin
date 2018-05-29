<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    const GENDER_FEMALE = 0;
    const GENDER_MALE   = 1;

    const ID_FULL_TIME  = 0;
    const ID_PART_TIME  = 1;
    const ID_RESIGNED   = 2;

    protected $table = 'staff_profile';
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

}
