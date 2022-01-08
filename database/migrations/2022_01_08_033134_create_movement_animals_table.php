<?php

use App\Models\Animal;
use App\Models\Movement;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movement_animals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Movement::class)->default(1);
            $table->foreignIdFor(Animal::class)->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movement_animals');
    }
}
