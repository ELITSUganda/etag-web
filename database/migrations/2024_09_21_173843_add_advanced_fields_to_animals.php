<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancedFieldsToAnimals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->string('has_produced_before')->nullable()->default('No');
            $table->integer('age_at_first_calving')->nullable();
            $table->float('weight_at_first_calving')->nullable();

            $table->string('has_been_inseminated')->nullable()->default('No');
            $table->integer('age_at_first_insemination')->nullable();
            $table->float('weight_at_first_insemination')->nullable();
            $table->integer('inter_calving_interval')->nullable();

            $table->float('calf_mortality_rate')->nullable();
            $table->float('weight_gain_per_day')->nullable();
            $table->float('number_of_isms_per_conception')->nullable();

            $table->string('is_a_calf')->nullable()->default('No');
            $table->string('is_weaned_off')->nullable()->default('No');
            $table->float('wean_off_weight')->nullable();
            $table->float('wean_off_age')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('animals', function (Blueprint $table) {
            //
        });
    }
}
