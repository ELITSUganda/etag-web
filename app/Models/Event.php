<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Excel;
use Exception;

class Event extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {


            if ($model->is_batch_import) {
                //$model->import_file = 'public/storage/files/1.xls';
                //Event::process_btach_important($model);
                //return false;
            }

            $animal = Animal::find($model->animal_id);
            if ($animal == null) {
                throw new Exception("Animal ID {$model->animal_id} not system.");
                return false;
                return false;
            }


            $model->district_id = $animal->district_id;
            $model->sub_county_id = $animal->sub_county_id;

            $model->e_id = $animal->e_id;
            $model->v_id = $animal->v_id;
            $model->status = 'success';
            $model->short_description = $model->type;



            $model->farm_id = $animal->farm_id;
            $model->animal_type = $animal->type;
            $model->administrator_id = $animal->farm->administrator_id;
            $model->type = trim($model->type);



            if ($model->type == 'Pregnancy check') {
                $ok = false;
                if (
                    isset($model->pregnancy_check_method) &&
                    isset($model->pregnancy_check_results)
                ) {
                    $pregnancy = new PregnantAnimal();
                    $pregnancy->administrator_id = $animal->farm->administrator_id;
                    $pregnancy->animal_id = $animal->id;
                    $pregnancy->district_id = $animal->farm->district_id;
                    $pregnancy->sub_county_id = $animal->farm->sub_county_id;
                    $pregnancy->original_status = $model->pregnancy_check_results;
                    $pregnancy->current_status = $model->pregnancy_check_results;
                    $pregnancy->fertilization_method = 'Natural breeding';
                    $pregnancy->expected_sex = 'Unknown';
                    $pregnancy->details = $model->detail;

                    if (isset($model->pregnancy_fertilization_method)) {
                        if ($model->pregnancy_fertilization_method != null) {
                            $pregnancy->fertilization_method = $model->pregnancy_fertilization_method;
                        }
                    }

                    if (isset($model->pregnancy_check_method)) {
                        if ($model->pregnancy_check_method != null) {
                            $pregnancy->pregnancy_check_method = $model->pregnancy_check_method;
                        }
                    }
                    if (isset($model->pregnancy_expected_sex)) {
                        if ($model->pregnancy_expected_sex != null) {
                            $pregnancy->expected_sex = $model->pregnancy_expected_sex;
                        }
                    }
                    $model->description = "Pregnancy check for animal {$animal->v_id} by {$pregnancy->pregnancy_check_method} method and found {$pregnancy->original_status}";
                    $pregnancy->save();
                    $ok = true;
                }
                if (!$ok) {
                    throw new Exception("enter valid Pregnancy check parametters");
                }
            } else if ($model->type == 'Disease test') {
                if (isset($model->disease_id)) {
                    if ($model->disease_id != null) {
                        if (isset($model->disease_test_results)) {
                            if ($model->disease_test_results != null) {
                                $sick = new SickAnimal();
                                $sick->administrator_id = $animal->administrator_id;
                                $sick->animal_id = $animal->id;
                                $sick->disease_id = $model->disease_id;
                                $sick->test_results = $model->test_results;
                                $sick->current_results = $model->current_results;
                                $sick->district_id = $animal->farm->district_id;
                                $sick->sub_county_id = $animal->farm->sub_county_id;
                                $sick->description = "Disease test for animal {$animal->v_id} and found it {$model->disease_test_results}.";
                                $sick->details = $model->detail;
                                $model->description = $sick->description;

                                $disease = Disease::find($model->disease_id);

                                if ($disease != null) {
                                    $model->disease_text = $disease->name;
                                } else {
                                    $model->disease_text = "Disease #{$model->disease_id}";
                                }


                                if ($model->disease_test_results == 'Positive') {
                                    $model->short_description = "Positive (Has {$model->disease_text})";
                                    $model->status = 'danger';
                                } else {
                                    $model->short_description = "Negative (Has no {$model->disease_text})";
                                    $model->status = 'success';
                                }

                                $sick->save();
                            }
                        }
                    }
                }
            } else if ($model->type == 'Milking') {
                $ok = false;
                if (isset($model->milk)) {
                    if ($animal->sex != 'Female') {
                        throw new Exception("You cannot milk a non female animal.");
                    }
                    if ($model->milk != null) {
                        $ok = true;
                        $model->milk = (float)($model->milk);
                        $model->description = "Milked {$model->milk} liters from {$animal->v_id}.";
                    }
                }
                if (!$ok) {
                    throw new Exception("enter valid milking parametters");
                }
            } else if ($model->type == 'Weight check') {
                if (isset($model->weight)) {
                    if ($model->weight != null) {
                        $ok = true;
                        $model->weight = (float)($model->weight);
                        $model->description = "{$animal->v_id} wighed {$model->weight} KGs.";
                        $animal->weight = $model->weight;
                        $_time = Utils::my_date(Carbon::now());
                        $animal->weight_text = $animal->weight . "KGs - $_time";
                        $animal->save();
                    }
                }
            } else if ($model->type == 'Treatment') {
                $ok = false;
                if (isset($model->medicine_quantity)) {
                    if ($model->medicine_id != null) {
                        if (((int)($model->medicine_quantity)) > 0) {
                            $medicine = DrugStockBatch::find($model->medicine_id);
                            if ($medicine != null) {

                                $medicine_quantity = ((int)($model->medicine_quantity));
                                if ($medicine->current_quantity < $medicine_quantity) {
                                    throw new Exception("Failed to created event because available drug quantity is less than what you have entered.");
                                }
                                $ok = true;
                                $record = new DrugStockBatchRecord();
                                $record->record_type = 'animal_event';
                                $record->administrator_id = $animal->administrator_id;
                                $record->drug_stock_batch_id = $medicine->id;
                                $record->batch_number = $medicine->batch_number;
                                $record->receiver_account = null;
                                $record->other_explantion = $model->detail;
                                $record->buyer_info = null;
                                $record->is_generated = 'no';
                                $record->event_animal_id = $animal->id;
                                $record->quantity = $medicine_quantity;
                                $record->description = "Applied Quantity: {$medicine_quantity} {$medicine->category->unit} of  Drug: {$medicine->category->name}, Stock ID: #{$medicine->id}, Batch number: {$medicine->batch_number} to Animal ID: {$animal->id}, E-ID:  {$animal->e_id}, V-ID:  {$animal->v_id}.";
                                $model->description = $record->description;

                                $model->short_description = "Applied {$medicine->category->name} {$animal->id}, E-ID:  {$animal->e_id}, V-ID:  {$animal->v_id}.";

                                $model->medicine_text = $medicine->category->name;
                                $model->medicine_quantity = "{$medicine_quantity} {$medicine->category->unit}";

                                $model->medicine_name = $medicine->name;
                                $model->medicine_batch_number = $medicine->batch_number;
                                $model->medicine_supplier = $medicine->source_text;
                                $model->medicine_manufacturer = $medicine->manufacturer;
                                $model->medicine_expiry_date = $medicine->expiry_date;
                                $model->medicine_image = $medicine->image;




                                $record->save();
                            }
                        }
                    }
                }
                if (!$ok) {
                    throw new Exception("enter valid treament parametters");
                }
            } else if ($model->type == 'Temperature check') {
                $model->description = "{$animal->v_id} body temperature measured {$model->temperature} degrees Celsius.";
            } else if ($model->type == 'Stolen') {
                $model->description = "{$animal->v_id} - {$animal->e_id} was reported stollen.";
            } else if ($model->type == 'Home slaughter') {
                $model->description = "{$animal->v_id} was slaughtered from home.";
            } else if ($model->type == 'Death') {
                $model->description = "{$animal->v_id} died.";
            } else if ($model->type == 'Photo') {
                $model->detail =  "{$animal->v_id}'s photo was recorded.";
            } else if ($model->type == 'Note') {
                $model->detail =  "{$animal->v_id}'s note was recorded.";
            } else {
                $model->description = "{$animal->v_id} {$model->type} event was recorded.";
            }





            unset($model->disease_id);
            unset($model->disease_test_results);
            unset($model->pregnancy_check_method);
            unset($model->pregnancy_check_results);
            unset($model->pregnancy_fertilization_method);
            unset($model->pregnancy_expected_sex);


            if ($model->description == null || (strlen($model->description) < 2)) {
                $model->description = $model->detail;
            }
            if ($model->detail == null || (strlen($model->detail) < 2)) {
                $model->detail = $model->description;
            }

            return $model;
        });

        self::created(function ($model) {



            if ($model->type == 'Milking') {
                $animal = Animal::find($model->animal_id);
                if ($animal == null) {
                    return false;
                }
                $animal->calculateAverageMilk();
            }
            $type = trim($model->type);
            $events = ['Stolen', 'Home slaughter', 'Death'];
            $user = Administrator::find($model->administrator_id);
            if (in_array($type, $events)) {
                if ($user == null) {
                    $user = new Administrator();
                }
                $d['event'] = $type;
                $d['details'] =  $type . " By " . $user->name . " - " . $user->id;
                $d['animal_id'] = $model->animal_id;
                Utils::archive_animal($d);
                return false;
            }


            $animal = Animal::find($model->animal_id);
            if ($animal == null) {
                return false;
            }
            $animal->status = $model->type;
            $animal->save();
        });

        self::updating(function ($model) {


            if (isset($model->disease_id)) {
                unset($model->disease_id);
            }
            if (isset($model->disease_test_results)) {
                unset($model->disease_test_results);
            }

            $type = trim($model->type);
            $events = ['Stolen', 'Home slaughter', 'Slaughter', 'Death'];
            $user = Administrator::find($model->administrator_id);
            if (in_array($type, $events)) {
                if ($user == null) {
                    $user = new Administrator();
                }
                $d['event'] = $type;
                $d['details'] =  $type . " By " . $user->name . " - " . $user->id;
                $d['animal_id'] = $model->animal_id;
                Utils::archive_animal($d);
                return false;
            }


            $animal = Animal::find($model->animal_id);
            if ($animal == null) {
                throw new Exception("Animal not found.", 1);
                return false;
            }

            $model->district_id = $animal->district_id;
            $model->sub_county_id = $animal->sub_county_id;
            $model->parish_id = $animal->parish_id;
            $model->farm_id = $animal->farm_id;
            $model->animal_type = $animal->type;
            return $model;
        });

        self::updated(function ($model) {
            $animal = Animal::find($model->animal_id);
            if ($animal == null) {
                throw new Exception("Animal with same elecetronic ID aready exist in the system.");
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
            throw new Exception("not found");
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


    public function getUpdatedAtTextAttribute(){
        return Carbon::parse($this->updated_at)->timestamp;
    } 
    protected $appends = ['updated_at_text']; 
}
