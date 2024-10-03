<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBornInformationAnimals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pregnant_animals', function (Blueprint $table) {
            //calving information
            // born date
            $table->string('born_date')->nullable();
            // calf id
            $table->string('calf_id')->nullable();
            // total calving milk
            $table->integer('total_calving_milk')->nullable();
            // is_weaned_off
            $table->string('is_weaned_off')->nullable();
            // weaning date
            $table->string('weaning_date')->nullable();
            // weaning weight
            $table->string('weaning_weight')->nullable();
            // weaning age
            $table->string('weaning_age')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pregnant_animals', function (Blueprint $table) {
            //
        });
    }
}
