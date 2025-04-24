<?php

use App\Models\DistrictTagDistributionBatch;
use App\Models\Farm;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamerTagsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('famer_tags_orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Farm::class, 'farm_id')->nullable();
            $table->foreignIdFor(User::class, 'famer_id')->nullable();
            $table->foreignIdFor(Location::class, 'district_id')->nullable();
            $table->foreignIdFor(Location::class, 'sub_county_id')->nullable();
            $table->foreignIdFor(DistrictTagDistributionBatch::class, 'district_tag_distribution_batch_id')->nullable();
            $table->text('farmer_message')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('delivery_date')->nullable();
            $table->string('order_status')->nullable()->default('Pending');
            $table->string('pending_message_sent')->nullable()->default('Pending');
            $table->string('shipping_started_message_sent')->nullable()->default('Pending');
            $table->string('delivered_message_sent')->nullable()->default('Pending');
            $table->integer('total_tags_ordered_quantity')->nullable();
            $table->integer('total_tags_ordered_amount')->nullable();
            $table->integer('total_tags_delivered_quantity')->nullable();
            $table->integer('flutterwave_amount')->nullable();
            $table->string('flutterwave_status')->nullable()->default('Pending');
            $table->text('flutterwave_link')->nullable();
            $table->text('flutterwave_phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('famer_tags_orders');
    }
}
