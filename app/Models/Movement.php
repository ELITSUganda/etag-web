<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->status = "Pending";

            $s = SubCounty::find($model->sub_county_to); 
            if ($s == null) {
                die("SubCounty to not found.");
                return false;
            } 

            if ($s->district == null) {
                die("District to not found.");
                return false;
            }
            $model->district_to = $s->district->id;

            $s1 = SubCounty::find($model->sub_county_from); 
            if ($s1 == null) {
                die("SubCounty from not found.");
                return false;
            } 

            if ($s1->district == null) {
                die("District from not found.");
                return false;
            }
            $model->district_from = $s1->district->id;
            return $model;
        });
        

        self::created(function ($model) {
        });

        self::updating(function ($model) {
            // ... code here
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            
            MovementAnimal::where('movement_id', $model->id)->delete();
            MovementHasMovementAnimal::where('movement_id', $model->id)->delete();

            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    public function movement_has_movement_animals()
    {
        return $this->hasMany(MovementHasMovementAnimal::class);
    }


    public function movement_animals()
    {
        return $this->hasMany(MovementAnimal::class);
    }


    public function from_farm()
    {
        return $this->belongsTo(Farm::class,'from');
    } 

    public function to_farm()
    {
        return $this->belongsTo(Farm::class,'to');
    } 

}
