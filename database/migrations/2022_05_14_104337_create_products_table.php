<?php

use App\Models\ProductCategory;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->foreignIdFor(Administrator::class)->default(1);
            $table->foreignIdFor(ProductCategory::class)->default(1);
            $table->text('name')->nullable(); 
            $table->text('price')->nullable(); 
            $table->text('quantity')->nullable(); 
            $table->text('thumbnail')->nullable(); 
            $table->text('images')->nullable(); 
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
        Schema::dropIfExists('products');
    }
}
