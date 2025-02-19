<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

use function PHPUnit\Framework\fileExists;

class FarmReport extends Model
{
    use HasFactory;

    //boot created 
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            /* $model = FarmReport::do_process($model);
            if (isset($model->inseminations)) {
                unset($model->inseminations);
            } */
        });

        //updating 
        static::updating(function ($model) {
            /* $model = FarmReport::do_process($model);
            if (isset($model->serviced_animals)) {
                unset($model->serviced_animals);
            }
            if (isset($model->inseminations)) {
                unset($model->inseminations);
            } */
            //unset animals
        });
    }

    public static function do_process($r)
    {
        $r->pdf_prepared = 'Yes';
        $r->pdf_prepare_date = Carbon::now();
        $r->save();
        $start_date = Carbon::parse($r->start_date);
        $end_date = Carbon::parse($r->end_date);
        $farm = Farm::find($r->farm_id);
        $r->serviced_animals = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $r->inseminations = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'fertilization_method' => 'AI'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $r->ai_conception_rate = 0;
        if ($r->inseminations->count() > 0) {
            $r->ai_conception_rate = ($r->inseminations->where('did_animal_conceive', 'Yes')->count() / $r->inseminations->count()) * 100;
            $r->ai_conception_rate = round($r->ai_conception_rate, 2);
        }
        $r->natural_conception_rate = 0;
        if ($r->serviced_animals->count() > 0) {
            $r->natural_conception_rate = ($r->serviced_animals->where('did_animal_conceive', 'Yes')->count() / $r->serviced_animals->count()) * 100;
            $r->natural_conception_rate = round($r->natural_conception_rate, 2);
        }

        //ai_abortion_rate
        $r->ai_abortion_rate = 0;
        if ($r->inseminations->count() > 0) {
            $r->ai_abortion_rate = ($r->inseminations->where('did_animal_abort', 'Yes')->count() / $r->inseminations->count()) * 100;
            $r->ai_abortion_rate = round($r->ai_abortion_rate, 2);
        }

        //natural_abortion_rate
        $r->natural_abortion_rate = 0;
        if ($r->serviced_animals->count() > 0) {
            $r->natural_abortion_rate = ($r->serviced_animals->where('did_animal_abort', 'Yes')->count() / $r->serviced_animals->count()) * 100;
            $r->natural_abortion_rate = round($r->natural_abortion_rate, 2);
        }

        //ai_gestation_length
        $r->ai_gestation_length = 0;
        if ($r->inseminations->count() > 0) {
            $r->ai_gestation_length = $r->inseminations
                ->where('did_animal_conceive', 'Yes')
                ->sum('gestation_length') / $r->inseminations->count();
            $r->ai_gestation_length = round($r->ai_gestation_length, 2);
        }

        //natural_gestation_length
        $r->natural_gestation_length = 0;
        if ($r->serviced_animals->count() > 0) {
            $r->natural_gestation_length = $r->serviced_animals
                ->where('did_animal_conceive', 'Yes')
                ->sum('gestation_length') / $r->serviced_animals->count();
            $r->natural_gestation_length = round($r->natural_gestation_length, 2);
        }

        //Total Calves Weaned
        $r->total_calves_weaned = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'is_weaned_off' => 'Yes'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        //Average Weaning Weight
        $r->average_weaning_weight = 0;
        if ($r->total_calves_weaned->count() > 0) {
            $r->average_weaning_weight = $r->total_calves_weaned->sum('weaning_weight') / $r->total_calves_weaned->count();
            $r->average_weaning_weight = round($r->average_weaning_weight, 2);
        }

        //•	Average Weaning Age
        $r->average_weaning_age = 0;
        if ($r->total_calves_weaned->count() > 0) {
            $r->average_weaning_age = $r->total_calves_weaned->sum('weaning_age') / $r->total_calves_weaned->count();
            $r->average_weaning_age = round($r->average_weaning_age, 2);
        }

        //reason_for_animal_abort_disease
        $r->reason_for_animal_abort_disease = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'did_animal_abort' => 'Yes',
                'reason_for_animal_abort' => 'Disease'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();
        //Accident
        $r->reason_for_animal_abort_accident = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'did_animal_abort' => 'Yes',
                'reason_for_animal_abort' => 'Accident'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        //Other
        $r->reason_for_animal_abort_other = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'did_animal_abort' => 'Yes',
                'reason_for_animal_abort' => 'Other'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        //•	Total Complications Recorded
        $r->total_complications_recorded = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'post_calving_complications' => 'Yes'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        //natural mating
        $r->natural_mating = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'fertilization_method' => 'Mating'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $r->got_pregnant_animals = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'original_status' => 'Pregnant'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $r->animals_that_produced  = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'original_status' => 'Pregnant',
                'did_animal_conceive' => 'Yes'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $r->animals_that_aborted  = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'original_status' => 'Pregnant',
                'did_animal_abort' => 'Yes'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $r->failed_get_pregnant_animals = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'got_pregnant' => 'No'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();
        $r->weaned_off_animals = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'is_weaned_off' => 'Yes'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();

        $r->calves_that_died = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'is_weaned_off' => 'Death'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();


        $got_pregnant_animals_total = $r->got_pregnant_animals->count();
        $failed_get_pregnant_animals_total = $r->failed_get_pregnant_animals->count();
        $pregnancy_rate = 0;

        if (($got_pregnant_animals_total + $failed_get_pregnant_animals_total) > 0) {
            $pregnancy_rate = ($got_pregnant_animals_total / ($got_pregnant_animals_total + $failed_get_pregnant_animals_total)) * 100;
            //$pregnancy_rate to 2 decimal places
            $pregnancy_rate  = round($pregnancy_rate, 2);
        }
        $r->pregnancy_rate = $pregnancy_rate;

        //CALVING RATE
        $calving_rate = 0;
        if ($got_pregnant_animals_total > 0) {
            $calving_rate = ($r->animals_that_produced->count() / $got_pregnant_animals_total) * 100;
            $calving_rate = round($calving_rate, 2);
        }
        $r->calving_rate = $calving_rate;

        //Weaning Rate
        $weaning_rate = 0;
        if ($r->animals_that_produced->count() > 0) {
            $weaning_rate = ($r->weaned_off_animals->count() / $r->animals_that_produced->count()) * 100;
            $weaning_rate = round($weaning_rate, 2);
        }
        $r->weaning_rate = $weaning_rate;

        //Abortion Rate
        $abortion_rate = 0;
        if ($r->got_pregnant_animals->count() > 0) {
            $abortion_rate = ($r->animals_that_aborted->count() / $r->got_pregnant_animals->count()) * 100;
            $abortion_rate = round($abortion_rate, 2);
        }
        $r->abortion_rate = $abortion_rate;

        //Weaning weight
        $weaning_weight = 0;
        $min_weaning_weight = 0;
        $max_weaning_weight = 0;
        if ($r->weaned_off_animals->count() > 0) {
            $weaning_weight = $r->weaned_off_animals->sum('weaning_weight') / $r->weaned_off_animals->count();
            $weaning_weight = round($weaning_weight, 2);
            $min_weaning_weight = $r->weaned_off_animals->min('weaning_weight');
            $max_weaning_weight = $r->weaned_off_animals->max('weaning_weight');
        }
        $r->weaning_weight = $weaning_weight;
        $r->min_weaning_weight = $min_weaning_weight;
        $r->max_weaning_weight = $max_weaning_weight;

        //Pregnancies in Progress
        $r->pregnancies_in_progress = PregnantAnimal::where(
            [
                'farm_id' => $r->farm_id,
                'current_status' => 'Pregnant'
            ]
        )->whereBetween('ferilization_date', [$start_date, $end_date])->get();


        $r->title = "Farm Report for the period " . Utils::my_date($start_date) . " to " . Utils::my_date($end_date);
        $r->animals = Animal::where([
            'farm_id' => $r->farm_id,
            'type' => 'Cattle'
        ])->get();
        $r->female_animals = Animal::where([
            'farm_id' => $r->farm_id,
            'sex' => 'Male'
        ])->get();


        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('farm-report', [
            'report' => $r
        ]));

        //check if file pdf 
        if ($r->pdf != null) {
            if (fileExists(Utils::docs_root() . "/storage/" . $r->pdf)) {
                try {
                    unlink(Utils::docs_root() . "/storage/" . $r->pdf);
                } catch (\Throwable $th) {
                }
            }
        }

        $name  = "files/" . Utils::get_unique_text() . ".pdf";
        $source = Utils::docs_root() . "/storage/" . $name;
        $pdf->save($source);
        $r->pdf = $name;
        $r->pdf_prepared = 'Yes';
        $r->pdf_prepare_date = Carbon::now();

        $path = Utils::docs_root() . "/storage/" . $name;
        $sql = "UPDATE farm_reports SET pdf = '$name', pdf_prepared = 'Yes', pdf_prepare_date = NOW() WHERE id = $r->id";
        $r->save();


        return $pdf->stream();
        return $r;
    }


    //belongs to farm
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }
}
