<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrderItem extends Model
{
    use HasFactory;

    //belongs to ProductOrder
    public function product_order()
    {
        return $this->belongsTo(ProductOrder::class);
    }
    //fillables /*  */
    /* 
    product_order_id	
product_id	
quantity	
price	
total	
product_name	
product_photo	 */
    protected $fillable = [
        'product_order_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'product_name',
        'product_photo'
    ];
}
