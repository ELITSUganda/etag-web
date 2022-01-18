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

            $p = Parish::find($model->parish_id)->first();
            if ($p == null) {
                dd("Parish not found.");
                return null;
            }
            if ($p->sub_county == null) {
                dd("Sub county not found.");
                return null;
            }
            $model->sub_county_id = $p->sub_county->id;

            if ($p->sub_county->district == null) {
                dd("District not found.");
                return null;
            }
            $model->district_id = $p->sub_county->district->id;

            $num = (int) (Farm::where(['sub_county_id' => $model->sub_county_id])->count());
            $num++;
 
            $model->holding_code = $p->code."-F".$num;
            
            return $model;
        });

        self::created(function ($model) {
            $p = Parish::find($model->parish_id)->first();
            if ($p == null) {
                return null;
            }
            $model->sub_county_id = $p->sub_county->id;
            $model->district_id = $p->sub_county->district->id;

            return $model;
        });

        self::updating(function ($model) {
            // ... code here
        });

        self::updated(function ($model) {
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

    public function owner()
    {
        return Administrator::findOrFail($this->administrator_id);
    }
}
