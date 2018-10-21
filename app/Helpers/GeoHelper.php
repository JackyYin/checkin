<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class GeoHelper
{
    public static function distanceWithin($lat1, $long1, $lat2, $long2, $distance) : bool 
    {
        $actual = 6371 * acos(
            cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * cos(deg2rad($long2)
            - deg2rad($long1))
            + sin(deg2rad($lat1)) * sin(deg2rad($lat2))
        );

        return $actual < $distance; 
    }
}
