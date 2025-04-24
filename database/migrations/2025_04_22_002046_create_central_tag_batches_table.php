<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentralTagBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('central_tag_batches', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('batch_name')->nullable();
            $table->text('batch_description')->nullable();
            $table->text('vid_range_start')->nullable();
            $table->text('vid_range_end')->nullable();
            $table->text('eid_range_start')->nullable();
            $table->text('eid_range_end')->nullable();
            $table->text('supplier_name')->nullable();
            $table->text('supplier_contact')->nullable();
            $table->text('supplier_country')->nullable();
            $table->text('supplier_address')->nullable();
            $table->text('supplier_type')->nullable();
            $table->text('purchase_date')->nullable();
            $table->integer('purchase_total_price')->nullable();
            $table->integer('purchase_unit_price')->nullable();
            $table->integer('total_purchase_quantity')->nullable();
            $table->text('purchase_invoice_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('central_tag_batches');
    }
}
