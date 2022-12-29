<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugStockBatch extends Model
{
    use HasFactory;


        public static function boot()
        {
            parent::boot();

            self::deleting(function ($m) {
                DrugStockBatchRecord::where(['batch_number' => $m->batch_number])->delete();
            });

            self::creating(function ($m) {

                $m->current_quantity = $m->original_quantity;
                $f = Farm::where([
                    'administrator_id' => $m->administrator_id
                ])->first();
                if ($f == null) {
                    throw new Exception("You must have at least one farm to create drug stock.");
                }
                $m->sub_county_id = $f->sub_county_id;
                //$m->batch_number .= '-' . time();
                return $m;
            });
            self::created(function ($m) {
                $rec = new DrugStockBatchRecord();
                $rec->administrator_id = $m->administrator_id;
                $rec->drug_stock_batch_id = $m->id;
                $rec->description = $m->last_activity;
                if ($m->details == 'New drug stock') {
                    $rec->record_type = 'nda_approval';
                } else if ($m->details == 'Received drugs') {
                    $rec->record_type = 'received_drugs';
                }
                $rec->save();
            });
        }

    function records()
    {
        return $this->hasMany(DrugStockBatchRecord::class);
    }

    public static function all_records($batch_number)
    {
        return DrugStockBatchRecord::where(['batch_number' => $batch_number])->get();
    }

    function getQuantityTextAttribute($x)
    {
        $units = "";
        if ($this->category != null) {
            $units = " " . $this->category->unit;
        }
        return number_format($this->current_quantity) . $units;
    }

    function category()
    {
        return $this->belongsTo(DrugCategory::class, 'drug_category_id');
    }


    function getCurrentQuantityTextAttribute($x)
    {
        $units = "";
        if ($this->category != null) {
            $units = " " . $this->category->unit;
        }
        return number_format($this->current_quantity) . $units;
    }

    function getOriginalQuantityTextAttribute($x)
    {
        $units = "";
        if ($this->category != null) {
            $units = " " . $this->category->unit;
        }
        return number_format($this->original_quantity) . $units;
    }


    function getDrugCategoryTextAttribute($x)
    {
        $cat_name = $this->drug_category_id;
        if ($this->category != null) {
            $cat_name =  $this->category->name;
        }
        return $cat_name;
    }
    function getDrugCategoryText($x)
    {
        $units = "";
        if ($this->category != null) {
            $units = " " . $this->category->unit;
        }
        return number_format($this->original_quantity_text) . $units;
    }
    function getExpiryDateTextAttribute($x)
    {
        return Utils::my_date($this->expiry_date);
    }
    function getCreatedAtTextAttribute($x)
    {
        return Utils::my_date($this->created_at);
    }


    protected $appends = [
        'quantity_text',
        'created_at_text',
        'drug_category_text',
        'expiry_date_text',
        'original_quantity_text',
        'current_quantity_text',
    ];
}
