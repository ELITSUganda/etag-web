<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDrugStockApproval extends Model
{
    use HasFactory;
    function drugs_list (){
        return $this->hasMany(Drug);
    }
}
