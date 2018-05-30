<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/
Route::group(['namespace' => 'Auth'], function () {
    Route::get('login',  ['as' => 'login', 'uses' => 'AuthController@login']);
    Route::post('login', ['as' => 'authenticate', 'uses' => 'AuthController@authenticate']);
});


Route::group(['middleware' => ['auth.admin']], function () {
    Route::get('logout',  ['as' => 'logout', 'uses' => 'Auth\AuthController@logout']);
    Route::resource('staff', 'StaffController', ['except' => ['show', 'destroy']]);
    Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
        Route::get('import', ['as' => 'import', 'uses' => 'StaffController@import']);
        Route::post('assignSubscription', ['as' => 'assignSubscription', 'uses' => 'StaffController@assignSubscription']);
    });
    Route::group(['prefix' => 'check', 'as' => 'check.'], function () {
        Route::get('export_statistic', ['as' => 'export_statistic', 'uses' => 'CheckController@export_statistic']);
        Route::get('export_check', ['as' => 'export_check', 'uses' => 'CheckController@export_check']);
        Route::get('count_late', ['as' => 'count_late', 'uses' => 'CheckController@count_late']);
    });
    Route::group(['prefix' => 'manager', 'as' => 'manager.'], function () {
        Route::post('assign', ['as' => 'assign', 'uses' => 'ManagerController@assign']);
    });
});
