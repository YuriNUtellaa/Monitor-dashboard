<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    public function up()
{
    Schema::create('reservations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('parking_slot_id')->nullable();
        $table->string('license_plate')->unique();
        $table->timestamps();

        $table->foreign('parking_slot_id')
              ->references('id')->on('parking_slots')
              ->onDelete('set null');
    });
}

public function down()
{
    Schema::dropIfExists('reservations');
}
}
