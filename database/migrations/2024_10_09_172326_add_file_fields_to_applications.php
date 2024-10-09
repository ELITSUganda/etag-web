<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileFieldsToApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('file_inspection_report')->nullable()->comment('Inspection report');
            $table->string('file_objection_letter')->nullable()->comment('Attach no objection letter/import permit from importing country');
            $table->string('file_laboratory_results')->nullable()->comment('Laboratory results');
            $table->string('file_invoice')->nullable()->comment('Invoice and other supporting documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            //
        });
    }
}
