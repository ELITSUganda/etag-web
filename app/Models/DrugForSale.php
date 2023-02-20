<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugForSale extends Model
{
    use HasFactory;

    public function getImagesAttribute()
    {
        return  Image::where([
            'type' => 'DrugForSale',
            'parent_id' => $this->id,
        ])->get(); 
    }
    protected $appends = ['images'];
}
