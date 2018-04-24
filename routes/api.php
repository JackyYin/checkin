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
Route::get('register', ['uses' => 'RegisterController@register']);
Route::get('active', ['uses' => 'RegisterController@active']);

Route::group(['middleware' => ['client'], 'namespace' => 'Api'], function () {
//放入要驗證的api
});
