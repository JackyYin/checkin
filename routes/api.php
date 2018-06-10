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
    //api v1
    Route::post('register', ['uses' => 'RegisterController@register']);
    Route::post('active', ['uses' => 'RegisterController@active']);
    Route::post('checkin', ['uses' => 'CheckController@checkIn']);
    Route::post('checkout', ['uses' => 'CheckController@checkOut']);
    Route::post('get-check-list', ['uses' => 'CheckController@getCheckList']);
    Route::post('get-leave-type', ['uses' => 'LeaveController@getLeaveType']);
    Route::post('request-leave', ['uses' => 'LeaveController@requestLeave']);
    Route::post('request-late', ['uses' => 'LeaveController@requestLate']);
    Route::post('request-online', ['uses' => 'LeaveController@requestOnline']);
    //api v2
    Route::group(['prefix' => 'v2', 'namespace' => 'V2'], function () {
        Route::group(['prefix' => 'register'], function () {
            Route::post('/', ['uses' => 'RegisterController@register']);
            Route::get('active/{registration_token}', ['as' => 'api.register.active', 'uses' => 'RegisterController@active']);
        });
        Route::group(['middleware' => ['auth.api.bot', 'auth.api.user']], function () {
            Route::group(['prefix' => 'leave'], function () {
                Route::post('get-leave-type', ['uses' => 'LeaveController@getLeaveType']);
                Route::get('/', ['uses' => 'LeaveController@index']);
                Route::post('/', ['uses' => 'LeaveController@store']);
                Route::put('/{id}', ['uses' => 'LeaveController@update']);
                Route::delete('/{id}', ['uses' => 'LeaveController@destroy']);
            });
//            Route::group(['prefix' => 'check'], function () {
//                Route::post('/', ['uses' => 'CheckController@index']);
//                Route::get('start', ['uses' => 'CheckController@start']);
//                Route::get('end', ['uses' => 'CheckController@end']);
//            });
        });
    });
});
