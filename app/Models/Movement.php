<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Movement extends Model
{
    use HasFactory;



    public static function my_create($model)
    {
        if ($model->permit_Number == null || strlen($model->permit_Number) < 3) {
            $model->permit_Number = $model->generate_permit_number();
        }
        $applicant = Administrator::find($model->administrator_id);
        if ($applicant == null) {
            throw new Exception("Permit applicant not found on database.", 1);
        }

        $sub_county_from = Location::find($model->sub_county_from);
        if ($sub_county_from == null) {
            throw new Exception("Subcounty from not found.", 1);
        }
        $model->district_from = $sub_county_from->parent;



        if ($model->destination == "To farm") {
            $farm = Farm::find($model->destination_farm);
            if ($farm == null) {
                throw new Exception("Destination farm not found.", 1);
            }
            $model->sub_county_to = $farm->sub_county->id;
            $model->district_to = $farm->sub_county->parent;
            $model->destination_slaughter_house = 0;
        } else if ($model->destination == "To slaughter") {
            $house  = SlaughterHouse::find($model->destination_slaughter_house);
            if ($house == null) {
                throw new Exception("Slaughter house not fouassnd.", 1);
            }
            if ($house->subcounty == null) {
                throw new Exception("Slaughter house subcounty not found.", 1);
            }

            $model->sub_county_to = $house->subcounty->id;
            $model->district_to = $house->subcounty->parent;





            return $model;
        } else {
        }

        return $model;

        /*  
  
    "permit_Number" => null
    "valid_from_Date" => null
    "valid_to_Date" => null
    "status_comment" => null
    "destination" => "To farm"
    "destination_slaughter_house" => "0"
    "details" => "she deatils"
    "destination_farm" => 180
*/
    }
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->status = "Pending";
            $model = Movement::my_create($model);
            return $model;
        });


        self::created(function ($m) {

            $u = Administrator::find($m->administrator_id);
            $name = "";
            if ($u != null) {
                $name = "Hello {$u->name}, ";
            }
            $sub_county_from = Location::find($m->sub_county_from);



            if ($sub_county_from != null) {
                $rs = AdminRoleUser::where([
                    'role_type' => 'dvo',
                    'type_id' => $sub_county_from->parent,
                ])->get();
                foreach ($rs as $v) {
                    Utils::sendNotification(
                        "{$name} has applied for a movement permit and its now pending for your approval.",
                        $v->user_id,
                        $headings = 'Movement permit application - review'
                    );
                }
            }


            //$items = Movement::where('sub_county_from', '=', $user->scvo)->where('status', '=', 'Approved')->get(); 

            Utils::sendNotification(
                "Your movement permit application has been received.\nThank you.",
                $m->administrator_id,
                $headings = 'Movement permit application received!'
            );
        });


        self::updating(function ($model) {
            $model = Movement::my_create($model);

            $model->status = ((string)($model->status));
            $_s = 'Reviewed';


            if ($model->status == "Approved") {
                $_s = 'Approved';



                if ($model->destination == "To farm") {
                    if ($model->destination_farm != null) {
                        foreach ($model->movement_has_movement_animals as $key => $value) {
                            if ($value->movement_animal_id != null) {
                                $transfer['animal_id'] = $value->movement_animal_id;
                                $transfer['destination_farm_id'] = $model->destination_farm;
                                //Utils::move_animal($transfer);
                            }
                        }
                        $transfer['destination'] = $model->destination_farm;
                    }
                } else if ($model->destination == "To slaughter") {
                    $house  = SlaughterHouse::find($model->destination_slaughter_house);
                    if ($house == null) {
                        throw new Exception("Slaughter house not fouassnd.", 1);
                    }
                    if ($house->subcounty == null) {
                        throw new Exception("Slaughter house subcounty not found.", 1);
                    }

                    $model->sub_county_to = $house->subcounty->id;
                    $model->district_to = $house->subcounty->parent;

                    foreach ($model->movement_has_movement_animals as $key => $value) {
                        if ($value->movement_animal_id != null) {
                            $animal = Animal::find($value->movement_animal_id);
                            if ($animal != null) {
                                $animal->slaughter_house_id = $house->id;
                                $animal->movement_id = $model->id;
                                $animal->save();
                            }
                        }
                    }

                    return $model;
                } else {
                }
            } else if ($model->status  == 'Rejected') {
                $_s = 'Declined';
            } else if ($model->status  == 'Halted') {
                $_s = 'Halted';
            }
            Utils::sendNotification(
                "Your Movement Permit #{$model->id} has been {$_s}. Open the App for more details.",
                $model->administrator_id,
                $headings = "Movement permit application."
            );

            return $model;
        });


        self::updated(function ($model) {
            Utils::make_movement_qr($model);
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

    function animals()
    {
        return $this->belongsToMany(
            Animal::class,
            'movement_has_movement_animals',
            'movement_id',
            'movement_animal_id',
        );
    }


    public function getVillageToAttribute($value)
    {
        $data = $this->destination;
        if ($this->destination == 'To slaughter') {
            $house  = SlaughterHouse::find($this->destination_slaughter_house);
            if ($house != null) {
                $data .= ', ' . $house->name;
            }
        } else if ($this->destination == 'To farm') {
            $farm  = Farm::find($this->destination_farm);
            if ($farm != null) {
                $data .= ', ' . $farm->holding_code;
            }
        } else {
            $data .= ', ' . $value;
        }
        return $data;
    }

    public function generate_permit_number()
    {
        $sub_county_from = Location::find($this->sub_county_from);
        $permit_number = "";
        if ($sub_county_from != null) {
            if ($sub_county_from->code != null && strlen($sub_county_from->code) > 2) {
                $count = DB::table('movements')->where('sub_county_from', $this->sub_county_from)->count();
                $permit_number = 'MVP-' . $sub_county_from->code . "-" . ($count + 1);
            }
        }
        if (strlen($permit_number) < 5) {
            $permit_number = "MVP-UG-0000" . $this->id;
        }
        return $permit_number;
    }
    public function countAniamals()
    {
        return $this->movement_has_movement_animals()->count();
    }
    public function getAnimalsAttribute()
    {
        $has_animals = MovementHasMovementAnimal::where(['movement_id' => $this->id])->get();
        $ans = [];
        foreach ($has_animals as  $an) {
            $ans[] = $an->animal;
        }
        return  $ans;
    }


    public function getDestinationFarmTextAttribute()
    {
        $farm = Farm::find($this->destination_farm);
        if ($farm == null) {
            return "-";
        }
        return  $farm->holding_code;
    }

    public function getSubcountyFromTextAttribute()
    {
        $sub = Location::find($this->sub_county_from);
        if ($sub == null) {
            return "-";
        }
        return  $sub->name_text;
    }
    public function getSubcountyToTextAttribute()
    {
        $sub = Location::find($this->sub_county_to);
        if ($sub == null) {
            return "-";
        }
        return  $sub->name_text;
    }

    public function getDistrictFromTextAttribute()
    {
        $sub = Location::find($this->sub_county_from);
        if ($sub == null) {
            return "-";
        }
        $dis = Location::find($sub->parent);
        if ($dis == null) {
            return "-";
        }
        return  $dis->name;
    }

    public function movement_animals()
    {
        return $this->hasMany(MovementAnimal::class);
    }


    public function from_farm()
    {
        return $this->belongsTo(Farm::class, 'from');
    }

    public function owner()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function to_farm()
    {
        return $this->belongsTo(Farm::class, 'to');
    }
    protected $appends = [
        'animals', 'destination_farm_text',
        'subcounty_from_text',
        'subcounty_to_text',
        'district_from_text'
    ];
}
