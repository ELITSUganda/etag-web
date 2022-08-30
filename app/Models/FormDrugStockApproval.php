<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FormDrugStockApproval extends Model
{
    use HasFactory;
    function applicant()
    {
        return $this->belongsTo(Administrator::class);
    }
    function items()
    {
        return $this->hasMany(FormDrugStockApprovalItem::class);
    }

    public static function boot()
    {
        parent::boot();

        self::updating(function ($m) {

            if ((Auth::user()->isRole('nda')) && $m->status == 1) {
                foreach ($m->items as $item) {
                    if ($item->done_approving != 1) {


                        $item->form_drug_stock_approval_id;
                        $item->drug_category_id;
                        $item->quantity;
                        $item->status;

                        $item->name;
                        $item->manufacturer;
                        $item->batch_number;
                        $item->ingredients;
                        $item->expiry_date;
                        $item->original_quantity;
                        $item->selling_price;
                        $item->image;
                        $item->details;

                        $stock = new DrugStockBatch();
                        $stock->administrator_id = $m->applicant_id;
                        $stock->drug_category_id = $item->drug_category_id;
                        $cat = DrugCategory::find($stock->drug_category_id);
                        $stock->name = $item->name;
                        $stock->manufacturer = $item->manufacturer;
                        $stock->batch_number = $item->batch_number;
                        $stock->ingredients = $item->ingredients;
                        $stock->expiry_date = $item->expiry_date;
                        $stock->original_quantity = $item->quantity;
                        $stock->current_quantity = $item->quantity;
                        $stock->selling_price = ((float)($item->selling_price));
                        $stock->image = $item->image;
                        $stock->details = 'NDA Approval';
                        $stock->last_activity = 'Approved drugs stock of ' . number_format($stock->original_quantity) . " $cat->unit" . ' BY National Drug Authority.';
                        $stock->source_id = Auth::user()->id;
                        $stock->source_text = "Stock approved by NDA - " . Auth::user()->name;



                        $u = Administrator::find($stock->administrator_id);
                        if ($u != null) {
                            $stock->sub_county_id = $u->sub_county_id;
                        }


                        $stock->save();

                        $item->done_approving = 1;
                        $item->save();
                    }
                }
            }
        });
    }
}
