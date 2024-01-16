<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Use App\Models\Customer;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\API\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::put('customers/{id}', 'App\Http\Controllers\CustomerController@update');

//Route::post('register', 'App\Http\Controllers\Auth\RegisterController@register');


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});


Route::group(['middleware' => ['auth:api']], function(){

    Route::get('/customers', 'App\Http\Controllers\CustomerController@index'); 
    Route::post('customers', 'App\Http\Controllers\CustomerController@store');
    Route::delete('customers/{dni}', 'App\Http\Controllers\CustomerController@delete');
    Route::get('customers/{dni}', 'App\Http\Controllers\CustomerController@show');

});