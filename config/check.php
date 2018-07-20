<?php

return [
    'checkin' => [
        'start' => env('CHECKIN_RANGE_START', '09:00:00'),
        'end'   => env('CHECKIN_RANGE_END', '09:30:00'),
    ],
    'checkout' => [
        'start' => env('CHECKOUT_RANGE_START', '18:00:00'),
        'end'   => env('CHECKOUT_RANGE_END', '18:30:00'),
    ],
    'noon' => [
        'start' => env('NOON_BREAK_START', '12:00:00'),
        'end'   => env('NOON_BREAK_END', '13:00:00'),
    ]
];

