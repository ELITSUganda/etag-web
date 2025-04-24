<?php

use App\Models\CentralTagBatch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchIdDistrictTagDistributionBatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('district_tag_distribution_batches', function (Blueprint $table) {
            $table->foreignIdFor(CentralTagBatch::class, 'central_tag_batch_id')->nullable();
            $table->integer('total_tags_distributed_to_farms_quantity')->nullable();
            $table->integer('total_tags_distributed_to_farms_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('district_tag_distribution_batches', function (Blueprint $table) {
            //
        });
    }
}
