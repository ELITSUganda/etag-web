<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

/*     //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            $application = Application::process_application($application);
        });

        //updating
        static::updating(function ($application) {
            $application = Application::process_application($application);
        });
    }


    public static function process_application($app)
    {
        $subcounty = Location::find($app->origin_subscount_id);
        if ($subcounty != null) {
            $app->origin_district_id = $subcounty->name;
        }
        return $app;
    } */
}
