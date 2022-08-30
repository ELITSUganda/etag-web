<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugStockBatch extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();

        self::created(function ($m) {
            $rec = new DrugStockBatchRecord();
            $rec->administrator_id = $m->administrator_id;
            $rec->drug_stock_batch_id = $m->id;
            $rec->description = $m->last_activity;
            if ($m->details == 'NDA Approval') {
                $rec->record_type = 'nda_approval';
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
        return $this->belongsTo(DrugCategory::class);
    }

    protected $appends = ['quantity_text'];
}
