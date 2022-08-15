<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDrugStockApprovalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_drug_stock_approval_id',
        'drug_category_id',
        'quantity',
        'note',
        'status',
        'name',
        'manufacturer',
        'batch_number',
        'ingredients',
        'expiry_date',
        'original_quantity',
        'selling_price',
        'image',
        'details',
        'done_approving',
    ]; 

    function FormDrugStockApproval()
    {
        return $this->belongsTo(FormDrugStockApproval::class);
    }
    
}
