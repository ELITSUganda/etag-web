<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    use HasFactory;

    public static function get_sub_counties_array()
    {
        $subs = [];
        foreach (Location::get_sub_counties1() as $key => $value) {
            $subs[$value->id] = ((string)($value->name)) . ", " . ((string)($value->district_name));
        }
        return $subs;
    }


    public static function get_sub_counties1()
    {
        $sql = "SELECT locations.id as id, locations.name as name, districts.name as district_name FROM  locations, districts WHERE  locations.parent = districts.id AND locations.parent > 0";
        return DB::select($sql);
    }



    public static function get_sub_counties()
    {
        return Location::where(
            'parent',
            '>',
            0
        )->get();
    }
    public static function get_districts()
    {
        return Location::where(
            'parent',
            '<',
            1
        )->get();
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("You can't delete this item.");
        });
        self::updating(function ($m) {
            Location::my_update($m);
        });
        self::creating(function ($m) {
            Location::my_update($m);
        });
    }

    public static function my_update($m)
    {
        if ($m->type != 'District' && $m->type != 'Sub-County') {
            throw new \Exception("Invalid location type");
        }

        if ($m->type == 'Sub-County') {
            $dis = Location::find($m->parent);
            if ($dis == null) {
                throw new \Exception("Invalid district");
                $num = (int) (Location::where(['parent' => $dis->id])->count());
                $num++;
                $m->code = $dis->code . "-" . $num;
            }
        }

        if ($m->code == null || $m->code == "" || strlen($m->code) < 2) {
            throw new \Exception("Invalid district code");
        }

        return $m;
    }
    public function getNameTextAttribute()
    {
        if (((int)($this->parent)) > 0) {
            $mother = Location::find($this->parent);

            if ($mother != null) {
                return $mother->name . ", " . $this->name;
            }
        }
        return $this->name;
    }

    //is sub county
    public function isSubCounty()
    {
        if (((int)($this->parent)) > 0) {
            $p = Location::find($this->parent);
            if ($p != null) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        return false;
    }

    protected $appends = ['name_text'];

    //belongs to district
    public function district()
    {
        return $this->belongsTo(Location::class, 'parent', 'id');
    }
}
