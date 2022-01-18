<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    protected $fillable = ['name', 'detail','sub_county_id'];



    public static function boot()
    {
        parent::boot();

        self::creating(function($model){ 
            $sub = Parish::where(['name' => $model->name,'sub_county_id'=>$model->sub_county_id])->first();
            if($sub!=null){
                die("A parish with same name in same subc-county already exist.");
            }
            $num = (int) (Parish::where(['sub_county_id'=>$model->sub_county_id])->count());
            $sub = SubCounty::where('id',$model->sub_county_id)->first();
            if($sub == null){
                die("SubCounty not found.");
            }
            $num++;
            $model->code = $sub->code."-".$num;
            return $model;
        });

        self::created(function($model){
 
        });

        self::updating(function($model){ 
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



    use HasFactory; 
    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }
} 