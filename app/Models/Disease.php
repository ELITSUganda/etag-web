<?php

namespace App\Models;

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
        self::deleting(function ($m) {
            die("Ooops! You cannot delete this item.");
        });
    }
}
