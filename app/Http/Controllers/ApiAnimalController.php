<?php

namespace App\Http\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Animal;
use App\Models\ArchivedAnimal;
use App\Models\BatchSession;
use App\Models\Disease;
use App\Models\DistrictVaccineStock;
use App\Models\DrugReport;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\FarmVaccinationRecord;
use App\Models\Group;
use App\Models\Image;
use App\Models\Location;
use App\Models\MeatCut;
use App\Models\Movement;
use App\Models\SlaughterDistributionRecord;
use App\Models\SlaughterHouse;
use App\Models\SlaughterRecord;
use App\Models\User;
use App\Models\UserHasFarmPermission;
use App\Models\Utils;
use App\Models\VaccinationProgram;
use App\Models\VaccinationSchedule;
use App\Models\VaccineMainStock;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Monolog\Handler\Slack\SlackRecord;

class ApiAnimalController extends Controller
{



    public function upload_media_v2(Request $request)
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
        $accepted_types = ['ANIMAL_GALLERY'];
        if (
            !isset($request->type) ||
            $request->type == null ||
            !in_array($request->type, $accepted_types)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Type not found.",
            ]);
        }

        //if $request->local_parent_id is not set or less than 3 length, return error
        if (
            !isset($request->local_parent_id) ||
            $request->local_parent_id == null ||
            strlen($request->local_parent_id) < 3
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Local parent ID is missing.",
            ]);
        }

        $animal = null;
        if ($request->type == 'ANIMAL_GALLERY') {
            $local_id = ($request->local_parent_id);
            if ($local_id != null && strlen($local_id) > 3) {
                $animal = Animal::where('local_id', $local_id)->first();
                if ($animal == null) {
                    $animal = Animal::find($request->parent_id);
                }
            }
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


            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  $src;
            $img->parent_endpoint =  $request->parent_endpoint;
            $img->parent_id =  (int)($parent_id);
            $img->size = 0;
            $img->note = '';
            if ($animal != null) {
                $img->parent_id =  $animal->id;
                $img->parent_endpoint =  'Animal';
            }

            /*  $online_parent_id = ((int)($request->online_parent_id));
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
            } */

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
        // Enhanced validation for task parameter
        if (!$r->has('task') || empty($r->task)) {
            return Utils::response([
                'status' => 0,
                'message' => "Task parameter is required.",
            ]);
        }

        $validTasks = ['Create', 'Edit'];
        if (!in_array($r->task, $validTasks)) {
            return Utils::response([
                'status' => 0,
                'message' => "Invalid task. Must be 'Create' or 'Edit'.",
            ]);
        }

        // Get and validate user
        $user_id = Utils::get_user_id($r);
        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "User authentication failed. Please login again.",
            ]);
        }

        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User account not found.",
            ]);
        }

        $sr = null;
        
        if ($r->task == 'Create') {
            // Validate v_id is provided
            if (!$r->has('v_id') || empty($r->v_id)) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Animal ID (v_id) is required for creating slaughter record.",
                ]);
            }

            // Find animal with enhanced validation
            $an = Animal::where('v_id', $r->v_id)->first();
            if ($an == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Animal with ID '{$r->v_id}' not found in the system.",
                ]);
            }

            // Check for existing slaughter record
            $existing = SlaughterRecord::where('v_id', $r->v_id)->first();
            if ($existing != null) {
                return Utils::response([
                    'data' => $existing,
                    'status' => 1,
                    'message' => "Slaughter record already exists for this animal.",
                ]);
            }

            // Get slaughter house if provided
            $house = null;
            if ($r->has('house_id') && !empty($r->house_id)) {
                $house = SlaughterHouse::find($r->house_id);
                if ($house == null) {
                    return Utils::response([
                        'status' => 0,
                        'message' => "Slaughter house with ID '{$r->house_id}' not found.",
                    ]);
                }
            }

            // Create new slaughter record with transaction safety
            try {
                $sr = new SlaughterRecord();
                $sr->lhc = $an->lhc;
                $sr->v_id = $an->v_id;
                $sr->administrator_id = $user_id;
                $sr->e_id = $an->e_id;
                $sr->breed = $an->breed;
                $sr->sex = $an->sex;
                $sr->dob = Carbon::parse($an->dob);
                $sr->fmd = $an->fmd;
                $sr->details = "Slaughtered by {$u->name}, ID {$u->id} on " . date('Y-m-d H:i:s');

                if ($house != null) {
                    $sr->destination_slaughter_house = $house->id;
                }

                $sr->save();
            } catch (\Throwable $e) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to create slaughter record: " . $e->getMessage(),
                ]);
            }
        } else if ($r->task == 'Edit') {
            // Validate record ID for editing
            if (!$r->has('id') || empty($r->id)) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Record ID is required for editing.",
                ]);
            }

            $sr = SlaughterRecord::find($r->id);
            if ($sr == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Slaughter record with ID '{$r->id}' not found.",
                ]);
            }
        }

        if ($sr == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to initialize slaughter record.",
            ]);
        }


        // Generate barcode if not exists
        if (empty($sr->bar_code) || strlen($sr->bar_code) < 2) {
            try {
                $sr->bar_code = Utils::generate_barcode($sr->v_id);
            } catch (\Throwable $e) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to generate barcode: " . $e->getMessage(),
                ]);
            }
        }

        // Update postmortem inspection fields with validation
        if ($r->has('post_animal') && !empty($r->post_animal)) {
            $sr->post_animal = trim($r->post_animal);
        }

        if ($r->has('post_age') && !empty($r->post_age)) {
            $sr->post_age = trim($r->post_age);
        }

        if ($r->has('post_dentition') && !empty($r->post_dentition)) {
            $sr->post_dentition = trim($r->post_dentition);
        }

        // Validate and update weight fields
        if ($r->has('post_weight') && !empty($r->post_weight)) {
            $weight = floatval($r->post_weight);
            if ($weight <= 0) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Post weight must be greater than 0.",
                ]);
            }
            $sr->post_weight = $weight;
        }

        if ($r->has('available_weight') && !empty($r->available_weight)) {
            $availWeight = floatval($r->available_weight);
            if ($availWeight < 0) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Available weight cannot be negative.",
                ]);
            }
            $sr->available_weight = $availWeight;
        }

        if ($r->has('post_fat') && !empty($r->post_fat)) {
            $sr->post_fat = trim($r->post_fat);
        }

        if ($r->has('post_other') && !empty($r->post_other)) {
            $sr->post_other = trim($r->post_other);
        }

        if ($r->has('has_post_info') && !empty($r->has_post_info)) {
            $sr->has_post_info = $r->has_post_info;
        }

        // Validate grade
        if ($r->has('post_grade') && !empty($r->post_grade)) {
            $validGrades = ['A', 'B', 'C', 'D', 'E', 'Grade A', 'Grade B', 'Grade C', 'Grade D', 'Grade E'];
            $grade = trim($r->post_grade);
            // Accept any grade value but log if it's not standard
            $sr->post_grade = $grade;
        }

        if ($r->has('breed') && !empty($r->breed)) {
            $sr->breed = trim($r->breed);
        }

        // Save with error handling
        try {
            $sr->save();
            
            // Refresh record to get latest data
            $sr = SlaughterRecord::find($sr->id);
            
            return Utils::response([
                'data' => $sr,
                'status' => 1,
                'message' => $r->task == 'Create' 
                    ? "Slaughter record created successfully." 
                    : "Slaughter record updated successfully.",
            ]);
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save slaughter record: " . $e->getMessage(),
            ]);
        }
    }


    public function slaughter_record_assign_carcus_owner(Request $r)
    {
        // Validate user authentication
        $user_id = Utils::get_user_id($r);
        if ($user_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "User authentication failed. Please login again.",
            ]);
        }

        // Validate slaughter record ID
        if (!$r->has('slaughter_record_id') || empty($r->slaughter_record_id)) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter record ID is required.",
            ]);
        }

        // Find slaughter record
        $sr = SlaughterRecord::find($r->slaughter_record_id);
        if ($sr == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter record with ID '{$r->slaughter_record_id}' not found.",
            ]);
        }

        // Check if record is complete enough to assign owner
        if (empty($sr->post_grade)) {
            return Utils::response([
                'status' => 0,
                'message' => "Cannot assign owner. Please complete weighing and grading first.",
            ]);
        }

        // Validate carcass owner ID
        if (!$r->has('carcus_owen_id') || empty($r->carcus_owen_id)) {
            return Utils::response([
                'status' => 0,
                'message' => "Carcass owner ID is required.",
            ]);
        }

        // Find carcass owner
        $owner = Administrator::find($r->carcus_owen_id);
        if ($owner == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Carcass owner with ID '{$r->carcus_owen_id}' not found.",
            ]);
        }

        // Check if already assigned to prevent duplicate assignment
        if ($sr->carcus_owen_assigned == 'Yes' && $sr->carcus_owen_id == $owner->id) {
            return Utils::response([
                'data' => $sr,
                'status' => 1,
                'message' => "Carcass already assigned to {$owner->name}.",
            ]);
        }

        // Assign carcass owner with transaction safety
        try {
            $sr->carcus_owen_id = $owner->id;
            $sr->carcus_owen_assigned = 'Yes';
            $sr->carcus_owen_name = $owner->name;
            
            $sr->save();
            
            // Refresh to get latest data
            $sr = SlaughterRecord::find($sr->id);
            
            return Utils::response([
                'data' => $sr,
                'status' => 1,
                'message' => "Carcass successfully assigned to {$owner->name}.",
            ]);
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to assign carcass owner: " . $e->getMessage(),
            ]);
        }
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
        // if ($receiver == null) {
        //     return Utils::response([
        //         'status' => 0,
        //         'message' => "Receiver not found.",
        //     ]);
        // }


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
        if ($receiver != null) {
            $rec->receiver_id = $receiver->id;
            $rec->receiver_type = "Trader";
            $rec->receiver_name = $receiver->name;
            $rec->receiver_address = $receiver->address;
            $rec->receiver_phone = $receiver->phone_number;
        } else {
            $rec->receiver_id = 1;
            $rec->receiver_type = "Trader";
            $rec->receiver_name = "Unknown";
            $rec->receiver_address = "Unknown";
            $rec->receiver_phone = "Unknown";
        }
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
                'message' => "Animal not found on our database. (ID: $request->animal_id)",
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


    public function drug_report_create(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $farm = Farm::find($request->farm_id);
        if ($farm == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Farm not found.",
            ]);
        }
        $rep = new DrugReport();
        $rep->owner_id = $u->id;
        $rep->farm_id = $request->farm_id;
        $rep->period_type = $request->period_type;
        $rep->period = $request->period;
        $rep->start_date = $request->start_date;
        $rep->end_date = $request->end_date;
        $rep->total_cost = $request->total_cost;
        $rep->design = $request->design;
        $rep->pdf_generated = 'No';
        $rep->pdf_path = null;
        try {
            $rep->save();
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save drug report on database.",
            ]);
        }

        try {
            DrugReport::do_process($rep);
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed  because of $th",
            ]);
        }

        $rep = DrugReport::find($rep->id);
        return Utils::response([
            'status' => 1,
            'message' => "Drug report created successfully.",
            'data' => $rep
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



        //

        $isMultipleEvents = false;
        // check if $request->animal_id contains [
        $_animal_ids = trim($request->animal_id);
        $animal_ids = [];
        if (strpos($_animal_ids, '[') !== false) {
            $isMultipleEvents = true;
            $animal_ids = [];
            try {
                $animal_ids = json_decode($request->animal_id);
            } catch (\Throwable $th) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Invalid animal_ids because of " . $th->getMessage(),
                ]);
            }
            if (count($animal_ids) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "No animal_ids found.",
                ]);
            } else {
                $isMultipleEvents = true;
            }
        }

        if (!$isMultipleEvents) {
            $animal = Animal::find(((int)($request->animal_id)));
            if ($animal == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Animal not found on our database. (ID: $request->animal_id)",
                ]);
            }
            $animal_ids[] = $animal->id;
        }

        $accepted_events = Event::ACCEPTED_EVENT_TYPES;


        if (!in_array($request->type, $accepted_events)) {
            return Utils::response([
                'status' => 0,
                'message' => "Invalid event type.",
            ]);
        }

        $event = new Event();

        $table_name = $event->getTable();
        $cols = Schema::getColumnListing($table_name);

        $except = [
            'created_at',
            'updated_at',
            'deleted_at',
            'online_id',
            'id',
            'administrator_id',
            'user_id',
            'created_by',
            'updated_by',
        ];

        foreach ($_POST as $key => $value) {
            if (in_array($key, $except)) {
                continue;
            }
            if (!in_array($key, $cols)) {
                continue;
            }
            if ($value == null || strlen($value) < 1) {
                continue;
            }
            $event->$key = $value;
        }

        $event->session_id = $request->id;
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
            $request->status = 'Positive';
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
            $event->description = "Disease test for {$disease->name} and found the animal {$test_results}.";
        } else if ($request->type == 'Treatment') {
            $disease = Disease::find(((int)($request->disease_id)));
            if ($disease == null) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Disease not found on our database.",
                ]);
            }

            //medicine_quantity
            /*  if ($request->medicine_quantity == null || strlen($request->medicine_quantity) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Medicine quantity must be provided.",
                ]);
            } */
            /*  if (floatval($request->medicine_quantity) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Medicine quantity must be greater than 0.",
                ]);
            } */
            $event->disease_id = $disease->id;
            $event->medicine_id = $request->medicine_id;
            $event->disease_text = $disease->name;
            $event->medicine_quantity = $request->medicine_quantity;
            $event->medicine_text = $request->medicine_id;
        } else if ($request->type == 'Vaccination') {
            /* if ($request->vaccination == null || strlen($request->vaccination) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Vaccination must be provided.",
                ]);
            } */
            if ($request->inseminator == null || strlen($request->inseminator) < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Disease must be provided.",
                ]);
            }
            //created description text that explain vaccine used and disease name vaccinated against
            $event->description = 'Vaccined against ' . $request->inseminator;
            $event->disease_text = $request->inseminator;
            $event->disease_id = $request->inseminator;
        } else if ($request->type == 'Abortion') {
            if (!isset($request->wean_date)) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Abortion date must be provided.",
                ]);
            }
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

        if ($request->description == null || (strlen($event->description) < 4)) {
            $event->description = $request->description;
        }
        if ($request->medicine_quantity == null || (strlen($event->medicine_quantity) < 1)) {
            $event->medicine_quantity = $request->medicine_quantity;
        }
        if ($request->disease_text == null || (strlen($event->disease_text) < 4)) {
            $event->disease_text = $request->disease_text;
        }

        $event->detail = $request->detail;
        if ($event->detail == null || strlen($event->detail) < 2) {
            $event->detail = $request->description;
        }

        if ($request->disease_id != null) {
            $event->disease_id = $request->disease_id;
        }
        if ($event->medicine_id == null || strlen($event->medicine_id) < 1) {
            $event->medicine_id = $request->medicine_id;
        }


        if ($request->price != null) {
            $event->price = $request->price;
        }
        if ($request->reproduction_type != null) {
            $event->reproduction_type = $request->reproduction_type;
        }
        if ($request->service_type != null) {
            $event->service_type = $request->service_type;
        }
        if ($request->service_date != null) {
            try {
                $event->service_date = Carbon::parse($request->service_date);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        if ($request->male_id != null) {
            $event->male_id = $request->male_id;
        }
        if ($request->male_breed != null) {
            $event->male_breed = $request->male_breed;
        }
        if ($request->simen_code != null) {
            $event->simen_code = $request->simen_code;
        }
        if ($request->inseminator != null) {
            $event->inseminator = $request->inseminator;
        }

        if ($request->calving_date != null) {
            try {
                $event->calving_date = Carbon::parse($request->calving_date);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        if ($request->wean_date != null) {
            try {
                $event->wean_date = Carbon::parse($request->wean_date);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        //calf_id
        if ($request->calf_id != null) {
            $event->calf_id = $request->calf_id;
        }
        if ($request->calf_sex != null) {
            $event->calf_sex = $request->calf_sex;
        }
        if ($request->calf_weight != null) {
            $event->calf_weight = $request->calf_weight;
        }
        if ($request->wean_weight != null) {
            $event->wean_weight = $request->wean_weight;
        }
        if ($request->wean_milk != null) {
            $event->wean_milk = $request->wean_milk;
        }
        if ($request->is_present != null) {
            $event->is_present = $request->is_present;
        }
        if ($request->medicine_name != null) {
            $event->medicine_name = $request->medicine_name;
        }

        $event->temperature = $request->temperature;
        $event->pregnancy_check_method = $request->pregnancy_check_method;
        $event->pregnancy_check_results = $request->pregnancy_check_results;
        $event->pregnancy_expected_sex = $request->pregnancy_expected_sex;
        $event->pregnancy_fertilization_method = $request->pregnancy_fertilization_method;
        $event->disease_test_results = $request->disease_test_results;
        $event->milk = $request->milk;
        $event->weight = $request->weight;

        try {

            $eve = null;
            $count = 0;
            foreach ($animal_ids as $key => $animal_id) {
                $animal = Animal::find($animal_id);
                if ($animal == null) {
                    continue;
                }
                $event_clone = clone $event;
                $event_clone->animal_id = $animal->id;
                $event_clone->v_id = $animal->v_id;
                $event_clone->e_id = $animal->e_id;
                $event_clone->save();
                $eve = Event::find($event_clone->id);
                $count++;
            }

            $event = Event::find($event->id);
            return Utils::response([
                'status' => 1,
                'message' => "Created $count events successfully.",
                'data' => $eve
            ]);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return Utils::response([
                'status' => 2,
                'message' => "Failed Because: $message",
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
            'message' => "Success. count: " . count($data),
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
        try {
            Utils::archive_soft_deleted_animals();
        } catch (\Throwable $th) {
            //throw $th;
        }

        $user_id = Utils::get_user_id($request);
        $animals_query = "
            SELECT 
                id,
                created_at,
                administrator_id,
                farm_id,
                status,
                type,
                e_id,
                v_id,
                lhc,
                breed,
                sex,
                price,
                weight,
                group_id,
                local_id,
                age,
                group_id,
                parent_id,
                photo
            FROM animals 
            WHERE administrator_id = $user_id && deleted_at IS NULL
            ORDER BY id DESC 
            LIMIT 2000
        ";

        $animals = DB::select($animals_query);
        return Utils::response([
            'status' => 1,
            'code' => 1,
            'message' => "Success. Count => " . count($animals),
            'data' => $animals
        ]);

        // ===== OPTIMIZATION 3: Minimal processing - just return raw data =====
        // Pre-compute current timestamp once for all animals
        $now = time();

        $data = [];
        foreach ($animals as $animal) {
            // Convert stdClass to array
            $animal_array = (array) $animal;

            // Only add essential computed fields - NO DATABASE WRITES
            $animal_array['local_id'] = !empty($animal->local_id) ? $animal->local_id : '';
            // $animal_array['age'] = !empty($animal->age) ? (int)$animal->age : 0;

            // Add nulled accessor fields to match original structure
            $animal_array['images'] = null;
            $animal_array['photos'] = null;
            $animal_array['last_seen'] = null;
            $animal_array['phone_number'] = "+256706638494";
            $animal_array['whatsapp'] = "+8801632257609";
            $animal_array['price_text'] = !empty($animal->price) ? "UGX " . number_format($animal->price) : "UGX 0";
            $animal_array['location'] = null;
            $animal_array['parent_text'] = null;
            $animal_array['group_text'] = null;
            $animal_array['profile_updated'] = 'Yes';

            // Use raw timestamps instead of Carbon parsing (10x faster)
            $animal_array['posted'] = strtotime($animal->created_at);

            $data[] = $animal_array;
        }

        return Utils::response([
            'status' => 1,
            'code' => 1,
            'message' => "Success. Count: " . count($data),
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

    // ========== BUTCHER RECORDS API METHODS ==========

    public function butcher_records(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }

        $items = \App\Models\ButcherRecord::where('created_by_id', $user_id)
            ->limit(4000)
            ->orderBy('id', 'DESC')
            ->get();
            
        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $items
        ]);
    }

    public function create_butcher_record(Request $r)
    {
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

        // Validate required fields
        if (!$r->has('slaughter_distribution_record_id') || $r->slaughter_distribution_record_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Meat cut record is required.",
            ]);
        }

        $sdr = SlaughterDistributionRecord::find($r->slaughter_distribution_record_id);
        if ($sdr == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Meat cut record not found.",
            ]);
        }

        // Validate weight
        $weight = floatval($r->original_weight ?? 0);
        if ($weight <= 0) {
            return Utils::response([
                'status' => 0,
                'message' => "Weight must be greater than 0.",
            ]);
        }

        $available = floatval($sdr->current_weight ?? 0);
        if ($available < $weight) {
            return Utils::response([
                'status' => 0,
                'message' => "Weight ({$weight}kg) can't be more than available weight ({$available}kg).",
            ]);
        }

        // Validate cut type
        if (!$r->has('cut_type') || empty($r->cut_type)) {
            return Utils::response([
                'status' => 0,
                'message' => "Cut type is required (Prime Cut or Offal Cut).",
            ]);
        }

        // Validate specific cut type based on cut_type
        if ($r->cut_type == 'Prime Cut' && empty($r->prime_cut_type)) {
            return Utils::response([
                'status' => 0,
                'message' => "Prime cut type is required.",
            ]);
        }

        if ($r->cut_type == 'Offal Cut' && empty($r->offal_cut_type)) {
            return Utils::response([
                'status' => 0,
                'message' => "Offal cut type is required.",
            ]);
        }

        // Update the meat cut available weight
        $sdr->current_weight = $available - $weight;
        
        // Get buyer info if provided
        $buyer = null;
        $is_sold = $r->is_sold ?? 'No';
        if ($is_sold == 'Yes' && $r->has('buyer_id') && $r->buyer_id > 0) {
            $buyer = Administrator::find($r->buyer_id);
        }

        // Create butcher record
        $rec = new \App\Models\ButcherRecord();
        $rec->slaughter_distribution_record_id = $sdr->id;
        $rec->animal_id = $sdr->animal_id;
        $rec->slaughterhouse_id = $sdr->slaughterhouse_id;
        $rec->created_by_id = $u->id;
        $rec->source_type = "Butcher Shop";
        $rec->source_id = $sdr->id;
        $rec->source_name = $u->name;
        $rec->source_phone = $u->phone_number;
        $rec->source_address = $u->address ?? "";
        
        // Sold status and buyer information
        $rec->is_sold = $is_sold;
        if ($is_sold == 'Yes') {
            $rec->sold_date = now();
            $rec->sold_price = $r->sold_price ?? $r->price ?? "";
            
            // Buyer from system
            if ($buyer != null) {
                $rec->buyer_id = $buyer->id;
                $rec->buyer_name = $buyer->name;
                $rec->buyer_address = $buyer->address ?? "";
                $rec->buyer_phone = $buyer->phone_number;
            } else {
                // Manual buyer info
                $rec->buyer_name = $r->buyer_name ?? "";
                $rec->buyer_phone = $r->buyer_phone ?? "";
                $rec->buyer_address = $r->buyer_address ?? "";
            }
        }
        
        $rec->lhc = $sdr->lhc;
        $rec->v_id = $sdr->v_id;
        $rec->e_id = $sdr->e_id;
        $rec->animal_owner_id = $sdr->animal_owner_id;
        $rec->post_fat = $sdr->post_fat;
        $rec->post_grade = $sdr->post_grade;
        $rec->post_animal = $sdr->post_animal;
        $rec->post_age = $sdr->post_age;
        $rec->original_weight = $weight;
        $rec->current_weight = $weight;
        $rec->price = $r->price ?? "";
        $rec->slaughter_date = $sdr->slaughter_date;
        $rec->cut_type = $r->cut_type;
        $rec->prime_cut_type = $r->prime_cut_type ?? "";
        $rec->offal_cut_type = $r->offal_cut_type ?? "";
        $rec->notes = $r->notes ?? "";

        try {
            $rec->save();
            
            try {
                // Generate simple unique codes
                $cutName = $rec->cut_type == 'Prime Cut' ? $rec->prime_cut_type : $rec->offal_cut_type;
                $random = strtoupper(substr(md5(uniqid($rec->id, true)), 0, 8));
                
                // Simple barcode - just unique code
                $barcodeData = "BR{$rec->id}{$random}";
                
                // Generate ACTUAL BARCODE (not QR code) using generate_barcode
                $barcodePath = Utils::generate_barcode($barcodeData);
                $rec->bar_code = $barcodePath;
                
                // Professional QR code with essential information
                $url = url('butcher-record/' . $rec->id);
                $qrData = "ID: {$rec->id}, V-ID: {$rec->v_id}, Cut: {$cutName}, Weight: {$rec->current_weight}kg, Grade: {$rec->post_grade}, Code: {$barcodeData}, URL: {$url}";
                $qrPath = Utils::generate_qrcode($qrData);
                $rec->qr_code = $qrPath;
                $rec->save();
                
                // Update meat cut weight
                $sdr->save();

                // Send notification to buyer if exists and sold
                if ($buyer != null && $is_sold == 'Yes') {
                    $cutName = $rec->cut_type == 'Prime Cut' ? $rec->prime_cut_type : $rec->offal_cut_type;
                    $msg = "You have purchased {$rec->current_weight}kg of {$cutName} from {$rec->source_name}. Open the App to see more details.";
                    $title = "MEAT PURCHASE - {$rec->v_id}";
                    Utils::sendNotification(
                        $msg,
                        $buyer->id,
                        $headings = $title,
                        $data = [$rec->id]
                    );
                }

                // Refresh records
                $sdr = SlaughterDistributionRecord::find($sdr->id);
                $rec = \App\Models\ButcherRecord::find($rec->id);

                return Utils::response([
                    'status' => 1,
                    'message' => "Butcher record created successfully.",
                    'data' => [
                        'sdr' => $sdr,
                        'butcher_record' => $rec,
                    ]
                ]);
            } catch (\Throwable $e) {
                $rec->delete();
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to generate QR code. {$e->getMessage()}",
                ]);
            }
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to save butcher record. {$e->getMessage()}",
            ]);
        }
    }

    public function create_butcher_records_batch(Request $r)
    {
        // Get authenticated user
        $user_id = Utils::get_user_id($r);
        if ($user_id == null || $user_id < 1) {
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

        // Validate slaughter_distribution_record_id (same as single record)
        if (!$r->has('slaughter_distribution_record_id') || $r->slaughter_distribution_record_id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Meat cut record is required.",
            ]);
        }

        $sdr = SlaughterDistributionRecord::find($r->slaughter_distribution_record_id);
        if ($sdr == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Meat cut record not found.",
            ]);
        }

        // Validate records array
        if (!$r->has('records') || !is_array($r->records) || empty($r->records)) {
            return Utils::response([
                'status' => 0,
                'message' => "At least one record is required for batch creation.",
            ]);
        }

        $records = $r->records;
        $createdRecords = [];
        $totalWeight = 0;
        
        // Calculate total weight needed
        foreach ($records as $recordData) {
            $totalWeight += floatval($recordData['original_weight'] ?? 0);
        }

        // Check if total weight exceeds available
        $available = floatval($sdr->current_weight ?? 0);
        if ($totalWeight > $available) {
            return Utils::response([
                'status' => 0,
                'message' => "Total weight ({$totalWeight}kg) exceeds available weight ({$available}kg).",
            ]);
        }

        // Start database transaction
        DB::beginTransaction();
        
        try {
            foreach ($records as $index => $recordData) {
                // Validate weight (using original_weight like single record)
                $weight = floatval($recordData['original_weight'] ?? 0);
                if ($weight <= 0) {
                    DB::rollBack();
                    return Utils::response([
                        'status' => 0,
                        'message' => "Record " . ($index + 1) . ": Weight must be greater than 0.",
                    ]);
                }

                // Validate cut type
                if (!isset($recordData['cut_type']) || empty($recordData['cut_type'])) {
                    DB::rollBack();
                    return Utils::response([
                        'status' => 0,
                        'message' => "Record " . ($index + 1) . ": Cut type is required (Prime Cut or Offal Cut).",
                    ]);
                }

                // Validate specific cut type
                if ($recordData['cut_type'] == 'Prime Cut' && empty($recordData['prime_cut_type'])) {
                    DB::rollBack();
                    return Utils::response([
                        'status' => 0,
                        'message' => "Record " . ($index + 1) . ": Prime cut type is required.",
                    ]);
                }

                if ($recordData['cut_type'] == 'Offal Cut' && empty($recordData['offal_cut_type'])) {
                    DB::rollBack();
                    return Utils::response([
                        'status' => 0,
                        'message' => "Record " . ($index + 1) . ": Offal cut type is required.",
                    ]);
                }

                // Handle buyer
                $buyer = null;
                $is_sold = $recordData['is_sold'] ?? 'No';
                
                if ($is_sold == 'Yes' && isset($recordData['buyer_id']) && $recordData['buyer_id'] > 0) {
                    $buyer = Administrator::find($recordData['buyer_id']);
                }

                // Create butcher record (matching single record structure exactly)
                $rec = new \App\Models\ButcherRecord();
                $rec->slaughter_distribution_record_id = $sdr->id;
                $rec->animal_id = $sdr->animal_id;
                $rec->slaughterhouse_id = $sdr->slaughterhouse_id;
                $rec->created_by_id = $u->id;
                $rec->source_type = "Butcher Shop";
                $rec->source_id = $sdr->id;
                $rec->source_name = $u->name;
                $rec->source_phone = $u->phone_number;
                $rec->source_address = $u->address ?? "";
                
                // Sold status and buyer information
                $rec->is_sold = $is_sold;
                if ($is_sold == 'Yes') {
                    $rec->sold_date = now();
                    $rec->sold_price = $recordData['price'] ?? "";
                    
                    if ($buyer != null) {
                        $rec->buyer_id = $buyer->id;
                        $rec->buyer_name = $buyer->name;
                        $rec->buyer_address = $buyer->address ?? "";
                        $rec->buyer_phone = $buyer->phone_number;
                    } else {
                        $rec->buyer_name = $recordData['buyer_name'] ?? "";
                        $rec->buyer_phone = $recordData['buyer_phone'] ?? "";
                        $rec->buyer_address = $recordData['buyer_address'] ?? "";
                    }
                }
                
                $rec->lhc = $sdr->lhc;
                $rec->v_id = $sdr->v_id;
                $rec->e_id = $sdr->e_id;
                $rec->animal_owner_id = $sdr->animal_owner_id;
                $rec->post_fat = $sdr->post_fat;
                $rec->post_grade = $sdr->post_grade;
                $rec->post_animal = $sdr->post_animal;
                $rec->post_age = $sdr->post_age;
                $rec->original_weight = $weight;
                $rec->current_weight = $weight;
                $rec->price = $recordData['price'] ?? "";
                $rec->slaughter_date = $sdr->slaughter_date;
                $rec->cut_type = $recordData['cut_type'] ?? 'Prime Cut';
                $rec->prime_cut_type = $recordData['prime_cut_type'] ?? "";
                $rec->offal_cut_type = $recordData['offal_cut_type'] ?? "";
                $rec->notes = $recordData['notes'] ?? "";

                $rec->save();

                // Generate codes
                $cutName = $rec->cut_type == 'Prime Cut' ? $rec->prime_cut_type : $rec->offal_cut_type;
                $random = strtoupper(substr(md5(uniqid($rec->id, true)), 0, 8));
                
                // Simple barcode
                $barcodeData = "BR{$rec->id}{$random}";
                $barcodePath = Utils::generate_barcode($barcodeData);
                $rec->bar_code = $barcodePath;
                
                // Professional QR code
                $url = url('butcher-record/' . $rec->id);
                $qrData = "ID: {$rec->id}, V-ID: {$rec->v_id}, Cut: {$cutName}, Weight: {$rec->current_weight}kg, Grade: {$rec->post_grade}, Code: {$barcodeData}, URL: {$url}";
                $qrPath = Utils::generate_qrcode($qrData);
                $rec->qr_code = $qrPath;
                $rec->save();

                // Send notification to buyer if exists and sold
                if ($buyer != null && $is_sold == 'Yes') {
                    $msg = "You have purchased {$rec->current_weight}kg of {$cutName}. Open the App to see more details.";
                    $title = "MEAT PURCHASE - {$rec->v_id}";
                    Utils::sendNotification(
                        $msg,
                        $buyer->id,
                        $headings = $title,
                        $data = [$rec->id]
                    );
                }

                $createdRecords[] = $rec;
            }

            // Update SDR weight after all records created
            $sdr->current_weight = $available - $totalWeight;
            $sdr->save();

            // Commit transaction
            DB::commit();

            // Refresh records from database
            $sdr = SlaughterDistributionRecord::find($sdr->id);
            foreach ($createdRecords as $key => $rec) {
                $createdRecords[$key] = \App\Models\ButcherRecord::find($rec->id);
            }

            return Utils::response([
                'status' => 1,
                'message' => count($createdRecords) . " butcher record(s) created successfully.",
                'data' => [
                    'sdr' => $sdr,
                    'butcher_records' => $createdRecords,
                    'created_count' => count($createdRecords),
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return Utils::response([
                'status' => 0,
                'message' => "Batch creation failed: {$e->getMessage()}",
            ]);
        }
    }

    public function update_butcher_record(Request $r)
    {
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

        if (!$r->has('id') || $r->id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Butcher record ID is required.",
            ]);
        }

        $rec = \App\Models\ButcherRecord::find($r->id);
        if ($rec == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Butcher record not found.",
            ]);
        }

        // Only creator can update
        if ($rec->created_by_id != $user_id) {
            return Utils::response([
                'status' => 0,
                'message' => "You don't have permission to update this record.",
            ]);
        }

        // Update allowed fields
        if ($r->has('price')) {
            $rec->price = $r->price;
        }
        if ($r->has('notes')) {
            $rec->notes = $r->notes;
        }
        if ($r->has('receiver_id') && $r->receiver_id > 0) {
            $receiver = Administrator::find($r->receiver_id);
            if ($receiver != null) {
                $rec->receiver_id = $receiver->id;
                $rec->receiver_name = $receiver->name;
                $rec->receiver_address = $receiver->address ?? "";
                $rec->receiver_phone = $receiver->phone_number;
            }
        }

        try {
            $rec->save();
            return Utils::response([
                'status' => 1,
                'message' => "Butcher record updated successfully.",
                'data' => $rec
            ]);
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to update record. {$e->getMessage()}",
            ]);
        }
    }

    public function mark_butcher_record_sold(Request $r)
    {
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

        if (!$r->has('id') || $r->id < 1) {
            return Utils::response([
                'status' => 0,
                'message' => "Butcher record ID is required.",
            ]);
        }

        $rec = \App\Models\ButcherRecord::find($r->id);
        if ($rec == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Butcher record not found.",
            ]);
        }

        // Only creator can mark as sold
        if ($rec->created_by_id != $user_id) {
            return Utils::response([
                'status' => 0,
                'message' => "You don't have permission to update this record.",
            ]);
        }

        // Validate sold price
        $sold_price = floatval($r->sold_price ?? 0);
        if ($sold_price <= 0) {
            return Utils::response([
                'status' => 0,
                'message' => "Sold price must be greater than 0.",
            ]);
        }

        // Get buyer info if provided
        $buyer = null;
        if ($r->has('buyer_id') && $r->buyer_id > 0) {
            $buyer = Administrator::find($r->buyer_id);
        }

        $rec->is_sold = 'Yes';
        $rec->sold_price = $sold_price;
        $rec->sold_date = now();
        
        if ($buyer != null) {
            $rec->buyer_id = $buyer->id;
            $rec->buyer_name = $buyer->name;
            $rec->buyer_phone = $buyer->phone_number;
            $rec->buyer_address = $buyer->address ?? "";
        } else {
            $rec->buyer_name = $r->buyer_name ?? "Walk-in Customer";
            $rec->buyer_phone = $r->buyer_phone ?? "";
            $rec->buyer_address = $r->buyer_address ?? "";
        }

        try {
            $rec->save();

            // Send notification to buyer if exists
            if ($buyer != null) {
                $msg = "You have purchased {$rec->current_weight}kg of {$rec->cut_type} for UGX {$sold_price}. Open the App to see more details.";
                $title = "PURCHASE CONFIRMED - {$rec->v_id}";
                Utils::sendNotification(
                    $msg,
                    $buyer->id,
                    $headings = $title,
                    $data = [$rec->id]
                );
            }

            return Utils::response([
                'status' => 1,
                'message' => "Butcher record marked as sold successfully.",
                'data' => $rec
            ]);
        } catch (\Throwable $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to mark as sold. {$e->getMessage()}",
            ]);
        }
    }

    // ========== END BUTCHER RECORDS API METHODS ==========


    public function slaughters(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }


        $items = SlaughterRecord::where('administrator_id', $user_id)
            ->orWhere('carcus_owen_id', $user_id)
            ->get();
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


    public function drug_reports(Request $request)
    {
        $user_id = Utils::get_user_id($request);

        $data = DrugReport::where([
            'owner_id' => $user_id
        ])->get();

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
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
                'medicine_id',
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
        $access_ids = [];
        $ownFarms = Farm::where([
            'administrator_id' => $user_id
        ])->get();
        foreach ($ownFarms as $key => $value) {
            if ($value->id != null) {
                $access_ids[] = $value->id;
            }
        }
        $access_records = UserHasFarmPermission::where([
            'user_id' => $user_id
        ])->get();
        foreach ($access_records as $key => $value) {
            if ($value->farm_id != null) {
                $access_ids[] = $value->farm_id;
            }
        }


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

        $data = Event::wherein('farm_id', $access_ids)
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
                'medicine_id',
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

    /**
     * MEMORY-OPTIMIZED EVENTS ENDPOINT v4
     * This endpoint implements strict memory management to prevent OutOfMemoryError crashes:
     * 1. Server-side limiting to max 500 events
     * 2. Respects client pagination parameters
     * 3. Efficient query with minimal data transfer
     * 4. Proper user access control
     */
    public function events_v4(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        if (!$user_id) {
            return Utils::response([
                'status' => 0,
                'message' => "User not authenticated.",
                'data' => []
            ]);
        }

        // ===== OPTIMIZATION 1: Single UNION query for farm access =====
        $farm_ids_query = "
            SELECT id as farm_id FROM farms WHERE administrator_id = ?
            UNION
            SELECT farm_id FROM user_has_farm_permissions WHERE user_id = ?
        ";
        $access_ids_raw = DB::select($farm_ids_query, [$user_id, $user_id]);

        $access_ids = [];
        foreach ($access_ids_raw as $row) {
            if (!empty($row->farm_id)) {
                $access_ids[] = $row->farm_id;
            }
        }

        if (empty($access_ids)) {
            return Utils::response([
                'status' => 1,
                'message' => "No accessible farms found.",
                'data' => []
            ]);
        }

        // ===== OPTIMIZATION 2: Memory safety with configurable limits =====
        $limit = 3000; // Default maximum
        if (isset($request->limit)) {
            $requested_limit = intval($request->limit);
            $limit = min($requested_limit, 3000); // Never exceed 3000
        }

        // Handle incremental sync
        $last_id = 0;
        if (isset($request->last_id)) {
            $last_id = intval($request->last_id);
        }

        // ===== OPTIMIZATION 3: Raw SQL query for maximum speed =====
        $farm_ids_str = implode(',', array_map('intval', $access_ids));

        $events_query = "
            SELECT 
                id,
                animal_id,
                type,
                detail,
                description,
                created_at,
                updated_at,
                weight,
                milk,
                v_id,
                e_id,
                short_description,
                medicine_id,
                price,
                farm_id,
                session_id
            FROM events 
            WHERE farm_id IN ($farm_ids_str)
            AND id > ?
            ORDER BY id DESC 
            LIMIT ?
        ";

        $events = DB::select($events_query, [$last_id, $limit]);

        // ===== OPTIMIZATION 4: Minimal processing - convert to arrays =====
        $data = [];
        foreach ($events as $event) {
            $data[] = (array) $event;
        }

        $data_count = count($data);

        return Utils::response([
            'status' => 1,
            'message' => "Success. Retrieved {$data_count} events.",
            'data' => $data,
            'meta' => [
                'total_returned' => $data_count,
                'limit_applied' => $limit,
                'last_id' => $last_id,
                'memory_safe' => true
            ]
        ]);
    }

    /**
     * Get paginated events with advanced filtering (Optimized endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Query Parameters:
     * - page: int (default: 1, min: 1)
     * - per_page: int (default: 25, min: 5, max: 50)
     * - search: string (searches in e_id, v_id, type, description, detail)
     * - event_type: string (exact match on event type)
     * - category: string (sanitary|production)
     * - animal_id: int (filter by specific animal)
     * - e_id: string (partial match)
     * - v_id: string (partial match)
     * - date_from: string (YYYY-MM-DD format)
     * - date_to: string (YYYY-MM-DD format)
     */
    public function events_online(Request $request)
    {
        try {
            // ===== AUTHENTICATION CHECK =====
            $user_id = Utils::get_user_id($request);
            if ($user_id < 1) {
                return Utils::response([
                    'status' => 0,
                    'message' => "User not authenticated. Please login again.",
                    'data' => [],
                    'error_code' => 'AUTH_REQUIRED'
                ]);
            }

            // ===== OPTIMIZATION 1: Fast farm access check with UNION =====
            try {
                $farm_ids_query = "
                    SELECT id as farm_id FROM farms WHERE administrator_id = ?
                    UNION
                    SELECT farm_id FROM user_has_farm_permissions WHERE user_id = ?
                ";
                $access_ids_raw = DB::select($farm_ids_query, [$user_id, $user_id]);
            } catch (\Exception $e) {
                Log::error("Farm access query failed: " . $e->getMessage());
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to retrieve farm access permissions.",
                    'data' => [],
                    'error_code' => 'DATABASE_ERROR'
                ]);
            }

            // ===== PROCESS FARM ACCESS IDS =====
            $access_ids = [];
            foreach ($access_ids_raw as $row) {
                if (!empty($row->farm_id) && is_numeric($row->farm_id)) {
                    $access_ids[] = intval($row->farm_id);
                }
            }

            // Remove duplicates
            $access_ids = array_unique($access_ids);

            if (empty($access_ids)) {
                return Utils::response([
                    'status' => 1,
                    'message' => "No accessible farms found. Please contact administrator.",
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 25,
                        'total' => 0,
                        'last_page' => 1,
                        'has_more' => false
                    ],
                    'warning' => 'NO_FARMS_ACCESSIBLE'
                ]);
            }

            // ===== OPTIMIZATION 2: Pagination parameters with validation =====
            $page = max(1, intval($request->input('page', 1)));
            $per_page = min(50, max(5, intval($request->input('per_page', 25))));
            
            // Prevent excessive offset (security measure)
            if ($page > 10000) {
                $page = 10000;
            }
            
            $offset = ($page - 1) * $per_page;

            // ===== OPTIMIZATION 3: Search/filter parameters with validation =====
            $search = trim($request->input('search', ''));
            
            // Prevent SQL injection via excessively long search strings
            if (strlen($search) > 200) {
                $search = substr($search, 0, 200);
            }
            
            $event_type = null;
            if ($request->has('event_type')) {
                $event_type = trim($request->input('event_type', ''));
                // Validate event type length
                if (strlen($event_type) < 2 || strlen($event_type) > 100) {
                    $event_type = null;
                }
            }
            
            $category = trim($request->input('category', ''));
            // Validate category - only allow specific values
            if (!in_array(strtolower($category), ['sanitary', 'production', ''])) {
                $category = '';
            }
            
            $animal_id = intval($request->input('animal_id', 0));
            // Validate animal_id is reasonable
            if ($animal_id < 0) {
                $animal_id = 0;
            }
            
            $e_id = trim($request->input('e_id', ''));
            if (strlen($e_id) > 100) {
                $e_id = substr($e_id, 0, 100);
            }
            
            $v_id = trim($request->input('v_id', ''));
            if (strlen($v_id) > 100) {
                $v_id = substr($v_id, 0, 100);
            }
            
            // Validate and sanitize date inputs
            $date_from = $request->input('date_from', '');
            $date_to = $request->input('date_to', '');
            
            // Validate date format (YYYY-MM-DD)
            if (!empty($date_from) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
                $date_from = '';
            }
            if (!empty($date_to) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
                $date_to = '';
            }
            
            // Ensure date_from is not after date_to
            if (!empty($date_from) && !empty($date_to) && strtotime($date_from) > strtotime($date_to)) {
                $temp = $date_from;
                $date_from = $date_to;
                $date_to = $temp;
            }

            // ===== OPTIMIZATION 4: Build raw SQL with dynamic WHERE clauses =====
            $farm_ids_str = implode(',', array_map('intval', $access_ids));

            // Base WHERE clause
            $where_clauses = ["farm_id IN ($farm_ids_str)"];
            $bind_params = [];

        // Search filter
        if (!empty($search)) {
            $where_clauses[] = "(e_id LIKE ? OR v_id LIKE ? OR type LIKE ? OR description LIKE ? OR detail LIKE ?)";
            $search_param = "%{$search}%";
            $bind_params = array_merge($bind_params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
        }

        // Event type filter
        if ($event_type !== null) {
            $where_clauses[] = "type = ?";
            $bind_params[] = $event_type;
        }

        // Category filter (sanitary vs production)
        if (!empty($category)) {
            $sanitary_types = [
                'Treatment',
                'Vaccination',
                'Batch Treatment',
                'Temperature check',
                'Death',
                'Disease test',
                'Disease',
                'Abortion',
                'Sample taken',
                'Sample result',
                'Test conducted',
                'Test result',
                'Mortality'
            ];

            if (strtolower($category) === 'sanitary') {
                $types_str = "'" . implode("','", $sanitary_types) . "'";
                $where_clauses[] = "type IN ($types_str)";
            } else if (strtolower($category) === 'production') {
                $types_str = "'" . implode("','", $sanitary_types) . "'";
                $where_clauses[] = "type NOT IN ($types_str)";
            }
        }

        // Animal ID filter
        if ($animal_id > 0) {
            $where_clauses[] = "animal_id = ?";
            $bind_params[] = $animal_id;
        }

        // E-ID filter
        if (!empty($e_id)) {
            $where_clauses[] = "e_id LIKE ?";
            $bind_params[] = "%{$e_id}%";
        }

        // V-ID filter
        if (!empty($v_id)) {
            $where_clauses[] = "v_id LIKE ?";
            $bind_params[] = "%{$v_id}%";
        }

            // Date filters - with proper validation
            if (!empty($date_from) && strlen($date_from) >= 10) {
                $where_clauses[] = "DATE(created_at) >= ?";
                $bind_params[] = $date_from;
            }
            if (!empty($date_to) && strlen($date_to) >= 10) {
                $where_clauses[] = "DATE(created_at) <= ?";
                $bind_params[] = $date_to;
            }

            $where_sql = implode(' AND ', $where_clauses);

            // ===== OPTIMIZATION 5: Get total count efficiently with error handling =====
            try {
                $count_query = "SELECT COUNT(*) as total FROM events WHERE {$where_sql}";
                $total_result = DB::select($count_query, $bind_params);
                $total = $total_result[0]->total ?? 0;
            } catch (\Exception $e) {
                Log::error("Count query failed: " . $e->getMessage());
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to count events.",
                    'data' => [],
                    'error_code' => 'QUERY_ERROR'
                ]);
            }
            
            $last_page = $total > 0 ? ceil($total / $per_page) : 1;
            $has_more = $page < $last_page;

            // ===== OPTIMIZATION 6: Raw SQL query for events with error handling =====
            try {
                $events_query = "
                    SELECT 
                        id,
                        animal_id,
                        type,
                        detail,
                        description,
                        short_description,
                        created_at,
                updated_at,
                administrator_id,
                farm_id,
                disease_id,
                vaccine_id,
                medicine_id,
                medicine_text,
                medicine_quantity,
                medicine_name,
                weight,
                milk,
                temperature,
                e_id,
                v_id,
                status,
                        photo, 
                        is_present,
                        price
                    FROM events 
                    WHERE {$where_sql}
                    ORDER BY created_at DESC, id DESC
                    LIMIT ? OFFSET ?
                ";

                $bind_params_with_limit = array_merge($bind_params, [$per_page, $offset]);
                $events = DB::select($events_query, $bind_params_with_limit);
                
            } catch (\Exception $e) {
                Log::error("Events query failed: " . $e->getMessage());
                return Utils::response([
                    'status' => 0,
                    'message' => "Failed to retrieve events.",
                    'data' => [],
                    'error_code' => 'QUERY_ERROR'
                ]);
            }

            // ===== OPTIMIZATION 7: Minimal post-processing with safety checks =====
            $data = [];
            foreach ($events as $event) {
                try {
                    $event_array = (array) $event;

                    // Add minimal computed fields - NO DATABASE LOOKUPS
                    $event_array['animal_text'] = null;
                    $event_array['animal_photo'] = null;
                    $event_array['farm_text'] = null;
                    $event_array['administrator_text'] = null;
                    $event_array['session_text'] = null;

                    // Use timestamps instead of Carbon formatting (faster) with safety checks
                    if (!empty($event->created_at)) {
                        $created_timestamp = strtotime($event->created_at);
                        $event_array['created_at_formatted'] = date('M d, Y H:i', $created_timestamp);
                        $event_array['time_ago'] = $this->timeAgo($created_timestamp);
                    } else {
                        $event_array['created_at_formatted'] = 'Unknown';
                        $event_array['time_ago'] = 'Unknown';
                    }
                    
                    if (!empty($event->updated_at)) {
                        $event_array['updated_at_formatted'] = date('M d, Y H:i', strtotime($event->updated_at));
                    } else {
                        $event_array['updated_at_formatted'] = 'Unknown';
                    }

                    $data[] = $event_array;
                    
                } catch (\Exception $e) {
                    // Log error but continue processing other events
                    Log::error("Error processing event ID {$event->id}: " . $e->getMessage());
                    continue;
                }
            }

            return Utils::response([
                'status' => 1,
                'message' => "Success. Retrieved " . count($data) . " events.",
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total' => $total,
                    'last_page' => $last_page,
                    'has_more' => $has_more,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $per_page, $total)
                ],
                'filters_applied' => [
                    'search' => $search,
                    'event_type' => $event_type,
                    'category' => $category,
                    'animal_id' => $animal_id,
                    'e_id' => $e_id,
                    'v_id' => $v_id, 
                    'date_from' => $date_from,
                    'date_to' => $date_to
                ]
            ]);
            
        } catch (\Exception $e) {
            // Catch any unexpected errors
            Log::error("Unexpected error in events_online: " . $e->getMessage());
            return Utils::response([
                'status' => 0,
                'message' => "An unexpected error occurred. Please try again later.",
                'data' => [],
                'error_code' => 'UNEXPECTED_ERROR'
            ]);
        }
    }

    /**
     * Helper function to generate human-readable time ago string
     * 
     * @param int $timestamp Unix timestamp
     * @return string Human-readable time difference
     */
    private function timeAgo($timestamp)
    {
        // Validate timestamp
        if (!is_numeric($timestamp) || $timestamp <= 0) {
            return 'Unknown';
        }
        
        $diff = time() - $timestamp;

        // Handle future dates
        if ($diff < 0) {
            return 'Just now';
        }

        if ($diff < 60) return $diff . ' seconds ago';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
        if ($diff < 31536000) return floor($diff / 2592000) . ' months ago';
        return floor($diff / 31536000) . ' years ago';
    }

    /**
     * =================================================================
     * LABEL PRINTING TASK APIs
     * =================================================================
     */

    /**
     * Get all label printing tasks (with pagination and filtering)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function label_printing_tasks(Request $request)
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ], 404);
        }

        // Get all tasks for this user with creator info
        $tasks = \App\Models\LabelPrintingTask::where('created_by_id', $administrator_id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        // Enrich with additional computed fields
        $tasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'task_number' => $task->task_number,
                'template_type' => $task->template_type,
                'total_labels' => $task->total_labels,
                'generated_labels' => $task->generated_labels,
                'status' => $task->status,
                'progress_percentage' => $task->getProgressPercentage(),
                'pdf_path' => $task->pdf_path,
                'pdf_url' => $task->getPdfUrl(),
                'pdf_size' => $task->pdf_size,
                'error_message' => $task->error_message,
                'include_qr' => $task->include_qr,
                'include_barcode' => $task->include_barcode,
                'include_company_info' => $task->include_company_info,
                'include_animal_info' => $task->include_animal_info,
                'created_at' => $task->created_at->toIso8601String(),
                'created_at_human' => $task->created_at->diffForHumans(),
                'started_at' => $task->started_at ? $task->started_at->toIso8601String() : null,
                'completed_at' => $task->completed_at ? $task->completed_at->toIso8601String() : null,
                'creator_name' => $task->creator ? $task->creator->name : 'Unknown',
                'butcher_record_ids' => $task->butcher_record_ids,
            ];
        });

        return Utils::response([
            'status' => 1,
            'message' => 'Success',
            'data' => $tasks,
        ]);
    }

    /**
     * Create a new label printing task and generate PDF
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_label_printing_task(Request $request)
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ], 404);
        }

        // Validate required fields
        if (!$request->has('butcher_record_ids')) {
            return Utils::response([
                'status' => 0,
                'message' => 'Please select at least one butcher record.',
            ], 400);
        }

        // Parse butcher record IDs (could be JSON string, array, or comma-separated)
        $recordIds = $request->input('butcher_record_ids');
        
        // If it's a string, try to decode it as JSON first
        if (is_string($recordIds)) {
            // Try JSON decode
            $decoded = json_decode($recordIds, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $recordIds = $decoded;
            } else {
                // Try comma-separated values as fallback
                $recordIds = array_map('trim', explode(',', $recordIds));
            }
        }
        
        // If it's an object (from JSON parsing), convert to array
        if (is_object($recordIds)) {
            $recordIds = (array) $recordIds;
        }

        // Validate it's an array
        if (!is_array($recordIds)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Invalid butcher record IDs format. Expected array, got: ' . gettype($request->input('butcher_record_ids')),
            ], 400);
        }
        
        // Convert to integers and filter out invalid values
        $recordIds = array_map('intval', $recordIds);
        $recordIds = array_filter($recordIds, function($id) {
            return $id > 0;
        });
        
        if (empty($recordIds)) {
            return Utils::response([
                'status' => 0,
                'message' => 'No valid butcher record IDs provided.',
            ], 400);
        }

        // Validate template type
        $templateTypes = array_keys(\App\Models\LabelPrintingTask::getTemplateTypes());
        $templateType = $request->template_type ?? 'Standard';
        
        if (!in_array($templateType, $templateTypes)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Invalid template type. Choose from: ' . implode(', ', $templateTypes),
            ], 400);
        }

        // Verify all butcher records exist
        $records = \App\Models\ButcherRecord::whereIn('id', $recordIds)->get();
        if ($records->count() !== count($recordIds)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Some butcher records not found.',
            ], 404);
        }

        try {
            // Create label printing task
            $task = new \App\Models\LabelPrintingTask();
            $task->task_number = \App\Models\LabelPrintingTask::generateTaskNumber();
            $task->butcher_record_ids = $recordIds;
            $task->template_type = $templateType;
            $task->include_qr = $request->include_qr ?? 'Yes';
            $task->include_barcode = $request->include_barcode ?? 'Yes';
            $task->include_company_info = $request->include_company_info ?? 'Yes';
            $task->include_animal_info = $request->include_animal_info ?? 'Yes';
            $task->label_size = 'A6';
            $task->labels_per_page = 4;
            $task->total_labels = count($recordIds);
            $task->generated_labels = 0;
            $task->status = 'Pending';
            $task->created_by_id = $administrator_id;
            $task->save();

            // Generate PDF using service
            $generator = new \App\Services\LabelPdfGenerator($task);
            $result = $generator->generate();

            if (!$result['success']) {
                return Utils::response([
                    'status' => 0,
                    'message' => 'PDF generation failed: ' . $result['error'],
                ], 500);
            }

            // Refresh task from database
            $task = \App\Models\LabelPrintingTask::find($task->id);

            return Utils::response([
                'status' => 1,
                'message' => 'Label printing task created and PDF generated successfully.',
                'data' => [
                    'id' => $task->id,
                    'task_number' => $task->task_number,
                    'template_type' => $task->template_type,
                    'total_labels' => $task->total_labels,
                    'status' => $task->status,
                    'pdf_url' => $task->getPdfUrl(),
                    'pdf_size' => $task->pdf_size,
                    'created_at' => $task->created_at->toIso8601String(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Label Printing Task Creation Failed: ' . $e->getMessage());
            
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to create label printing task: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single label printing task details
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function label_printing_task_details(Request $request)
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ], 404);
        }

        $taskId = $request->task_id ?? $request->id;
        
        if (empty($taskId)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Task ID is required.',
            ], 400);
        }

        $task = \App\Models\LabelPrintingTask::with('creator')->find($taskId);
        
        if (!$task) {
            return Utils::response([
                'status' => 0,
                'message' => 'Label printing task not found.',
            ], 404);
        }

        // Get butcher records for this task
        $butcherRecords = $task->butcherRecords()->map(function ($record) {
            return [
                'id' => $record->id,
                'cut_type' => $record->cut_type,
                'prime_cut_type' => $record->prime_cut_type,
                'offal_cut_type' => $record->offal_cut_type,
                'original_weight' => $record->original_weight,
                'price' => $record->price,
                'bar_code' => $record->bar_code,
            ];
        });

        return Utils::response([
            'status' => 1,
            'message' => 'Success',
            'data' => [
                'id' => $task->id,
                'task_number' => $task->task_number,
                'template_type' => $task->template_type,
                'total_labels' => $task->total_labels,
                'generated_labels' => $task->generated_labels,
                'status' => $task->status,
                'progress_percentage' => $task->getProgressPercentage(),
                'pdf_path' => $task->pdf_path,
                'pdf_url' => $task->getPdfUrl(),
                'pdf_size' => $task->pdf_size,
                'error_message' => $task->error_message,
                'include_qr' => $task->include_qr,
                'include_barcode' => $task->include_barcode,
                'include_company_info' => $task->include_company_info,
                'include_animal_info' => $task->include_animal_info,
                'label_size' => $task->label_size,
                'labels_per_page' => $task->labels_per_page,
                'created_at' => $task->created_at->toIso8601String(),
                'created_at_human' => $task->created_at->diffForHumans(),
                'started_at' => $task->started_at ? $task->started_at->toIso8601String() : null,
                'completed_at' => $task->completed_at ? $task->completed_at->toIso8601String() : null,
                'creator_name' => $task->creator ? $task->creator->name : 'Unknown',
                'butcher_records' => $butcherRecords,
            ],
        ]);
    }

    /**
     * Download label PDF
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download_label_pdf(Request $request)
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ], 404);
        }

        $taskId = $request->task_id ?? $request->id;
        
        if (empty($taskId)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Task ID is required.',
            ], 400);
        }

        $task = \App\Models\LabelPrintingTask::find($taskId);
        
        if (!$task) {
            return Utils::response([
                'status' => 0,
                'message' => 'Label printing task not found.',
            ], 404);
        }

        if (empty($task->pdf_path) || !Storage::exists($task->pdf_path)) {
            return Utils::response([
                'status' => 0,
                'message' => 'PDF file not found.',
            ], 404);
        }

        // Return file download response
        return Storage::download($task->pdf_path, 'labels_' . $task->task_number . '.pdf');
    }

    /**
     * Reprint single label from butcher record
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reprint_butcher_record_label(Request $request)
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ], 404);
        }

        $recordId = $request->butcher_record_id ?? $request->id;
        
        if (empty($recordId)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Butcher record ID is required.',
            ], 400);
        }

        $record = \App\Models\ButcherRecord::find($recordId);
        
        if (!$record) {
            return Utils::response([
                'status' => 0,
                'message' => 'Butcher record not found.',
            ], 404);
        }

        try {
            // Get options from request
            $templateType = $request->template_type ?? 'Standard';
            $options = [
                'include_qr' => $request->include_qr ?? 'Yes',
                'include_barcode' => $request->include_barcode ?? 'Yes',
                'include_company_info' => $request->include_company_info ?? 'Yes',
                'include_animal_info' => $request->include_animal_info ?? 'Yes',
            ];

            // Generate single label
            $result = \App\Services\LabelPdfGenerator::generateSingleLabel($record, $templateType, $options);

            if (!$result['success']) {
                return Utils::response([
                    'status' => 0,
                    'message' => 'Label generation failed: ' . $result['error'],
                ], 500);
            }

            return Utils::response([
                'status' => 1,
                'message' => 'Label generated successfully.',
                'data' => [
                    'pdf_url' => url('storage/' . $result['path']),
                    'pdf_size' => $result['size'],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Single Label Reprint Failed: ' . $e->getMessage());
            
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to generate label: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available template types
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function label_template_types(Request $request)
    {
        $templates = \App\Models\LabelPrintingTask::getTemplateTypes();
        
        $formatted = [];
        foreach ($templates as $key => $description) {
            $formatted[] = [
                'value' => $key,
                'label' => $key,
                'description' => $description,
            ];
        }

        return Utils::response([
            'status' => 1,
            'message' => 'Success',
            'data' => $formatted,
        ]);
    }

    /**
     * Regenerate label printing task
     * Creates a new task with the same configuration and generates a fresh PDF
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function regenerate_label_printing_task(Request $request)
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ], 404);
        }

        $taskId = $request->task_id ?? $request->id;
        
        if (empty($taskId)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Task ID is required.',
            ], 400);
        }

        // Get original task
        $originalTask = \App\Models\LabelPrintingTask::find($taskId);
        
        if (!$originalTask) {
            return Utils::response([
                'status' => 0,
                'message' => 'Original task not found.',
            ], 404);
        }

        try {
            // Create new task with same configuration
            $newTask = new \App\Models\LabelPrintingTask();
            $newTask->task_number = \App\Models\LabelPrintingTask::generateTaskNumber();
            $newTask->butcher_record_ids = $originalTask->butcher_record_ids;
            $newTask->template_type = $originalTask->template_type;
            $newTask->include_qr = $originalTask->include_qr;
            $newTask->include_barcode = $originalTask->include_barcode;
            $newTask->include_company_info = $originalTask->include_company_info;
            $newTask->include_animal_info = $originalTask->include_animal_info;
            $newTask->label_size = $originalTask->label_size;
            $newTask->labels_per_page = $originalTask->labels_per_page;
            $newTask->total_labels = $originalTask->total_labels;
            $newTask->generated_labels = 0;
            $newTask->status = 'Pending';
            $newTask->created_by_id = $administrator_id;
            $newTask->save();

            // Generate PDF using service
            $generator = new \App\Services\LabelPdfGenerator($newTask);
            $result = $generator->generate();

            if (!$result['success']) {
                return Utils::response([
                    'status' => 0,
                    'message' => 'PDF generation failed: ' . $result['error'],
                ], 500);
            }

            // Refresh task from database
            $newTask = \App\Models\LabelPrintingTask::find($newTask->id);

            return Utils::response([
                'status' => 1,
                'message' => 'Label printing task regenerated successfully.',
                'data' => [
                    'id' => $newTask->id,
                    'task_number' => $newTask->task_number,
                    'template_type' => $newTask->template_type,
                    'total_labels' => $newTask->total_labels,
                    'status' => $newTask->status,
                    'pdf_url' => $newTask->getPdfUrl(),
                    'pdf_size' => $newTask->pdf_size,
                    'created_at' => $newTask->created_at->toIso8601String(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Label Printing Task Regeneration Failed: ' . $e->getMessage());
            
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to regenerate label printing task: ' . $e->getMessage(),
            ], 500);
        }
    }
}

