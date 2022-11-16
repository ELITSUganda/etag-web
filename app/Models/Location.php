<?php

namespace App\Models;

use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    use HasFactory;

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
    }

    public function getNameTextAttribute()
    {
        return $this->name;

        if (!isset($this->parent)) {
            return $this->name;
        }
        if ($this->parent == null) {
            return $this->name;
        }

        if (((int)($this->parent)) < 1) {
            return $this->name;
        }

        if (((int)($this->parent)) == ((int)($this->id))) {
            return $this->name;
        }

        if (((int)($this->parent)) > 0) {

            $parent = ((int)($this->parent));
            $sql = "SELECT name FROM locations WHERE id = ($parent)";
            $recs = DB::select($sql);

            if ($recs != null) {
                if (is_array($recs)) {
                    if (!empty($recs)) {
                        if (isset($recs[0])) {
                            return $recs[0]->name . ", " . $this->name;
                        }
                    }
                }
            }
        }
        return $this->name;
    }

    protected $appends = ['name_text'];

    public function get_name()
    {
        $parent_id = ((int)($this->parent));
        if ($parent_id < 1) {
            return $this->name;
        }
        $m = Location::find($parent_id);
        if ($m == null) {
            return $this->name;
        }

        return $m->name . ", " . $this->name;
    }
}
