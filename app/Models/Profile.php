<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Enums;

class Profile extends Model
{
    use Enums;

    const GENDER_FEMALE = 0;
    const GENDER_MALE   = 1;

    protected $enumGenders = [
        self::GENDER_FEMALE => '女',
        self::GENDER_MALE   => '男',
    ];

    const ID_FULL_TIME  = 0;
    const ID_PART_TIME  = 1;
    const ID_RESIGNED   = 2;
    const ID_ABSENCE    = 3;

    protected $enumIdentities = [
        self::ID_FULL_TIME  => '全職',
        self::ID_PART_TIME  => '工讀',
        self::ID_RESIGNED   => '離職',
        self::ID_ABSENCE    => '留職停薪',
    ];

    protected $casts = [
        'identity' => 'integer',
        'gender'   => 'integer',
    ];

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

    protected $dates = [
        'on_board_date'
    ];
}
