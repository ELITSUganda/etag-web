<?php

use App\Models\District;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistrictTagDistributionBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('district_tag_distribution_batches', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('batch_name')->nullable();
            $table->text('batch_description')->nullable();
            $table->text('vid_range_start')->nullable();
            $table->text('vid_range_end')->nullable();
            $table->text('eid_range_start')->nullable();
            $table->text('eid_range_end')->nullable();
            $table->text('district_name')->nullable();
            $table->text('district_code')->nullable();
            $table->foreignIdFor(District::class, 'district_id')->nullable();
            //quantity distributed to each district
            $table->integer('total_distributed_quantity')->nullable();
            //quantity remaining
            $table->integer('available_quantity')->nullable();
            //selling price
            $table->integer('selling_price')->nullable();
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
        Schema::dropIfExists('district_tag_distribution_batches');
    }
}
