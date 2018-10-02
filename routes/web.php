<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['prefix' => 'web'], function () {
    Route::group(['namespace' => 'Auth'], function () {
        Route::get('login',  ['as' => 'login', 'uses' => 'AuthController@login']);
        Route::post('login', ['as' => 'authenticate', 'uses' => 'AuthController@authenticate']);
        Route::get('login/facebook', 'LoginController@redirectToProvider');
        Route::get('login/facebook/callback', 'LoginController@handleProviderCallback');
    });
});

Route::group(['middleware' => ['auth.web']], function () {

    Route::group(['prefix' => 'web'], function () {

        Route::group(['as' => 'check.'], function () {
            Route::get('check', ['as' => 'index', 'uses' => 'CheckController@index']);
            Route::get('on',    ['as' => 'on', 'uses' => 'CheckController@on']);
            Route::get('off',   ['as' => 'off', 'uses' => 'CheckController@off']);
        });
    });
});

Route::group(['prefix' => 'swagger'], function () {
    Route::get('json', 'SwaggerController@getJSON');
    Route::get('my-data', 'SwaggerController@getMyData');
});


