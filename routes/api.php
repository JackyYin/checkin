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
        Route::get('bot/{bot_name}/auth/verify/{registration_token}', ['as' => 'api.bot.auth.verify', 'uses' => 'AuthController@verify']);
        Route::group(['middleware' => ['auth.api.bot']], function () {
            Route::post('/bot/auth', ['uses' => 'AuthController@auth']);
            Route::post('/bot/auth/refresh', ['uses' => 'AuthController@refresh']);
        });
        Route::group(['middleware' => ['auth.api.user']], function () {
            Route::group(['prefix' => 'leave'], function () {
                Route::get('/', ['uses' => 'LeaveController@index']);
                Route::get('/{leaveId}', ['uses' => 'LeaveController@show']);
                Route::get('/types/list', ['uses' => 'LeaveController@getLeaveType']);
                Route::get('/annual/stat', ['uses' => 'LeaveController@getAnnualStat']);
                Route::post('/', ['uses' => 'LeaveController@store']);
                Route::post('/online', ['uses' => 'LeaveController@requestOnline']);
                Route::post('/late', ['uses' => 'LeaveController@requestLate']);
                Route::put('/{leaveId}', ['uses' => 'LeaveController@update']);
                Route::delete('/{leaveId}', ['uses' => 'LeaveController@destroy']);
            });
        //    Route::group(['prefix' => 'check'], function () {
        //        Route::post('/', ['uses' => 'CheckController@index']);
        //        Route::get('start', ['uses' => 'CheckController@start']);
        //        Route::get('end', ['uses' => 'CheckController@end']);
        //    });
        });
    });
});
