<?php

use App\Models\DrugCategory;
use App\Models\FormDrugSeller;
use App\Models\SubCounty;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrugStockBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drug_stock_batches', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(DrugCategory::class);
            $table->foreignIdFor(SubCounty::class);
            $table->integer('source_id');
            $table->text('source_text');
            $table->text('name');
            $table->text('manufacturer');
            $table->text('batch_number');
            $table->text('ingredients');
            $table->text('expiry_date');
            $table->float('original_quantity');
            $table->float('current_quantity');
            $table->text('selling_price');
            $table->text('image');
            $table->text('last_activity');
            $table->text('details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drug_stock_batches');
    }
}
