<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOld extends Model
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


    public function getSlugAttribute($s)
    {
        if ($s == null) {
            $s = Utils::makeSlug($this->name);
            $this->slug = $s;
            $this->save();
        }
        return $s;
    }


    public function images()
    { 
        return $this->hasMany(Image::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $m->name = "$m->weight KGs $m->breed $m->type for sell 2023 - best for $m->best_for @ UGX $m->price";
            $m->slug = Utils::makeSlug($m->name);
        });
        self::created(function ($m) {
            Utils::process_images_in_foreround();
        });
    }
}
