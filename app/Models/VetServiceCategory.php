<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VetServiceCategory extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $name = trim($m->name);
            $resp = VetServiceCategory::where([
                'name' => $name,
            ])->first();
            if ($resp != null) {
                return false;
            }
        });
    }
}
