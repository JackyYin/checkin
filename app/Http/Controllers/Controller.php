<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const PAGER = 30;

    public $CHECK_TYPE = [
        1  => "事假",
        2  => "特休",
        3  => "出差",
        4  => '病假',
        5  => 'Online',
        6  => '晚到',
        7  => '喪假',
        8  => '產假',
        9  => '陪產假',
        10 => '婚假',
    ];

    public $WEEK_DAY = [
        "Sunday"    => "日",
        "Monday"    => "一",
        "Tuesday"   => "二",
        "Wednesday" => "三",
        "Thursday"  => "四",
        "Friday"    => "五",
        "Saturday"  => "六",
    ];

    public function paginate($items, $perPage = self::PAGER, $page = null, Request $request)
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $options = [
            'path'  => $request->url(),
            'query' => $request->query(),
        ];

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
