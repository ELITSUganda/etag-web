<?php

use App\Models\SlaughterDistributionRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateButcherRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('butcher_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(SlaughterDistributionRecord::class);
            $table->text('animal_id')->nullable();
            $table->text('slaughterhouse_id')->nullable();
            $table->text('created_by_id')->nullable();
            $table->text('source_type')->nullable();
            $table->text('source_id')->nullable();
            $table->text('source_name')->nullable();
            $table->text('source_address')->nullable();
            $table->text('source_phone')->nullable();
            $table->text('receiver_type')->nullable();
            $table->text('receiver_id')->nullable();
            $table->text('receiver_name')->nullable();
            $table->text('receiver_address')->nullable();
            $table->text('receiver_phone')->nullable();
            $table->text('lhc')->nullable();
            $table->text('v_id')->nullable();
            $table->text('e_id')->nullable();
            $table->text('animal_owner_id')->nullable();
            $table->text('bar_code')->nullable();
            $table->text('qr_code')->nullable();
            $table->text('post_fat')->nullable();
            $table->text('post_grade')->nullable();
            $table->text('post_animal')->nullable();
            $table->text('post_age')->nullable();
            $table->text('original_weight')->nullable();
            $table->text('current_weight')->nullable();
            $table->text('price')->nullable();
            $table->text('slaughter_date')->nullable();
            $table->string('cut_type')->nullable(); //Prime Cut or Offal Cut
            $table->string('prime_cut_type')->nullable(); //Beef boneless,Beef Stew,Bones,Brisket,Chops,Chuck ribs,Eye Round,Family Steak,Fillet,Fore rib,Leg Cut,Middle rib,Minced meat,Neck,Ossubucco ,Oxtail,Rib eye,Ribs,Rolled loin,Rump ,Shin,Silver side ,Sirloin / Striploin,Staff Meat,T Bone,Thick flank ,Top rib ,Topside /Beef Roast ,Veal Steak
            $table->string('offal_cut_type')->nullable(); //Liver,Heart,Kidneys,Tongue
            $table->string('is_sold')->default('No'); //Yes or No
            $table->text('buyer_id')->nullable();
            $table->text('buyer_name')->nullable();
            $table->text('buyer_phone')->nullable();
            $table->text('buyer_address')->nullable();
            $table->text('sold_date')->nullable();
            $table->text('sold_price')->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('butcher_records');
    }
}
