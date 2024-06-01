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
            die("Ooops! You cannot delete this item.");
        });
        self::creating(function ($m) {
            //FarmVaccinationRecord::my_update($m);

            $mainStock = VaccineMainStock::find($m->vaccine_main_stock_id);
            if ($mainStock == null) {
                throw new Exception("Stock not found.");
            }

            $vaccine_main_stock = DistrictVaccineStock::find($m->district_vaccine_stock_id);
            if ($vaccine_main_stock == null) {
                throw new Exception("Stock not found.");
            }

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
}
