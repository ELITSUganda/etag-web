<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            try {
                $imgs = Image::where('parent_id', $m->id)->orwhere('product_id', $m->id)->get();
                foreach ($imgs as $img) {
                    $img->delete();
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        });
    }

    public function getRatesAttribute()
    {
        $imgs = Image::where('parent_id', $this->id)->orwhere('product_id', $this->id)->get();
        return json_encode($imgs);
    }

    //getter for drug_category_text 
    public function getDrugCategoryTextAttribute()
    {
        $drug_category = DrugCategory::find($this->drug_category_id);
        if ($drug_category == null) {
            return 'N/A';
        }
        return $drug_category->name . ".";
    }

    //getter for administrator_text
    public function getAdministratorTextAttribute()
    {
        $admin = Administrator::find($this->administrator_id);
        if ($admin == null) {
            return 'N/A';
        }
        return $admin->business_name . "-" . $admin->name . ".";
    }

    protected $casts = [
        'data' => 'json',
    ];
}
