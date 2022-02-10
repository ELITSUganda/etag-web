<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
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
            $applicant = Administrator::find($model->administrator_id);

            if ($applicant == null) {
                die(json_encode(Utils::response([
                    'status' => 0,
                    'message' => "Permit applicant not found on database."
                ])));
            }

            $model->trader_nin = $applicant->nin;
            $model->trader_name = $applicant->name;
            $model->trader_phone = $applicant->phone_number;
            if ($model->destination == "To farm") {
                $farm = Farm::find($model->destination_farm);
                if ($farm == null) {
                    die(json_encode(Utils::response([
                        'status' => 0,
                        'message' => "Destination farm was not found on our database."
                    ])));
                } 
                $model->sub_county_to = $farm->sub_county->id;
                $model->district_to = $farm->sub_county->district_id; 
            } 
            return $model;
        });


        self::created(function ($model) {
        });

        self::updating(function ($model) {
            if ($model->status == "Approved") {

                $model->permit_Number = time() . rand(1, 100);

                if ($model->destination == "To farm") {
                    if ($model->destination_farm != null) {
                        foreach ($model->movement_has_movement_animals as $key => $value) {
                            if ($value->movement_animal_id != null) {
                                $transfer['animal_id'] = $value->movement_animal_id;
                                $transfer['destination_farm_id'] = $model->destination_farm;
                                Utils::move_animal($transfer);
                            }
                        }
                        $transfer['destination'] = $model->destination_farm;
                    }
                }
            }
            return $model;
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
        return $this->belongsTo(Farm::class, 'from');
    }

    public function to_farm()
    {
        return $this->belongsTo(Farm::class, 'to');
    }
}
