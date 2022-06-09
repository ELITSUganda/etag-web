<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Excel;

class Event extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {


            if ($model->is_batch_import) {
                //$model->import_file = 'public/storage/files/1.xls';
                Event::process_btach_important($model);
                return false;
            }

            $animal = Animal::where('id', $model->animal_id)->first();
            if ($animal == null) {
                die("Animal ID not system.");
                return false;
            }
            $model->district_id = $animal->district_id;
            $model->sub_county_id = $animal->sub_county_id;
            $model->parish_id = $animal->parish_id;
            $model->farm_id = $animal->farm_id;
            $model->administrator_id = $animal->administrator_id;
            $model->animal_type = $animal->type;

            return $model;
        });

        self::created(function ($model) {
            $animal = Animal::find($model->animal_id)->first();
            if ($animal == null) {
                die("Animal ID not found.");
                return false;
            }
            $animal->status = $model->type;
            $animal->save();
        });

        self::updating(function ($model) {

            $animal = Animal::find($model->animal_id)->first();
            if ($animal == null) {
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }


            $model->district_id = $animal->district_id;
            $model->sub_county_id = $animal->sub_county_id;
            $model->parish_id = $animal->parish_id;
            $model->farm_id = $animal->farm_id;
            $model->administrator_id = $animal->administrator_id;
            $model->animal_type = $animal->type;

            return $model;
        });

        self::updated(function ($model) {
            $animal = Animal::find($model->animal_id);
            if ($animal == null) {
                die("Animal with same elecetronic ID aready exist in the system.");
                return false;
            }
            $animal->status = $model->type;
            $animal->save();
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    public static function process_btach_important($m)
    {


        $file = null;
        $file_path = $m->import_file;
        $event_type = "Treatment";

        if (file_exists($file_path)) {
            $file = $file_path;
        }

        if ($file == null) {
            die("not found");
            return;
        }

        $array = Excel::toArray([], $file);
        $i = 0;
        $_not_found = [];
        $_success = [];
        $_duplicates = [];
        foreach ($array[0] as $key => $v) {
            $i++;
            if (
                $i <= 1 ||
                (count($v) < 6) ||
                (!isset($v[5])) ||
                (!isset($v[3])) ||
                (!isset($v[0])) ||
                ($v[0] == null) ||
                ($v[3] == null) ||
                ($v[5] == null)
            ) {
                continue;
            }

            $tag = trim($v[5]);
            $t = $v[3];
            $id = $v[0];

            $animal = Animal::where([
                'v_id' => $tag
            ])
                ->orWhere([
                    'e_id' => $tag
                ])
                ->first();

            if ($animal == null) {
                $_not_found[] = $id;
                continue;
            }

            $time = Carbon::parse($t);
            if ($time == null) {
                $time = new Carbon();
            }

            $time_stamp = $time->timestamp . "";
            $time_stamp = trim($time_stamp);
            $exist = Event::where([
                'animal_id' => $animal->id,
                'time_stamp' => $time_stamp,
            ])->first();

            if ($exist != null) {
                $_duplicates[] = $id;
                continue;
            }
            $e = new Event();

            $e->animal_id = $animal->id;
            $e->district_id = $animal->district_id;
            $e->sub_county_id = $animal->sub_county_id;
            $e->parish_id = $animal->parish_id;
            $e->farm_id = $animal->farm_id;
            $e->administrator_id = $animal->administrator_id;
            $e->animal_type = $animal->type;

            $e->type = $m->type;
            $e->detail = $m->detail;
            $e->approved_by = $m->approved_by;
            $e->import_file = null;
            $e->time_stamp = $time_stamp;
            if (isset($m->disease_id)) {
                $e->disease_id = $m->disease_id;
            }
            if (isset($m->vaccine_id)) {
                $e->vaccine_id = $m->vaccine_id;
            }

            if (isset($m->medicine_id)) {
                $e->medicine_id = $m->medicine_id;
            }

            $_success[]  = $id;
            $e->save();
        }

        if (!empty($_not_found)) {
            $error_1 = "Records ";
            foreach ($_not_found as $key => $v) {
                $error_1 .=  $v . ", ";
            }
            $error_1 .= " were skipped because animals with their respective e-tags were not round in the system.";
            Utils::alert_message('danger', $error_1);
        }

        if (!empty($_duplicates)) {
            $error_1 = "Records ";
            foreach ($_duplicates as $key => $v) {
                $error_1 .=  $v . ", ";
            }
            $error_1 .= " were skipped because were already recorded into the system. The system does not allow duplicates of events.";
            Utils::alert_message('danger', $error_1);
        }

        if (!empty($_success)) {
            $error_1 = "Records ";
            foreach ($_success as $key => $v) {
                $error_1 .=  $v . ", ";
            }
            $error_1 .= " events were successfully saved into the system.";
            Utils::alert_message('success', $error_1);
        }
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
