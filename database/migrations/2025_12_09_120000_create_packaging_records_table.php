<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagingRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packaging_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            // Foreign Keys (NO CASCADING - handled in application)
            $table->unsignedBigInteger('slaughter_record_id');
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->unsignedBigInteger('slaughter_distribution_record_id')->nullable();
            
            // Animal/Carcass Information (denormalized for quick access)
            $table->string('v_id')->nullable();
            $table->string('e_id')->nullable();
            $table->string('lhc')->nullable();
            $table->string('breed')->nullable();
            $table->string('sex', 50)->nullable();
            
            // Barcode Linking (from slaughter_distribution_record)
            $table->string('barcode')->nullable();
            $table->text('qr_code_link')->nullable();
            
            // Package Type
            $table->enum('package_type', ['Prime Cut', 'Offal']);
            
            // PRIME CUT WEIGHTS (in kg, nullable - only for Prime Cut type)
            $table->decimal('beef_boneless', 10, 2)->nullable()->default(0);
            $table->decimal('beef_stew', 10, 2)->nullable()->default(0);
            $table->decimal('bones', 10, 2)->nullable()->default(0);
            $table->decimal('brisket', 10, 2)->nullable()->default(0);
            $table->decimal('chops', 10, 2)->nullable()->default(0);
            $table->decimal('chuck_ribs', 10, 2)->nullable()->default(0);
            $table->decimal('family_steak', 10, 2)->nullable()->default(0);
            $table->decimal('fore_rib', 10, 2)->nullable()->default(0);
            $table->decimal('leg_cut', 10, 2)->nullable()->default(0);
            $table->decimal('middle_rib', 10, 2)->nullable()->default(0);
            $table->decimal('minced_meat', 10, 2)->nullable()->default(0);
            $table->decimal('neck', 10, 2)->nullable()->default(0);
            $table->decimal('ossubucco', 10, 2)->nullable()->default(0);
            $table->decimal('oxtail', 10, 2)->nullable()->default(0);
            $table->decimal('ribs', 10, 2)->nullable()->default(0);
            $table->decimal('shin', 10, 2)->nullable()->default(0);
            $table->decimal('staff_meat', 10, 2)->nullable()->default(0);
            $table->decimal('thick_flank', 10, 2)->nullable()->default(0);
            $table->decimal('fillet', 10, 2)->nullable()->default(0);
            $table->decimal('rib_eye', 10, 2)->nullable()->default(0);
            $table->decimal('rolled_loin', 10, 2)->nullable()->default(0);
            $table->decimal('rump', 10, 2)->nullable()->default(0);
            $table->decimal('silver_side', 10, 2)->nullable()->default(0);
            $table->decimal('sirloin_striploin', 10, 2)->nullable()->default(0);
            $table->decimal('t_bone', 10, 2)->nullable()->default(0);
            $table->decimal('topside_beef_roast', 10, 2)->nullable()->default(0);
            $table->decimal('veal_steak', 10, 2)->nullable()->default(0);
            
            // OFFAL WEIGHTS (in kg, nullable - only for Offal type)
            $table->decimal('heart', 10, 2)->nullable()->default(0);
            $table->decimal('kidneys', 10, 2)->nullable()->default(0);
            $table->decimal('liver', 10, 2)->nullable()->default(0);
            $table->decimal('tongue', 10, 2)->nullable()->default(0);
            $table->decimal('lungs', 10, 2)->nullable()->default(0);
            $table->decimal('tripe', 10, 2)->nullable()->default(0);
            $table->decimal('tail', 10, 2)->nullable()->default(0);
            $table->decimal('head', 10, 2)->nullable()->default(0);
            $table->decimal('feet', 10, 2)->nullable()->default(0);
            $table->decimal('testicles', 10, 2)->nullable()->default(0);
            
            // Calculated Fields
            $table->decimal('total_weight', 10, 2)->default(0);
            
            // Dates
            $table->date('packaging_date');
            $table->date('expiry_date');
            
            // User Tracking
            $table->unsignedBigInteger('packaged_by');
            
            // PDF Generation Status
            $table->enum('pdf_generated', ['Yes', 'No'])->default('No');
            $table->string('pdf_file_path', 500)->nullable();
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->enum('status', ['Active', 'Sold', 'Expired', 'Discarded'])->default('Active');
            
            // Indexes for performance
            $table->index('slaughter_record_id');
            $table->index('animal_id');
            $table->index('package_type');
            $table->index('packaging_date');
            $table->index('expiry_date');
            $table->index('barcode');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packaging_records');
    }
}
