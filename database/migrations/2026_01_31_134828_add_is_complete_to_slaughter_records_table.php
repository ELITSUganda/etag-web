<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCompleteToSlaughterRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slaughter_records', function (Blueprint $table) {
            if (!Schema::hasColumn('slaughter_records', 'is_complete')) {
                $table->string('is_complete')->default('No')->nullable()->after('breed');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slaughter_records', function (Blueprint $table) {
            if (Schema::hasColumn('slaughter_records', 'is_complete')) {
                $table->dropColumn('is_complete');
            }
        });
    }
}
