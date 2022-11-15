<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->text('name')->nullable(); 
            $table->text('type')->nullable(); 
            $table->text('details')->nullable();
            $table->integer('parent')->nullable()->default(0); 

        }); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
