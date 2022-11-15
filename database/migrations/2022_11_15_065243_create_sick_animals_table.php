<?php

use App\Models\Animal;
use App\Models\Disease;
use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSickAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sick_animals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Animal::class);
            $table->foreignIdFor(Disease::class);
            $table->foreignIdFor(Location::class, 'district_id')->default(1);
            $table->foreignIdFor(Location::class, 'sub_county_id')->default(1);
            $table->string('test_results', '20')->nullable()->default('Positive');
            $table->string('current_results', '20')->nullable()->default('Positive');
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
        Schema::dropIfExists('sick_animals');
    }
}
