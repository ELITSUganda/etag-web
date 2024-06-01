<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmVaccinationRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farm_vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(\App\Models\Farm::class);
            $table->foreignIdFor(\App\Models\VaccineMainStock::class);
            $table->foreignIdFor(\App\Models\DistrictVaccineStock::class);
            $table->foreignIdFor(\App\Models\District::class);
            $table->foreignIdFor(User::class,'created_by_id');
            $table->foreignIdFor(User::class,'updated_by_id');
            $table->integer('number_of_doses');
            $table->integer('number_of_animals_vaccinated');
            $table->string('vaccination_batch_number');
            $table->text('remarks')->nullable();
            $table->text('gps_location')->nullable();
            $table->string('lhc')->nullable();
            $table->text('farmer_name')->nullable();
            $table->text('farmer_phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('farm_vaccination_records');
    }
}
