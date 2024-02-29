<?php

use App\Models\District;
use App\Models\DistrictVaccineStock;
use App\Models\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccinationProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccination_programs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('title')->nullable();
            $table->foreignIdFor(DistrictVaccineStock::class, 'district_vaccine_stock_id')->nullable();
            $table->foreignIdFor(Location::class, 'district_id');
            $table->foreignIdFor(Location::class, 'sub_district_id');
            $table->foreignIdFor(Location::class, 'parish_id');
            $table->integer('dose_per_animal');
            $table->string('status')->default('Upcoming');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');

            $table->integer('total_target_farms')->nullable()->default(0);
            $table->integer('total_target_animals')->nullable()->default(0);
            $table->integer('total_target_doses')->nullable()->default(0);

            $table->integer('total_vaccinated_farms')->nullable()->default(0);
            $table->integer('total_vaccinated_animals')->nullable()->default(0);
            $table->integer('total_vaccinated_doses')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccination_programs');
    }
}
