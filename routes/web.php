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
Route::group(['namespace' => 'Auth', 'prefix' => 'web'], function () {
    Route::get('login',  ['as' => 'login', 'uses' => 'AuthController@login']);
    Route::post('login', ['as' => 'authenticate', 'uses' => 'AuthController@authenticate']);
});

Route::group(['middleware' => ['auth.web']], function () {

    Route::get('/', ['uses' => 'CheckController@index']);

    Route::group(['prefix' => 'web'], function () {

        Route::group(['as' => 'check.'], function () {
            Route::get('check', ['as' => 'index', 'uses' => 'CheckController@index']);
        });
    });
});
