<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => 'v1'], function() {
    Route::group(['prefix' => 'auth'], function() {
        Route::post('/login', 'AuthController@login');
        Route::get('/logout', 'AuthController@logout');
    });
    Route::get('/place', 'PlaceController@index');
    Route::group(['middleware' => 'auth'], function() {
        Route::get('/place/{ID}', 'PlaceController@show');
        Route::get('/route/search/{from_place_id}/{to_place_id}/{departure_time}', 'RouteController@search');
        Route::post('/route/selection', 'RouteController@store');
    });
    Route::group(['middleware' => 'admin'], function() {
        Route::resource('place', 'PlaceController', ['only' => ['store','update','destroy']]);
        Route::resource('schedule', 'ScheduleController');
    });
});