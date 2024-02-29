<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['license_plate', 'slot_number'];

    public function parkingSlot()
    {
        // This assumes that 'parking_slot_id' is the foreign key in the 'vehicles' table
        return $this->belongsTo(ParkingSlot::class, 'parking_slot_id');
    }

}


    

