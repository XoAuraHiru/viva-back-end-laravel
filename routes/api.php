<?php

use App\Http\Controllers\GenreController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SheduleController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TicketController;
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

Route::get('/movies/{type?}/{code?}', [MovieController::class, 'index']);

Route::get('/genres', [GenreController::class, 'view']);

Route::get('/shows/{type?}/{id?}', [SheduleController::class, 'index']);
Route::get('/seats', [SeatController::class, 'index']);
Route::post('/order/create', [OrderController::class, 'createOrder']);
Route::get('/order/{order_no}', [OrderController::class, 'index']);

Route::post('/create-payment-intent', [StripeController::class, 'createPaymentIntent']);
Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);
Route::get('/user/orders', [OrderController::class, 'getUserOrders']);

Route::get('/admin/orders', [OrderController::class, 'getOrders']);
Route::delete('/admin/order/{id}', [OrderController::class, 'deleteOrder']);
Route::get('/admin/ticket/{id}', [TicketController::class, 'viewTickets']);
Route::post('/admin/movie/add', [MovieController::class, 'store']);
