<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VetHasService extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
 
            $resp = VetHasService::where([ 
                'vet_id' => $m->vet_id,
                'vet_service_category_id' => $m->vet_service_category_id,
            ])->first();
            if ($resp != null) {
                return false;
            }
        });
    }

    public function cat()
    {
        return $this->belongsTo(VetServiceCategory::class,'vet_service_category_id');
    }

}
