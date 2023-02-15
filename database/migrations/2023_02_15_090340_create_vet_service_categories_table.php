<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVetServiceCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vet_service_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('service_name')->nullable();
            $table->text('service_description')->nullable();
            $table->text('photo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vet_service_categories');
    }
}
