<?php

use App\Models\DrugDosage;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrugDosageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drug_dosage_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(DrugDosage::class);
            $table->text('drug_name')->nullable();
            $table->text('done_details')->nullable();
            $table->float('quantity')->nullable();
            $table->integer('times_per_day')->nullable();
            $table->integer('times_days')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drug_dosage_items');
    }
}
