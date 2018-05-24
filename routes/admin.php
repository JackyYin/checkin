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
    Route::resource('staff', 'StaffController', ['except' => ['show', 'destroy']]);
    Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
        Route::get('import', ['as' => 'import', 'uses' => 'StaffController@import']);
    });
    Route::group(['prefix' => 'check', 'as' => 'check.'], function () {
        Route::get('export_statistic_page', ['as' => 'export_statistic_page', 'uses' => 'CheckController@export_statistic_page']);
        Route::post('exportST', ['as' => 'exportST', 'uses' => 'CheckController@exportST']);
        Route::get('export_check_page', ['as' => 'export_check_page', 'uses' => 'CheckController@export_check_page']);
        Route::post('exportCheck', ['as' => 'exportCheck', 'uses' => 'CheckController@exportCheck']);
    });
});
