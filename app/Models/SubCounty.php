<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCounty extends Model
{
    protected $fillable = ['name', 'detail','district_id'];
    use HasFactory; 


    public static function boot()
    {
        parent::boot();

        self::creating(function($model){ 
            $sub = SubCounty::where(['name' => $model->name,'district_id'=>$model->district_id])->first();
            if($sub!=null){
                die("A subc-county with same name in same district already exist.");
            }
            $num = (int) (SubCounty::where(['district_id'=>$model->district_id])->count());
            $dis = District::where('id',$model->district_id)->first();
            if($dis == null){
                die("District not found.");
            }
            $num++;
            $model->code = $dis->code."-".$num;


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


    
    public function district()
    {
        return $this->belongsTo(District::class);
    }   
}
