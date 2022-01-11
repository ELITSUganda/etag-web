<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Farm extends Model
{
    use HasFactory;
    
    public function animals(){
        return $this->hasMany(Animal::class);
    }
    public static function boot()
    {
        parent::boot();

        self::creating(function($model){ 
 
            $p = Parish::find($model->parish_id)->first();
            if($p == null){
                return null;
            }
            $model->sub_county_id = $p->sub_county->id;
            $model->district_id = $p->sub_county->district->id;
             
            return $model;
        });

        self::created(function($model){
            $p = Parish::find($model->parish_id)->first();
            if($p == null){
                return null;
            }
            $model->sub_county_id = $p->sub_county->id;
            $model->district_id = $p->sub_county->district->id;
             
            return $model;
        });

        self::updating(function($model){ 
            // ... code here
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



    public function owner()
    {

        return Administrator::findOrFail($this->administrator_id);
    }   
}
 