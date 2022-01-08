<?php

use App\Models\Movement;
use App\Models\MovementAnimal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementHasMovementAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movement_has_movement_animals', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->foreignIdFor(Movement::class)->default(1);
            $table->foreignIdFor(MovementAnimal::class)->default(1);
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movement_has_movement_animals');
    }
}
