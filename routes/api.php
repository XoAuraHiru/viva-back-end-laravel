<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SheduleController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/movies/{type?}/{id?}', [MovieController::class, 'index']);
Route::post('/movie/add', [MovieController::class, 'store']);
Route::get('/shows/{type?}/{id?}', [SheduleController::class, 'index']);
Route::get('/seats', [SeatController::class, 'index']);
Route::post('/order/create', [OrderController::class, 'createOrder']);
Route::get('/order/{id}', [OrderController::class, 'index']);