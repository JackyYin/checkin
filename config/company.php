<?php

//用GPS打卡時,公司的位置以及可以接受打卡的距離(km)

return [
    'latitude'       => env('COMPANY_LATITUDE'),
    'longitude'      => env('COMPANY_LONGITUDE'),
    'legal_distance' => env('COMPANY_LEGAL_DISTANCE'),
];

