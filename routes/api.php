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

// Route::middleware('jwt.verify')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::get('outlet', 'OutletController@outlet');

Route::get('outletAll', 'OutletController@outletAuth')->middleware(['jwt.verify', 'role:client,cashier,admin']);
Route::get('refresh', 'UserController@refreshAuth')->middleware(['jwt.verify']);
Route::post('logout', 'UserController@logout')->middleware(['jwt.verify']);

Route::group(['middleware' => 'jwt.verify'], function() {
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', 'UserController@list')->middleware(['role:admin']);
    });
    Route::group(['prefix' => 'product-types'], function () {
        Route::get('/', 'Api\ProductType@index')->middleware(['role:admin']);
        Route::get('/{id}', 'Api\ProductType@show')->middleware(['role:admin']);
        Route::post('/save', 'Api\ProductType@store')->middleware(['role:admin']);
        Route::put('/save/{id}', 'Api\ProductType@update')->middleware(['role:admin']);
        Route::delete('/{id}', 'Api\ProductType@destroy')->middleware(['role:admin']);
    });
    Route::group(['prefix' => 'uom'], function () {
        Route::get('/', 'Api\UomController@index')->middleware(['role:admin']);
        Route::post('/save', 'Api\UomController@store')->middleware(['role:admin']);
        Route::get('/{id}', 'Api\UomController@show')->middleware(['role:admin']);
        Route::put('/save/{id}', 'Api\UomController@update')->middleware(['role:admin']);
        Route::delete('/{id}', 'Api\UomController@destroy')->middleware(['role:admin']);
    });
    Route::group(['prefix' => 'supplier'], function () {
        Route::get('/', 'Api\SupplierController@index')->middleware(['role:admin']);
        Route::get('/{id}', 'Api\SupplierController@show')->middleware(['role:admin']);
        Route::delete('/{id}', 'Api\SupplierController@destroy')->middleware(['role:admin']);
        Route::post('/save', 'Api\SupplierController@store')->middleware(['role:admin']);
        Route::post('/save/{id}', 'Api\SupplierController@update')->middleware(['role:admin']);
    });
});



