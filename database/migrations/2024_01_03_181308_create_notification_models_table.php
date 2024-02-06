<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        Schema::create('notification_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->longText('message')->nullable();
            $table->longText('data')->nullable();
            $table->string('reciever_id')->nullable(); 
            $table->string('status')->nullable()->default('NOT READ');
            $table->string('type')->nullable();
            $table->text('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_models');
    }
}
