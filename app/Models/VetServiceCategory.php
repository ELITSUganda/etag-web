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
            $service_name = trim($m->service_name);
            $resp = VetServiceCategory::where([
                'service_name' => $service_name,
            ])->first();
            if ($resp != null) {
                return false;
            }
        });
    }
}
