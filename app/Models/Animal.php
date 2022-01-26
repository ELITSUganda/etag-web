<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;
    protected $fillable = [
        'administrator_id', 
        'district_id', 
        'sub_county_id',
        'parish_id', 
        'status',
        'type',
        'breed',
        'sex',
        'e_id',
        'v_id',
        'lhc',
        'dob',
        'color',
        'farm_id', 
    ]; 
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

            $f = Farm::find($model->farm_id);
            if($f == null){
                die("Farm not found.");
                return null;
            }
            if($f->holding_code == null){
                die("holding_code  not found.");
                return null;
            }
            /*
            if($f->parish->code == null){
                die("Farm parish code not found.");
                return null;
            }
             
            $model->status = "Live";
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            $model->parish_id = $f->parish_id;

             $num = (int) (Animal::where(['parish_id'=>$model->parish_id])->count());
            $num++;
            $num = $num."";
            if(strlen($num)<2){
                $num = "000000".$num;
            }else if(strlen($num)<3){
                $num = "00000".$num;
            }else if(strlen($num)<4){ 
                $num = "000".$num;
            }else if(strlen($num)<5){
                $num = "00".$num;
            }else if(strlen($num)<6){
                $num = "0".$num;
            }else{
                $num = "".$num;
            }*/

            $model->lhc = $f->holding_code;
 
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


    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }
    
}
