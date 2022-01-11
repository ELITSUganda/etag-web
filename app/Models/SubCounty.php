<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCounty extends Model
{
    protected $fillable = ['name', 'detail','district_id'];
    use HasFactory; 
    public function district()
    {
        return $this->belongsTo(District::class);
    }   
}
