<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedIpsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('blocked_ips');
        if (Schema::hasTable('blocked_ips')) {
            return;
        }
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason', 255);
            $table->integer('attempts')->default(0);
            $table->boolean('is_permanent')->default(false);
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            try {
                $table->index('ip_address');
                $table->index('expires_at');
            } catch (\Exception $e) {
                // Handle index creation failure gracefully (e.g., log the error)
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('blocked_ips');
    }
}
