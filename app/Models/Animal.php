<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();

        self::creating(function($model){ 

            $animal = Animal::where('e_id',$model->e_id)->first();
            if($animal != null){
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }

            $animal = Animal::where('v_id',$model->v_id)->first();
            if($animal != null){
                die("Animal with same Tag ID aready exist in the system.");
                return false;
            }

            $f = Farm::find($model->farm_id)->first();
            if($f == null){
                return null;
            }
             

            $model->status = "Live";
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            $model->parish_id = $f->parish_id;

            return $model;
        });

        self::created(function($model){
 
        });

        self::updating(function($model){ 
            $f = Farm::find($model->farm_id)->first();
            if($f == null){
                return null;
            }

            $model->status = "Live";
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            $model->parish_id = $f->parish_id;

            return $model;
        });

        self::updated(function($model){
            // ... code here
        });

        self::deleting(function($model){
            // ... code here
        });

        self::deleted(function($model){
            // ... code here
        });
    }

    public function before_save(){
        dd($this);
    }
    
}
