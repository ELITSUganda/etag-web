<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementAnimal extends Model
{
    use HasFactory; 

    public function movement()
    {
        return $this->belongsTo(Movement::class);
    }   
    
}
