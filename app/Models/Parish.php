<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    protected $fillable = ['name', 'detail','sub_county_id'];

    use HasFactory; 
    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }   
}
