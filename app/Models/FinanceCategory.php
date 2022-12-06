<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceCategory extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {

            $name = trim($m->name);
            $administrator_id = ((int)(trim($m->administrator_id)));
            $resp = FinanceCategory::where([
                'name' => $name,
                'administrator_id' => $administrator_id,
            ])->first();
            if ($resp != null) {
                throw new Exception('You alreafy have account with same name.');
            }
        });
    }
}
