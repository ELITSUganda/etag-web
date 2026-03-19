<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemAlertsTable extends Migration
{
    public function up()
    {
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->enum('severity', ['info', 'warning', 'critical']);
            $table->string('message', 500);
            $table->json('payload')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('severity');
            $table->index('read_at');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_alerts');
    }
}
