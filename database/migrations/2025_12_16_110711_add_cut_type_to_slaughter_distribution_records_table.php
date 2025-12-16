<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCutTypeToSlaughterDistributionRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slaughter_distribution_records', function (Blueprint $table) {
            $table->string('cut_type')->nullable()->after('slaughter_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slaughter_distribution_records', function (Blueprint $table) {
            $table->dropColumn('cut_type');
        });
    }
}
