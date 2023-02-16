<?php

use App\Models\DrugCategory;
use App\Models\Vet;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrugForSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drug_for_sales', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(DrugCategory::class)->default(1);
            $table->foreignIdFor(Vet::class)->default(1);
            $table->text('name')->nullable();
            $table->text('manufacturer')->nullable();
            $table->text('batch_number')->nullable();
            $table->text('ingredients')->nullable();
            $table->text('expiry_date')->nullable();
            $table->float('original_quantity')->nullable();
            $table->float('current_quantity')->nullable();
            $table->text('selling_price')->nullable();
            $table->text('image')->nullable();
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
        Schema::dropIfExists('drug_for_sales');
    }
}
