<?php

namespace App\Http\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\ArchivedAnimal;
use App\Models\BatchSession;
use App\Models\Disease;
use App\Models\DistrictVaccineStock;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\FarmVaccinationRecord;
use App\Models\Group;
use App\Models\Image;
use App\Models\Location;
use App\Models\Movement;
use App\Models\SlaughterDistributionRecord;
use App\Models\SlaughterHouse;
use App\Models\SlaughterRecord;
use App\Models\User;
use App\Models\Utils;
use App\Models\VaccinationProgram;
use App\Models\VaccinationSchedule;
use App\Models\VaccineMainStock;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\Slack\SlackRecord;

class ApiAnimalController extends Controller
{



    public function upload_media(Request $request)
    {

        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $parent_id = 0;

        if (
            !isset($request->parent_id) ||
            $request->parent_id == null ||
            ((int)($request->parent_id)) < 1
        ) {


            if (
                !isset($request->online_parent_id) ||
                $request->online_parent_id == null ||
                ((int)($request->online_parent_id)) < 1
            ) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Local parent ID is missing.",
                ]);
            } else {
                $parent_id = ((int)($request->online_parent_id));
            }
        } else {
            $parent_id = ((int)($request->parent_id));
        }


        if (
            !isset($request->parent_endpoint) ||
            $request->parent_endpoint == null ||
            (strlen(($request->parent_endpoint))) < 3
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Local parent ID endpoint is missing.",
            ]);
        }

        if (
            empty($_FILES)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Files not found.",
            ]);
        }

        $images = Utils::upload_images_1($_FILES, false);
        $_images = [];

        if (empty($images)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to upload files.',
                'data' => null
            ]);
        }

        $msg = "";
        foreach ($images as $src) {

            if ($request->parent_endpoint == 'edit') {
                $img = Image::find($request->local_parent_id);
                if ($img) {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Original photo not found",
                    ]);
                }
                $img->src =  $src;
                $img->thumbnail =  null;
                $img->save();
                return Utils::response([
                    'status' => 1,
                    'data' => json_encode($img),
                    'message' => "File updated.",
                ]);
            }


            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_endpoint =  $request->parent_endpoint;
            $img->parent_id =  (int)($parent_id);
            $img->size = 0;
            $img->note = '';
            if (
                isset($request->note)
            ) {
                $img->note =  $request->note;
                $msg .= "Note not set. ";
            }

            $online_parent_id = ((int)($request->online_parent_id));
            if (
                $online_parent_id > 0
            ) {
                $animal = Animal::find($online_parent_id);
                if ($animal != null) {
                    $img->parent_endpoint =  'Animal';
                    $img->parent_id =  $animal->id;
                } else {
                    $msg .= "parent_id NOT not found => {$request->online_parent_id}.";
                }
            } else {
                $msg .= "Online_parent_id NOT set. => {$online_parent_id} ";
            }

            $img->save();
            $_images[] = $img;
        }
        //Utils::process_images_in_backround();
        return Utils::response([
            'status' => 1,
            'data' => json_encode($_POST),
            'message' => "File uploaded successfully.",
        ]);
    }


    public function create_slaughter(Request $request)
    {

        if ($request->animal_ids == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animals must be provided.",
            ]);
        }



        $details =  ((string)($request->details));

        $user_id = Utils::get_user_id($request);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u  == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter house not found.",
            ]);
        }
        $animal = json_decode($request->animal_ids);

        if ($animal == null || empty($animal)) {
            return Utils::response([
                'status' => 0,
                'message' => "No animals found.",
            ]);
        }
        $i = 0;
        foreach ($animal as $key => $id) {
            $_id = ((int)($id));
            if ($_id < 1) {
                continue;
            }
            $an = Animal::find($_id);
            if ($an == null) {
                continue;
            }

            $sr = new SlaughterRecord();
            $sr->lhc = $an->lhc;
            $sr->v_id = $an->v_id;
            $sr->administrator_id = $user_id;
            $sr->e_id = $an->e_id;
            $sr->breed = $an->breed;
            $sr->sex = $an->sex;
            $sr->dob = $an->dob;
            $sr->fmd = $an->fmd;
            $sr->details = "Slautered by " . $u->name . ", ID " . $u->id . ". " . $details;
            $sr->destination_slaughter_house = $u->name;

            if ($sr->save()) {
                Utils::archive_animal([
                    'animal_id' => $_id,
                    'details' => $sr->details,
                    'event' => 'Slautered',
                ]);
            }
            $i++;
        }

        return Utils::response([
            'status' => 1,
            'message' => "{$i} Slauhter records have been created successfully.",
        ]);
    }

    public function vaccination_schedules_list(Request $r)
    {
        $user_id = Utils::get_user_id($r);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }
        $conds = [];
        if ($u->isRole('farmer')) {
            $conds['applicant_id'] = $user_id;
        } else if (
            $u->isRole('dvo') ||
            $u->isRole('admin') ||
            $u->isRole('admininistrator') ||
            $u->isRole('scvo')
        ) {
            $conds = [];
            if ($u != null) {
                if (
                    $u->isRole('dvo') ||
                    $u->isRole('administrator') ||
                    $u->isRole('scvo') ||
                    $u->isRole('clo') ||
                    $u->isRole('admin')
                ) {
                    $dov_roles = AdminRoleUser::where('user_id', $user_id)->get();
                    foreach ($dov_roles as $key => $value) {
                        $dis = Location::find($value->type_id);
                        if ($dis != null) {
                            $dis_id = $dis->id;
                            if ($dis->isSubCounty()) {
                                $dis_id = $dis->parent;
                            }
                            $conds = [];
                            $conds['district_id'] = $dis_id;
                            break;
                        }
                    }
                }
            }
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => VaccinationSchedule::where($conds)->get()
        ]);
    }
    public function farm_vaccination_records(Request $r)
    {
        $user_id = Utils::get_user_id($r);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }
        $conds = [];
        if ($u->isRole('farmer')) {
            $conds['applicant_id'] = $user_id;
        } else if (
            $u->isRole('dvo') ||
            $u->isRole('admin') ||
            $u->isRole('admininistrator') ||
            $u->isRole('scvo')
        ) {
            $conds = [];
            if ($u != null) {
                if (
                    $u->isRole('dvo') ||
                    $u->isRole('administrator') ||
                    $u->isRole('scvo') ||
                    $u->isRole('clo') ||
                    $u->isRole('admin')
                ) {
                    $dov_roles = AdminRoleUser::where('user_id', $user_id)->get();
                    foreach ($dov_roles as $key => $value) {
                        $dis = Location::find($value->type_id);
                        if ($dis != null) {
                            $dis_id = $dis->id;
                            if ($dis->isSubCounty()) {
                                $dis_id = $dis->parent;
                            }
                            $conds = [];
                            $conds['district_id'] = $dis_id;
                            break;
                        }
                    }
                }
            }
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => FarmVaccinationRecord::where($conds)->get()
        ]);
    }

    public function district_vaccine_stocks(Request $r)
    {
        $user_id = Utils::get_user_id($r);

        $u = Administrator::find($user_id);
        $where = [
            'district_id' => 0
        ];

        if ($u != null) {
            if (
                $u->isRole('dvo') ||
                $u->isRole('administrator') ||
                $u->isRole('scvo') ||
                $u->isRole('clo') ||
                $u->isRole('admin')
            ) {
                $dov_roles = AdminRoleUser::where('user_id', $user_id)->get();
                foreach ($dov_roles as $key => $value) {
                    $dis = Location::find($value->type_id);
                    if ($dis != null) {
                        $dis_id = $dis->id;
                        if ($dis->isSubCounty()) {
                            $dis_id = $dis->parent;
                        }
                        $where = [];
                        $where['district_id'] = $dis_id;
                        break;
                    }
                }
            }
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => DistrictVaccineStock::where($where)->get()
        ]);
    }


    public function vaccination_programs(Request $r)
    {
        $user_id = Utils::get_user_id($r);

        /* if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }
        $adminRole = AdminRoleUser::where([
            'user_id' => $user_id,
            'role_type' => 'dvo'
        ])->first();

        $conds = [];
        if ($adminRole != null) {
            $conds =  [
                'district_id' => $adminRole->type_id_1,
            ];
        }

        if (count($conds) == 0) {
            $adminRole = AdminRoleUser::where([
                'user_id' => $user_id,
                'role_type' => 'scvo'
            ])->first();
            if ($adminRole != null) {
                $sub = Location::find($adminRole->type_id_2);
                if ($sub != null) {
                    $conds =  [
                        'district_id' => $sub->parent_id,
                    ];
                }
            }
        }

        if (count($conds) == 0) {
            return Utils::response([
                'status' => 0,
                'message' => "District not found.",
            ]);
        } */

        $conds = [];
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => VaccinationProgram::where($conds)->get()
        ]);
    }


    public function create_vaccination_schedules(Request $r)
    {
        if ($r->task == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Task not specified.",
            ]);
        }
        if (
            ($r->task != 'Create') &&
            ($r->task != 'Edit')
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Task not specified.",
            ]);
        }

        $user_id = Utils::get_user_id($r);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "User not set.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        if ($r->task == 'Create') {
            $farm = Farm::where([
                'id' => $r->farm_id
            ])->first();
            if ($farm == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Farm not found.",
                ]);
            }
            //check if farm already have another pending request
            $existing = VaccinationSchedule::where([
                'farm_id' => $farm->id,
                'status' => 'Pending'
            ])->first();
            if ($existing != null) {
                return Utils::response([
                    'data' => $existing,
                    'status' => 0,
                    'message' => "Vaccination schedule already created.",
                ]);
            }
            $owner = $farm->owner();
            if ($owner == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Farm owner not found.",
                ]);
            }

            $rec = new VaccinationSchedule();
            $rec->farm_id = $farm->id;
            $rec->vaccination_type = $r->vaccination_type;
            $rec->schedule_date = $r->schedule_date;
            $rec->applicant_message = $r->applicant_message;
            $rec->applicant_name = $owner->name;
            $rec->applicant_id = $owner->id;
            $rec->district_id = $farm->district_id;
            $rec->veterinary_officer_id = $u->id;
            $district = Location::find($farm->district_id);
            if ($district == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "District not found.",
                ]);
            }
            $rec->sub_county_id = $farm->sub_county_id;
            $sub = Location::find($farm->sub_county_id);
            if ($sub == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Subcounty not found.",
                ]);
            }

            $rec->applicant_contact = $r->applicant_contact;
            $rec->applicant_address = $farm->village;
            $rec->gps_latitute = $farm->latitude;
            $rec->gps_longitude = $farm->longitude;
            $rec->farm_id = $r->farm_id;
            $rec->applicant_message = $r->applicant_message;
            $rec->veterinary_officer_message = $r->veterinary_officer_message;
            $rec->dvo_message = $r->dvo_message;
            $rec->reason_for_rejection = $r->reason_for_rejection;
            $rec->details = $r->details;
            $rec->status = 'Pending';
            $rec->vaccination_type = 'FMD';

            try {
                $rec->save();
            } catch (\Throwable $e) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to save record. {$e->getMessage()}",
                ]);
            }

            //send notification farmer how we have received the request 
            $msg = "Your farm {$farm->holding_code} has been scheduled for vaccination on {$rec->schedule_date}. Open the App to see more details.";
            $title = "VACCINATION REQUEST - {$farm->holding_code}";
            Utils::sendNotification(
                $msg,
                $owner->id . "",
                $headings =  $title,
                $data = [$farm->id]
            );
            $owenr_phone = Utils::prepare_phone_number($rec->applicant_contact);
            if (Utils::phone_number_is_valid($owenr_phone)) {
                Utils::send_message($owenr_phone, $msg);
            }

            $user_roles = AdminRoleUser::where([
                'role_type' => 'dvo',
                'type_id_1' => $farm->district_id
            ])->get();
            foreach ($user_roles as $key => $value) {
                break;
                $admin = Administrator::find($value->user_id);
                if ($admin == null) {
                    continue;
                }
                $msg = "You have received a new vaccination request from {$owner->name}. Open the App to see more details.";
                $title = "NEW VACCINATION REQUEST - {$farm->holding_code}";
                Utils::sendNotification(
                    $msg,
                    $value->user_id . "",
                    $headings =  $title,
                    $data = [$farm->id]
                );
                $dvo_phone = Utils::prepare_phone_number($admin->phone_number);
                if (Utils::phone_number_is_valid($dvo_phone)) {
                    Utils::send_message($dvo_phone, $msg);
                }
            }

            return Utils::response([
                'status' => 1,
                'message' => "Vaccination schedule created successfully.",
                'data' => $rec,
            ]);
        }
        if (trim($r->task) == 'Edit') {
            $original = VaccinationSchedule::find($r->id);
            if ($original == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Record not found.",
                ]);
            }
            $update = $original;

            if ($r->status != null && strlen($r->status) > 0) {
                $update->status = $r->status;
            }
            if ($r->actual_date != null && strlen($r->actual_date) > 0) {
                $update->actual_date = $r->actual_date;
            }

            if ($original->status != 'Approved') {
                if ($update->status == 'Approved') {
                    $update->approver_id = $user_id;
                    $update->verification_code = rand(1000, 9999) . "";
                }
            }

            if ($r->veterinary_officer_id != null && strlen($r->veterinary_officer_id) > 0) {
                $update->veterinary_officer_id = $r->veterinary_officer_id;
            }
            if ($r->schedule_date != null && strlen($r->schedule_date) > 0) {
                $update->schedule_date = Carbon::parse($r->schedule_date);
            }
            if ($r->veterinary_officer_message != null && strlen($r->veterinary_officer_message) > 0) {
                $update->veterinary_officer_message = ($r->veterinary_officer_message);
            }
            if ($r->dvo_message != null && strlen($r->dvo_message) > 0) {
                $update->dvo_message = ($r->dvo_message);
            }
            if ($r->reason_for_rejection != null && strlen($r->reason_for_rejection) > 0) {
                $update->reason_for_rejection = ($r->reason_for_rejection);
            }
            if ($r->details != null && strlen($r->details) > 0) {
                $update->details = ($r->details);
            }
            try {
                $update->save();
            } catch (\Throwable $e) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to save record. {$e->getMessage()}",
                ]);
            }

            $owner = $original->owner();
            $farm = $original->farm;
            if ($owner == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Farm owner not found.",
                ]);
            }
            $msg = "Your vaccination request has been {$update->status}. Open the App to see more details.";

            if ($original->status != 'Approved') {
                if ($update->status == 'Approved') {
                    if ($update->verification_code == null || strlen($update->verification_code) < 1) {
                        $update->verification_code = rand(1000, 9999) . "";
                        $update->save();
                    }
                    $msg = "Your vaccination request has been approved. The verification code is {$update->verification_code}. Open the App to see more details.";
                }
            }

            $title = "VACCINATION REQUEST - {$original->farm->holding_code}";
            Utils::sendNotification(
                $msg,
                $owner->id . "",
                $headings =  $title,
                $data = [$original->farm->id]
            );
            $owenr_phone = Utils::prepare_phone_number($original->applicant_contact);
            if (Utils::phone_number_is_valid($owenr_phone)) {
                Utils::send_message($owenr_phone, $msg);
            }
            return Utils::response([
                'data' => $update,
                'status' => 1,
                'message' => "Record saved successfully.",
            ]);
        }

        return Utils::response([
            'data' => null,
            'status' => 0,
            'message' => " Type not found.",
        ]);
    }


    public function farm_vaccination_records_create(Request $r)
    {
        $user_id = Utils::get_user_id($r);
        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "User account not found.",
            ]);
        }

        $farm = Farm::find($r->farm_id);
        if ($farm == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Farm not found.",
            ]);
        }



        $district_vaccine = DistrictVaccineStock::find($r->district_vaccine_stock_id);
        if ($district_vaccine == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Vaccine not found.",
            ]);
        }
        $number_of_doses = ((int)(($r->number_of_doses)));
        $number_of_animals_vaccinated = ((int)(($r->number_of_animals_vaccinated)));
        if ($number_of_doses < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Number of doses not specified.",
            ]);
        }
        if ($number_of_animals_vaccinated < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Number of animals vaccinated not specified.",
            ]);
        }
        if ($number_of_doses > $district_vaccine->current_quantity) {
            return Utils::response([
                'status' => 0,
                'message' => "Insufficient stock.",
            ]);
        }

        $main_vaccine = VaccineMainStock::find($district_vaccine->drug_stock_id);

        if ($main_vaccine == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Vaccine not found.",
            ]);
        }


        $record = new FarmVaccinationRecord();
        $record->farm_id = $r->farm_id;
        $record->vaccine_main_stock_id = $main_vaccine->id;
        $record->remarks = $r->remarks;
        $record->gps_location = $r->gps_location;
        $record->district_vaccine_stock_id = $r->district_vaccine_stock_id;
        $record->number_of_doses = $r->number_of_doses;
        $record->district_id = $farm->district_id;
        $record->created_by_id = $user_id;
        $record->updated_by_id = $user_id;
        $record->number_of_animals_vaccinated = $number_of_animals_vaccinated;
        $record->vaccination_batch_number = $district_vaccine->drug_stock->batch_number;
        $record->lhc = $farm->holding_code;
        $owner = $farm->owner();


        if ($owner == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Owner not found.",
            ]);
        }
        $record->farmer_name = $owner->name;
        $record->farmer_phone_number = $owner->phone_number;

        //check if the record already exists
        $dupe = FarmVaccinationRecord::where([
            'farm_id' => $record->farm_id,
            'vaccine_main_stock_id' => $record->vaccine_main_stock_id,
            'district_vaccine_stock_id' => $record->district_vaccine_stock_id,
            'vaccination_batch_number' => $record->vaccination_batch_number,
            'number_of_doses' => $record->number_of_doses,
        ])->first();
        if ($dupe != null) {
            return Utils::response([
                'status' => 0,
                'message' => "Record already exists.",
            ]);
        }

        try {
            $record->save();

            if (isset($r->vaccine_schedule_id)) {
                $schedule = VaccinationSchedule::find($r->vaccine_schedule_id);
                if ($schedule == null) {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Vaccination schedule not found.",
                    ]);
                }
                if ($schedule->status == 'Conducted') {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Vaccination schedule already conducted.",
                    ]);
                }
                $schedule->status = 'Conducted';
                $schedule->save();
            }
            //sms to famrer, vaccination vaccinator has been conducted at 
            $msg = "Vaccination has been conducted at your farm {$farm->holding_code}. Open the App to see more details.";
            $title = "VACCINATION CONDUCTED - {$farm->holding_code}";
            Utils::sendNotification(
                $msg,
                $owner->id . "",
                $headings =  $title,
                $data = [$farm->id]
            );
            $owenr_phone = Utils::prepare_phone_number($owner->phone_number);
            if (Utils::phone_number_is_valid($owenr_phone)) {
                Utils::send_message($owenr_phone, $msg);
            }
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save record. {$e->getMessage()}",
            ]);
        }

        try {
            Utils::check_duplicates();
        } catch (\Throwable $th) {
            //throw $th;
        }

        $record = FarmVaccinationRecord::find($record->id);
        return Utils::response([
            'status' => 1,
            'message' => "Record saved successfully.",
            'data' => $record,
        ]);
    }
    public function create_vaccination_programs(Request $r)
    {
        if ($r->task == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Task not specified.",
            ]);
        }
        if (
            ($r->task != 'Create') &&
            ($r->task != 'Edit')
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Task not specified.",
            ]);
        }

        $user_id = Utils::get_user_id($r);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "User account not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }
        //VaccinationProgram::where([])->delete();

        $rec = null;
        if ($r->task == 'Create') {
            $rec = new VaccinationProgram();
        } else if (trim($r->task) == 'Edit') {
            $rec = VaccinationProgram::find($r->id);
            if ($rec == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Record not found.",
                ]);
            }
        } else {
            return Utils::response([
                'data' => null,
                'status' => 0,
                'message' => " Type not found.",
            ]);
        }

        $rec->sub_district_id = $r->sub_district_id;
        $sub = Location::find($r->sub_district_id);
        if ($sub == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Subcounty not found.",
            ]);
        }

        $dist = Location::find($sub->parent);
        if ($dist == null) {
            return Utils::response([
                'status' => 0,
                'message' => "District not found.",
            ]);
        }
        $rec->district_id = $dist->id;
        $rec->parish_id = 1;


        $rec->title = $r->title ? $r->title : $rec->title;
        $rec->dose_per_animal = $r->dose_per_animal ? $r->dose_per_animal : $rec->dose_per_animal;
        $rec->status = $r->status ? $r->status : $rec->status;
        $rec->description = $r->description ? $r->description : $rec->description;
        $rec->district_vaccine_stock_id = 1;
        $rec->sub_district_id = $r->sub_district_id;
        $rec->start_date = $r->start_date ? $r->start_date : $rec->start_date;
        $rec->end_date = $r->end_date ? $r->end_date : $rec->end_date;


        $farms = [];
        if ($r->task == 'Create') {
            $rec->status = 'Upcoming';
            //farms in a selected subcounty
            $farms = Farm::where('sub_county_id', $rec->sub_district_id)->get();
            $rec->total_target_farms = count($farms);
            $total_target_animals = 0;
            foreach ($farms as $key => $farm) {
                $total_target_animals += count(Animal::where('farm_id', $farm->id)->get());
            }
            $rec->total_target_animals = $total_target_animals;
            $rec->total_target_doses = $total_target_animals * ((int)($rec->dose_per_animal));
        }


        try {
            $rec->save();

            foreach ($farms as $key => $f) {
                $schedule = new VaccinationSchedule();
                $schedule->vaccination_program_id = $rec->id;
                $schedule->farm_id = $f->id;
                $schedule->applicant_id = $f->administrator_id;
                $schedule->approver_id = $user_id;
                $schedule->veterinary_officer_id = $user_id;
                $schedule->district_id = $f->district_id;
                $schedule->sub_county_id = $f->sub_county_id;
                $schedule->gps_latitute = $f->latitude;
                $schedule->gps_longitude = $f->longitude;
                $schedule->schedule_date = $rec->start_date;
                $schedule->applicant_address = $f->village;
                $schedule->actual_date = null;
                $schedule->verification_code = null;
                $schedule->status = 'Pending';
                $schedule->vaccination_type = 'FMD';
                $owner = $f->owner();
                if ($owner != null) {
                    $schedule->applicant_name = $owner->name;
                    $schedule->applicant_contact = $f->owner()->phone_number;
                }
                try {
                    $schedule->save();
                    //send notification to farmer
                    $msg = "Your farm has been scheduled for vaccination between {$rec->start_date} and {$rec->end_date}. Open the App to see more details.";
                    $title = "VACCINATION SCHEDULE - {$f->holding_code}";
                    Utils::sendNotification(
                        $msg,
                        $owner->id . "",
                        $headings =  $title,
                        $data = [$f->id]
                    );
                } catch (\Throwable $e) {
                    continue;
                }
            }

            return Utils::response([
                'data' => $rec,
                'status' => 1,
                'message' => "Record saved successfully.",
            ]);
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save record. {$e->getMessage()}",
            ]);
        }
    }

    public function vaccination_session_submit(Request $r)
    {
        if ($r->session_id == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Session ID not found.",
            ]);
        }
        if ($r->animal_ids == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animals must be provided.",
            ]);
        }
        if ($r->quantity == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Quantity must be provided.",
            ]);
        }
        $quantity = ((int)($r->quantity));
        $district_vaccine_id = ((int)($r->district_vaccine_id));
        if ($quantity < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Quantity must be greater than 0.",
            ]);
        }
        if ($district_vaccine_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Vaccine not found.",
            ]);
        }


        $animal_ids = null;
        try {
            $animal_ids = json_decode($r->animal_ids);
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Invalid animal IDs.",
            ]);
        }
        if ($animal_ids == null || empty($animal_ids)) {
            return Utils::response([
                'status' => 0,
                'message' => "No animals found.",
            ]);
        }

        $session = VaccinationSchedule::find($r->session_id);
        if ($session == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Session not found.",
            ]);
        }

        if ($session->status != 'Approved') {
            return Utils::response([
                'status' => 0,
                'message' => "Session not approved. Status: {$session->status}.",
            ]);
        }

        $user_id = Utils::get_user_id($r);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "User ID not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $vaccince = DistrictVaccineStock::find($district_vaccine_id);
        if ($vaccince == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Vaccine not found.",
            ]);
        }

        if ($vaccince->drug_category == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Vaccine category not found.",
            ]);
        }
        $drug_category_text = $vaccince->drug_category_text;

        $i = 0;
        foreach ($animal_ids as $key => $id) {
            $_id = ((int)($id));
            if ($_id < 1) {
                continue;
            }
            $an = Animal::find($_id);
            if ($an == null) {
                continue;
            }

            $oldEvent = Event::where([
                'animal_id' => $an->id,
                'session_id' => $session->id,
                'type' => 'Vaccination'
            ])->first();
            if ($oldEvent != null) {
                continue;
            }
            $event = new Event();
            $event->administrator_id = $an->administrator_id;
            $farm = Farm::find($an->farm_id);
            if ($farm != null) {
                $event->district_id = $farm->district_id;
                $event->sub_county_id = $farm->sub_county_id;
                $event->parish_id = $farm->parish_id;
                $event->farm_id = $farm->id;
            }
            $event->animal_id = $an->id;
            $event->type = 'Vaccination';
            $event->approved_by = $u->id;
            $event->detail = "Vaccinated by $quantity Mills of " . $drug_category_text . " by " . $u->name . ", ID " . $u->id . ". ";
            $event->description = "Vaccinated by $quantity Mills of " . $drug_category_text . " by " . $u->name . ", ID " . $u->id . ". ";
            $event->short_description = "Vaccination of " . $an->type . " by " . $u->name . ", ID " . $u->id . ". ";
            $event->vaccination = $quantity;
            $event->animal_type = $an->type;
            $event->vaccine_id = $vaccince->id;
            $event->medicine_id = $vaccince->id;
            $event->time_stamp = Carbon::now();
            $event->e_id = $an->e_id;
            $event->v_id = $an->v_id;
            $event->medicine_quantity = $quantity;
            $event->session_id = $session->id;
            $event->save();
            $i++;
        }

        $session->status = 'Conducted';
        $session->save();

        //message to the farmer
        $owner = $session->owner();
        if ($owner != null) {
            $msg = "Vaccinated $i animals with $quantity Mills of " . $drug_category_text . ". Open the App to see more details.";
            $title = "VACCINATION SESSION - {$session->vaccination_type}";
            Utils::sendNotification(
                $msg,
                $owner->id . "",
                $headings =  $title,
                $data = [$session->id]
            );
            $owenr_phone = Utils::prepare_phone_number($session->applicant_contact);
            if (Utils::phone_number_is_valid($owenr_phone)) {
                Utils::send_message($owenr_phone, $msg);
            }
        }

        try {
            $vaccince->current_quantity = $vaccince->current_quantity - ($quantity * $i);
            $vaccince->save();
        } catch (\Throwable $e) {
        }

        return Utils::response([
            'status' => 1,
            'message' => "{$i} Vaccination records have been created successfully.",
        ]);
    }

    public function create_slaughter_single(Request $r)
    {
        if ($r->task == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Task not found.",
            ]);
        }
        if (
            ($r->task != 'Create') &&
            ($r->task != 'Edit')
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Task not specified.",
            ]);
        }

        $user_id = Utils::get_user_id($r);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $sr = null;
        if ($r->task == 'Create') {
            $an = Animal::where([
                'v_id' => $r->v_id
            ])->first();
            if ($an == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Animal not found.",
                ]);
            }

            $existing = SlaughterRecord::where([
                'v_id' => $r->v_id
            ])->first();

            if ($existing != null) {
                return Utils::response([
                    'data' => $existing,
                    'status' => 1,
                    'message' => "Slaughter record already created.",
                ]);
            }

            $house = SlaughterHouse::find($r->house_id);

            $sr = new SlaughterRecord();
            $sr->lhc = $an->lhc;
            $sr->v_id = $an->v_id;
            $sr->administrator_id = $user_id;
            $sr->e_id = $an->e_id;
            $sr->breed = $an->breed;
            $sr->sex = $an->sex;
            $sr->dob = Carbon::parse($an->dob);
            $sr->fmd = $an->fmd;
            $sr->details = "Slautered by " . $u->name . ", ID " . $u->id . ". ";

            if ($house != null) {
                $sr->destination_slaughter_house = $house->id;
            }

            if ($sr->save()) {
                /* Utils::archive_animal([
                    'animal_id' => $an->name,
                    'details' => $sr->details,
                    'event' => 'Slautered',
                ]); */
            }
        } else if ($r->task == 'Edit') {
            $sr = SlaughterRecord::find($r->id);
        }

        if ($sr == null) {
            return Utils::response([
                'data' => $sr,
                'status' => 0,
                'message' => "Record not found.",
            ]);
        }


        if ($sr->bar_code == null || strlen($sr->bar_code) < 2) {
            try {
                $sr->bar_code = Utils::generate_barcode($sr->v_id);
            } catch (\Throwable $e) {
                $err = $e->getMessage();
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to generate barcode. {$err}",
                ]);
            }
        }



        if ($r->post_animal != null && strlen($r->post_animal) > 0) {
            $sr->post_animal = $r->post_animal;
        }

        if ($r->post_age != null && strlen($r->post_age) > 0) {
            $sr->post_age = $r->post_age;
        }


        //forpost_dentition
        if ($r->post_dentition != null && strlen($r->post_dentition) > 0) {
            $sr->post_dentition = $r->post_dentition;
        }

        //for post_weight
        if ($r->post_weight != null && strlen($r->post_weight) > 0) {
            $sr->post_weight = $r->post_weight;
        }
        //available_weight
        if ($r->available_weight != null && strlen($r->available_weight) > 0) {
            $sr->available_weight = $r->available_weight;
        }
        if ($r->post_fat != null && strlen($r->post_fat) > 0) {
            $sr->post_fat = $r->post_fat;
        }
        if ($r->post_other != null && strlen($r->post_other) > 0) {
            $sr->post_other = $r->post_other;
        }
        if ($r->has_post_info != null && strlen($r->has_post_info) > 0) {
            $sr->has_post_info = $r->has_post_info;
        }
        if ($r->post_grade != null && strlen($r->post_grade) > 0) {
            $sr->post_grade = $r->post_grade;
        }
        if ($r->breed != null && strlen($r->breed) > 0) {
            $sr->breed = $r->breed;
        }

        $sr->save();

        $sr = SlaughterRecord::find($sr->id);
        return Utils::response([
            'data' => $sr,
            'status' => 1,
            'message' => "Record saved successfully.",
        ]);
    }

    public function create_slaughter_distribution_record(Request $r)
    {

        $user_id = Utils::get_user_id($r);

        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $sr = SlaughterRecord::find($r->source_id);
        if ($sr == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter record not found.",
            ]);
        }

        $receiver = Administrator::find($r->receiver_id);
        if ($receiver == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Receiver not found.",
            ]);
        }


        if ($sr->available_weight == null || (strlen($sr->available_weight) < 1)) {
            $sr->available_weight = $sr->post_weight;
            $sr->save();
        }

        $available = ((int)($sr->available_weight));
        if ($available < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Current available weight is $sr.",
            ]);
        }

        $weight = ((int)($r->original_weight));
        if ($weight < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Weight must be greater than 0.",
            ]);
        }

        if ($available < $weight) {
            return Utils::response([
                'status' => 0,
                'message' => "Quantity can't be more than available quantity.",
            ]);
        }
        $sr->available_weight = $available - $weight;

        $rec = new SlaughterDistributionRecord();
        $rec->animal_id = $r->animal_id;
        $rec->slaughterhouse_id = $sr->id;
        $rec->created_by_id  = $u->id;
        $rec->source_type = "Slaughter House";
        $rec->source_id = $sr->id;
        $rec->source_name = $u->name;
        $rec->source_phone = $u->phone_number;
        $rec->receiver_id = $receiver->id;
        $rec->receiver_type = "Trader";
        $rec->receiver_name = $receiver->name;
        $rec->receiver_address = $receiver->address;
        $rec->receiver_phone = $receiver->phone_number;
        $rec->lhc = $sr->lhc;
        $rec->v_id = $sr->v_id;
        $rec->e_id = $sr->e_id;
        $rec->animal_owner_id = 1;
        $rec->source_address = $r->source_address;
        $rec->bar_code = $sr->bar_code;
        $rec->post_fat = $sr->post_fat;
        $rec->post_grade = $sr->post_grade;
        $rec->post_animal = $sr->post_animal;
        $rec->post_age = $sr->post_age;
        $rec->original_weight = $weight;
        $rec->current_weight = $weight;
        $rec->price = $r->price;
        $rec->slaughter_date = $sr->created_at;

        try {
            $rec->save();

            try {
                $url = url('sdr/' . $rec->id);
                $data =
                    'ID: ' . $rec->id .
                    ', Meat Grade: ' . $rec->post_grade .
                    /*                     '\nSource: ' . $rec->source_name; */
                    ', More Details: ' . $url;
                $path = Utils::generate_qrcode($data);
                $rec->qr_code = $path;
                $rec->save();
                $sr->save();

                //Reciever message
                $msg = "You have received {$rec->current_weight}kg of meat from {$rec->source_name}. Open the App to see more details.";
                $title = "MEAT RECEIVED - {$rec->v_id}";
                Utils::sendNotification(
                    $msg,
                    $u->id,
                    $headings =  $title,
                    $data = [$rec->animal_id]
                );


                $sr = SlaughterRecord::find($sr->id);
                $rec = SlaughterDistributionRecord::find($rec->id);
                if ($sr == null) {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Slaughter record not found.",
                    ]);
                }

                //return success
                return Utils::response([
                    'status' => 1,
                    'message' => "Record saved successfully.",
                    'data' => [
                        'sr' => $sr,
                        'sdr' => $rec,
                    ]
                ]);
            } catch (\Throwable $e) {
                $rec->delete();
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to save record. {$e->getMessage()}",
                ]);
            }
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save record. {$e->getMessage()}",
            ]);
        }
    }



    public function archive_animal(Request $r, $id)
    {

        $worker_id = Utils::get_user_id($r);
        $worker = User::find($worker_id);
        $animal = Animal::find($id);
        if ($animal == null) {
            return Utils::response(['status' => 0, 'message' => "Animal was not found.",]);
        }

        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }
        if (strtolower($u->user_type) != 'worker') {
            $worker = null;
        } else {
            if ($worker == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Worker account not found. Update the app and try again.",
                ]);
            }
        }



        if ($worker != null) {
            $animal->destination = 'pending_for_deletion';
            $animal->decline_reason = $r->reason;
            $animal->comments = $r->details;

            try {
                $animal->save();
            } catch (\Throwable $e) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to save animal. {$e->getMessage()}",
                ]);
            }


            $mgs = "{$worker->first_name} is requesting for deletion of animal {$animal->v_id}, Reason:  {$r->reason}, Details: {$r->details}. Open the App to see more details.";
            $title = "ANIMAL DELETION REQUEST - {$animal->v_id}";
            Utils::sendNotification(
                $mgs,
                $u->id,
                $headings =  $title,
                $data = [$animal->id]
            );

            return Utils::response([
                'status' => 1,
                'message' => "Animal deleted has been requested successfully.",
            ]);
        }


        $mgs = "{$animal->type} - {$animal->v_id} has been archived. Reason: {$r->reason}, {$r->details}. Open the App to see more details.";
        $title = "DELETED ANIMAL - {$animal->v_id}";

        if ($r->reason == null) {
            return Utils::response(['status' => 0, 'message' => "Reason is required.",]);
        }

        if ($r->details == null) {
            return Utils::response(['details' => 0, 'message' => "Details is required.",]);
        }




        try {
            Utils::archive_animal([
                'animal_id' => $animal->id,
                'reason' => $r->reason,
                'details' => $r->details,
            ]);
        } catch (\Throwable $e) {
            try {
                $ArchivedAnimal = new ArchivedAnimal();
                $ArchivedAnimal->administrator_id = $u->id;
                $ArchivedAnimal->type = $animal->type;
                $ArchivedAnimal->e_id = $animal->e_id;
                $ArchivedAnimal->v_id = $animal->v_id;
                $ArchivedAnimal->lhc = $animal->lhc;
                $ArchivedAnimal->breed = $animal->breed;
                $ArchivedAnimal->sex = $animal->sex;
                $ArchivedAnimal->dob = $animal->dob;
                $ArchivedAnimal->last_event = $r->reason;
                $ArchivedAnimal->save();
            } catch (\Throwable $e) {
            }
            Event::where([
                'animal_id' => $animal->id
            ])->delete();
            $animal->delete();
        }

        Utils::sendNotification(
            $mgs,
            $u->id,
            $headings =  $title,
            $data = [$animal->id]
        );


        return Utils::response([
            'status' => 1,
            'message' => $mgs,
        ]);
    }

    public function cancel_delete_request(Request $r, $id)
    {

        $worker_id = Utils::get_user_id($r);
        $worker = User::find($worker_id);
        $animal = Animal::find($id);
        if ($animal == null) {
            return Utils::response(['status' => 0, 'message' => "Animal was not found.",]);
        }
        $animal->destination = null;
        $animal->decline_reason = null;
        $animal->comments = null;
        $animal->save();

        try {
            $animal->save();
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save animal. {$e->getMessage()}",
            ]);
        }

        return Utils::response([
            'status' => 1,
            'message' => "Animal deletion request has been cancelled successfully.",
        ]);
    }




    public function change_tag(Request $r, $id)
    {

        $animal = Animal::find($id);
        if ($animal == null) {
            return Utils::response(['status' => 0, 'message' => "Animal was not found.",]);
        }
        if ($r->new_v_id == null) {
            return Utils::response(['status' => 0, 'message' => "V-id is required.",]);
        }

        if ($r->new_e_id == null) {
            return Utils::response(['status' => 0, 'message' => "E-id is required.",]);
        }


        $an_1 = Animal::where('v_id', $r->new_v_id)->first();

        if ($an_1 != null) {
            return Utils::response(['status' => 0, 'message' => "Animal with same V-id already exist.",]);
        }

        $an_1 = Animal::where('e_id', $r->new_e_id)->first();
        if ($an_1 != null) {
            return Utils::response(['status' => 0, 'message' => "Animal with same E-id already exist.",]);
        }

        $animal->v_id = $r->new_v_id;
        $animal->e_id = $r->new_e_id;
        $animal->save();

        return Utils::response([
            'status' => 1,
            'message' => "Animal's E-ID and V-ID was changed successfully.",
        ]);
    }


    public function create_sale(Request $request)
    {
        if ($request->animal_ids == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animals must be provided.",
            ]);
        }

        if ($request->trader == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Trader ID must be provided.",
            ]);
        }

        $trader =  ((int)($request->trader));
        if ($trader < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Trader ID not found.",
            ]);
        }
        $t = Administrator::find($trader);
        if ($t  == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Trader not found.",
            ]);
        }
        $animal = json_decode($request->animal_ids);

        if ($animal == null || empty($animal)) {
            return Utils::response([
                'status' => 0,
                'message' => "No animals found.",
            ]);
        }
        $i = 0;
        foreach ($animal as $key => $id) {
            $_id = ((int)($id));
            if ($_id < 1) {
                continue;
            }
            $an = Animal::find($_id);
            if ($an == null) {
                continue;
            }
            $i++;
            $an->trader = $trader;
            $an->save();
        }

        return Utils::response([
            'status' => 1,
            'message' => "{$i} Animals were assigned to trader successfully.",
        ]);
    }


    public function store_batch_event(Request $r)
    {
        $user_id = Utils::get_user_id($r);

        if (

            $r->name == null ||
            $r->session_date == null ||
            $r->type == null ||
            $user_id == null ||
            $r->items == null
        ) {
            return Utils::response([
                'status' => 2,
                'message' => "Some parameters missing.",
            ]);
        }

        $exist = BatchSession::where([
            'session_date' => $r->session_date,
            'administrator_id' => $user_id
        ])->first();

        if ($exist != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Events already created.",
                'data' => null
            ]);
        }

        $items = json_decode($r->items);
        $date = Carbon::parse($r->session_date);
        $type = "";
        if ($r->type == 'Roll call') {
            $type = 'Roll call';
        } else if ($r->type == 'Treatment') {
            $type = 'Treatment';
        } else if ($r->type == 'Milk') {
            $type = 'Milking';
        }


        if ($r->type == 'Milk') {



            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->session_date = $r->session_date;
            $session->type = 'Milking';
            $session->description = "Milked animals";
            $session->save();
            $animal_ids_found = [];
            $litters = 0;


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->animal_id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->milk =  $v->milk;
                $ev->type = 'Milking';
                $ev->is_batch_import =  0;
                $ev->detail =  "$ev->milk litteres milked from $ev->v_id";
                $ev->description =  $ev->detail;
                $ev->short_description =  $ev->detail;
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
                $litters += ((int)($ev->milk));
            }

            $num = count($animal_ids_found);
            $session->description =    "Milked {$litters} litters from {$num} animals in a {$session->name} session. Open the App to see details.";
            $session->save();
            Utils::sendNotification(
                $session->description,
                $session->administrator_id,
                $headings = "Milked {$num} animals."
            );
        }




        if ($r->type == 'Treatment') {
            $meds =   [];

            try {
                $meds = json_decode($r->drugItem);
            } catch (\Throwable $th) {
                $meds = [];
            }

            if (
                $r->drugItem == null
            ) {
                return Utils::response([
                    'status' => 2,
                    'message' => "Drugs   missing.",
                ]);
            }

            $meds_text = "";

            foreach ($meds as $m) {
                $meds_text .= "$m->name: $m->quantity units, ";
                $d = DrugStockBatch::find(((int)($m->id)));
                if ($d == null) {
                    continue;
                }
                $d->current_quantity = $d->current_quantity - ((float)($m->quantity));
                $d->save();
            }
            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->session_date;
            $session->description = "Treated animals with  $meds_text.";
            $session->save();
            $animal_ids_found = [];


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->animal_id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type = 'Batch Treatment';
                $ev->is_batch_import =  0;
                $ev->detail =  "$meds_text was applied to this animal.";
                $ev->description =  "$meds_text was applied to this animal.";
                $ev->short_description =  "Treatment - {$meds_text}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
            }

            $num = count($animal_ids_found);

            Utils::sendNotification(
                "Treated {$num} animals with  {$meds_text} in a {$session->name} session. Open the App to see details.",
                $session->administrator_id,
                $headings = $session->name . ' - Batch treatment'
            );
        }


        if ($r->type == 'Roll call') {

            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->session_date;
            $session->session_category = $r->session_category;
            $session->description = $r->description;
            $session->save();
            $animal_ids_found = [];


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->animal_id)),
                    'type' => $r->session_category,
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                if ($r->type == 'Roll call') {
                    $ev = new Event();
                    $ev->created_at =  $date;
                    $ev->updated_at =  $date;
                    $ev->time_stamp =  $date;
                    $ev->administrator_id =  $an->administrator_id;
                    $ev->animal_id =  $an->id;
                    $ev->e_id =  $an->e_id;
                    $ev->v_id =  $an->v_id;
                    $ev->type =  $type;
                    $ev->is_batch_import =  0;
                    $ev->detail =  "Present in Roll-call - {$r->name}.";
                    $ev->description =  "Present in Roll-call - {$r->name}.";
                    $ev->short_description =  "Roll-call - {$r->name}.";
                    $ev->session_id =  $session->id;
                    $ev->is_present =  1;
                    $ev->save();
                }
            }


            $absent = 0;
            foreach (
                Animal::where([
                    'administrator_id' => $user_id,
                    'type' => $r->session_category,
                ])->get() as $an
            ) {
                if (in_array($an->id, $animal_ids_found)) {
                    continue;
                }
                $absent++;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type =  $type;
                $ev->is_batch_import =  0;
                $ev->detail =  "Absent from Roll-call - {$r->name}.";
                $ev->description =  "Absent from Roll-call - {$r->name}.";
                $ev->short_description =  "Roll-call - {$r->name}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  0;
                $ev->save();
            }

            $session->present = count($animal_ids_found);
            $session->absent =  $absent;
            $session->save();

            Utils::sendNotification(
                "{$session->name}. Animals present: {$session->present}, Animals absent: {$session->absent}. Open the App to see full list.",
                $session->administrator_id,
                $headings = $r->session_category . ' Roll-call'
            );
        }



        return Utils::response([
            'status' => 1,
            'message' => "Events were created successfully.",
            'data' => null
        ]);
    }



    public function batch_events_create(Request $r)
    {

        $user_id = Utils::get_user_id($r);

        if (

            $r->name == null ||
            $r->date_time == null ||
            $r->type == null ||
            $user_id == null ||
            $r->details == null
        ) {
            return Utils::response([
                'status' => 2,
                'message' => "Some parameters missing.",
            ]);
        }


        $exist = BatchSession::where([
            'session_date' => $r->date_time,
            'administrator_id' => $user_id
        ])->first();

        if ($exist != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Events already created.",
                'data' => null
            ]);
        }

        $items = json_decode($r->details);

        $date = Carbon::parse($r->date_time);
        $type = "";
        if ($r->type == 'Roll call') {
            $type = 'Roll call';
        } else if ($r->type == 'Treatment') {
            $type = 'Treatment';
        } else if ($r->type == 'Milking') {
            $type = 'Milking';
        } else if ($r->type == 'Milking') {
            $type = 'Weight';
        }



        if ($r->type == 'Milking') {
            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->session_date = $r->date_time;
            $session->type = 'Milking';
            $session->description = "Milked animals";
            $session->save();
            $animal_ids_found = [];
            $litters = 0;
            $price = 1000;
            if (isset($r->milk_price)) {
                if ($r->milk_price != null) {
                    $price = (int)($r->milk_price);
                }
            }


            $total_price = 0;
            foreach ($items as $v) {

                $an = Animal::where([
                    'id' => ((int)($v->id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->milk =  $v->milk;
                $ev->price =  $price * $v->milk;
                $total_price += $ev->price;
                $ev->type = 'Milking';
                $ev->is_batch_import =  0;
                $ev->detail =  "$ev->milk litteres milked from $ev->v_id";
                $ev->description =  $ev->detail;
                $ev->short_description =  $ev->detail;
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
                $litters += ((int)($ev->milk));
            }

            $num = count($animal_ids_found);
            $total_price_text = number_format($total_price);
            $session->description = "Milked {$litters} litters worth {$total_price_text} shs from {$num} animals in a {$session->name} session. Open the App to see details.";
            $session->save();
            try {
                Utils::CreateNotification([
                    'title' => "Milked $num animals",
                    'message' => $session->description,
                    'receiver_id' => $session->administrator_id,
                    'receiver' => $session->administrator_id,
                    'type' => 'Milking',
                    'session_id' =>  $session->id . "",
                    'animal_ids' => $animal_ids_found,
                ]);
            } catch (\Throwable $th) {
                try {
                    Utils::sendNotification(
                        $session->description,
                        $session->administrator_id,
                        $headings = "Milked {$num} animals. v1"
                    );
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        } else if ($r->type == 'Treatment') {


            $meds =   [];
            if (
                $r->drugs == null
            ) {
                return Utils::response([
                    'status' => 2,
                    'message' => "Drugs   missing.",
                ]);
            }

            try {
                $meds = json_decode($r->drugs);
            } catch (\Throwable $th) {
                $meds = [];
            }

            $meds_text = "";
            $total_worth = 0;
            foreach ($meds as $m) {

                $d = DrugStockBatch::find(((int)($m->drug_id)));
                if ($d == null) {
                    $meds_text .= "$m->drug_text: $m->quantity units, ";
                    continue;
                }

                if ($d->category != null) {
                    $meds_text .= "$m->drug_text: $m->quantity {$d->category->unit}, ";
                } else {
                    $meds_text .= "$m->drug_text: $m->quantity units, ";
                }

                $worth = 0;
                try {
                    if ($d->original_quantity > 0) {
                        if ($m->quantity > 0) {
                            $worth = ($m->quantity / $d->original_quantity) * $d->selling_price;
                        }
                    }
                } catch (\Throwable $th) {
                    $worth = 0;
                }
                $total_worth += $worth;

                $d->current_quantity = $d->current_quantity - ((float)($m->quantity));

                if ($d->current_quantity < 0) {
                    $d->current_quantity = 0;
                }
                $meds_text .= "Worth: UGX " . number_format($worth) . ", ";
                $d->save();
            }

            $meds_text = substr($meds_text, 0, -2) . ".";
            $meds_text .= " Total worth: UGX " . number_format($total_worth) . ".";



            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->date_time;
            $session->description = "Treated animals with  $meds_text.";
            $session->save();
            $animal_ids_found = [];
            $animal_text_found = [];

            $worth_per_animal = 0;
            //try and catch
            try {
                $worth_per_animal = $total_worth / count($items);
            } catch (\Throwable $th) {
                $worth_per_animal = 0;
            }

            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->drug_worth =  $worth_per_animal;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type = 'Batch Treatment';
                $ev->is_batch_import =  0;
                $ev->detail =  "$meds_text was applied to this animal.";
                $ev->description =  "$meds_text was applied to this animal.";
                $ev->short_description =  "Treatment - {$meds_text} Average worth per animal: UGX " . number_format($worth_per_animal) . ".";
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
            }

            $num = count($animal_ids_found);
            $title = "Treated {$num} animals in a {$session->name} session. Open the App to see details.";
            $body = "Treated {$num} animals with  {$meds_text} in a {$session->name} session. Open the App to see details.";
            try {
                Utils::CreateNotification([
                    'title' => $title,
                    'message' => $body,
                    'receiver_id' => $session->administrator_id,
                    'receiver' => $session->administrator_id,
                    'type' => 'Treatment',
                    'session_id' =>  $session->id . "",
                    'animal_ids' => $animal_ids_found,
                ]);
            } catch (\Throwable $th) {
                try {
                    Utils::sendNotification(
                        $body . " => " . $th->getMessage(),
                        $session->administrator_id,
                        $headings = $title
                    );
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        } else if (
            $r->type == 'Roll call' ||
            $r->type == 'RollCall'
        ) {

            $group_id = ((int)($r->group_id));
            $group = Group::find($group_id);
            if ($group == null) {
                return Utils::response([
                    'status' => 2,
                    'message' => "Group not found.",
                ]);
            }

            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->type = $r->type;
            $session->session_date = $r->session_date;
            $session->session_category = $r->roll_call_type;
            $session->description = $r->description;
            $session->group_id = $group_id;
            $session->save();
            $animal_ids_found = [];


            foreach ($items as $v) {
                $an = Animal::where([
                    'id' => ((int)($v->id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;

                $animal_text['id'] = $an->id;
                $animal_text['v_id'] = $an->v_id;
                $animal_text['e_id'] = $an->e_id;
                $animal_text['photo'] = $an->photo;
                $animal_text_found[] = $animal_text;

                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type =  $type;
                $ev->is_batch_import =  0;
                $ev->detail =  "Present in Roll-call - {$r->name}.";
                $ev->description =  "Present in Roll-call - {$r->name}.";
                $ev->short_description =  "Roll-call - {$r->name}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
            }

            $session->animal_text_found = json_encode($animal_text_found);
            $session->animal_ids_found = json_encode($animal_ids_found);

            $animal_ids_not_found = [];
            $animal_text_not_found = [];
            $absent = 0;
            foreach (
                Animal::where([
                    'group_id' => $group_id,
                ])->get() as $an
            ) {
                if (in_array($an->id, $animal_ids_found)) {
                    continue;
                }

                $animal_text['id'] = $an->id;
                $animal_text['v_id'] = $an->v_id;
                $animal_text['e_id'] = $an->e_id;
                $animal_text['photo'] = $an->photo;
                $animal_text_not_found[] = $animal_text;
                $animal_ids_not_found[] = $an->id;

                $absent++;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->type =  $type;
                $ev->is_batch_import =  0;
                $ev->detail =  "Absent from Roll-call - {$r->name}.";
                $ev->description =  "Absent from Roll-call - {$r->name}.";
                $ev->short_description =  "Roll-call - {$r->name}.";
                $ev->session_id =  $session->id;
                $ev->is_present =  0;
                $ev->save();
            }

            $session->animal_text_not_found = json_encode($animal_text_not_found);
            $session->animal_ids_not_found = json_encode($animal_ids_not_found);

            $session->present = count($animal_ids_found);
            $session->absent =  $absent;
            $session->save();

            Utils::sendNotification(
                "{$session->name}. Animals present: {$session->present}, Animals absent: {$session->absent}. Open the App to see full list.",
                $session->administrator_id,
                $headings = $r->session_category . ' Roll-call'
            );
        } else if ($r->type == 'Weight') {
            $session = new BatchSession();
            $session->administrator_id = $user_id;
            $session->name = $r->name;
            $session->session_date = $r->date_time;
            $session->type = 'Weight';
            $session->description = "Weighed animals";
            $session->save();
            $animal_ids_found = [];
            $litters = 0;


            foreach ($items as $v) {

                $an = Animal::where([
                    'id' => ((int)($v->id)),
                ])->first();
                if ($an == null) {
                    continue;
                }
                $animal_ids_found[] = $an->id;
                $ev = new Event();
                $ev->created_at =  $date;
                $ev->updated_at =  $date;
                $ev->time_stamp =  $date;
                $ev->administrator_id =  $an->administrator_id;
                $ev->animal_id =  $an->id;
                $ev->e_id =  $an->e_id;
                $ev->v_id =  $an->v_id;
                $ev->weight =  $v->milk;
                $ev->type = 'Weight check';
                $ev->is_batch_import =  0;
                $ev->detail =  "$ev->v_id weighed $v->milk KGs on date " . Utils::my_date(Carbon::now());
                $ev->description =  $ev->detail;
                $ev->short_description =  $ev->detail;
                $ev->session_id =  $session->id;
                $ev->is_present =  1;
                $ev->save();
            }

            $num = count($animal_ids_found);
            $session->description =    "{$num} animals Weighed in a {$session->name} session. Open the App to see details.";
            $session->save();
            $title = "Milked {$num} animals.";
            try {
                Utils::CreateNotification([
                    'title' => "Weighed $num animals. v2",
                    'message' => $session->description,
                    'receiver_id' => $session->administrator_id,
                    'receiver' => $session->administrator_id,
                    'type' => 'Animal',
                    'session_id' =>  $session->id . "",
                    'animal_ids' => $animal_ids_found,
                ]);
            } catch (\Throwable $th) {
                try {
                    Utils::sendNotification(
                        $session->description,
                        $session->administrator_id,
                        $headings = "Weighed {$num} animals. v1"
                    );
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }



        return Utils::response([
            'status' => 1,
            'message' => "Events were created successfully.",
            'data' => null
        ]);
    }


    public function store_event(Request $request)
    {

        $user_id = Utils::get_user_id($request);


        if ($request->session_id != null) {
            if (strlen($request->session_id) > 3) {
                $e =  Event::where([
                    'session_id' => $request->session_id,
                    'administrator_id' => $user_id
                ])->first();
                if ($e != null) {
                    return Utils::response([
                        'status' => 1,
                        'message' => "This event is a duplicate.",
                        'data' => null
                    ]);
                }
            }
        }


        if ($request->animal_id == null) {
            return Utils::response([
                'status' => 2,
                'message' => "Animal ID must be provided.",
            ]);
        }

        if ($request->type == null) {
            return Utils::response([
                'status' => 2,
                'message' => "Event type must be provided.",
            ]);
        }

        $animal = Animal::find(((int)($request->animal_id)));
        if ($animal == null) {
            return Utils::response([
                'status' => 2,
                'message' => "Animal not found on our database.",
            ]);
        }

        if ($request->type == 'Milking') {
            $milk = ((int)($request->milk));
            if ($milk < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Enter valid milk parameters.",
                ]);
            }
        }


        if (!isset($request->session_id)) {
            return Utils::response([
                'status' => 0,
                'message' => "Session not set.",
            ]);
        }
        $session_id = trim($request->session_id);

        $ev = Event::where([
            'session_id' => $session_id,
            'animal_id' => $request->animal_id,
        ])->first();

        if ($ev != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Duplicate of event is detected.",
            ]);
        }

        $event = new Event();
        $event->animal_id = (int)($request->animal_id);


        $event->detail = $request->detail;
        $event->session_id = $session_id;
        $event->sub_county_id = $request->sub_county_id;
        $event->farm_id = $request->farm_id;
        $event->animal_id = $request->animal_id;
        $event->type = $request->type;
        $event->approved_by = $request->approved_by;
        $event->animal_type = $request->animal_type;
        $event->vaccine_id = $request->vaccine_id;
        $event->medicine_id = $request->medicine_id;
        $event->medicine_quantity = $request->medicine_quantity;
        $event->vaccination = $request->vaccination;
        $event->time_stamp = $request->time_stamp;
        $event->import_file = $request->import_file;
        $event->description = $request->description;
        $event->temperature = $request->temperature;
        $event->e_id = $request->e_id;
        $event->v_id = $request->v_id;
        $event->pregnancy_check_method = $request->pregnancy_check_method;
        $event->pregnancy_check_results = $request->pregnancy_check_results;
        $event->pregnancy_expected_sex = $request->pregnancy_expected_sex;
        $event->pregnancy_fertilization_method = $request->pregnancy_fertilization_method;
        $event->disease_test_results = $request->disease_test_results;
        $event->disease_id = $request->disease_id;
        $event->milk = $request->milk;
        $event->weight = $request->weight;



        try {
            $event->save();
            return Utils::response([
                'status' => 1,
                'message' => "Event was created successfully.",
                'data' => $event
            ]);
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 2,
                'message' => "Failed -  $th",
            ]);
        }


        return Utils::response([
            'status' => 0,
            'message' => "Failed to save event on database.",
        ]);
    }


    public function store_event_2(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        if ($request->id == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Unique ID not provided.",
                'data' => null
            ]);
        }
        if (strlen($request->id) < 3) {
            return Utils::response([
                'status' => 0,
                'message' => "Unique ID is too short.",
                'data' => null
            ]);
        }

        $e =  Event::where([
            'session_id' => $request->id,
        ])->first();

        if ($e != null) {
            return Utils::response([
                'status' => 0,
                'message' => "Event already created. ref: #$e->id",
                'data' => null
            ]);
        }

        if ($request->animal_id == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animal ID must be provided.",
            ]);
        }

        if ($request->type == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Event type must be provided.",
            ]);
        }

        $animal = Animal::find(((int)($request->animal_id)));
        if ($animal == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animal not found on our database.",
            ]);
        }

        $accepted_events = [
            'Vaccination',
            'Treatment',
            'Disease test',
            'Pregnancy check',
            'Temperature check',
            'Home slaughter',
            'Stolen',
            'Death',
            'Note',
            'Weight check',
            'Milking',
            'Other',
        ];


        if (!in_array($request->type, $accepted_events)) {
            return Utils::response([
                'status' => 0,
                'message' => "Invalid event type.",
            ]);
        }

        $event = new Event();
        $event->session_id = $request->id;
        $event->animal_id = $animal->id;
        if ($request->created_at != null && strlen($request->created_at) > 3) {
            try {
                $event->created_at = Carbon::parse($request->created_at);
            } catch (\Throwable $th) {
                $event->created_at = Carbon::now();
            }
        }

        if ($request->type == 'Disease test') {
            $disease = Disease::find(((int)($request->disease_id)));
            if ($disease == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Disease not found on our database.",
                ]);
            }
            if ($request->status != 'Positive' && $request->status != 'Negative') {
                return Utils::response([
                    'status' => 0,
                    'message' => "Disease status must be either Positive or Negative. But found {$request->status}.",
                ]);
            }
            $event->disease_id = $disease->id;
            $event->status = $request->status;
            $event->disease_text = $disease->name;
            $test_results = $event->status;
            $event->description = "Disease test for animal {$animal->v_id} and found it {$test_results}.";
        } else if ($request->type == 'Treatment') {
            $disease = Disease::find(((int)($request->disease_id)));
            if ($disease == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Disease not found on our database.",
                ]);
            }
            $stock = DrugStockBatch::find($request->medicine_id);
            if ($stock == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Medicine not found on our database.",
                ]);
            }

            //medicine_quantity
            if ($request->medicine_quantity == null || strlen($request->medicine_quantity) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Medicine quantity must be provided.",
                ]);
            }
            if (floatval($request->medicine_quantity) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Medicine quantity must be greater than 0.",
                ]);
            }
            $event->disease_id = $disease->id;
            $event->medicine_id = $stock->id;
            $event->disease_text = $disease->name;
            $event->medicine_quantity = $request->medicine_quantity;
            if ($stock->drug_category_text != null) {
                $event->medicine_text = $stock->drug_category_text;
            }
        } else if ($request->type == 'Vaccination') {
            if ($request->vaccination == null || strlen($request->vaccination) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Vaccination must be provided.",
                ]);
            }
            if ($request->disease_id == null || strlen($request->disease_id) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Disease must be provided.",
                ]);
            }
            $disease = Disease::find(((int)($request->disease_id)));
            if ($disease == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Disease not found on our database.",
                ]);
            }
            //created description text that explain vaccine used and disease name vaccinated against
            $event->description = 'Vaccined against ' . $disease->name . ' using ' . $request->vaccination . ' Vaccine.';
            $event->disease_text = $disease->name;
            $event->disease_id = $request->disease_id;
        }

        $event->type = $request->type;
        $event->detail = $request->detail;
        $event->sub_county_id = $request->sub_county_id;
        $event->farm_id = $request->farm_id;
        $event->approved_by = $request->approved_by;
        $event->animal_type = $request->animal_type;
        $event->vaccine_id = $request->vaccine_id;
        $event->vaccination = $request->vaccination;
        $event->time_stamp = $request->time_stamp;
        $event->import_file = $request->import_file;

        if ($event->description == null || (strlen($event->description) < 4)) {
            $event->description = $request->description;
        }
        if ($event->medicine_quantity == null || (strlen($event->medicine_quantity) < 1)) {
            $event->medicine_quantity = $request->medicine_quantity;
        }
        if ($event->disease_text == null || (strlen($event->disease_text) < 4)) {
            $event->disease_text = $request->disease_text;
        }

        $event->detail = $request->detail;
        if ($event->detail == null || strlen($event->detail) < 2) {
            $event->detail = $request->description;
        }

        if ($event->disease_id == null || strlen($event->disease_id) < 1) {
            $event->disease_id = $request->disease_id;
        }
        if ($event->medicine_id == null || strlen($event->medicine_id) < 1) {
            $event->medicine_id = $request->medicine_id;
        }
        if ($event->medicine_text == null || strlen($event->medicine_text) < 1) {
            $event->medicine_text = $request->medicine_text;
        }

        $event->temperature = $request->temperature;
        $event->e_id = $animal->e_id;
        $event->v_id = $animal->v_id;
        $event->pregnancy_check_method = $request->pregnancy_check_method;
        $event->pregnancy_check_results = $request->pregnancy_check_results;
        $event->pregnancy_expected_sex = $request->pregnancy_expected_sex;
        $event->pregnancy_fertilization_method = $request->pregnancy_fertilization_method;
        $event->disease_test_results = $request->disease_test_results;
        $event->milk = $request->milk;
        $event->weight = $request->weight;

        try {
            $event->save();
            $event = Event::find($event->id);
            return Utils::response([
                'status' => 1,
                'message' => "Event was created successfully.",
                'data' => $event
            ]);
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 2,
                'message' => "Failed -  $th",
            ]);
        }


        return Utils::response([
            'status' => 0,
            'message' => "Failed to save event on database.",
        ]);
    }



    public function photo_downloads(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        $data = [];

        foreach (
            Animal::where([
                'administrator_id' => $user_id
            ])
                ->orderBy('id', 'desc')
                ->limit(1000)
                ->get() as $animal
        ) {

            foreach ($animal->photos as $key => $pic) {
                $path = $_SERVER['DOCUMENT_ROOT'] . "/public/storage/images/" . $pic->src;
                if (!file_exists($path)) {
                    //  $pic->delete();
                    continue;
                }

                unset($pic->updated_at);
                unset($pic->administrator_id);
                unset($pic->thumbnail);
                unset($pic->size);
                unset($pic->deleted_at);
                unset($pic->type);
                unset($pic->product_id);
                unset($pic->parent_endpoint);
                $data[] = $pic;
            }



            //  $data[] = $animal->photos;
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);
    }

    public function animals_small(Request $request)
    {
        //start time
        $start = microtime(true);

        $sql = "SELECT id,e_id,v_id,photo,lhc FROM animals";
        $animals = DB::select($sql);

        /* $animals =
            Animal::where([])
            ->get()
            ->map(function ($animal) {
                $d['id'] = $animal->id;
                $d['e_id'] = $animal->e_id;
                $d['v_id'] = $animal->v_id;
                $d['photo'] = $animal->photo;
                $d['lhc'] = $animal->lhc;
                return $d;
            });  */

        //end time
        $end = microtime(true);
        $time = number_format(($end - $start), 2);
        //die($time); 
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $animals
        ]);

        die('');
    }
    public function index(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        $data = [];

        foreach (
            Animal::where([
                'administrator_id' => $user_id
            ])
                ->orderBy('id', 'desc')
                ->limit(1000)
                ->get() as $animal
        ) {
            $animal->district_text = "-";
            if ($animal->district != null) {
                $animal->district_text = $animal->district->name_text;
            }
            if ($animal->sub_county != null) {
                $animal->sub_county_text = $animal->sub_county->name_text;
            }

            $x['id'] = $animal->id;
            $x['administrator_id'] = $animal->administrator_id;
            $x['type'] = $animal->type;
            $x['e_id'] = $animal->e_id;
            $x['v_id'] = $animal->v_id;
            $x['lhc'] = $animal->lhc;
            $x['breed'] = $animal->breed;
            $x['sex'] = $animal->sex;
            $x['dob'] = $animal->dob;
            $x['color'] = $animal->color;
            $x['farm_id'] = $animal->farm_id;
            $x['fmd'] = $animal->fmd;
            $x['trader'] = $animal->fmd;
            $x['weight'] = $animal->weight;
            $x['parent_id'] = $animal->parent_id;
            $x['photo'] = $animal->photo;
            $x['stage'] = $animal->stage;
            $x['average_milk'] = $animal->average_milk;
            $x['weight_text'] = $animal->weight_text;
            $x['group_id'] = $animal->group_id;
            $x['local_id'] = $animal->local_id;
            $x['registered_by_id'] = $animal->registered_by_id;
            $x['registered_id'] = $animal->registered_id;
            $x['weight_change'] = $animal->weight_change;
            $x['district_text'] = $animal->district_text;
            $x['sub_county_text'] = $animal->sub_county_text;
            $images = [];

            foreach ($animal->photos as $img) {
                $image['id'] = $img->id;
                $image['src'] = $img->src;
                $image['thumbnail'] = $img->thumbnail;
                $images[] = $image;
            }
            $x['images'] = json_encode($images);
            $x['last_seen'] = $animal->last_seen;
            $x['age'] = $animal->age;
            // $x['location'] = $animal->location;
            $x['group_text'] = $animal->group_text;

            $data[] = $x;
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


        $user_role = Utils::is_admin($request);


        //$user_id = Utils::get_user_id($request);
        $user_id = $administrator_id;
        $u = Administrator::find($administrator_id);
        $role = Utils::get_role($u);

        $s = $request->s;
        $items = [];
        $_items = [];

        if ($s != null) {
            if (strlen($s) > 0) {

                $f = Farm::where("holding_code", $s)->first();
                if ($f != null) {
                    $items = $f->animals;
                }

                $_items = Animal::where(
                    'e_id',
                    'like',
                    '%' . trim($request->s) . '%',
                )->paginate(100000000)->withQueryString()->items();

                $__items = Animal::where(
                    'v_id',
                    'like',
                    '%' . trim($request->s) . '%',
                )->paginate(100000000)->withQueryString()->items();

                $items_ids = [];
                $___items = [];

                foreach ($items as $key => $v) {
                    if (!in_array($v->id, $items_ids)) {
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }

                foreach ($_items as $key => $v) {
                    if (!in_array($v->id, $items_ids)) {
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }

                foreach ($__items as $key => $v) {
                    if (!in_array($v->id, $items_ids)) {
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }


                return $___items;
            }
        }

        if (empty($items)) {
            $per_page = 100000000;
            if (isset($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($role == 'slaughter') {
                $moves = Movement::where('destination_slaughter_house', '=', $user_id)->where('status', '=', 'Approved')->get();
                foreach ($moves as $key => $value) {
                    if ($value->movement_has_movement_animals != null) {
                        foreach ($value->movement_has_movement_animals as $_value) {
                            if ($_value->movement_animal_id != null) {
                                $__an = Animal::find($_value->movement_animal_id);
                                if ($__an != null) {
                                    $items[] = $__an;
                                }
                            }
                        }
                    }
                }
            } else {
                $items = Animal::paginate($per_page)->withQueryString()->items();
            }
        }

        foreach ($items as $key => $value) {
            if ($role == 'farmer') {
                if ($value->administrator_id != $administrator_id) {
                    continue;
                }
            } else if ($role == 'trader') {
                if ($u->id != $value->trader) {
                    continue;
                }
            } else if ($role == 'dvo') {
                if ($u->dvo != $value->district_id) {
                    continue;
                }
            }

            $items[$key]->owner_name  = "";
            if ($items[$key]->farm != null) {
                if ($items[$key]->farm->user != null) {
                    $items[$key]->owner_name = $items[$key]->farm->user->name;
                }
            }

            $items[$key]->owner_name = "";
            $items[$key]->district_name = "";
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();
            if ($value->district != null) {
                $items[$key]->district_name = $value->district->name;
            }
            if ($value->sub_county != null) {
                $items[$key]->sub_county_name = $value->sub_county->name;
            }
            unset($items[$key]->farm);
            unset($items[$key]->district);
            unset($items[$key]->sub_county);
            $_items[] = $items[$key];
        }
        return $_items;
    }

    public function index_v2(Request $request)
    {

        $user_id = Utils::get_user_id($request);


        $query = Animal::where([
            'administrator_id' => $user_id
        ])
            ->orderBy('id', 'desc')
            ->limit(2000);

        if ($request->updated_at != null) {
            //$query->whereDate('updated_at', '>', Carbon::parse($request->updated_at));
        }
        $ans = $query->get();
        $data = [];
        foreach ($ans as $key => $v) {
            $v->local_id = $v->local_id;
            $v->images = null;
            $v->photos = null;
            $v->district = null;
            $v->sub_county = null;
            $data[] = $v; 
        }
        return Utils::response([
            'status' => 1,
            'code' => 1,
            'message' => "Success.",
            'data' => $data
        ]);
    }
    public function transporters(Request $request)
    {
        $transposers = [];
        foreach (AdminRoleUser::where('role_id', 18)->get() as $key => $value) {
            $transposers[] = $value->owner;
        }
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $transposers
        ]);
    }


    public function images_v2(Request $request)
    {

        $user_id = Utils::get_user_id($request);

        $query = Image::where([
            'administrator_id' => $user_id
        ])
            ->orderBy('id', 'desc')
            ->limit(10000);

        if ($request->updated_at != null) {
            //$query->whereDate('updated_at', '>', Carbon::parse($request->updated_at));
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $query->get()
        ]);
    }



    public function slaughter_houses(Request $request)
    {
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => SlaughterHouse::all()
        ]);
    }


    public function slaughter_distributions(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }

        $items = SlaughterDistributionRecord::where('created_by_id', $user_id)->limit(4000)->get();
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $items
        ]);
    }


    public function slaughters(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }


        $items = SlaughterRecord::where('administrator_id', $user_id)->get();
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $items
        ]);
    }


    public function show($id)
    {
        $item = Animal::find($id);
        return Utils::response([
            'status' => 1,
            'message' => "Success",
            'data' => $item
        ]);

        $item->owner_name  = "";
        if ($item->farm != null) {
            if ($item->farm->user != null) {
                $item->owner_name = $item->farm->user->name;
            }
        }

        $item->owner_name = "";
        $item->district_name = "";
        $item->created = Carbon::parse($item->created)->toFormattedDateString();
        if ($item->district != null) {
            $item->district_name = $item->district->name;
        }
        if ($item->sub_county != null) {
            $item->sub_county_name = $item->sub_county->name;
        }
        unset($item->farm);
        unset($item->district);
        unset($item->sub_county);


        return $item;
    }






    public function store(Request $request)
    {
        return Administrator::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $Administrator = Administrator::findOrFail($id);
        $Administrator->update($request->all());
        return $Administrator;
    }

    public function delete(Request $request, $id)
    {
        $Administrator = Administrator::findOrFail($id);
        $Administrator->delete();

        return 204;
    }

    public function create(Request $request)
    {

        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);


        $an  = Animal::where([
            'e_id' => $request->e_id
        ])->first();
        if ($an != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Animal with same E-ID already exist in the system."
            ]);
        }

        $an  = Animal::where([
            'v_id' => $request->v_id
        ])->first();
        if ($an != null) {
            return Utils::response([
                'status' => 1,
                'message' => "Animal with same V-ID already exist in the system."
            ]);
        }

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        if ($request->id != null && strlen($request->id) > 2) {
            $an = Animal::where([
                'local_id' => $request->id,
                'administrator_id' => $u->id
            ])->first();
            if ($an != null) {
                return Utils::response([
                    'status' => 2,
                    'message' => "Animal already registered.",
                ]);
            }
        }

        if (
            !isset($request->farm_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide farm."
            ]);
        }

        if (isset($request->has_no_tag)) {
            $has_no_tag = false;
            if ($request->has_no_tag == 'Yes') {
                $has_no_tag = true;
            }
            if (!$has_no_tag) {
                if (((int)($request->has_no_tag)) == 1) {
                    $has_no_tag = true;
                }
            }

            if ($has_no_tag) {
                $p = Animal::find($request->parent_id);
                if ($p == null) {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Parent animal not found."
                    ]);
                }

                $count = Animal::where([
                    'parent_id' => $p->id
                ])->count();
                $count++;
                if ($request->has_more_info != 'Yes') {
                    $request->e_id = "temp-{$p->e_id}-{$count}";
                    $request->v_id = "temp-{$p->v_id}-{$count}";
                }
            }
        }

        if (
            !isset($request->e_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide e_id."
            ]);
        }

        if (
            !isset($request->type)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide type."
            ]);
        }

        if (
            !isset($request->e_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide e_id."
            ]);
        }

        $animal = Animal::where('e_id', $request->e_id)->first();
        if ($animal != null) {
            return Utils::response([
                'status' => 2,
                'message' => "Animal with same E-ID already exist in the system."
            ]);
        }

        if (
            !isset($request->sex)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide animal's sex."
            ]);
        }

        $f = new Animal();
        $f->local_id = $request->id;
        $f->e_id = $request->e_id;
        $f->farm_id = $request->farm_id;
        $f->type = $request->type;
        $f->v_id = $request->v_id;
        $f->lhc = $request->lhc;
        $f->breed = $request->breed;
        $f->sex = $request->sex;
        $f->dob = $request->dob;
        if ($request->dob != null & strlen($request->dob) > 3) {
            $f->dob = Carbon::parse($request->dob);
        }
        if ($request->fmd != null && strlen($request->fmd) > 3) {
            $f->fmd = Carbon::parse($request->fmd);
        }
        $f->stage = $request->stage;
        $f->parent_id = $request->parent_id;
        $f->status = 'Active';
        try {
            $f->save();
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save animal on database. $th",
            ]);
            //throw $th;
        }

        if (isset($f->local_id)) {
            $local_id = (int)($f->local_id);

            $imgs = Image::where([
                'administrator_id' => $administrator_id,
                'parent_id' => $local_id,
                'parent_endpoint' => 'animals-local',
            ])->get();

            foreach ($imgs as  $img) {
                $img->parent_id = $f->id;
                $img->parent_endpoint = 'Animal';
                try {
                    $img->save();
                } catch (\Throwable $th) {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Failed to save image on database. $th",
                    ]);
                    //throw $th;
                }
            }
        }


        return Utils::response([
            'status' => 1,
            'message' => "Animal created successfully.",
            'data' => $f
        ]);
    }


    public function create_update(Request $request)
    {

        $an = Animal::find($request->id);
        if ($an == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Animal not found.",
            ]);
        }

        if ($request->e_id != null && strlen($request->e_id) > 2) {
            $an->e_id = $request->e_id;
        }
        if (
            $request->v_id != null &&
            strlen($request->v_id) > 2

        ) {
            $an->v_id = $request->v_id;
        }
        if ($request->parent_id != null && strlen($request->parent_id) > 0) {
            $an->parent_id = $request->parent_id;
        }
        $group = Group::find($request->group_id);
        if ($group != null) {
            $an->group_id = $request->group_id;
        }
        $an->breed = $request->breed;
        $an->dob = Carbon::parse($request->dob);
        try {
            $an->save();
            return Utils::response([
                'status' => 1,
                'message' => "Success",
                'data' => null
            ]);
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 0,
                'message' => $th,
                'data' => null
            ]);
        }
    }

    public function cut_by_id(Request $request)
    {
        if ($request->id == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Cut ID not provided.",
            ]);
        }
        $cut = SlaughterDistributionRecord::find(trim($request->id));
        if ($cut == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Cut not found.",
            ]);
        }
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $cut
        ]);
    }


    public function events(Request $request)
    {

        $user_id = Utils::get_user_id($request);

        $data = Event::where([
            'administrator_id' => $user_id
        ])->get();

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


        $per_page = 10000000;
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        }

        $administrator_id = Utils::get_user_id($request);
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }
        $role = Utils::get_role($u);

        $is_search = false;
        $items = [];
        $s = $request->s;
        if ($s != null) {
            if (strlen($s) > 0) {
                $is_search = true;

                $an = Animal::where("e_id", $s)->first();
                if ($an == null) {
                    $an = Animal::where("v_id", $s)->first();
                }
                if ($an == null) {
                    return [];
                }
                if (!isset($an->id)) {
                    return [];
                }

                $items = Event::where("animal_id", $an->id)->get();
                if (empty($items)) {
                    return [];
                }
            }
        }

        if (!$is_search) {
            $items = Event::paginate($per_page)->withQueryString()->items();
        }


        $_items = [];
        foreach ($items as $key => $value) {

            if ($role == 'farmer') {
                if ($value->administrator_id != $administrator_id) {
                    continue;
                }
            } else if ($role == 'scvo') {
                if ($u->scvo != $value->sub_county_id) {
                    continue;
                }
            }

            $items[$key]->e_id  = "";
            $items[$key]->v_id  = "";
            $items[$key]->lhc  = "";
            if ($items[$key]->animal != null) {
                if ($items[$key]->animal->e_id != null) {
                    $items[$key]->e_id  = $items[$key]->animal->e_id;
                    $items[$key]->v_id  = $items[$key]->animal->v_id;
                    $items[$key]->lhc  = $items[$key]->animal->lhc;
                }
                unset($items[$key]->animal);
            }
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();
            $_items[] = $items[$key];
        }
        return $_items;
    }

    public function events_v2(Request $request)
    {


        $user_id = Utils::get_user_id($request);
        $conds = [
            'administrator_id' => $user_id
        ];



        $per_page = 10000000;

        $data = Event::where(
            $conds
        )
            ->orderBy('id', 'desc')
            ->limit($per_page)
            ->get([
                'id',
                'animal_id',
                'type',
                'detail',
                'description',
                'created_at',
                'weight',
                'milk',
                'v_id',
                'short_description',
            ]);

        /* if ($request->updated_at != null) {
            if (strlen($request->updated_at) > 2) {
                $updated_at = Carbon::parse($request->updated_at);
                if ($updated_at != null) {
                    $query->whereDate('updated_at', '>', $updated_at);
                }
            }
        } */

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


        $per_page = 10000000;
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        }

        $administrator_id = Utils::get_user_id($request);
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }
        $role = Utils::get_role($u);

        $is_search = false;
        $items = [];
        $s = $request->s;
        if ($s != null) {
            if (strlen($s) > 0) {
                $is_search = true;

                $an = Animal::where("e_id", $s)->first();
                if ($an == null) {
                    $an = Animal::where("v_id", $s)->first();
                }
                if ($an == null) {
                    return [];
                }
                if (!isset($an->id)) {
                    return [];
                }

                $items = Event::where("animal_id", $an->id)->get();
                if (empty($items)) {
                    return [];
                }
            }
        }

        if (!$is_search) {
            $items = Event::paginate($per_page)->withQueryString()->items();
        }


        $_items = [];
        foreach ($items as $key => $value) {

            if ($role == 'farmer') {
                if ($value->administrator_id != $administrator_id) {
                    continue;
                }
            } else if ($role == 'scvo') {
                if ($u->scvo != $value->sub_county_id) {
                    continue;
                }
            }

            $items[$key]->e_id  = "";
            $items[$key]->v_id  = "";
            $items[$key]->lhc  = "";
            if ($items[$key]->animal != null) {
                if ($items[$key]->animal->e_id != null) {
                    $items[$key]->e_id  = $items[$key]->animal->e_id;
                    $items[$key]->v_id  = $items[$key]->animal->v_id;
                    $items[$key]->lhc  = $items[$key]->animal->lhc;
                }
                unset($items[$key]->animal);
            }
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();
            $_items[] = $items[$key];
        }
        return $_items;
    }



    public function events_v3(Request $request)
    {


        $user_id = Utils::get_user_id($request);
        $conds = [
            'administrator_id' => $user_id
        ];

        $last_id = 0;
        if ($request->last_id != null) {
            $last_id = $request->last_id;
            try {
            } catch (\Throwable $th) {
                $last_id = 0;
            }
        }

        /* if ($last_id < 1) {
            $lastEvent = Event::where([])->orderBy('id', 'desc')->first();
            if ($lastEvent != null) {
                $last_id = $lastEvent->id;
            }
        } */
        $per_page = 3000;
        // $last_update = $last_update->startOfDay();
        if (isset($request->isHotReload)) {
            if ($request->isHotReload == 'Yes') {
                $conds = [];
                $per_page = 10000000;
            }
        }
        $conds['administrator_id'] = $user_id;

        $data = Event::where(
            $conds
        )
            ->where('id', '>', $last_id)
            ->orderBy('id', 'asc')
            ->limit($per_page)
            ->get([
                'id',
                'animal_id',
                'type',
                'detail',
                'description',
                'created_at',
                'weight',
                'milk',
                'v_id',
                'short_description',
                'price',
            ]);

        /* if ($request->updated_at != null) {
            if (strlen($request->updated_at) > 2) {
                $updated_at = Carbon::parse($request->updated_at);
                if ($updated_at != null) {
                    $query->whereDate('updated_at', '>', $updated_at);
                }
            }
        } */

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


        $per_page = 10000000;
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        }

        $administrator_id = Utils::get_user_id($request);
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }
        $role = Utils::get_role($u);

        $is_search = false;
        $items = [];
        $s = $request->s;
        if ($s != null) {
            if (strlen($s) > 0) {
                $is_search = true;

                $an = Animal::where("e_id", $s)->first();
                if ($an == null) {
                    $an = Animal::where("v_id", $s)->first();
                }
                if ($an == null) {
                    return [];
                }
                if (!isset($an->id)) {
                    return [];
                }

                $items = Event::where("animal_id", $an->id)->get();
                if (empty($items)) {
                    return [];
                }
            }
        }

        if (!$is_search) {
            $items = Event::paginate($per_page)->withQueryString()->items();
        }


        $_items = [];
        foreach ($items as $key => $value) {

            if ($role == 'farmer') {
                if ($value->administrator_id != $administrator_id) {
                    continue;
                }
            } else if ($role == 'scvo') {
                if ($u->scvo != $value->sub_county_id) {
                    continue;
                }
            }

            $items[$key]->e_id  = "";
            $items[$key]->v_id  = "";
            $items[$key]->lhc  = "";
            if ($items[$key]->animal != null) {
                if ($items[$key]->animal->e_id != null) {
                    $items[$key]->e_id  = $items[$key]->animal->e_id;
                    $items[$key]->v_id  = $items[$key]->animal->v_id;
                    $items[$key]->lhc  = $items[$key]->animal->lhc;
                }
                unset($items[$key]->animal);
            }
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();
            $_items[] = $items[$key];
        }
        return $_items;
    }
}
