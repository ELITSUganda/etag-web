<?php

use App\Models\Location;
use App\Models\SubCounty;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::dropIfExists('check_points');
       /*  Schema::create('check_points', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name')->nullable(); 
            $table->text('details')->nullable();
            $table->text('longitude')->nullable();
            $table->text('latitube')->nullable();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Location::class,'sub_county_id')->default(1);
        }); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_points');
    }
}
