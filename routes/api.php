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

Route::middleware('jwt.verify')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::get('outlet', 'OutletController@outlet');

Route::get('outletAll', 'OutletController@outletAuth')->middleware(['jwt.verify', 'role:client,cashier,admin']);
Route::get('refresh', 'UserController@refreshAuth')->middleware(['jwt.verify']);
Route::post('logout', 'UserController@logout')->middleware(['jwt.verify']);



