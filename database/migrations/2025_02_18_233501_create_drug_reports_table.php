<?php

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrugReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drug_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(User::class, 'owner_id')->nullable();
            $table->foreignIdFor(Farm::class, 'farm_id')->nullable();
            $table->string('period_type')->nullable();
            $table->string('period')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->bigInteger('total_cost')->nullable();
            $table->string('pdf_generated')->nullable();
            $table->string('pdf_path')->nullable();
            $table->longText('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drug_reports');
    }
}
