<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaughterHouse extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $sub = Location::find($m->subcounty_id);
            if ($sub == null) {
                throw new Exception("Subcounty not found.", 1);
            }
            $m->district_id = $sub->parent;
            return $m;
        });
        self::updating(function ($m) {
            $sub = Location::find($m->subcounty_id);
            if ($sub == null) {
                throw new Exception("Subcounty not found.", 1);
            }
            $m->district_id = $sub->parent;
            return $m;
        });
    }

    public function district()
    {
        return $this->belongsTo(Location::class);
    }
    public function subcounty()
    {
        return $this->belongsTo(Location::class);
    }
    public function admin()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
}
