<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRangeToFamerTagsOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('famer_tags_orders', function (Blueprint $table) {
            $table->text('vid_range_start')->nullable();
            $table->text('vid_range_end')->nullable();
            $table->text('eid_range_start')->nullable();
            $table->text('eid_range_end')->nullable();
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
