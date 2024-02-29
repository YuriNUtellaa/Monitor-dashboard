<?php
use App\Http\Controllers\ParkingController;
use Illuminate\Support\Facades\Route;

use App\Models\ParkingSlot;
use App\Models\Vehicle;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/monitor', function () {
    return view('dashboard');
});



Route::get('/listing', [ParkingController::class, 'listParkedVehicles']);


Route::get('/monitor', [ParkingController::class, 'showDashboard']);

Route::get('/reservations', [ParkingController::class, 'listReservations'])->name('reservations.list');


Route::get('/parking-slots/{id}', function ($id) {
    $parkingSlot = ParkingSlot::find($id);
    $vehicle = $parkingSlot->vehicle;

    // Do something with $vehicle
});
Route::get('/vehicles/{id}', function ($id) {
    $vehicle = Vehicle::find($id);
    $parkingSlot = $vehicle->parkingSlot;

    // Do something with $parkingSlot
});


Route::get('/', function () {
    return view('welcome');
});
