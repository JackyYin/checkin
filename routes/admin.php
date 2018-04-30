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
    Route::resource('staff', 'StaffController',
                    ['only' => ['index', 'create', 'store']]);
    Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
        Route::get('import', ['as' => 'import', 'uses' => 'StaffController@import']);
    });
    Route::group(['prefix' => 'check', 'as' => 'check.'], function () {
        Route::get('export_page', ['as' => 'export_page', 'uses' => 'CheckController@export_page']);
        Route::post('export', ['as' => 'export', 'uses' => 'CheckController@export']);
    });
});
