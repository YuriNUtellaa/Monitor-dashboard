<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkingController;

Route::post('/park', [ParkingController::class, 'park']);

Route::get('/dashboard', [ParkingController::class, 'dashboard']);

Route::get('/slots', [ParkingController::class, 'getSlots']);
Route::put('/update-vehicle/{parkingSlotId}', [ParkingController::class, 'updateVehicle']);





Route::delete('/delete-vehicle/{parkingSlotId}', [ParkingController::class, 'deleteVehicle']);

Route::put('/slots/{id}', [ParkingController::class, 'updateVehicle']);

Route::post('/reserve', [ParkingController::class, 'reserve']);


Route::put('/update-reservation/{id}', [ParkingController::class, 'updateReservation']);


Route::delete('/delete-reservation/{id}', [ParkingController::class, 'deleteReservation']);




