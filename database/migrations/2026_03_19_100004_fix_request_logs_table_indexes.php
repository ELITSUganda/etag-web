<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixRequestLogsTableIndexes extends Migration
{
    public function up()
    {
        // Drop the partially-created table from the failed migration
        Schema::dropIfExists('request_logs');

        // Recreate with prefix indexes that respect MySQL key length limits
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

            // Simple indexes (these fit within key length limits)
            $table->index('ip_address');
            $table->index('response_status');
            $table->index('created_at');
            $table->index(['ip_address', 'created_at']);
        });

        // Add prefix indexes for the long `endpoint` column via raw SQL
        // 191 chars * 4 bytes (utf8mb4) = 764 bytes — safely under the 767/1000 byte limit
        DB::statement('ALTER TABLE `request_logs` ADD INDEX `request_logs_endpoint_index` (`endpoint`(191))');
        DB::statement('ALTER TABLE `request_logs` ADD INDEX `request_logs_endpoint_created_at_index` (`endpoint`(191), `created_at`)');
    }

    public function down()
    {
        Schema::dropIfExists('request_logs');
    }
}
