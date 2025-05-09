<?php

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserHasFarmPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_has_farm_permissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Farm::class)->nullable();
            $table->foreignIdFor(User::class)->nullable();
            $table->text('permissions')->nullable();
            $table->text('name')->nullable();
            $table->text('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_has_farm_permissions');
    }
}
