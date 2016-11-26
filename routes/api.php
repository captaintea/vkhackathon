<?php


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

Route::group(['prefix'=>'v1', 'namespace' => 'Api\V1'], function () {
    Route::get('auth', 'AuthController@index');

    Route::group(['middleware' => ['vk-session']], function () {
        Route::resource('group', 'GroupController', ['only' => [
            'index', 'store', 'update', 'destroy'
        ]]);
    });
});
