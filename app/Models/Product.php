<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function get_thumnail()
    {
        Utils::process_images_in_foreround();
        $imgs = Image::where([
            'product_id' => $this->id
        ]);

        $img = url('assets/images/cow.jpeg');
        foreach ($imgs as  $v) {
            $img = $v->src;
        }
        return $img;
    }


    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            Utils::process_images_in_foreround();
        });
    }
}
