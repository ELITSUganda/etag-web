<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

            if ($model->e_id != null && strlen($model->e_id) > 4) {
                $animal = Animal::where('e_id', $model->e_id)->first();
                if ($animal != null) {
                    throw new Exception("Animal with same elecetronic ID ($model->e_id) aready exist in the system.", 1);
                    return false;
                }
                $animal = Animal::where('v_id', $model->v_id)->first();
                if ($animal != null) {
                    throw new Exception("Animal with same v-ID ($model->v_id) aready exist in the system.");
                    return false;
                }
            }
            //$animal = Animal::where('v_id', $model->v_id)->first();
            /*  if ($animal != null) {
                die("Animal with same Tag ID aready exist in the system.");
                return false;
            } */

            $f = Farm::find($model->farm_id);
            if ($f == null) {
                throw new Exception("Farm not found.", 1);
                return false;
            }
            if ($f->holding_code == null) {
                throw new Exception("Holding code  not found.", 1);
                return false;
            }

            $model->status = "Active";
            $model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            $model->lhc = $f->holding_code;

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

            if (strlen($model->e_id) < 5) {
                $model->e_id = $num;
                $model->v_id = $num;
            }


            $model->has_fmd = "No";
            //check if fmd is not null
            if ($model->fmd != null && strlen($model->fmd) > 3) {
                try {
                    $fmd = Carbon::parse($model->fmd);
                    if ($fmd != null) {
                        $model->fmd = $fmd->format('Y-m-d');
                        $model->has_fmd = "Yes";
                    }
                } catch (\Throwable $th) {
                }
            }

            //check if images isset and unset
            if (isset($model->images)) {
                unset($model->images);
            }
            //check if images isset and unset
            if (isset($model->photos)) {
                unset($model->photos);
            }

            $model = self::do_prepare($model);
            return $model;
        });

        self::updating(function ($model) {
            //check if images isset and unset
            if (isset($model->images)) {
                unset($model->images);
            }
            //check if images isset and unset
            if (isset($model->photos)) {
                unset($model->photos);
            }

            $f = Farm::find($model->farm_id);
            if ($f == null) {
                $model->farm_id = 1;
                $f = Farm::find($model->farm_id);
                if ($f == null) {
                    throw new Exception("Farm not found. > {$model->farm_id}.", 1);
                    return false;
                }
            }
            if ($f->holding_code == null) {
                $sub = Location::find($f->sub_county_id);
                if ($sub == null) {
                    $f->sub_county_id = 1002007;
                    $sub = Location::find($f->sub_county_id);
                }
                $f->holding_code = Utils::get_next_lhc($sub);
            }

            $model->status = "Active";
            //$model->administrator_id = $f->administrator_id;
            $model->district_id = $f->district_id;
            $model->sub_county_id = $f->sub_county_id;
            $model->lhc = $f->holding_code;

            $model->has_fmd = "No";
            //check if fmd is not null
            if ($model->fmd != null && strlen($model->fmd) > 3) {
                try {
                    $fmd = Carbon::parse($model->fmd);
                    if ($fmd != null) {
                        $model->fmd = $fmd->format('Y-m-d');
                        $model->has_fmd = "Yes";
                    }
                } catch (\Throwable $th) {
                }
            }

            $model = self::do_prepare($model);
            return $model;
        });

        self::updated(function ($an) {});

        self::deleting(function ($model) {
            /* if ($model->events != null) {
                foreach ($model->events as $key => $eve) {
                    $eve->delete();
                }
            } */
            return $model;
        });

        self::deleted(function ($model) {
            Event::where([
                'animal_id' => $model->id
            ])->delete();
        });
    }

    //do_prepare
    public static function do_prepare($model)
    {
        $stage = 'Unknown';
        try {
            $stage = Utils::get_cattle_stage($model->dob, $model->sex);
        } catch (\Throwable $th) {
            $stage = 'Unknown';
        }
        $model->stage = $stage;

        //age
        $age = 0;
        try {
            $age = Carbon::parse($model->dob)->diffInMonths(Carbon::now());
        } catch (\Throwable $th) {
            $age = 0;
        }
        $model->age = $age;

        $w = Event::where([
            'type' => 'Weight check',
            'animal_id' => $model->id,
        ])->orderBy('id', 'Desc')->first();

        if ($w != null) {
            $weight = $w->weight;
            $model->weight = $weight;
        }

        return $model;
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'animal_id');
    }

    public function vaccinations()
    {
        return Event::where('type', 'vaccination')
            ->where('animal_id', $this->id)
            ->limit(5)
            ->get();
    }

    public function district()
    {
        return $this->belongsTo(Location::class);
    }

    public function sub_county()
    {
        return $this->belongsTo(Location::class, 'sub_county_id');
    }
    public function getLocationAttribute()
    {
        $loc = "";
        if ($this->district != null) {
            $loc = $this->district->name_text;
        }

        if ($this->sub_county != null) {
            if (strlen($loc) > 3) {
                $loc .= ",";
            }
            $loc .=  " " . $this->sub_county->name_text;
        }
        return $loc;
    }
    public function getImagesAttribute()
    {
        $imgs =   Image::where([
            'parent_id' => $this->id,
        ])->get();
        return json_encode($imgs);
    }

    public function getPhotosAttribute()
    {
        $imgs =   Image::where([
            'parent_id' => $this->id,
            'parent_endpoint' => 'Animal',
        ])->get();
        return  $imgs;
    }

    /* public function getPhotoAttribute($photo)
    {
        if ($photo !=  null && strlen($photo) > 3) {
            return $photo;
        }
        $imgs = Image::where([
            'parent_id' => $this->id,
            'parent_endpoint' => 'Animal',
        ])->get();

        if ($imgs == null || count($imgs) < 1) {
            return 'logo.png';
        }

        foreach ($imgs as $img) {
            if (
                $img->thumbnail != null && strlen($img->thumbnail) > 3 &&
                str_contains($img->thumbnail, "logo.png") == false
            ) {
                return $img->thumbnail;
            }

            if ($img->src != null && strlen($img->src) > 3) {
                return $img->src;
            }
        }

        return 'logo.png';
    } */

    public function getParentTextAttribute()
    {
        if ($this->parent_id == null) {
            return null;
        }
        $p = Animal::find($this->parent_id);
        if ($p == null) {
            return null;
        }
        return $p->v_id;
    }

    public function getPhoneNumberAttribute()
    {
        return "+256706638494";
    }

    public function getWhatsappAttribute()
    {
        return "+8801632257609";
    }

    public function getPriceTextAttribute() //romina
    {
        return "UGX " . number_format($this->price);
    }

    public function getPostedAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function getAverageMilkAttribute($x)
    {
        if ($x == null) {
            return 0;
        }
        return
            round($x, 2);
    }


    public function getWeightTextAttribute($x)
    {
        if ($x == null || strlen($x) < 2) {
            return "No weight";
        }
        return $x;
    }

    public function calculateAverageMilk()
    {
        $milk = Event::where([
            'type' => 'Milking',
            'animal_id' => $this->id,
        ])
            ->sum('milk');
        $count = Event::where([
            'type' => 'Milking',
            'animal_id' => $this->id,
        ])
            ->count('id');

        $avg = 0;
        if ($count > 0) {
            $avg = $milk / $count;
        }

        $this->average_milk = $avg;
        $this->save();
    }

    public function getUpdatedAtTextAttribute()
    {
        return Carbon::parse($this->updated_at)->timestamp;
    }
    public function getLastSeenAttribute()
    {
        $e = Event::where(['animal_id' => $this->id])->orderBy('id', 'Desc')->first();

        $last_seen = $this->created_at;
        if ($e != null) {
            $last_seen = $e->created_at;
        }
        $c = Carbon::parse($last_seen);
        $format = $c->format('d M, (') . $c->diffForHumans() . ").";
        return $format;
    }

    public function getGroupTextAttribute()
    {
        $g = Group::find($this->group_id);
        if ($g == null) {
            return null;
        }
        return $g->name;
    }

    protected $appends = [
        'images',
        'photos',
        'last_seen',
        'phone_number',
        'whatsapp',
        'price_text',
        'posted',
        'location',
        'parent_text',
        'updated_at_text',
        'group_text',
    ];

    //process_weight_change
    public function processWeightChange()
    {
        //most latest 2 weights
        $weights = Event::where([
            'type' => 'Weight check',
            'animal_id' => $this->id,
        ])->orderBy('id', 'Desc')->limit(2)->get();

        //if no weight found, return
        if ($weights == null || count($weights) < 1) {
            $this->weight = 0;
            $this->weight_change = 0;
            $this->save();
            return;
        }
        //if only one weight found, return
        if (count($weights) < 2) {
            $w1 = $weights[0];
            $this->weight = $w1->weight;
            $this->weight_change = 0;
            $this->save();
            return;
        }
        $w1 = $weights[0];
        $w2 = $weights[1];
        $this->weight = $w1->weight;
        $change = $w1->weight - $w2->weight;
        $this->weight_change = $change;
        $this->save();
    }

    //getter for local_id
    public function getLocalIdAttribute($val)
    {
        if ($val == null || strlen($val) < 4) {
            $val = Utils::get_unique_text();
            $this->local_id = $val;
            $sql = "UPDATE animals SET local_id = '$val' WHERE id = $this->id";
            DB::update($sql);
            return $val;
        }
        return $val;
    }

    //getter for age
    public function getAgeAttribute($val)
    {
        $val = (int) $val;
        if ($val == null || $val < 1) {
            $dob = null;
            try {
                $dob = Carbon::parse($this->dob);
            } catch (\Throwable $th) {
                $dob = null;
            }
            if ($dob == null) {
                try {
                    $dob = Carbon::parse($this->created_at);
                    $this->dob = $dob;
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }

            if ($dob == null) {
                $dob = Carbon::now();
                $this->dob = $dob;
            }

            $val = $dob->diffInMonths(Carbon::now());
            $this->age = $val;
            $sql = "UPDATE animals SET age = $val WHERE id = $this->id";
            DB::update($sql);
            return $val;
        }
        return $val;
    }

    /* 
    http://localhost:8888/etag-web/storage/images/thumb_1727031076-987752.jpg
    */
    //getter for profile_updated
    public function getProfileUpdatedAttribute($val)
    {
        $last_updated = null;
        if ($this->last_profile_update_date != null) {
            try {
                $last_updated = Carbon::parse($this->last_profile_update_date);
            } catch (\Throwable $th) {
                $last_updated = null;
            }
        }
        if ($last_updated == null) {
            return "No";
        }
        try {
            //updated 1 month ago
            $days = $last_updated->diffInDays(Carbon::now());

            if ($days < 30) {
                return "Yes";
            }
            return "No";
        } catch (\Throwable $th) {
            //throw $th;
        }
        return "No";
    }

    //ower
    public function owner()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    //get recent events
    public function getRecentSanitaryEvents()
    {
        $types = [
            'Disease test',
            'Vaccination',
            'Batch Treatment',
            'Temperature check',
            'Treatment',
            'Pregnancy check',
        ];
        $events = Event::where([
            'animal_id' => $this->id,
        ])->whereIn('type', $types)
            ->orderBy('id', 'Desc')
            ->limit(10)
            ->get();
        return $events;
    }
    //get recent events
    public function getRecentProductionEvents()
    {
        $types = [
            'Disease test',
            'Vaccination',
            'Batch Treatment',
            'Temperature check',
            'Treatment',
            'Pregnancy check',
        ];
        $events = Event::where([
            'animal_id' => $this->id,
        ])->whereNotIn('type', $types)
            ->orderBy('id', 'Desc')
            ->limit(10)
            ->get();
        return $events;
    }

    //get recent photos
    public function getRecentPhotos()
    {
        $imgs = Image::where([
            'parent_id' => $this->id,
            'parent_endpoint' => 'Animal',
        ])->orderBy('id', 'Desc')
            ->limit(10)
            ->get();
        return $imgs;
    }

    public function transfer_animal($receiver_farm_id)
    {
        $receiver_farm = Farm::find($receiver_farm_id);
        if ($receiver_farm == null) {
            throw new Exception("Receiver farm not found.", 1);
        }
        $default_group = Group::where([
            'administrator_id' => $receiver_farm->administrator_id,
            'is_main_group' => 'Yes'
        ])->first();

        if ($default_group == null) {
            throw new Exception("Default group not found.", 1);
        }

        $receiver = Administrator::find($receiver_farm->administrator_id);
        if ($receiver == null) {
            throw new Exception("Receiver not found.", 1);
        }
        $sender = Administrator::find($this->administrator_id);
        if ($sender == null) {
            throw new Exception("Sender not found.", 1);
        }

        $an = $this;

        $an->farm_id = $receiver_farm->id;
        $an->group_id = $default_group->id;

        $an->administrator_id = $receiver->id;
        $an->save();
        Event::where('animal_id', $an->v_id)->update(['administrator_id' => $receiver->id]);

        $ev = new Event();
        $ev->animal_id = $an->id;
        $ev->administrator_id = $receiver->administrator_id;
        $ev->administrator_id = $an->farm_id;
        $ev->type = 'Ownership Transfer';
        $ev->short_description = 'Ownership Transfer';
        $ev->detail = "Anima's ownership transfered from {$sender->name} to  {$receiver->name}.";
        $ev->description = "Anima's ownership transfered from {$sender->name} to  {$receiver->name}.";
        $ev->save();
        try {
            Utils::sendNotification(
                "Your animal {$an->v_id} ownership has been transfered to {$receiver->name} - {$receiver->phone_number}.",
                $sender->id,
                $headings = 'Animal ownership transfered'
            );
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {
            Utils::sendNotification(
                "Animal {$an->v_id} has been transfered to you by {$sender->name} - {$sender->phone_number}.",
                $receiver->id,
                $headings = 'Animal ownership received'
            );
        } catch (\Throwable $th) {
            //throw $th;
        }
        $new_an = Animal::find($an->id);
        return $new_an;
    }
}
