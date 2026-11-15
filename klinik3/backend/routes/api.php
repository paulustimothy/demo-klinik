<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/clinic', [ClinicController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/availability', [AvailabilityController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:api-bookings');
