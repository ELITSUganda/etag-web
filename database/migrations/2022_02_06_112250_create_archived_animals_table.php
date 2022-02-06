<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archived_animals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('owner')->nullable();
            $table->text('district')->nullable();
            $table->text('sub_county')->nullable();
            $table->text('type')->nullable();
            $table->text('e_id')->nullable();
            $table->text('v_id')->nullable();
            $table->text('lhc')->nullable();
            $table->text('breed')->nullable();
            $table->text('sex')->nullable();
            $table->text('dob')->nullable();
            $table->text('last_event')->nullable();
            $table->text('events')->nullable();
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
        Schema::dropIfExists('archived_animals');
    }
}
