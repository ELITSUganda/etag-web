<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    public static function get_sub_counties()
    {
        return Location::where(
            'parent',
            '>',
            0
        )->get();
    }
    public static function get_districts()
    {
        return Location::where(
            'parent',
            '<',
            1
        )->get();
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("You can't delete this item.");
        });
    }

    public function getNameTextAttribute()
    {
        if (((int)($this->parent)) > 0) {
            $mother = Location::find($this->parent);

            if ($mother != null) {
                return $mother->name . ", " . $this->name;
            }
        }
        return $this->name;
    }

    protected $appends = ['name_text'];
}
