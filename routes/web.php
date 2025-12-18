<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\BookingController;


Route::get('/', function () {
    return Redirect::to('/status');
});

Route::get('/status', function () {
    $list = Cache::get('api_status_list', []);
    return view('status', ['status_list' => $list]);
});

// Public booking details page
Route::get('/bookings/{id}', [BookingController::class, 'publicShow'])->whereNumber('id')->name('booking.show');
Route::post('/bookings/{id}/cancel', [BookingController::class, 'publicCancel'])->whereNumber('id')->name('booking.cancel');
