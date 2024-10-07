<?php

namespace App\Models;

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
        $start_date = $r->start_date;
        $end_date = $r->end_date;
        $farm = Farm::find($r->farm_id);

        $r->title = "Farm Report for the period $start_date to $end_date.";
        $r->animals = Animal::where([
            'farm_id' => $r->farm_id,
            'type' => 'Cattle'
        ])->get();
        $r->female_animals = Animal::where([
            'farm_id' => $r->farm_id,
            'sex' => 'Male'
        ])->get();

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
