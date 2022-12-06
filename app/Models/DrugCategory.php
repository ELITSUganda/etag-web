<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugCategory extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(DrugCategory::class, 'drug_category_id');
    }


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $name = trim($m->name);
            $resp = FinanceCategory::where([
                'name' => $name,
            ])->first();
            if ($resp != null) {
                throw new Exception('Drug category with same name already exist.');
            }
        });
    }
}
