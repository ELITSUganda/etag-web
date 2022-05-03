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


    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
  

            $s = MovementHasMovementAnimal::where([
                'movement_id' => $model->movement_id,
                'movement_animal_id' => $model->movement_animal_id,
            ])->first(); 
            if ($s != null) { 
                return false;
            } 
            

            return $model;
        });
        

        self::created(function ($model) {
        });

        self::updating(function ($model) {
            // ... code here
        });

        self::updated(function ($model) {
            // ... code here
        });
 
 
    }

}
