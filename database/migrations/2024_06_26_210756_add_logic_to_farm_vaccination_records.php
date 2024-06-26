<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogicToFarmVaccinationRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('farm_vaccination_records', function (Blueprint $table) {
            $table->string('duplicate_checked')->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('farm_vaccination_records', function (Blueprint $table) {
            //
        });
    }
}
