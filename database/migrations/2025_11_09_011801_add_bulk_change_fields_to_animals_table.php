<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBulkChangeFieldsToAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('animals', function (Blueprint $table) {
            // Add new fields for bulk changes (parent_id already exists)
            $table->decimal('current_worth', 15, 2)->nullable()->after('type');
            $table->string('conception_method')->nullable()->after('sex');
            
            // Check if sire_id doesn't exist before adding
            if (!Schema::hasColumn('animals', 'sire_id')) {
                $table->unsignedBigInteger('sire_id')->nullable()->after('conception_method');
                // Add foreign key constraint for sire_id
                $table->foreign('sire_id')->references('id')->on('animals')->onDelete('set null');
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
        Schema::table('animals', function (Blueprint $table) {
            // Drop foreign key if exists
            if (Schema::hasColumn('animals', 'sire_id')) {
                $table->dropForeign(['sire_id']);
                $table->dropColumn('sire_id');
            }
            
            // Drop other columns
            $table->dropColumn(['current_worth', 'conception_method']);
        });
    }
}
