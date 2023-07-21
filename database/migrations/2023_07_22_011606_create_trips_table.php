<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('transporter_id')->nullable();
            $table->string('transporter_name')->nullable();
            $table->string('transporter_nin')->nullable();
            $table->string('transporter_phone_number_1')->nullable();
            $table->string('transporter_phone_number_2')->nullable();
            $table->text('transporter_photo')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_registration_number')->nullable();
            $table->string('has_trip_started')->nullable();
            $table->string('has_trip_ended')->nullable();
            $table->string('trip_start_time')->nullable();
            $table->string('trip_end_time')->nullable();
            $table->string('start_latitude')->nullable();
            $table->string('start_longitude')->nullable();
            $table->string('current_latitude')->nullable();
            $table->string('current_longitude')->nullable();
            $table->string('trip_destination_type')->nullable();
            $table->integer('trip_destination_id')->nullable();
            $table->string('trip_destination_latitude')->nullable();
            $table->string('trip_destination_longitude')->nullable();
            $table->string('trip_destination_address')->nullable();
            $table->string('trip_destination_phone_number')->nullable();
            $table->string('trip_destination_contact_person')->nullable();
            $table->text('trip_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
