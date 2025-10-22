<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\RoomTypeImageController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/rooms', [RoomTypeController::class, 'index']);
// Place static route before dynamic and constrain id to numbers
Route::get('/rooms/availability', [RoomTypeController::class, 'availability']);
Route::get('/rooms/{id}', [RoomTypeController::class, 'show'])->whereNumber('id');
Route::get('/bookings/availability', [BookingController::class, 'availability']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::put('/bookings/cancel/{id}', [BookingController::class, 'cancelled'])->whereNumber('id');
Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
Route::match(['get','post'], '/payments/confirm', [PaymentController::class, 'confirm']);
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

// Admin routes (auth:sanctum + role:admin)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
     Route::get('/rooms/list', [RoomTypeController::class, 'listRoom']);
    Route::post('/rooms/add', [RoomTypeController::class, 'store']);
    Route::put('/rooms/{id}', [RoomTypeController::class, 'updateRoomType'])->whereNumber('id');

    // physical rooms
    Route::post('/admin/physical-rooms', [RoomController::class, 'store']);
    Route::put('/admin/physical-rooms/{id}', [RoomController::class, 'update'])->whereNumber('id');
    Route::delete('/admin/physical-rooms/{id}', [RoomController::class, 'destroy'])->whereNumber('id');

    // bookings admin
    Route::get('/admin/bookings', [BookingController::class, 'index']); 
    Route::get('/admin/bookings/{id}', [BookingController::class, 'show'])->whereNumber('id');
    Route::put('/admin/bookings/{id}', [BookingController::class, 'update'])->whereNumber('id');

    // analytics
    Route::get('/admin/analytics', [AnalyticsController::class, 'index']);

    // room type images
    Route::post('/admin/room-types/{roomTypeId}/images', [RoomTypeImageController::class, 'store'])->whereNumber('roomTypeId');
    Route::match(['put','post'], '/admin/room-types/{roomTypeId}/images/{imageId}', [RoomTypeImageController::class, 'update'])
        ->whereNumber('roomTypeId')
        ->whereNumber('imageId');
    Route::delete('/admin/room-types/{roomTypeId}/images/{imageId}', [RoomTypeImageController::class, 'destroy'])->whereNumber('roomTypeId')->whereNumber('imageId');
});
