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

    public function getNameTextAttribute()
    {
        $loc = Location::find($this->subcounty_id);
        if ($loc == null) {
            return $this->name;
        }
        return $this->name . ' - ' . $loc->name_text;
    }

    public function getSubcountyTextAttribute()
    {
        $loc = Location::find($this->subcounty_id);
        if ($loc == null) {
            return '-';
        }
        return $loc->name;
    }

    public function getDistrictTextAttribute()
    {
        $loc = Location::find($this->district_id);
        if ($loc == null) {
            return '-';
        }
        return $loc->name;
    }

    protected $appends = ['subcounty_text', 'name_text', 'district_text'];
}
