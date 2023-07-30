<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WholesaleOrder extends Model
{
    use HasFactory;

    public function order_items()
    {
        return $this->hasMany(WholesaleOrderItem::class, 'wholesale_order_id');
    }
}
