<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(User::class)->nullable();
            $table->string('enable_sms_notification')->nullable()->default('No');
            $table->string('sms_phone_number')->nullable();
            $table->string('paid_for_sms_notification')->nullable()->default('No');
            $table->string('enable_email_notification')->nullable()->default('No');
            $table->text('email_address')->nullable();
            $table->string('farm_worker_can_view_data')->nullable()->default('Yes');
            $table->string('farm_worker_can_edit_data')->nullable()->default('Yes');
            $table->string('farm_worker_can_add_data')->nullable()->default('Yes');
            $table->string('farm_worker_can_delete_data')->nullable()->default('Yes');
            $table->string('enable_automated_reports')->nullable()->default('No');
            $table->string('report_frequency')->nullable()->default('Monthly');
            $table->string('enable_milk_production_report')->nullable()->default('No');
            $table->string('enable_animal_health_report')->nullable()->default('No');
            $table->string('enable_animal_sales_report')->nullable()->default('No');
            $table->string('enable_animal_birth_report')->nullable()->default('No');
            $table->string('enable_animal_death_report')->nullable()->default('No');
            $table->string('enable_animal_movement_report')->nullable()->default('No');
            $table->string('enable_animal_treatment_report')->nullable()->default('No');
            $table->string('enable_animal_vaccination_report')->nullable()->default('No');
            $table->string('enable_animal_weighing_report')->nullable()->default('No');
            $table->string('enable_animal_tagging_report')->nullable()->default('No');
            $table->string('enable_animal_breeding_report')->nullable()->default('No');
            $table->string('enable_financial_report')->nullable()->default('No');
            $table->string('enable_milk_sales_report')->nullable()->default('No'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_settings');
    }
}
