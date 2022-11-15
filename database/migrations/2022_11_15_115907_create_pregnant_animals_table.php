<?php

use App\Models\Animal;
use App\Models\Disease;
use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePregnantAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pregnant_animals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Animal::class);
            $table->foreignIdFor(Disease::class);
            $table->foreignIdFor(Location::class, 'district_id')->default(1);
            $table->foreignIdFor(Location::class, 'sub_county_id')->default(1);
            $table->string('original_status', '50')->nullable()->default('Pregnant');
            $table->string('current_status', '50')->nullable()->default('Pregnant');
            $table->string('fertilization_method', '100')->nullable();
            $table->string('expected_sex', '100')->nullable();
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
        Schema::dropIfExists('pregnant_animals');
    }
}
