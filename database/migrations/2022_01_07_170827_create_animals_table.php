<?php

use App\Models\District;
use App\Models\Location;
use App\Models\Parish;
use App\Models\SubCounty;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 

            $table->foreignIdFor(Administrator::class)->default(1);
            $table->foreignIdFor(Location::class,'district_id')->default(1);
            $table->foreignIdFor(Location::class,'sub_county_id')->default(1);
            $table->foreignIdFor(Parish::class)->default(1);
            $table->string('status');
            $table->string('type');

            $table->text('e_id');
            $table->text('v_id');
            $table->text('lhc');
            $table->text('breed');
            $table->text('sex');
            $table->date('dob');
            $table->date('color');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('animals');
    }
}
