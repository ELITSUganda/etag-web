<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneTypeToFamerTagsOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('famer_tags_orders', function (Blueprint $table) {
            $table->string('phone_number_type')->nullable(); 
            $table->string('name')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('famer_tags_orders', function (Blueprint $table) {
            //
        });
    }
}
