<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColsInFarmReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('farm_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('farm_reports', 'title')) {
                $table->text('title')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'pdf')) {
                $table->text('pdf')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'pdf_prepared')) {
                $table->text('pdf_prepared')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'pdf_prepare_date')) {
                $table->text('pdf_prepare_date')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'serviced_animals')) {
                $table->text('serviced_animals')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'inseminations')) {
                $table->text('inseminations')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'ai_conception_rate')) {
                $table->text('ai_conception_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'natural_conception_rate')) {
                $table->text('natural_conception_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'ai_abortion_rate')) {
                $table->text('ai_abortion_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'natural_abortion_rate')) {
                $table->text('natural_abortion_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'ai_gestation_length')) {
                $table->text('ai_gestation_length')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'natural_gestation_length')) {
                $table->text('natural_gestation_length')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'total_calves_weaned')) {
                $table->text('total_calves_weaned')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'average_weaning_weight')) {
                $table->text('average_weaning_weight')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'average_weaning_age')) {
                $table->text('average_weaning_age')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'reason_for_animal_abort_disease')) {
                $table->text('reason_for_animal_abort_disease')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'reason_for_animal_abort_accident')) {
                $table->text('reason_for_animal_abort_accident')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'reason_for_animal_abort_other')) {
                $table->text('reason_for_animal_abort_other')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'total_complications_recorded')) {
                $table->text('total_complications_recorded')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'natural_mating')) {
                $table->text('natural_mating')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'got_pregnant_animals')) {
                $table->text('got_pregnant_animals')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'animals_that_produced')) {
                $table->text('animals_that_produced')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'animals_that_aborted')) {
                $table->text('animals_that_aborted')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'failed_get_pregnant_animals')) {
                $table->text('failed_get_pregnant_animals')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'weaned_off_animals')) {
                $table->text('weaned_off_animals')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'calves_that_died')) {
                $table->text('calves_that_died')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'pregnancy_rate')) {
                $table->text('pregnancy_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'calving_rate')) {
                $table->text('calving_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'weaning_rate')) {
                $table->text('weaning_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'abortion_rate')) {
                $table->text('abortion_rate')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'weaning_weight')) {
                $table->text('weaning_weight')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'min_weaning_weight')) {
                $table->text('min_weaning_weight')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'max_weaning_weight')) {
                $table->text('max_weaning_weight')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'pregnancies_in_progress')) {
                $table->text('pregnancies_in_progress')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'animals')) {
                $table->text('animals')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'female_animals')) {
                $table->text('female_animals')->nullable();
            }
            if (!Schema::hasColumn('farm_reports', 'updated_at')) {
                $table->text('updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('farm_reports', function (Blueprint $table) {
            //
        });
    }
}
