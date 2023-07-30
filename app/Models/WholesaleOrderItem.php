<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WholesaleOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wholesale_drug_stock_id',
        'quantity',
        'description',
        'wholesale_order_id',
    ];
}
