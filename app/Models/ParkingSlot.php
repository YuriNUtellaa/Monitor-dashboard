<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{


    use HasFactory;

    protected $fillable = ['slot_number', 'is_occupied', 'license_plate', 'parked_at', 'left_at'];
    protected $casts = [
        'parked_at' => 'datetime',
        'left_at' => 'datetime',
    ];
    protected $dates = ['parked_at', 'left_at'];

    public function parkingSlot()
{
    return $this->belongsTo(ParkingSlot::class, 'slot_number', 'slot_number');
}

public function vehicle()
{
    // This assumes that a parking slot can only have one vehicle at a time
    return $this->hasOne(Vehicle::class, 'parking_slot_id');
}

public function reservation()
{
    return $this->hasOne(Reservation::class);
}
}


