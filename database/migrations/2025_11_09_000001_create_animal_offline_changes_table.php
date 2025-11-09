<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimalOfflineChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('animal_offline_changes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            // Core identification
            $table->string('local_id')->unique()->index(); // Unique identifier from mobile
            
            // Change tracking
            $table->text('animal_ids'); // JSON array of animal IDs affected
            $table->string('change_type'); // e.g. change_group, change_farm, change_status, etc.
            $table->text('change_data'); // JSON data for the change
            
            // User tracking
            $table->integer('changed_by_user_id')->index(); // User who made the change
            $table->integer('processing_by_user_id')->nullable(); // User processing the change
            
            // Status tracking
            $table->string('status')->default('pending'); // pending, processing, synced, failed
            $table->text('error_message')->nullable(); // Error if sync failed
            
            // Timestamps
            $table->integer('timestamp'); // When change was made on mobile
            $table->integer('processed_at')->nullable(); // When successfully processed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('animal_offline_changes');
    }
}
