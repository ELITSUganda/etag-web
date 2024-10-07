<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PregnantAnimal extends Model
{
    use HasFactory;

    //creating boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model = PregnantAnimal::do_process($model);
        });

        //updating 
        static::updating(function ($model) {
            $model = PregnantAnimal::do_process($model);
        });
    }

    //do process
    public static function do_process($r)
    {

        $animal = Animal::find($r->animal_id);
        if ($animal == null) {
            throw new Exception("Animal not found");
        }
        $farm = Farm::find($animal->farm_id);
        if ($farm == null) {
            throw new Exception("Farm not found");
        }
        $r->farm_id = $farm->id;
        return $r;
    }
}
