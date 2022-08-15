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
            $rec->save();
        });
    }
}
