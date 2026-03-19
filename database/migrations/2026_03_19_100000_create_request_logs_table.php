<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('request_logs')) {
            return;
        }
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('method', 10);
            $table->string('endpoint', 500);
            $table->integer('response_status');
            $table->decimal('response_time_ms', 10, 2)->default(0);
            $table->decimal('memory_usage_mb', 8, 2)->default(0);
            $table->integer('query_count')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('ip_address');
            $table->index('endpoint');
            $table->index('response_status');
            $table->index('created_at');
            $table->index(['ip_address', 'created_at']);
            $table->index(['endpoint', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_logs');
    }
}
