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
            die("Ooops! You cannot delete this item.");
        });
        self::creating(function ($m) {
            VaccineMainStock::my_update($m);
            return $m;
        });
        self::updating(function ($m) {
            VaccineMainStock::my_update($m);
            return $m;
        });
    }

    public static function my_update($m)
    {
        if (isset($m->original_quantity_temp)) {
            if ($m->drug_state == 'Solid') {
                $m->original_quantity = ($m->original_quantity_temp * 1000000);
                $m->current_quantity = $m->original_quantity;
            } else  if ($m->drug_state == 'Liquid') {
                $m->original_quantity = ($m->original_quantity_temp * 1000);
                $m->current_quantity = $m->original_quantity;
            }
            unset($m->original_quantity_temp);
        }

        return $m;
    }

    public   function drug_category()
    {
        $this->drug_category_id = 1;
        return $this->belongsTo(VaccineCategory::class, 'drug_category_id', 'id');
    }
    public   function getDrugPackagingTypeTextAttribute()
    {
        $val = $this->current_quantity / $this->drug_packaging_unit_quantity;

        $val = $val / $this->drug_packaging_type_pieces;

        return number_format($val) . " " . $this->drug_packaging_type;
    }

    public function getDrugPackagingUnitQuantityTextAttribute()
    {
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
        return  Utils::quantity_convertor($this->current_quantity, $this->drug_state);
    }

    protected $appends = [
        'drug_packaging_type_text',
        'current_quantity_text',
        'drug_packaging_unit_quantity_text'
    ];
}
