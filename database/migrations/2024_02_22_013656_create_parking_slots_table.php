<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('slot_number')->unique();
            $table->boolean('is_occupied')->default(false);
            $table->string('license_plate')->nullable();
            $table->timestamp('parked_at')->nullable();
            $table->boolean('is_reserved')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_slots');

    }
};
