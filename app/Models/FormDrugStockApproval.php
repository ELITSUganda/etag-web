<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDrugStockApproval extends Model
{
    use HasFactory;
    function applicant(){
        return $this->belongsTo(Administrator::class);
    }
    function items()
    {
        return $this->hasMany(FormDrugStockApprovalItem::class);
    }
}
