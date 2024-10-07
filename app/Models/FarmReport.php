<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class FarmReport extends Model
{
    use HasFactory;

    //boot created 
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model = FarmReport::do_process($model);
        });

        //updating 
        static::updating(function ($model) {
            $model = FarmReport::do_process($model);
        });
    }

    public static function do_process($r)
    {
        $start_date = Carbon::parse($r->start_date);
        $end_date = Carbon::parse($r->end_date);
        $farm = Farm::find($r->farm_id);
        $r->serviced_animals = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();
        $r->got_pregnant_animals = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'original_status' => 'Pregnant'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();
        $r->failed_get_pregnant_animals = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'original_status' => 'Failed'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $got_pregnant_animals_total = $r->got_pregnant_animals->count();
        $failed_get_pregnant_animals_total = $r->failed_get_pregnant_animals->count();


        //failed_get_pregnant_animals
        dd($r->failed_get_pregnant_animals);

        /* 
    "id" => 168
    "created_at" => "2024-10-07 23:00:50"
    "updated_at" => "2024-10-07 23:00:50"
    "administrator_id" => 709
    "animal_id" => 19888
    "district_id" => 1
    "sub_county_id" => 1
    "original_status" => "Pregnant"
    "current_status" => "Pregnant"
    "fertilization_method" => "AI"
    "expected_sex" => "Female"
    "details" => "Some details...."
    "pregnancy_check_method" => "8"
    "born_sex" => "Male"
    "conception_date" => "2024-06-11"
    "expected_calving_date" => "2025-01-01"
    "gestation_length" => 285
    "did_animal_abort" => "Yes"
    "reason_for_animal_abort" => "Disease"
    "did_animal_conceive" => "Yes"
    "calf_birth_weight" => "24.00"
    "pregnancy_outcome" => "1"
    "calving_difficulty" => "Normal"
    "postpartum_recovery_time" => null
    "post_calving_complications" => null
    "total_pregnancies" => null
    "hormone_use" => null
    "nutritional_status" => null
    "number_of_calves" => 1
    "born_date" => "2025-09-04"
    "calf_id" => "19888"
    "total_calving_milk" => 22
    "is_weaned_off" => "No"
    "weaning_date" => "2027-03-04"
    "weaning_weight" => "50"
    "weaning_age" => "49"
    "got_pregnant" => "Yes"
    "ferilization_date" => "2024-06-11"
    "farm_id" => 128
*/
        dd($r->serviced_animals[0]);
        dd($start_date);
        $r->title = "Farm Report for the period $start_date to $end_date.";
        $r->animals = Animal::where([
            'farm_id' => $r->farm_id,
            'type' => 'Cattle'
        ])->get();
        $r->female_animals = Animal::where([
            'farm_id' => $r->farm_id,
            'sex' => 'Male'
        ])->get();

        #Pregnancy Rate
        $r->pregnancy_rate = 0;

        return view('farm-report', [
            'report' => $r
        ]);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('farm-report', [
            'report' => $r
        ]));
        return $pdf->stream($r->id . " - " . $r->title . ".pdf");

        dd($r->title);

        dd($r);
        return $r;
    }


    //belongs to farm
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
