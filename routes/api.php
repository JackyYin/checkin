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
    //api v2
    Route::group(['prefix' => 'v2', 'namespace' => 'V2'], function () {
        Route::group(['prefix' => 'bot'], function () {
            //App登入
            Route::post('auth/login', ['uses' => 'AuthController@login']);
            //信箱驗證連結
            Route::get('{bot_name}/auth/verify/{token}', ['as' => 'api.bot.auth.verify', 'uses' => 'AuthController@verify']);
            //機器人新增
            Route::post('/', ['uses' => 'BotController@store']);
            Route::group(['middleware' => ['auth.api.bot']], function () {
                //測試功能-以機器人使用者身份做社群登入
                Route::post('/auth/login/{provider}', 'AuthController@loginSocial');
                //寄發驗證信件
                Route::post('/auth', ['uses' => 'AuthController@auth']);
                //重發token機制
                Route::post('/auth/refresh', ['uses' => 'AuthController@refresh']);
                //機器人查看、更新
                Route::get('/me', ['uses' => 'BotController@me']);
                Route::patch('/me', ['uses' => 'BotController@update']);
            });
        });
        Route::group(['middleware' => ['auth.api.user']], function () {
            Route::group(['prefix' => 'leave'], function () {
                Route::get('/{id}', ['uses' => 'LeaveController@show'])->where('id', '[0-9]+');
                Route::get('/types', ['uses' => 'LeaveController@getLeaveType']);
                Route::post('/', ['uses' => 'LeaveController@store']);
                Route::post('/online', ['uses' => 'LeaveController@requestOnline']);
                Route::post('/late', ['uses' => 'LeaveController@requestLate']);
                Route::put('/{id}', ['uses' => 'LeaveController@update'])->where('id', '[0-9]+');
                Route::delete('/{id}', ['uses' => 'LeaveController@destroy'])->where('id', '[0-9]+');
                //統計相關
                Route:: group(['prefix' => 'stat', 'namespace' => 'Leave'], function () {
                    Route::get('/', ['uses' => 'StatController@index']);
                    Route::get('/me', ['uses' => 'StatController@me']);
                    Route::get('/annual', ['uses' => 'StatController@getAnnualStat']);
                });
                //紀錄
                Route:: group(['prefix' => 'record', 'namespace' => 'Leave'], function () {
                    Route::get('/', ['uses' => 'RecordController@index']);
                    Route::get('/search', ['uses' => 'RecordController@search']);
                    Route::get('/me', ['uses' => 'RecordController@me']);
                });
            });
            Route::group(['prefix' => 'staff'], function () {
                Route::get('/me', ['uses' => 'StaffController@me']);

                //使用者模組
                Route::group(['prefix' => 'module', 'namespace' => 'Staff'], function () {
                    Route::get('/', ['uses' => 'ModuleController@index']);
                    Route::post('on', ['uses' => 'ModuleController@on']);
                    Route::post('off', ['uses' => 'ModuleController@off']);
                });
            });

            Route::group(['prefix' => 'check'], function () {
                Route::post('in/location', ['uses' => 'CheckController@locationCheckIn']);
                Route::post('out/location', ['uses' => 'CheckController@locationCheckOut']);
            });
        });
    });
});
