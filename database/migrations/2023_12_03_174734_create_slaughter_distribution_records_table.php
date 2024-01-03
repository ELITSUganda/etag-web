<?php

use App\Models\Animal;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Monolog\Handler\Slack\SlackRecord;

class CreateSlaughterDistributionRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return; 
        Schema::create('slaughter_distribution_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Animal::class);
            $table->integer('slaughterhouse_id');
            $table->integer('created_by_id');
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();
            $table->string('source_name')->nullable();
            $table->text('source_address')->nullable();
            $table->text('source_phone')->nullable();

            $table->string('receiver_type')->nullable();
            $table->string('receiver_id')->nullable();
            $table->text('receiver_name')->nullable();
            $table->text('receiver_address')->nullable();
            $table->string('receiver_phone')->nullable();

            $table->string('lhc')->nullable();
            $table->string('v_id')->nullable();
            $table->string('e_id')->nullable();
            $table->integer('animal_owner_id')->nullable();
            $table->text('bar_code')->nullable();
            $table->text('qr_code')->nullable();
            $table->text('post_fat')->nullable();
            $table->text('post_grade')->nullable();
            $table->text('post_animal')->nullable();
            $table->text('post_age')->nullable();
            $table->string('original_weight')->nullable();
            $table->string('current_weight')->nullable();
            $table->string('price')->nullable();
            $table->string('slaughter_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slaughter_distribution_records');
    }
}
