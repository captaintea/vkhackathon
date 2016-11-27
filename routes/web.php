<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/


Route::any('vk-callback', [
    'as' => 'vk-callback', 'uses' => 'VkController@callback'
]);


Route::get('/', function () {
    return view('welcome');
});