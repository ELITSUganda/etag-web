<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictVaccineStock extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("Ooops! You cannot delete this item.");
        });
        self::creating(function ($m) {
            DistrictVaccineStock::my_update($m);
            return $m;
        });
        self::updating(function ($m) {
            DistrictVaccineStock::my_update($m);
            return $m;
        });
    }

    public static function my_update($m)
    {

        $mainStock = VaccineMainStock::find($m->drug_stock_id);
        if ($mainStock == null) {
            die("Stock not found.");
        }
        $m->drug_category_id = $mainStock->drug_category_id;
        if (isset($used_quantity)) {
            if ($mainStock->drug_state == 'Solid') {
                $m->original_quantity = ($used_quantity * 1000000);
                $m->current_quantity = $m->original_quantity;
            } else  if ($mainStock->drug_state == 'Liquid') {
                $m->original_quantity = ($used_quantity * 1000);
                $m->current_quantity = $m->original_quantity;
            }
            if ($m->original_quantity > $mainStock->current_quantity) {
                //die("Transfer failed because of insufitient sotck.");
            }

            $mainStock->current_quantity = $mainStock->current_quantity - $m->original_quantity;
            $mainStock->save();

            unset($used_quantity);
        }

        return $m;
    }

    public static function update_balance($m)
    {
        $mainStock = DistrictVaccineStock::find($m->drug_stock_id);
        if ($mainStock == null) {
            die("Stock not found.");
        }
        $used_quantity = Event::where('vaccine_id', $m->id)->sum('vaccination');
        if (isset($used_quantity)) {
            if ($mainStock->drug_state == 'Solid') {
                $m->original_quantity = ($used_quantity / 1000000);
                $m->current_quantity = $m->original_quantity;
            } else  if ($mainStock->drug_state == 'Liquid') {
                $m->original_quantity = ($used_quantity / 1000);
                $m->current_quantity = $m->original_quantity;
            }
            if ($m->original_quantity > $mainStock->current_quantity) {
                //die("Transfer failed because of insufitient sotck.");
            }
            $mainStock->current_quantity = $mainStock->current_quantity - $m->original_quantity;
            $mainStock->save();
        }
    }

    public   function district()
    {
        return $this->belongsTo(Location::class, 'district_id');
    }

    public   function drug_category()
    {
        return $this->belongsTo(VaccineCategory::class, 'drug_category_id');
    }

    public   function drug_stock()
    {
        return $this->belongsTo(VaccineMainStock::class, 'drug_stock_id');
    }

    public   function creator()
    {
        return $this->belongsTo(Administrator::class, 'created_by');
    }




    public function getCurrentQuantityTextAttribute()
    {
        return Utils::quantity_convertor($this->current_quantity, $this->drug_stock->drug_state);
    }

    //getter for drug_category_text
    public function getDrugCategoryTextAttribute()
    {
        return $this->drug_category->name_of_drug;
    } 

    //getter for district_text
    public function getDistrictTextAttribute()
    {
        return $this->district->name_text;
    } 


    protected $appends = [
        'current_quantity_text','drug_category_text','district_text'
    ];
}
