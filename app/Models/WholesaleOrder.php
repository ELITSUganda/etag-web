<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard\Duplicates;

class WholesaleOrder extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            if ($m->processed == 'Yes') {
                throw new Exception('You cannot delete this item.');
            }
        });
        /*   self::creating(function ($m) {
            $m = WholesaleOrder::my_update($m);
            return $m;
        });
        self::updating(function ($m) {
            $m = WholesaleOrder::my_update($m);
            return $m;
        }); */
    }

    public static function do_process_order($m)
    {
        if ($m->status != 'Processing') {
            return false;
        }
        if ($m->processed == 'Yes') {
            return false;
        }

        $status = $m->validate_order();
        if ($status != null) {
            throw new Exception($status);
        }
        $order_items = $m->order_items;
        foreach ($order_items as $key => $val) {
            $drug_stock = $val->wholesale_drug_stock;
            if ($drug_stock == null) {
                throw new Exception('Drug stock not found.');
            }
            $drug_category = $drug_stock->drug_category;
            if ($drug_category == null) {
                throw new Exception('Drug category not found.');
            }
            $available_quantity = 0;
            $multiplier = 1;
            if ($drug_stock->drug_state == 'Solid') {
                $multiplier = 1000000;
                $available_quantity = ($drug_stock->current_quantity / $multiplier);
            } else  if ($drug_stock->drug_state == 'Liquid') {
                $multiplier = 1000;
                $available_quantity = ($drug_stock->current_quantity / $multiplier);
            }
            if ($available_quantity < $val->quantity) {
                throw new Exception('Order Item #' . $drug_category->name . ', You cannot order more than available quantity.');
            }
            $quantity_value = ($val->quantity * $multiplier);
            $new_drug_stock = $drug_stock->replicate();
            $new_drug_stock->original_quantity = $quantity_value;
            $new_drug_stock->current_quantity = $quantity_value;
            $new_drug_stock->wholesale_order_id = $m->id;
            $new_drug_stock->wholesale_order_item_id = $val->id;
            $new_drug_stock->administrator_id = $m->customer_id;
            try {
                $new_drug_stock->save();
                $drug_stock->current_quantity = $drug_stock->current_quantity - $quantity_value;
                $drug_stock->save();
            } catch (\Throwable $th) {
            }
        }

        $m->processed == 'Yes';
        $m->save();
    }

    public function validate_order()
    {
        $order_items = $this->order_items;
        $status = null;
        foreach ($order_items as $key => $val) {
            $drug_stock = $val->wholesale_drug_stock;
            if ($drug_stock == null) {
                $status = 'Drug stock not found.';
            }
            $drug_category = $drug_stock->drug_category;
            if ($drug_category == null) {
                $status = 'Drug category not found.';
            }
            $available_quantity = 0;

            if ($drug_stock->drug_state == 'Solid') {
                $available_quantity = ($drug_stock->current_quantity / 1000000);
            } else  if ($drug_stock->drug_state == 'Liquid') {
                $available_quantity = ($drug_stock->current_quantity / 1000);
            }
            if ($available_quantity < $val->quantity) {
                $status = 'Order Item #' . $drug_category->name . ', You cannot order more than available quantity.';
            }
        }
        return $status;
    }

    public function order_items()
    {
        return $this->hasMany(WholesaleOrderItem::class, 'wholesale_order_id');
    }
}
