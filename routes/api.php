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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/game', 'GameController@index');
Route::post('/game', 'GameController@store');
Route::get('/game/{id}', 'GameController@show');
Route::put('/game/{id}/join', 'GameController@join');
Route::put('/game/{id}/start', 'GameController@start');
