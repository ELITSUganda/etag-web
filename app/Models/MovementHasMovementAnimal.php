<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementHasMovementAnimal extends Model
{
    use HasFactory;

    public function movement()
    {
        return $this->belongsTo(Movement::class, 'movement_id');
    } 
 
    public function animal()
    {
        return $this->belongsTo(Animal::class, 'movement_animal_id');
    }  
 
    public function movement_animal()
    {
        return $this->belongsTo(MovementAnimal::class, 'movement_animal_id');
    }  

    protected $fillable = [
        'movement_id',
        'movement_animal_id',
    ];

}
