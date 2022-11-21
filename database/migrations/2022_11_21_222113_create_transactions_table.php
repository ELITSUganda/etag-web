<?php

use App\Models\Farm;
use App\Models\FinanceCategory;
use App\Models\Location;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Location::class, 'district_id')->default(1);
            $table->foreignIdFor(Location::class, 'sub_county_id')->default(1);
            $table->foreignIdFor(Farm::class);
            $table->foreignIdFor(FinanceCategory::class);
            $table->float('amount')->default(0)->nullable();
            $table->boolean('is_income')->default(0)->nullable();
            $table->text('description')->nullable();
            $table->date('transaction_date')->nullable();
        });
    } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
