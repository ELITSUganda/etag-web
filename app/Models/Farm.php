<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Farm extends Model
{
    use HasFactory;
    protected $fillable = [
        'administrator_id',
        'district_id',
        'sub_county_id',
        'parish_id',
        'size',
        'latitude',
        'longitude',
        'dfm',
        'name',
        'farm_type',
        'holding_code',
    ];

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

            $s = SubCounty::find($model->sub_county_id); 
            if ($s == null) {
                dd("SubCounty not found.");
                return null;
            }
           

            if ($s->district == null) {
                dd("District not found.");
                return null;
            }
            $model->district_id = $s->district->id;

            $num = (int) (Farm::where(['sub_county_id' => $model->sub_county_id])->count());
            $num++;
 
            $model->holding_code = $s->code."-".$num;
            
            return $model;
        });
 

        self::updating(function ($model) {

            
            // ... code here
        });

        self::updated(function ($model) {
            if($model->animals!=null){
                foreach ($model->animals as $key => $animal) {
                    $animal->administrator_id = $model->administrator_id;
                    $animal->save();
                }
            }
            // ... code here
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function user()
    {
        return $this->belongsTo(Administrator::class,'administrator_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }

    public function owner()
    {
        return Administrator::findOrFail($this->administrator_id);
    }
}
