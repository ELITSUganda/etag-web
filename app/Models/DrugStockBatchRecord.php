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

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            if ($m->is_generated != 'no') {
                return $m;
            }
            $batch = DrugStockBatch::find(10);
            if ($batch == null) {
                die("Drug batch not found.");
            }

            if ($batch->current_quantity < $m->quantity) {
                die("Insufitient quantity of selected batch.
                $m->quantity cannot be removed from $batch->current_quantity
                ");
            }

            if ($m->record_type == 'transfer') {
                $reciever = User::find($m->receiver_account);
                if ($reciever == null) {
                    admin_error("Error", 'Receiver account not found.');
                    die("");
                }
 
            } else {
                die("record type not recorgnized.");
            }



            dd($m->is_generated);
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