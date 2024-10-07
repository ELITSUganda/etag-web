<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPregnantToPregnantAnimals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pregnant_animals', function (Blueprint $table) {
            $table->string('got_pregnant')->default('Pending')->nullable();
            $table->date('ferilization_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pregnant_animals', function (Blueprint $table) {
            //
        });
    }
}
