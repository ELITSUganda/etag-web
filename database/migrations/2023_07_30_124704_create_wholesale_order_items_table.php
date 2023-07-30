<?php

use App\Models\WholesaleDrugStock;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWholesaleOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wholesale_order_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(WholesaleDrugStock::class);
            $table->integer('quantity');
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wholesale_order_items');
    }
}
