<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PregnantAnimal extends Model
{
    use HasFactory;

    //creating boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model = PregnantAnimal::do_process($model);
        });

        //updating 
        static::updating(function ($model) {
            $model = PregnantAnimal::do_process($model);
        });
    }

    //do process
    public static function do_process($r)
    {

        $animal = Animal::find($r->animal_id);
        if ($animal == null) {
            throw new Exception("Animal not found #".$r->animal_id);
        }
        $farm = Farm::find($animal->farm_id);
        if ($farm == null) {
            throw new Exception("Farm not found");
        }
        $r->farm_id = $farm->id;
        $r->parent_v_id = $animal->v_id;
        $calf = Animal::find($r->calf_id);
        if ($calf != null) {
            $r->calf_v_id = $calf->v_id;
        }
        return $r;
        /* 
        
Full texts
id
created_at
updated_at
administrator_id
animal_id
district_id
sub_county_id
original_status
current_status
fertilization_method
expected_sex
details
pregnancy_check_method
born_sex
conception_date
expected_calving_date
gestation_length
did_animal_abort
reason_for_animal_abort
did_animal_conceive
calf_birth_weight
pregnancy_outcome
calving_difficulty
postpartum_recovery_time
post_calving_complications
total_pregnancies
hormone_use
nutritional_status
number_of_calves
born_date
calf_id
total_calving_milk
is_weaned_off
weaning_date
weaning_weight
weaning_age
got_pregnant
ferilization_date
farm_id

calf_v_id
parent_photo
calf_photo
        */
    }

    //getter parent_v_id
    public function getParentVIdAttribute($value)
    {
        if ($value == null || $value == "") {
            $animal = Animal::find($this->animal_id);
            if ($animal != null) {
                $this->parent_v_id = $animal->v_id;
                $this->save();
                return $animal->v_id;
            }
        }
        return $value;
    }

    //belong to animal
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
