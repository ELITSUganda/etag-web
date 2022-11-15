<?php
 
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Location;

class CreateFormDrugSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       /*  Schema::create('form_drug_sellers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->text('name')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('nin')->nullable();
            $table->text('license')->nullable();
            $table->text('address')->nullable();
            $table->foreignIdFor(Location::class,'sub_county_id')->default(1);
            $table->foreignIdFor(Administrator::class, 'applicant_id')->default(1);
            $table->foreignIdFor(Administrator::class, 'approved_by')->default(1);
            $table->text('type')->nullable();
            $table->text('details')->nullable();
            $table->integer('status')->nullable()->default(0);

        }); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_drug_sellers');
    }
}
