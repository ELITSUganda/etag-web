<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlaughterRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slaughter_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->text('lhc')->nullable();
            $table->text('v_id')->nullable();
            $table->text('e_id')->nullable();
            $table->text('breed')->nullable();
            $table->text('sex')->nullable();
            $table->text('dob')->nullable();
            $table->text('fmd')->nullable();
            $table->text('destination_slaughter_house')->nullable();


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
        Schema::dropIfExists('slaughter_records');
    }
}
