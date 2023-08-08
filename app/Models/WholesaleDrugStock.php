<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class WholesaleDrugStock extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            if ($m->status == 'Approved') {
                throw new Exception('You cannot delete this item.');
            }
        });
        self::creating(function ($m) {
            WholesaleDrugStock::my_update($m);
            return $m;
        });
        self::updating(function ($m) {
            WholesaleDrugStock::my_update($m);
            return $m;
        });
    }

    public static function get_items($u)
    {
        $drugs = [];
        $data = [];
        if (
            $u->isRole('nda') ||
            $u->isRole('admin') ||
            $u->isRole('administrator') 
        ) {
            $data = WholesaleDrugStock::where('current_quantity', '>', 0)
                ->where('status', 'Approved')
                ->get();
        }else{
            $data = WholesaleDrugStock::where('current_quantity', '>', 0)
                ->where('status', 'Approved')
                ->where('administrator_id', $u->id)
                ->get();
        }
        foreach ($data as $key => $value) {
            $drugs[$value->id] = $value->drug_category->name . " - " . $value->drug_packaging_type_text;
        }
        return $drugs;
    }
    public static function my_update($m)
    {
        if ($m->status == 'Approved') {
            return $m;
        }
        if (isset($m->original_quantity_temp)) {
            if ($m->drug_state == 'Solid') {
                $m->original_quantity = ($m->original_quantity_temp * 1000000);
                $m->current_quantity = $m->original_quantity;
            } else  if ($m->drug_state == 'Liquid') {
                $m->original_quantity = ($m->original_quantity_temp * 1000);
                $m->current_quantity = $m->original_quantity;
            }
        }

        return $m;
    }

    public   function drug_category()
    {
        $cat = DrugCategory::find($this->drug_category_id);
        if ($cat == null) {
            DB::update("update wholesale_drug_stocks set drug_category_id = 1 where drug_category_id = ?", [$this->drug_category_id]);
        }
        return $this->belongsTo(DrugCategory::class);
    }
    public   function getDrugPackagingTypeTextAttribute()
    {
        $val = $this->current_quantity / $this->drug_packaging_unit_quantity;

        $val = $val / $this->drug_packaging_type_pieces;

        $units = 'KGs';
        $divider = 1000000;
        if ($this->drug_state != 'Solid') {
            $units = 'Litres';
            $divider = 1000;
        }



        return  number_format($this->current_quantity / $divider) . " " . $units . ", " . number_format($val) . " - " . Str::plural($this->drug_packaging_type, $val) . " of " . $this->drug_packaging_unit_quantity . " " .   $units;
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
