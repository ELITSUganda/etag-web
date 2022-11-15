<?php

use App\Models\DrugCategory;
use App\Models\FormDrugStockApproval;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateFormDrugStockApprovalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        Schema::create('form_drug_stock_approval_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(FormDrugStockApproval::class);
            $table->foreignIdFor(DrugCategory::class);
            $table->integer('quantity');
            $table->text('note');
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_drug_stock_approval_items');
    }
}
