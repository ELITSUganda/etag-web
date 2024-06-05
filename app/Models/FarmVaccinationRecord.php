<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmVaccinationRecord extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            //throw
            throw new Exception("You can't delete this item.");
        });
        self::creating(function ($m) {
            //FarmVaccinationRecord::my_update($m);


            $district_vaccine = DistrictVaccineStock::find($m->district_vaccine_stock_id);
            if ($district_vaccine == null) {
                throw new Exception("Stock not found.");
            }

            $farm = Farm::find($m->farm_id);
            if ($farm == null) {
                throw new Exception("Farm not found.");
            }

            $m->vaccine_main_stock_id = $district_vaccine->drug_stock_id;
            $mainStock = VaccineMainStock::find($m->vaccine_main_stock_id);
            if ($mainStock == null) {
                throw new Exception("Stock not found.");
            }

            $m->district_id = $farm->district_id;
            $m->vaccination_batch_number = $district_vaccine->drug_stock->batch_number;
            $m->lhc = $farm->holding_code;

            $owner = $farm->owner();
            if ($owner == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Owner not found.",
                ]);
            }
            $m->farmer_name = $owner->name;
            $m->farmer_phone_number = $owner->phone_number;

            return $m;
        });

        self::created(function ($m) {
            $vaccine_main_stock = DistrictVaccineStock::find($m->district_vaccine_stock_id);
            if ($vaccine_main_stock == null) {
                throw new Exception("Stock not found.");
            }
            $vaccine_main_stock->new_update_balance();
        });
        //updated
        self::updated(function ($m) {
            $vaccine_main_stock = DistrictVaccineStock::find($m->district_vaccine_stock_id);
            if ($vaccine_main_stock == null) {
                throw new Exception("Stock not found.");
            }
            $vaccine_main_stock->new_update_balance();
            return $m;
        });
    }

    //belongs to farm
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    //belongs to vaccine_main_stock_id
    public function vaccine_main_stock()
    {
        return $this->belongsTo(VaccineMainStock::class);
    }

    //belongs to district
    public function district()
    {
        return $this->belongsTo(Location::class);
    }

    //created_by_id
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
