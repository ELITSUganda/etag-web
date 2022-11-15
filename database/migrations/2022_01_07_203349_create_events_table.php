<?php

use App\Models\Animal;
use App\Models\District;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Parish;
use App\Models\SubCounty;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();


            $table->foreignIdFor(Administrator::class)->default(1);
            $table->foreignIdFor(District::class)->default(1);
            $table->foreignIdFor(Location::class,'sub_county_id')->default(1);
            $table->foreignIdFor(Parish::class)->default(1);
            $table->foreignIdFor(Farm::class)->default(1);
            $table->foreignIdFor(Animal::class)->default(1);
            $table->string('type');
            $table->string('approved_by');
            $table->string('detail');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
