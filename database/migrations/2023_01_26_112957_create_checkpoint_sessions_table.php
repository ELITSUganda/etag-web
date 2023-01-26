<?php

use App\Models\CheckPoint;
use App\Models\Movement;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckpointSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkpoint_sessions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class, 'checked_by');
            $table->foreignIdFor(Movement::class);
            $table->foreignIdFor(CheckPoint::class);
            $table->text('animals_expected')->nullable();
            $table->text('animals_checked')->nullable();
            $table->text('animals_found')->nullable();
            $table->text('animals_missed')->nullable();
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
        Schema::dropIfExists('checkpoint_sessions');
    }
}
