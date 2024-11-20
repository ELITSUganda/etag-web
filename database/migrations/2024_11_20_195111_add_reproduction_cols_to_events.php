<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReproductionColsToEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('reproduction_type')->nullable();
            $table->text('service_type')->nullable();
            $table->text('service_date')->nullable();
            $table->text('male_id')->nullable();
            $table->text('male_breed')->nullable();
            $table->text('simen_code')->nullable();
            $table->text('inseminator')->nullable();
            $table->text('calving_date')->nullable();
            $table->text('calf_id')->nullable();
            $table->text('calf_sex')->nullable();
            $table->text('calf_weight')->nullable();
            $table->text('wean_date')->nullable();
            $table->text('wean_weight')->nullable();
            $table->text('wean_milk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
}
