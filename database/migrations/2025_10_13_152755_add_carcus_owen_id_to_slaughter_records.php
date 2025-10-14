<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarcusOwenIdToSlaughterRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slaughter_records', function (Blueprint $table) {
            $table->unsignedBigInteger('carcus_owen_id')->nullable();
            $table->string('carcus_owen_assigned')->nullable()->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slaughter_records', function (Blueprint $table) {
            //
        });
    }
}
