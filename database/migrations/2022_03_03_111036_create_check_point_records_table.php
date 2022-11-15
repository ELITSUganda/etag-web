<?php

use App\Models\CheckPoint;
use App\Models\Movement;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckPointRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     *  
     * 
     * @return void
     */
    public function up()
    {
       /*  Schema::create('check_point_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(CheckPoint::class); 
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Movement::class);
            $table->text('time')->nullable(); 
            $table->text('latitude')->nullable(); 
            $table->text('longitude')->nullable(); 
            $table->text('on_permit')->nullable();
            $table->text('checked')->nullable();
            $table->text('success')->nullable();
            $table->text('failed')->nullable();
            $table->text('details')->nullable();

        }); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_point_records');
    }
}
