<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingInfoToFarms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('farms', function (Blueprint $table) {
            $table->string('farm_owner_is_new')->nullable()->default('No');
            $table->string('is_processed')->nullable()->default('No');
            $table->string('has_fmd')->nullable()->default('No');
            $table->string('farm_owner_name')->nullable();
            $table->string('farm_owner_nin')->nullable();
            $table->string('farm_owner_phone_number')->nullable();
            $table->integer('pigs_count')->nullable()->default(0);
            $table->string('local_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('farms', function (Blueprint $table) {
            //
        });
    }
}
