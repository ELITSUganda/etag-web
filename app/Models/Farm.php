<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
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

            $model->district_id = 1;
            $sub = Location::find($model->sub_county_id);
            if ($model->sub_county_id != null) {
                if ($sub != null) {
                    $model->district_id = $sub->parent;
                }
            }

            $num = (int) (Farm::where(['sub_county_id' => $model->sub_county_id])->count());
            $num++;
            if ($sub != null) {
                $model->holding_code = $sub->code . "-" . $num;
            } else {
                $model->holding_code = 'UG-000-00' . "-" . $num;
            }

            return $model;
        });


        self::updating(function ($model) {

            $model->district_id = 1;
            $sub = Location::find($model->sub_county_id);
            if ($model->sub_county_id != null) {
                if ($sub != null) {
                    $model->district_id = $sub->parent;
                }
            }

            $num = (int) (Farm::where(['sub_county_id' => $model->sub_county_id])->count());
            $num++;
            if ($sub != null) {
                $model->holding_code = $sub->code . "-" . $num;
            } else {
                //  $model->holding_code = 'UG-000-00' . "-" . $num;
            }

            return $model;
        });

        self::updated(function ($model) {
            if ($model->animals != null) {
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
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function sub_county()
    {
        return $this->belongsTo(Location::class, 'sub_county_id');
    }

    public function owner()
    {
        $id = $this->administrator_id;
        $u = Administrator::find($id);
        if (!$u) {
            return Admin::user();
        }
        return $u;

        //return Administrator::findOrFail();
    }
}
