<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreInfoToNotificationModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_models', function (Blueprint $table) { 
            $table->integer('animal_id')->nullable();
            $table->text('animal_ids')->nullable();
            $table->integer('event_id')->nullable();
            $table->text('event_ids')->nullable();
            $table->integer('notification_id')->nullable();
            $table->text('notification_ids')->nullable();
            $table->integer('session_id')->nullable();
            $table->text('session_ids')->nullable();
            $table->text('url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_models', function (Blueprint $table) {
            //
        });
    }
}
