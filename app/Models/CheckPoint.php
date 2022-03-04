<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckPoint extends Model
{
    use HasFactory; 

    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }
} 
