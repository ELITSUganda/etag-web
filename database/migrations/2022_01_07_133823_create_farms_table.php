<?php

use App\Models\District;
use App\Models\Parish;
use App\Models\SubCounty;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class)->default(1);
            $table->foreignIdFor(District::class)->default(1);
            $table->foreignIdFor(SubCounty::class)->default(1);
            $table->foreignIdFor(Parish::class)->default(1);
            $table->text('farm_type');
            $table->text('holding_code');
            $table->text('size');
            $table->text('latitude');
            $table->text('longitude');
            $table->text('dfm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('farms');
    }
}
