<?php

use App\Models\Movement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckExpectationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_expectations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('seen')->default('No')->nullable();
            $table->foreignIdFor(\App\Models\CheckPoint::class)->nullable();
            $table->foreignIdFor(Movement::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_expectations');
    }
}
