<?php

use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Location::class, 'business_subcounty_id');
            $table->foreignIdFor(Location::class, 'business_district_id');
            $table->string('verified')->nullable();
            $table->text('business_name')->nullable();
            $table->text('business_cover_photo')->nullable();
            $table->text('business_logo')->nullable();
            $table->text('business_phone_number_1')->nullable();
            $table->text('business_phone_number_2')->nullable();
            $table->text('business_email')->nullable();
            $table->text('business_address')->nullable();
            $table->text('business_about')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vets');
    }
}
