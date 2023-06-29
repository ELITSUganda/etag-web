<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementRoute extends Model
{
    public function checkpoints()
    {
        return $this->hasMany(CheckPoint::class);
    }
    use HasFactory;

    public function getStartLocationTextAttribute()
    {
        $l = Location::find($this->start_location_id);
        if ($l == null) {
            return $this->start_location_id;
        }
        return $l->name_text;
    }
    public function getEndLocationTextAttribute()
    {
        $l = Location::find($this->end_location_id);
        if ($l == null) {
            return $this->end_location_id;
        }
        return $l->name_text;
    }
    protected $appends = [
        'start_location_text',
        'end_location_text',
    ];
}
