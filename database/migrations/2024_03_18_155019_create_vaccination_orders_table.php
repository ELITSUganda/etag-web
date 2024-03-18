<?php

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccinationOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccination_orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('order_status')->default('pending');
            $table->string('order_is_paid')->default('Not Paid');
            $table->foreignIdFor(User::class, 'customer_id');
            $table->text('animals_data');
            $table->text('customer_data');
            $table->text('note');
            $table->text('farmer_name');
            $table->text('farme_address');
            $table->foreignIdFor(Farm::class);
            $table->string('phone_number');
            $table->string('phone_number_2');
            $table->string('latitude');
            $table->string('longitude');
            $table->text('payment_link');
            $table->decimal('total_price', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccination_orders');
    }
}
