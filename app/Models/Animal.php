<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use SoftDeletes;
    use HasFactory; 
    protected $fillable = [
        'administrator_id',
        'district_id',
        'sub_county_id',
        'status',
        'type',
        'breed',
        'sex',
        'e_id',
        'v_id',
        'lhc',
        'dob',
        'color',
        'farm_id',
    ];
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

            $animal = Animal::where('e_id', $model->e_id)->first();
            if ($animal != null) {
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }

            $animal = Animal::where('v_id', $model->v_id)->first();
            if ($animal != null) {
                die("Animal with same Tag ID aready exist in the system.");
                return false;
            }

            $f = Farm::find($model->farm_id);
            if ($f == null) {
                die("Farm not found.");
                return false;
            }
            if ($f->holding_code == null) {
                die("holding_code  not found.");
                return false;
            }


            $model->status = "Live";
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            $num = (int) (Animal::where(['sub_county_id' => $model->sub_county_id])->count());

            $num = $num . "";
            if (strlen($num) < 2) {
                $num = "000000" . $num;
            } else if (strlen($num) < 3) {
                $num = "00000" . $num;
            } else if (strlen($num) < 4) {
                $num = "000" . $num;
            } else if (strlen($num) < 5) {
                $num = "00" . $num;
            } else if (strlen($num) < 6) {
                $num = "0" . $num;
            } else {
                $num = "" . $num;
            }

            $model->lhc = $f->holding_code;

            return $model;
        });

        self::updating(function ($model) {
            $f = Farm::find($model->farm_id);
            if ($f == null) {
                return false;
            }
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            return $model;
        });

        self::updated(function ($an) {
        });

        self::deleting(function ($model) {
            if ($model->events != null) {
                foreach ($model->events as $key => $eve) {
                    $eve->delete();
                }
            }
            return $model;
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }
    public function getImagesAttribute()
    {
        return  Image::where([
            'parent_id' => $this->id,
        ])->get();
    }

    public function getPhoneNumberAttribute()
    {
        return "+256706638494";
    }

    public function getWhatsappAttribute()
    {
        return "+8801632257609";
    }

    public function getPriceTextAttribute()//romina
    {
        return "UGX ".number_format($this->price);
    }

    public function getPostedAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->dob)->diffForHumans();
    }

    protected $appends = ['images', 'phone_number', 'whatsapp', 'price_text', 'posted','age'];
}
