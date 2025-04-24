<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentralTagBatch extends Model
{
    use HasFactory;
    /*         
        $form->number('purchase_unit_price', __('Purchase unit price'));
 */

    //boot
    protected static function boot()
    {
        parent::boot();

        // Add your custom logic here
        static::creating(function ($model) {
            // Perform actions before creating a new record
            $total_purchase_quantity = $model->total_purchase_quantity;
            $total_purchase_quantity = $model->total_purchase_quantity;
            if ($total_purchase_quantity <= 0 || $total_purchase_quantity <= 0) {
                throw new \Exception('Total purchase quantity and purchase unit price must be greater than zero.');
            }
            $model->purchase_unit_price = $model->purchase_total_price / $total_purchase_quantity;
            if ($model->supplier_type == 'local') {
                $model->supplier_country = 'Uganda';
            }
            $model->available_quantity = $total_purchase_quantity;
        });
 

        static::deleting(function ($model) {
            // Perform actions before deleting a record
        });
    }
}
