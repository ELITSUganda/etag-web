<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugStockBatchRecord extends Model
{
    use HasFactory;

    function batch()
    {
        return $this->belongsTo(DrugStockBatch::class, 'drug_stock_batch_id');
    }
}
