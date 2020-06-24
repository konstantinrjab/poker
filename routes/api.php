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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/games', 'GameController@store');
Route::get('/games/{id}', 'GameController@show');
Route::put('/games/{id}', 'GameController@update');
Route::put('/games/{id}/join', 'GameController@join');
Route::put('/games/{id}/start', 'GameController@start');
Route::put('/games/{id}/ready', 'GameController@ready');

Route::post('/test', 'TestDealController');
