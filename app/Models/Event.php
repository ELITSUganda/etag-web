<?php

namespace App\Models;

use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Excel;
use Exception;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;

    public const ACCEPTED_EVENT_TYPES = [
        'Milking',
        'Treatment',
        'Weight check',
        'Death',
        'Roll call',
        'Vaccination',
        'Temperature check',
        'Picture',
        'Note',
        'Batch Treatment',
        'Ownership Transfer',
        'Check point',
        'Slaughter',
        'Disease test',
        'Sample taken',
        'Sample result',
        'Test conducted',
        'Test result',
        'Calving',
        'Weaning',
        'Service',
        'Pregnancy check',
        'Abortion',
        'Mortality',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

            //first change if type is in ACCEPTED_EVENT_TYPES
            if (!in_array($model->type, self::ACCEPTED_EVENT_TYPES)) {
                throw new Exception("Event type {$model->type} is not accepted.");
            }

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

            $isMale = strtolower(trim($animal->sex)) == 'male';


            $model->district_id = $animal->district_id;
            $model->sub_county_id = $animal->sub_county_id;

            $model->e_id = $animal->e_id;
            $model->v_id = $animal->v_id;
            // $model->status = 'success';
            $model->short_description = $model->type;



            $model->farm_id = $animal->farm_id;
            $model->animal_type = $animal->type;
            $model->administrator_id = $animal->farm->administrator_id;
            $model->type = trim($model->type);

            if ($model->type == 'Calving') {
                if ($isMale) {
                    throw new Exception("Only female animals can undergo calving.");
                }
                if ($animal->is_pregnant != 'Yes') {
                    throw new Exception("Animal is not marked as pregnant. First create a Pregnancy Check event and mark the animal as pregnant before recording calving.");
                }
                // Get last Pregnancy check event that is marked Pregnant
                $lastPregnancyCheck = DB::selectOne(
                    "SELECT * FROM events WHERE animal_id = ? AND type = ? AND status = ? ORDER BY id DESC LIMIT 1",
                    [$animal->id, 'Pregnancy check', 'Pregnant']
                );
                if (!$lastPregnancyCheck) {
                    throw new Exception("Animal is not marked as pregnant. First create a Pregnancy Check event and mark the animal as pregnant before recording calving.");
                }

                $calf = Animal::find($model->calf_id);
                if ($calf == null) {
                    throw new Exception("Calf ID {$model->calf_id} not found.");
                }

                if ($calf->id == $animal->id) {
                    throw new Exception("Calf ID cannot be the same as the mother ID.");
                }

                //copy all pregnancy_check_results to the new event
                $model->service_type = $lastPregnancyCheck->service_type;
                $model->service_date = $lastPregnancyCheck->service_date;
                $model->male_id = $lastPregnancyCheck->male_id;
                $model->male_breed = $lastPregnancyCheck->male_breed;
                $model->simen_code = $lastPregnancyCheck->simen_code;
                $model->inseminator = $lastPregnancyCheck->inseminator;
                $model->calving_date = $lastPregnancyCheck->calving_date;
                $model->calf_id = $lastPregnancyCheck->calf_id;
                $model->calf_sex = $lastPregnancyCheck->calf_sex;
                $model->calf_weight = $lastPregnancyCheck->calf_weight;
                $model->wean_date = $lastPregnancyCheck->wean_date;
            } else if ($model->type == 'Service') {
                if ($isMale) {
                    throw new Exception("Only female animals can undergo service.");
                }
                $model->status = 'Pending';
                if ($model->service_type == null || $model->service_type == '') {
                    throw new Exception("Service type is required for service events.");
                }
                //service_date
                if ($model->service_date == null || $model->service_date == '') {
                    throw new Exception("Service date is required for service events.");
                }
            } else if ($model->type == 'Weaning') {
                if ($isMale) {
                    throw new Exception("Only female animals can undergo weaning.");
                }
            } else if ($model->type == 'Abortion') {
                if ($isMale) {
                    throw new Exception("Only female animals can undergo abortion.");
                }
                //animal that is not pregnant cant abort
                $lastPregnancyCheck = DB::selectOne(
                    "SELECT * FROM events WHERE animal_id = ? AND type = ? AND status = ? ORDER BY id DESC LIMIT 1",
                    [$animal->id, 'Pregnancy check', 'Pregnant']
                );
                if (!$lastPregnancyCheck) {
                    throw new Exception("Animal is not marked as pregnant. First create a Pregnancy Check event and mark the animal as pregnant before recording abortion.");
                }
            } else if ($model->type == 'Pregnancy check') {
                if ($isMale) {
                    throw new Exception("Only female animals can undergo pregnancy checks.");
                }
                $accepted_pregnancy_check_results = [
                    'Pregnant',
                    'Not Pregnant',
                    'Retest',
                ];
                //check if pregnancy_check_results is not in
                if (!in_array($model->pregnancy_check_results, $accepted_pregnancy_check_results)) {
                    throw new Exception("Invalid pregnancy check result: {$model->pregnancy_check_results}");
                }

                //pregnancy_delivery_expected_date is required
                if ($model->pregnancy_check_results == 'Pregnant') {
                    if ($model->pregnancy_delivery_expected_date == null || strlen($model->pregnancy_delivery_expected_date) < 3) {
                        throw new Exception("Pregnancy delivery expected date is required for pregnancy check events.");
                    }
                    $model->pregnancy_delivery_expected_date = Carbon::parse($model->pregnancy_delivery_expected_date);
                    $model->status = 'Pregnant';
                    //check if is already pregnant
                    $sql = "SELECT * FROM events WHERE animal_id = {$animal->id} AND type = 'Pregnancy check' ORDER BY id DESC LIMIT 1";
                    $existing = DB::select($sql);
                    if (!empty($existing)) {
                        $last = $existing[0];
                        if (isset($last->status) && strtolower($last->status) === 'pregnant') {
                            throw new Exception("Animal {$animal->id} already has an active pregnancy (status: Pregnant). Record a Calving or Abortion event before creating another Pregnant result.");
                        }
                    }

                    $service_event = Event::where([
                        'animal_id' => $animal->id,
                        'type' => 'Service',
                        'status' => 'Pending'
                    ])->orderBy('id', 'desc')->first();
                    if ($service_event != null) {
                        //copy service fields
                        $model->service_type = $service_event->service_type;
                        $model->service_date = $service_event->service_date;
                        $model->male_id = $service_event->male_id;
                        $model->male_breed = $service_event->male_breed;
                        $model->simen_code = $service_event->simen_code;
                        $model->inseminator = $service_event->inseminator;
                        $model->calving_date = $service_event->calving_date;
                        $model->calf_id = $service_event->calf_id;
                        $model->calf_sex = $service_event->calf_sex;
                        $model->calf_weight = $service_event->calf_weight;
                        $model->wean_date = $service_event->wean_date;
                    } else {
                        $model->has_error = 'Yes';
                        $model->error_code = 'EVENT_SERVICE_NOT_FOUND';
                        $error = Utils::get_error($model->error_code);
                        $model->error_message = $error['error_message'];
                        $model->error_solution = $error['error_solution'];
                    }
                }
            } else if ($model->type == 'Disease test') {
                /* if (isset($model->disease_id)) {
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
                } */
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
                        if (((float)($model->medicine_quantity)) > 0) {
                            $medicine = DrugStockBatch::find($model->medicine_id);
                            if ($medicine != null) {

                                $medicine_quantity = ((float)($model->medicine_quantity));
                                $ok = true;
                                if ($medicine->current_quantity < $medicine_quantity) {
                                    // throw new Exception("Failed to created event because available drug quantity is less than what you have entered.");
                                    $ok = false;
                                }
                                if ($ok) {
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
                                    $worth = ($medicine_quantity / $medicine->original_quantity) * $medicine->selling_price;
                                    $model->drug_worth = $worth;
                                    try {
                                        $record->save();
                                    } catch (\Throwable $th) {
                                        //throw $th;
                                    }
                                }
                            }
                        }
                    }
                }
                if (!$ok) {
                    // throw new Exception("enter valid treament parametters");
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
            } else if ($model->type == 'Vaccination') {
                //$model = self::process_vaccination($model);
            } else {
                $model->description = "{$animal->v_id} {$model->type} event was recorded.";
            }



            unset($model->disease_test_results);
            unset($model->pregnancy_check_method);
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



            $animal = Animal::find($model->animal_id);
            if ($animal == null) {
                return false;
            }
            if ($model->type == 'Milking') {
                $animal->calculateAverageMilk();
            } else if ($model->type == 'Weight check') {
                $animal->weight = $model->weight;
                $animal->save();
                try {
                    $animal->processWeightChange();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            } else if ($model->type == 'Pregnancy check') {
                //pregnancy_delivery_expected_date is required
                if ($model->pregnancy_check_results == 'Pregnant') {
                    //get service event for pregnancy check
                    $serviceEvent = DB::selectOne(
                        "SELECT id FROM events WHERE animal_id = ? AND type = ? AND status = ? ORDER BY id DESC LIMIT 1",
                        [$animal->id, 'Pregnancy check', 'Pending']
                    );

                    if ($serviceEvent) {
                        DB::update("UPDATE events SET status = ? WHERE id = ?", ['Pregnant', $model->id]);
                        $model->status = 'Pregnant';
                    }

                    //update the animal of following
                    $animal->is_pregnant = 'Yes';
                    $animal->pregnancy_delivery_expected_date = $model->pregnancy_delivery_expected_date;
                    $animal->service_date = $model->service_date;
                    $animal->save();
                } else {
                    //set not pregnant
                    $animal->is_pregnant = 'No';
                    $animal->pregnancy_delivery_expected_date = null;
                    $animal->service_date = null;
                    $animal->save();
                }
            } else if ($model->type == 'Calving') {

                $serviceEvent = DB::selectOne(
                    "SELECT id FROM events WHERE animal_id = ? AND type = ? AND status = ? ORDER BY id DESC LIMIT 1",
                    [$animal->id, 'Pregnancy check', 'Pregnant']
                );

                if ($serviceEvent) {
                    DB::update("UPDATE events SET status = ? WHERE id = ?", ['Pregnant', $model->id]);
                    $model->status = 'Not Pregnant';
                }
                $animal->is_pregnant = 'No';
                $animal->pregnancy_delivery_expected_date = null;
                $animal->service_date = null;
                $animal->save();

                $calf = Animal::find($model->calf_id);
                if ($calf != null) {
                    $calf->parent_id = $animal->id;
                    $calf->has_parent = 'Yes';
                    $calf->stage = 'Calf';
                    $calf->save();
                }

                // Close the previous active pregnancy (if any) so new pregnancies can be recorded
                $previousPregnancy = Event::where('animal_id', $animal->id)
                    ->where('type', 'Pregnancy check')
                    ->where('status', 'Pregnant')
                    ->orderByDesc('id')
                    ->first();

                if ($previousPregnancy) {
                    $previousPregnancy->status = 'Not Pregnant';
                    $previousPregnancy->save();
                }
            } else if ($model->type == 'Abortion') {

                $serviceEvent = DB::selectOne(
                    "SELECT id FROM events WHERE animal_id = ? AND type = ? AND status = ? ORDER BY id DESC LIMIT 1",
                    [$animal->id, 'Pregnancy check', 'Pregnant']
                );

                if ($serviceEvent) {
                    DB::update("UPDATE events SET status = ? WHERE id = ?", ['Pregnant', $model->id]);
                    $model->status = 'Not Pregnant';
                }
                $animal->is_pregnant = 'No';
                $animal->pregnancy_delivery_expected_date = null;
                $animal->service_date = null;
                $animal->save();

                // Close the previous active pregnancy (if any) so new pregnancies can be recorded
                $previousPregnancy = Event::where('animal_id', $animal->id)
                    ->where('type', 'Pregnancy check')
                    ->where('status', 'Pregnant')
                    ->orderByDesc('id')
                    ->first();

                if ($previousPregnancy) {
                    $previousPregnancy->status = 'Not Pregnant';
                    $previousPregnancy->save();
                }
            }


            $type = trim($model->type);
            $events = ['Stolen', 'Home slaughter', 'Death', 'Mortality'];
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

            if ($model->type == 'Vaccination') {
                //today date in this formart 2021-2-1
                try {
                    $animal->fmd = date('Y-m-d');
                    $animal->save();
                } catch (Exception $e) {
                }
                /* $vaccine = DistrictVaccineStock::find($model->vaccine_id);
                if ($vaccine == null) {
                    throw new Exception("Vaccine not found.");
                    return false;
                }
                DistrictVaccineStock::update_balance($vaccine); */
            }
        });

        self::updating(function ($model) {

            //first change if type is in ACCEPTED_EVENT_TYPES
            if (!in_array($model->type, self::ACCEPTED_EVENT_TYPES)) {
                throw new Exception("Event type {$model->type} is not accepted.");
            }

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
                throw new Exception("Animal not found ($model->animal_id).", 1);
                return false;
            }

            if ($model->type == 'Vaccination') {
                //$model = self::process_vaccination($model);
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
            if ($model->type == 'Vaccination') {
                $vaccine = DistrictVaccineStock::find($model->vaccine_id);
                if ($vaccine == null) {
                    throw new Exception("Vaccine not found.");
                    return false;
                }
                DistrictVaccineStock::update_balance($vaccine);
            }
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }

    //process_vaccination
    public static function process_vaccination($m)
    {
        return $m;
        if ($m->type != 'Vaccination') {
            return $m;
        }
        $vaccine = DistrictVaccineStock::find($m->vaccine_id);
        if ($vaccine == null) {
            throw new Exception("Vaccine not found.");
            return $m;
        }
        $vaccination_quantity = (float)($m->vaccination);
        if ($vaccination_quantity < 1) {
            throw new Exception("Vaccination quantity must be greater than 0.");
            return $m;
        }
        return $m;
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


    public function getUpdatedAtTextAttribute()
    {
        return Carbon::parse($this->updated_at)->timestamp;
    }
    // protected $appends = ['updated_at_text'];
}
