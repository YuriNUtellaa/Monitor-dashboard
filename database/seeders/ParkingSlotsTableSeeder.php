<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingSlot;

class ParkingSlotsTableSeeder extends Seeder
{
    public function run()
    {

        
        for ($i = 1; $i <= 50; $i++) {
            ParkingSlot::create([
                'slot_number' => $i,
                'is_occupied' => false,
            ]);
        }
    }
}
