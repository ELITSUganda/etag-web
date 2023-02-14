<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{ 
    use HasFactory;
  /*   public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $r = AdminRole::where([
                'user_id' => $m->id,
                'role_id' => $m->role_id,
            ])->first();
            if ($r != null) {
                return false;
            }

            return $m;
        });
    } */
}
