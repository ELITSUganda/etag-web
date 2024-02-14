<?php

use App\Models\Farm;
use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccinationSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccination_schedules', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Farm::class);
            $table->foreignIdFor(Administrator::class, 'applicant_id');
            $table->foreignIdFor(Administrator::class, 'approver_id')->nullable();
            $table->foreignIdFor(Administrator::class, 'veterinary_officer_id')->nullable();
            $table->foreignIdFor(Location::class, 'district_id');
            $table->foreignIdFor(Location::class, 'sub_county_id');
            $table->string('gps_latitute')->nullable();
            $table->date('schedule_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('gps_longitude')->nullable();
            $table->string('status')->default('Pending');
            $table->string('vaccination_type')->default('FMD')->nullable();
            $table->string('applicant_name')->nullable();
            $table->string('applicant_contact')->nullable();
            $table->text('applicant_address')->nullable();
            $table->text('applicant_message')->nullable();
            $table->text('veterinary_officer_message')->nullable();
            $table->text('dvo_message')->nullable();
            $table->text('reason_for_rejection')->nullable();
            $table->text('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccination_schedules');
    }
}
