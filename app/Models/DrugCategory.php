<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        self::deleting(function ($m) {
            if ($m->id == 1) {
                throw new Exception('You cannot delete this item.');
            }
            DB::update("update wholesale_drug_stocks set drug_category_id = 1 where drug_category_id = ?", [$this->id]);
        });
        
        self::creating(function ($m) {
            $name = trim($m->name);
            $resp = DrugCategory::where([
                'name' => $name,
            ])->first();
            if ($resp != null) {
                throw new Exception('Drug category with same name already exist.');
            }
        });
    }
}
