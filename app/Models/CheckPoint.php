<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sub_county_id',
        'administrator_id',
        'movement_route_id',
        'latitube',
        'longitude',
    ];

    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }

    public function movement_route()
    {
        return $this->belongsTo(MovementRoute::class);
    }
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }
    public function records()
    {
        return $this->hasMany(CheckPointRecord::class);
    }
}
