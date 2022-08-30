<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugStockBatchRecord extends Model
{
    use HasFactory;

    function batch()
    {
        return $this->belongsTo(DrugStockBatch::class, 'drug_stock_batch_id');
    }



    public function get_created_date()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y - h:m a');
    }

    public function get_details()
    {
        /* 
    "id" => 12
    "created_at" => "2022-08-30 06:24:08"
    "updated_at" => "2022-08-30 06:25:25"
    "administrator_id" => 10
    "drug_category_id" => 1
    "sub_county_id" => 5
    "source_id" => 17
    "source_text" => "Stock approved by NDA - Beau Mayer"
    "name" => "Paracetamol"
    "manufacturer" => "JPE"
    "batch_number" => "1291899"
    "ingredients" => "80% fats"
    "expiry_date" => "2024-07-17"
    "original_quantity" => 50000.0
    "current_quantity" => 50000.0
    "selling_price" => "1000"
    "image" => "public/storage/images/full web.png"
    "last_activity" => "Approved drugs stock of 50,000 KGs BY National Drug Authority."
    "details" => "NDA Approval"
  ]
*/

        $description = "-";
        if ($this->record_type == 'nda_approval') {
            $description = "NDA Approved " . number_format($this->batch->original_quantity) . " units this drug.";
        } else if ($this->record_type == 'transfer') {
            $reciever = Administrator::find($this->receiver_account);
            $reciever_name = "-";
            if ($reciever != null) {
                $reciever_name = $reciever->name;
            }
            $description = "Transfered " . number_format($this->quantity) . " Units of this drugs to {$reciever_name}.";
        }
        return $description;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $batch = DrugStockBatch::find($m->drug_stock_batch_id);
            if ($batch == null) {
                die("Drug batch not found.");
            }
            $batch->batch_number = $batch->batch_number;

            if ($m->is_generated != 'no') {
                return $m;
            }

            $m->administrator_id = $batch->administrator_id;

            if ($batch->current_quantity < $m->quantity) {
                die("Insufitient quantity of selected batch.
                $m->quantity cannot be removed from $batch->current_quantity
                ");
            }
            $batch->current_quantity -= $m->quantity;

            $batch->save();

            if ($m->record_type == 'transfer') {
                $reciever = User::find($m->receiver_account);
                $sender = User::find($m->administrator_id);
                if ($reciever == null) {
                    admin_error("Error", 'Receiver account not found.');
                }
                if ($sender == null) {
                    admin_error("Error", 'Sender account not found.');
                }

                $new_batch = new DrugStockBatch();
                $new_batch->administrator_id = $reciever->id;
                $new_batch->drug_category_id = $batch->drug_category_id;
                $new_batch->sub_county_id = $batch->sub_county_id;
                $new_batch->source_id = $batch->administrator_id;
                $new_batch->name = $batch->name;
                $new_batch->manufacturer = $batch->manufacturer;
                $new_batch->batch_number = $batch->batch_number;
                $new_batch->ingredients = $batch->ingredients;
                $new_batch->expiry_date = $batch->expiry_date;
                $new_batch->original_quantity = $m->quantity;
                $new_batch->current_quantity = $m->quantity;
                $new_batch->selling_price = $batch->selling_price;
                $new_batch->image = $batch->image;
                $new_batch->source_text = "Drug of " . number_format($m->quantity) . " UNITS tarnsfered from {$sender->name} to  {$reciever->name} Account.";
                $new_batch->last_activity = $new_batch->source_text;
                $new_batch->details = $new_batch->source_text;
                $new_batch->save();
            } else {
            }
        });
    }
}
/* 


administrator_id	
description	
event_animal_id	
buyer_info	
other_explantion



$rec->drug_stock_batch_id = $batch->id;
$rec->record_type = 'transfer';
$rec->receiver_account = 4;
$rec->is_generated = 'no';
*/