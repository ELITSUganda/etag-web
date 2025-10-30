<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Encore\Admin\Auth\Database\Administrator;

return new class extends Migration
{
    /**
     * Run the migrations - Label Printing Tasks Table
     * 
     * This table tracks label generation tasks for butcher records.
     * Each task can generate labels for one or multiple butcher records
     * with customizable templates and content options.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('label_printing_tasks', function (Blueprint $table) {
            $table->id();
            
            // Task identification
            $table->string('task_number')->unique()->nullable(); // e.g., LPT-20251030-001
            
            // Butcher records to print labels for (JSON array of IDs)
            $table->json('butcher_record_ids')->nullable();
            
            // Template and configuration
            $table->string('template_type')->default('Standard'); // Compact, Standard, Detailed, Premium
            $table->string('include_qr')->default('Yes'); // Yes/No
            $table->string('include_barcode')->default('Yes'); // Yes/No
            $table->string('include_company_info')->default('Yes'); // Yes/No - company name, contact
            $table->string('include_animal_info')->default('Yes'); // Yes/No - breed, age, weight etc
            
            // Label information
            $table->string('label_size')->default('A6'); // A6 (105x148mm) for space efficiency
            $table->integer('labels_per_page')->default(4); // How many labels fit per page
            
            // Generation tracking
            $table->integer('total_labels')->default(0); // Total labels requested
            $table->integer('generated_labels')->default(0); // Labels generated so far
            $table->string('status')->default('Pending'); // Pending, Processing, Completed, Failed
            
            // PDF storage
            $table->text('pdf_path')->nullable(); // Storage path for generated PDF
            $table->string('pdf_size')->nullable(); // File size in human readable format
            
            // Error tracking
            $table->text('error_message')->nullable();
            
            // Timestamps for generation
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Creator tracking
            $table->foreignIdFor(Administrator::class, 'created_by_id')->nullable();
            
            // Standard timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
            $table->index('created_by_id');
            $table->index('task_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('label_printing_tasks');
    }
};
