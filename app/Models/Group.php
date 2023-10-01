<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }

    public function getAnimalCountAttribute()
    {
        return Animal::where('group_id', $this->id)->count();
    }

    protected $appends = ['animal_count'];
}
