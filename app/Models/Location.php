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
            'type',
            'District'
        )->where('id', '>', 0)
            ->orderBy('name', 'asc')
            ->get();
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("You can't delete this item.");
        });
        self::updating(function ($m) {
            $m = Location::my_update($m);
            return $m;
        });
        self::creating(function ($m) {
            $m = Location::my_update($m);
            return $m;
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
            }

            if ($m->code == null || $m->code == "" || strlen($m->code) < 4) {
                if ($dis->code == null || $dis->code == "" || strlen($dis->code) < 2) {
                    throw new \Exception("Invalid district code for the subcounty.");
                }
                $code = Location::generate_sub_county_code($dis->id);
                $m->code = $code;
                if ($m->code == null || $m->code == "" || strlen($m->code) < 4) {
                    throw new \Exception("Invalid subcounty code.");
                }
            }
        } else if ($m->type == 'District') {
            $m->parent = 0;
            if ($m->code == null || $m->code == "" || strlen($m->code) < 4) {
                throw new \Exception("Invalid district code.");
            }
        }
        $m->processed = 'Yes';
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

    //generate subcount code
    public static function generate_sub_county_code($district_id)
    {
        $dis = Location::find($district_id);
        if ($dis == null) {
            throw new \Exception("Invalid district");
        }
        $subs = Location::where([
            'parent' => $dis->id,
            'type' => 'Sub-County',
            'processed' => 'Yes'
        ])->orderBy('id', 'asc')->get();
        $max_num = 0;

        foreach ($subs as $key => $value) {
            $explodes =   (explode("-", $value->code));
            if (count($explodes) > 1) {
                //get last
                $last = $explodes[count($explodes) - 1];
                $last = (int)($last);
                if ($last > $max_num) {
                    $max_num = $last;
                }
            }
        }
        $max_num++;
        return $dis->code . "-" . $max_num;
    }

    public static function update_counts($lcoation_id)
    {
        /* 
        $table->integer('farm_count')->nullable()->default(0);
        $table->integer('')->nullable()->default(0);
        $table->integer('')->nullable()->default(0);
        $table->integer('')->nullable()->default(0);

        
 */
        $location = Location::find($lcoation_id);
        if ($location == null) {
            return;
        }
        if ($location->type == 'District') {
            $location->farm_count = Farm::where([
                'district_id' => $location->id
            ])->count();
            $location->cattle_count = Farm::where([
                'district_id' => $location->id
            ])->sum('cattle_count');
            $location->goat_count = Farm::where([
                'district_id' => $location->id
            ])->sum('goats_count');
            $location->sheep_count = Farm::where([
                'district_id' => $location->id
            ])->sum('sheep_count');
            $location->save();
        } else if ($location->type == 'Sub-County') {
            $location->farm_count = Farm::where([
                'sub_county_id' => $location->id
            ])->count();
            $location->cattle_count = Farm::where([
                'sub_county_id' => $location->id
            ])->sum('cattle_count');
            $location->goat_count = Farm::where([
                'sub_county_id' => $location->id
            ])->sum('goats_count');
            $location->sheep_count = Farm::where([
                'sub_county_id' => $location->id
            ])->sum('sheep_count');
            $location->save();
        }
    }
}
