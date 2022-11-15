<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        Schema::create('product_orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->boolean('status')->nullable()->default(null);
            $table->integer('customer_id')->nullable()->default(null);
            $table->integer('product_id')->nullable()->default(null);
            $table->text('product_data')->nullable()->default(null);
            $table->text('customer_data')->nullable()->default(null); 
            $table->text('address')->nullable()->default(null); 
            $table->text('note')->nullable()->default(null); 

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_orders');
    }
}
