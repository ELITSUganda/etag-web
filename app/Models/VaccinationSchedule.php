<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationSchedule extends Model
{
    use HasFactory;

    protected $appends = ['farm_text','district_text','sub_county_text'];
    //Getter for farm_text
    public function getFarmTextAttribute()
    {
        $f = Farm::find($this->farm_id);
        if ($f == null) {
            return "N/A";
        }
        return $f->holding_code;
    }
    //Getter for district_text
    public function getDistrictTextAttribute()
    {
        $d = Location::find($this->district_id);
        if ($d == null) {
            return "N/A";
        }
        return $d->name;
    } 
    //Getter for subcounty_text
    public function getSubCountyTextAttribute()
    {
        $s = Location::find($this->sub_county_id);
        if ($s == null) {
            return "N/A";
        }
        return $s->name_text;
    } 
    public function owner()
    { 
        $id = $this->applicant_id;
        $u = Administrator::find($id);
        if ($u == null) {
            return null;
        }
        return $u;

        //return Administrator::findOrFail();
    }

    //belongsTo relationship with Farm
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    } 
}
