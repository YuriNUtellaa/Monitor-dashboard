<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\ParkingSlot;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ParkingController extends Controller
{
    public function park(Request $request)
    {
        $request->validate([
            'slot_number' => 'required|integer',
            'license_plate' => 'required|string'
        ]);

        // Find the slot the vehicle will be parked in
        $slot = ParkingSlot::where('slot_number', $request->slot_number)->firstOrFail();

        // Check if the slot is already occupied
        if ($slot->is_occupied) {
            return response()->json(['message' => 'Slot is already occupied'], 422);
        }
        $reservation = Reservation::where('parking_slot_id', $slot->id)->first();
        if ($reservation) {
            // Option 1: Overwrite the reservation
            $reservation->delete(); // or handle it differently

            // Option 2: Reject the parking if the slot is reserved
            return response()->json(['message' => 'Slot is reserved'], 422);
        }
        // Create or get the vehicle
        $vehicle = Vehicle::firstOrCreate(['license_plate' => $request->license_plate]);

        // Associate the vehicle with the slot and save
        $vehicle->parkingSlot()->associate($slot);
        $vehicle->save();

        // Now you can update the slot as being occupied
        $slot->is_occupied = true;
        $slot->license_plate = $vehicle->license_plate;
        $slot->parked_at = Carbon::now();
        $slot->save();

        return response()->json(['message' => 'Vehicle parked successfully']);
    }

    public function getSlots()
    {
        $slots = ParkingSlot::with('reservation')->get()->map(function ($slot) {
            $slot->is_reserved = (bool) $slot->reservation; // Assumes reservation is null if not reserved
            return $slot;
        });

        return response()->json($slots);
    }





    public function dashboard()
    {
        $totalSlots = ParkingSlot::count();
        $occupiedSlots = ParkingSlot::where('is_occupied', true)->count();
        $reservedSlots = Reservation::count(); // Add this line to count reserved slots
        $recentActivity = ParkingSlot::latest('parked_at')->take(10)->get();

        return response()->json([
            'total_slots' => $totalSlots,
            'occupied_slots' => $occupiedSlots,
            'reserved_slots' => $reservedSlots, // Add this to the response
            'available_slots' => $totalSlots - $occupiedSlots - $reservedSlots, // Subtract reserved slots from available slots
            'recent_activity' => $recentActivity
        ]);
    }

    public function edit(Request $request, $slot_number)
    {
        $slot = ParkingSlot::where('slot_number', $slot_number)->firstOrFail();
        $slot->update([
            'is_occupied' => false,
            'license_plate' => null,
            'left_at' => Carbon::now()
        ]);

        return response()->json(['message' => 'Parking slot updated successfully']);
    }

    public function listParkedVehicles()
    {
        $parkedVehicles = ParkingSlot::where('is_occupied', true)
                                     ->orderBy('parked_at', 'desc')
                                     ->get(['id', 'license_plate', 'parked_at', 'slot_number']);

        // Convert parked_at to a carbon instance if it's not already
        foreach ($parkedVehicles as $vehicle) {
            $vehicle->parked_at = Carbon::parse($vehicle->parked_at);
        }

        return view('listing', compact('parkedVehicles'));
    }
    public function deleteVehicle($parkingSlotId)
{
    Log::info("Received Parking Slot ID for deletion: " . $parkingSlotId);

    // Start a database transaction
    DB::beginTransaction();

    try {
        // Find the vehicle by parking slot ID
        $vehicle = Vehicle::where('parking_slot_id', $parkingSlotId)->firstOrFail();
        $slot = $vehicle->parkingSlot;

        // Check if the vehicle has an associated parking slot
        if ($slot) {
            // Update the parking slot status
            $slot->is_occupied = false;
            $slot->license_plate = null;
            $slot->parked_at = null;
            $slot->save();
        }

        // Delete the vehicle
        $vehicle->delete();

        // Commit the database transaction
        DB::commit();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    } catch (ModelNotFoundException $e) {
        // Rollback the transaction in case of errors
        DB::rollBack();
        Log::error('Vehicle not found for Parking Slot ID: ' . $parkingSlotId);
        return response()->json(['message' => "Vehicle for Parking Slot ID $parkingSlotId not found"], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting vehicle: ' . $e->getMessage());
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

public function showDashboard()
{
    return view('dashboard');
}

public function parkingSlot()
{
    return $this->belongsTo(ParkingSlot::class, 'parking_slot_id', 'id');
}

public function updateVehicle(Request $request, $parkingSlotId)
{
    $request->validate([
        'new_slot_number' => 'required|integer|exists:parking_slots,slot_number',
        'new_license_plate' => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        // Find the vehicle associated with the provided parking slot ID
        $vehicle = Vehicle::where('parking_slot_id', $parkingSlotId)->firstOrFail();

        // Check if new license plate is unique for other vehicles
        $existingVehicle = Vehicle::where('license_plate', $request->new_license_plate)
                                  ->where('id', '!=', $vehicle->id)
                                  ->first();
        if ($existingVehicle) {
            throw new \Exception('License plate already in use by another vehicle.');
        }

        // Find the new slot by the new slot number
        $newSlot = ParkingSlot::where('slot_number', $request->new_slot_number)->firstOrFail();

        // Ensure the new slot is not occupied by another vehicle
        if ($newSlot->is_occupied && $newSlot->id != $parkingSlotId) {
            throw new \Exception('The selected new slot is already occupied.');
        }

        // Update the old slot if it is not the same as the new slot
        if ($newSlot->id !== $parkingSlotId) {
            $oldSlot = ParkingSlot::findOrFail($parkingSlotId);
            $oldSlot->is_occupied = false;
            $oldSlot->license_plate = null;
            $oldSlot->save();
        }

        // Update the new slot to be occupied
        $newSlot->is_occupied = true;
        $newSlot->license_plate = $request->new_license_plate;
        $newSlot->save();

        // Update the vehicle's parking slot ID and license plate
        $vehicle->parking_slot_id = $newSlot->id;
        $vehicle->license_plate = $request->new_license_plate;
        $vehicle->save();

        DB::commit();

        return response()->json(['message' => 'Vehicle and slot updated successfully']);
    } catch (ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json(['message' => 'Vehicle or slot not found'], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => $e->getMessage()], 500);
    }
}


public function showParkingSlot($id)
{
    $parkingSlot = ParkingSlot::find($id);
    $vehicle = $parkingSlot->vehicle; // Access the related vehicle

    // Return a view or JSON response
    return view('parking_slots.show', compact('parkingSlot', 'vehicle'));
}

public function reserve(Request $request)
{
    $request->validate([
        'license_plate' => 'required|string',
        'slot_number' => 'required|integer|exists:parking_slots,slot_number'
    ]);

    DB::beginTransaction();
    try {
        $slot = ParkingSlot::where('slot_number', $request->slot_number)->firstOrFail();

        if ($slot->is_occupied) {
            // Return an error response if the slot is already occupied
            return response()->json(['message' => 'Slot is already occupied'], 422);
        }

        if ($slot->reservation) {
            // Return an error response if the slot is already reserved
            return response()->json(['message' => 'Slot is already reserved'], 422);
        }

        // Create the reservation
        $reservation = new Reservation([
            'license_plate' => $request->license_plate,
            'parking_slot_id' => $slot->id
        ]);
        $reservation->save();

        // Set the slot as reserved - assuming you have an `is_reserved` column in your table
        $slot->is_reserved = true;
        $slot->save();

        DB::commit();
        return response()->json(['message' => 'Reservation successful']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Reservation failed: ' . $e->getMessage()], 500);
    }
}


public function reservation()
{
    return $this->hasOne(Reservation::class);
}

public function listReservations()
{
    $reservations = Reservation::with('parkingSlot')->get();
    return view('reservations', compact('reservations'));
}

public function deleteReservation($id)
{
    try {
        $reservation = Reservation::findOrFail($id);
        // If you have related logic to free up the parking slot, add it here
        $slot = ParkingSlot::findOrFail($reservation->parking_slot_id);
        $slot->is_reserved = false;
        $slot->save();

        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error deleting reservation: ' . $e->getMessage()], 500);
    }
}

public function updateReservation(Request $request, $id)
{
    $request->validate([
        'slot_number' => 'required|integer|exists:parking_slots,slot_number',
        'license_plate' => 'required|string'
    ]);

    try {
        $reservation = Reservation::findOrFail($id);
        $slot = ParkingSlot::findOrFail($reservation->parking_slot_id);

        // Check if the new slot number is different and not occupied
        if ($slot->slot_number != $request->slot_number && !ParkingSlot::where('slot_number', $request->slot_number)->where('is_reserved', false)->exists()) {
            return response()->json(['message' => 'New slot is occupied or does not exist'], 422);
        }


        // Update reservation details
        $reservation->parking_slot_id = $request->slot_number;
        $reservation->license_plate = $request->license_plate;
        $reservation->save();

        // Update the old slot to be not reserved
        $slot->is_reserved = false;
        $slot->save();

        // Update the new slot to be reserved
        $newSlot = ParkingSlot::where('slot_number', $request->slot_number)->first();
        $newSlot->is_reserved = true;
        $newSlot->save();

        return response()->json(['message' => 'Reservation updated successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error updating reservation: ' . $e->getMessage()], 500);
    }
}



}
