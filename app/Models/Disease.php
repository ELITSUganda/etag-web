<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;



    public function getPhotoAttribute($photo)
    {
        if (
            ($photo == null) ||
            ((strlen($photo) < 2))
        ) {
            return url('assets/logo.png');
        }
        return url('storage/' . $photo);
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $name = trim($m->name);
            $resp = Disease::where([
                'name' => $name,
            ])->first();
            if ($resp != null) {
                throw new Exception('A disease with same name already exist in the system.');
            }
        });

        self::deleting(function ($m) {
            throw new Exception('Ooops! You cannot delete this item.');
        });
    }
}
