<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfFarmReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('farm_reports', function (Blueprint $table) {
            $table->text('pdf')->nullable();
            $table->string('pdf_prepared')->nullable()->default('No');
            $table->string('pdf_prepare_date')->nullable();
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
