<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancedFieldsToPregnantAnimalsAnimals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pregnant_animals', function (Blueprint $table) {

            $table->text('born_sex')->nullable(); // Sex of the born calf, if applicable
            $table->string('conception_date')->nullable(); // Date of conception
            $table->string('expected_calving_date')->nullable(); // Expected calving date
            $table->integer('gestation_length')->nullable(); // Length of pregnancy in days
            $table->string('did_animal_abort')->nullable(); // Whether the animal aborted or not
            $table->string('reason_for_animal_abort')->nullable(); // Reason for abortion, if applicable
            $table->string('did_animal_conceive')->nullable();
            $table->decimal('calf_birth_weight')->nullable(); // Weight of the calf at birth
            $table->string('pregnancy_outcome')->nullable(); // Pregnancy outcome (successful, aborted, stillborn)
            $table->string('calving_difficulty')->nullable(); // Difficulty of calving , ['easy', 'moderate', 'difficult', 'cesarean']
            $table->integer('postpartum_recovery_time')->nullable(); // Time (days) for cow's recovery after calving
            $table->text('post_calving_complications')->nullable(); // Complications after calving
            $table->integer('total_pregnancies')->nullable(); // Number of pregnancies to date
            $table->string('hormone_use')->nullable(); // Any hormone used (progesterone, oxytocin, etc.)
            $table->text('nutritional_status')->nullable(); // Nutritional condition of the animal during pregnancy
            $table->integer('number_of_calves')->nullable(); // Number of calves born

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
