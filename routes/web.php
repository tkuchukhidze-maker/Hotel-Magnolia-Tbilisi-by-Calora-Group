<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/rooms', [PageController::class, 'rooms'])->name('rooms');
Route::get('/our-hotel', [PageController::class, 'ourHotel'])->name('our-hotel');
Route::get('/explore', [PageController::class, 'explore'])->name('explore');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bookings.store');
