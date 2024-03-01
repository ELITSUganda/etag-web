<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationProgram extends Model
{

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vaccinationProgram) {
            $vaccinationProgram->status = 'Upcoming';
            //get a program in same sub_county that is upcoming
            $upcomingProgram = VaccinationProgram::where('sub_district_id', $vaccinationProgram->sub_district_id)
                ->where('status', 'Upcoming')
                ->first();
            if ($upcomingProgram != null) {
                throw new \Exception('There is an upcoming program in the same sub county');
            }
        });
    }

    //appends for sub_district_text
    protected $appends = [
        'sub_district_text'
    ];

    //getter for sub_district_text
    public function getSubDistrictTextAttribute()
    {
        $s = Location::find($this->sub_district_id);
        if ($s == null) {
            return "N/A";
        }
        return $s->name_text;
    }

    use HasFactory;
}
