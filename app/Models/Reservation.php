<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;


    protected $fillable = ['license_plate', 'parking_slot_id'];

    public function parkingSlot()
    {
        return $this->belongsTo(ParkingSlot::class);
    }

}
