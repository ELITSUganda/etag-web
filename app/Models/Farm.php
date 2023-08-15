<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Farm extends Model
{
    use HasFactory;
    protected $appends = [
        'administrator_text',
        'district_text',
        'sub_county_text',
        'created_text',
    ];
    protected $fillable = [
        'administrator_id',
        'district_id',
        'sub_county_id',
        'parish_id',
        'size',
        'latitude',
        'longitude',
        'dfm',
        'name',
        'farm_type',
        'holding_code',
    ];

    public function getCreatedTextAttribute()
    {
        return Utils::my_date($this->created_at);
    }

    public function getAnimalsCountAttribute()
    {
        return Animal::where([
            'farm_id' => $this->id
        ])->count();
    }

    public function getSheepCountAttribute()
    {
        return Animal::where([
            'farm_id' => $this->id,
            'type' => 'Sheep'
        ])->count();
    }
    public function getGoatCountAttribute()
    {
        return Animal::where([
            'farm_id' => $this->id,
            'type' => 'Goat'
        ])->count();
    }
    public function getCattleCountAttribute()
    {
        return Animal::where([
            'farm_id' => $this->id,
            'type' => 'Cattle'
        ])->count();
    }

    public function getAdministratorTextAttribute()
    {
        return Utils::get_object(Administrator::class, $this->administrator_id)->name;
    }

    public function getSubCountyTextAttribute()
    {
        return Utils::get_object(Location::class, $this->sub_county_id)->name_text;
    }

    public function getDistrictTextAttribute()
    {
        return Utils::get_object(Location::class, $this->district_id)->name_text;
    }

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            return Farm::my_update($model);
        });


        self::updating(function ($model) {
            return Farm::my_update($model);
        });




        self::updated(function ($model) {
            if ($model->animals != null) {
                foreach ($model->animals as $key => $animal) {
                    $animal->administrator_id = $model->administrator_id;
                    $animal->save();
                }
            }
            // ... code here
        });


        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    public static function my_update($m)
    {
        $sub = Location::find($m->sub_county_id);
        if ($sub == null) {
            $m->sub_county_id = 1002007;
            $sub = Location::find($m->sub_county_id);
        }
        if ($sub == null) {
            throw new Exception("Subcounty not found.", 1);
        }

        if ($m->holding_code == null || strlen($m->holding_code) < 3) {
            $num = (int) (Farm::where(['sub_county_id' => $m->sub_county_id])->count());
            $num++;
            $m->holding_code = $sub->code . "-" . $num;
        }

        if ($sub->parent != null) {
            $m->district_id = $sub->parent;
        }


        $sub = Location::find($m->sub_county_id);
        $dis = Location::find($sub->parent);

        //echo $m->owner()->name.". <b>District:</b>" . $dis->name . " <b>Subcounty: </b>" . $sub->name . " Holding: " . $m->holding_code . "<br>";
        

        return $m;
    }


    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function user()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function district()
    {
        return $this->belongsTo(Location::class, 'district_id');
    }

    public function sub_county()
    {
        return $this->belongsTo(Location::class, 'sub_county_id');
    }

    public function owner()
    {
        $id = $this->administrator_id;
        $u = Administrator::find($id);
        if ($u == null) {
            $this->administrator_id = 1;
            $this->save();
            $id = $this->administrator_id;
            $u = Administrator::find($id);
        }
        return $u;

        //return Administrator::findOrFail();
    }
}
