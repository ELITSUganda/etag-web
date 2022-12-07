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
        return number_format($this->current_quantity);
    }

    function category()
    {
        return $this->belongsTo(DrugCategory::class, 'drug_category_id');
    }

    protected $appends = ['quantity_text'];
}
