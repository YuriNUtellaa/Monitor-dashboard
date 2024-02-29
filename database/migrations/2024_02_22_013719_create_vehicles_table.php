<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parking_slot_id')->nullable();
            $table->string('license_plate')->unique();
            $table->timestamps();

            // Ensure that the parking_slots table exists before adding the foreign key constraint.
            if (Schema::hasTable('parking_slots')) {
                $table->foreign('parking_slot_id')->references('id')->on('parking_slots')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
