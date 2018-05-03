<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('login',  ['as' => 'login', 'uses' => 'Auth\AuthController@login']);

Route::group(['namespace' => 'Api'], function () {
    Route::post('register', ['uses' => 'RegisterController@register']);
    Route::post('active', ['uses' => 'RegisterController@active']);
    Route::post('checkin', ['uses' => 'CheckController@checkIn']);
    Route::post('checkout', ['uses' => 'CheckController@checkOut']);
    Route::post('get-leave-type', ['uses' => 'LeaveController@getLeaveType']);
    Route::post('request-leave', ['uses' => 'LeaveController@requestLeave']);
});

Route::group(['middleware' => ['client'], 'namespace' => 'Api'], function () {
//放入要驗證的api
});
