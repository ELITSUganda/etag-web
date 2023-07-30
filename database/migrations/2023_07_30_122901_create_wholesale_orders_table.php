<?php

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWholesaleOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wholesale_orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class, 'customer_id')->default(1);
            $table->foreignIdFor(Administrator::class, 'supplier_id')->default(1);
            $table->text('status', 55)->nullable()->default('Pending');
            $table->text('delivery_type')->nullable();
            $table->text('customer_name')->nullable();
            $table->text('customer_contact')->nullable();
            $table->text('customer_address')->nullable();
            $table->text('customer_gps_patitude')->nullable();
            $table->text('customer_gps_longitude')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wholesale_orders');
    }
}
