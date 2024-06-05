<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineMainStock extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            //throw
            throw new Exception("You can't delete this item.");
        });
        self::creating(function ($m) {
            $m->current_quantity = $m->original_quantity;
            return $m;
        });
        self::updating(function ($m) {
            $sum_suplied = DistrictVaccineStock::where('drug_stock_id', $m->id)->sum('original_quantity');
            $m->current_quantity = $m->original_quantity - $sum_suplied;
            return $m;
        });
    }

    public static function my_update($m)
    {
        //return $m;

        /* if ($m->drug_state == 'Solid') {
            $m->original_quantity = (((int)($m->drug_packaging_unit_quantity)) *  (int)($m->drug_packaging_type_pieces));
            $m->current_quantity = $m->original_quantity;
        } else  if ($m->drug_state == 'Liquid') {
            $m->current_quantity = $m->original_quantity;
        } else {
            throw new Exception("Invalid drug state");
        } */
        $m->original_quantity =   (int)($m->drug_packaging_type_pieces);
        return $m;
    }

    public   function drug_category()
    {
        //$this->drug_category_id = 1;
        return $this->belongsTo(VaccineCategory::class, 'drug_category_id', 'id');
    }
    public   function getDrugPackagingTypeTextAttribute()
    {
        return $this->drug_packaging_type_pieces . " " . $this->drug_packaging_type;
        $val = $this->current_quantity / $this->drug_packaging_unit_quantity;

        $val = $val / $this->drug_packaging_type_pieces;

        return number_format($val) . " " . $this->drug_packaging_type;
    }

    public function getDrugPackagingUnitQuantityTextAttribute()
    {

        return number_format($this->current_quantity) . " Doses";
        $val = $this->current_quantity / $this->drug_packaging_unit_quantity;
        $unit = "";
        if ($this->drug_state == 'Solid') {
            $unit = "Tablets";
        } else {
            $unit = "Bottoles";
        }
        return number_format($val) . " " . $unit;
    }
    public function getCurrentQuantityTextAttribute()
    {
        return number_format($this->current_quantity) . " Doses";
        return  Utils::quantity_convertor($this->current_quantity, $this->drug_state);
    }

    //update_self 
    public function update_self()
    {
        $sum_suplied = DistrictVaccineStock::where('drug_stock_id', $this->id)->sum('original_quantity');
        $this->current_quantity = $this->original_quantity - $sum_suplied;
        $this->save();
    }

    protected $appends = [
        'drug_packaging_type_text',
        'current_quantity_text',
        'drug_packaging_unit_quantity_text'
    ];
}
