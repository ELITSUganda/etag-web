<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function($model){ 
 
            $animal = Animal::where('id',$model->animal_id)->first();
            if($animal == null){
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }
            $model->district_id = $animal->district_id;
            $model->sub_county_id = $animal->sub_county_id;
            $model->parish_id = $animal->parish_id;
            $model->farm_id = $animal->farm_id;
            $model->administrator_id = $animal->administrator_id;
            $model->animal_type = $animal->type;
  
            return $model;
        });

        self::created(function($model){
            $animal = Animal::find($model->animal_id)->first();
            if($animal == null){
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }
            $animal->status = $model->type;
            $animal->save();
        });

        self::updating(function($model){ 
            
            $animal = Animal::find($model->animal_id)->first();
            if($animal == null){
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }

            
            $model->district_id = $animal->district_id;
            $model->sub_county_id = $animal->sub_county_id;
            $model->parish_id = $animal->parish_id;
            $model->farm_id = $animal->farm_id;
            $model->administrator_id = $animal->administrator_id;
            $model->animal_type = $animal->type;
  
            return $model;
            
        });

        self::updated(function($model){
            $animal = Animal::find($model->animal_id)->first();
            if($animal == null){
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }
            $animal->status = $model->type;
            $animal->save();
        });

        self::deleting(function($model){
            // ... code here
        });

        self::deleted(function($model){
            // ... code here
        });
    }

}
