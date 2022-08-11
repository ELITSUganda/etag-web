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
    ];
}
