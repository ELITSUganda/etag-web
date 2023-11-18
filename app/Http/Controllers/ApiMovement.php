<?php
//rominah P
namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\BatchSession;
use App\Models\CheckPoint;
use App\Models\CheckPointRecord;
use App\Models\CheckpointSession;
use App\Models\DrugCategory;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\FinanceCategory;
use App\Models\Location;
use App\Models\Movement;
use App\Models\MovementHasMovementAnimal;
use App\Models\MovementRoute;
use App\Models\Product;
use App\Models\SlaughterHouse;
use App\Models\Trip;
use App\Models\TripRecord;
use App\Models\User;
use App\Models\Utils;
use App\Models\VetHasService;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use COM;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiMovement extends Controller
{

    use ApiResponser;

    public function fiance_categories(Request $r)
    {
        $user_id = ((int)(Utils::get_user_id($r)));
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }
        $cats = FinanceCategory::where('administrator_id', $user_id)->get();
        return Utils::response([
            'status' => 1,
            'data' => $cats,
            'message' => 'Success'
        ]);
    }
    public function drug_product_create(Request $r)
    {
        $user_id = ((int)(Utils::get_user_id($r)));
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }

        //validate all
        if (
            $r->name == null ||
            strlen($r->name) < 2
        ) {
            return $this->error('Name is missing.');
        }

        //validate all
        if (
            $r->drug_category_id == null ||
            strlen($r->drug_category_id) < 2
        ) {
            return $this->error('Drug category is missing.');
        }

        //validate all
        if (
            $r->source_text == null ||
            strlen($r->source_name) < 2
        ) {
            return $this->error('Source is missing.');
        }

        if ($r->drug_state != 'Solid') {
            if ($r->drug_state != 'Liquid') {
                return $this->error('Invalid drug state.');
            }
        }

        if (
            $r->drug_packaging_type == null ||
            strlen($r->drug_packaging_type) < 2
        ) {
            return $this->error('Drug packaging type is missing.');
        }
        if (
            $r->price == null ||
            strlen($r->price) < 1
        ) {
            return $this->error('Price is missing.');
        }



        $p = new Product();

        $msg = "";
        $p->name = $r->name;
        $p->drug_category_id = $r->drug_category_id;
        $p->administrator_id = $u->id;
        $p->source_id = $r->source_id;
        $p->source_name = $r->source_text;
        $p->manufacturer = $r->manufacturer;
        $p->batch_number = $r->batch_number;
        $p->expiry_date = $r->expiry_date;
        $p->original_quantity = $r->original_quantity;
        $p->current_quantity = $r->current_quantity;
        $p->drug_state = $r->drug_state;
        $p->drug_packaging_unit_quantity = $r->drug_packaging_unit_quantity;
        $p->drug_packaging_type = $r->drug_packaging_type;
        $p->drug_packaging_type_pieces = $r->drug_packaging_type_pieces;
        $p->original_quantity_temp = $r->original_quantity_temp;
        $p->source_type = $r->source_type;
        $p->source_name = $r->source_name;
        $p->source_contact = $r->source_contact;
        $p->ingredients = $r->ingredients;
        $p->other_photos = $r->other_photos;
        $p->details = $r->details;
        $p->price = $r->price;

        $images = [];
        if (!empty($_FILES)) {
            $images = Utils::upload_images_2($_FILES, false);
        }
        if (!empty($images)) {
            $p->image = 'storage/images/' . $images[0];
        }


        $code = 1;
        try {
            $p->save();
            $msg = "Submitted successfully.";
            return $this->success(null, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
    }


    public function become_vendor(Request $request)
    {
        $user_id = ((int)(Utils::get_user_id($request)));
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }

        if (
            $request->first_name == null ||
            strlen($request->first_name) < 2
        ) {
            return $this->error('First name is missing.');
        }

        //validate all
        if (
            $request->last_name == null ||
            strlen($request->last_name) < 2
        ) {
            return $this->error('Last name is missing.');
        }

        //validate all
        if (
            $request->business_name == null ||
            strlen($request->business_name) < 2
        ) {
            return $this->error('Business name is missing.');
        }

        if (
            $request->business_license_number == null ||
            strlen($request->business_license_number) < 2
        ) {
            return $this->error('Business license number is missing.');
        }

        if (
            $request->business_license_issue_authority == null ||
            strlen($request->business_license_issue_authority) < 2
        ) {
            return $this->error('Business license issue authority is missing.');
        }

        if (
            $request->business_license_issue_date == null ||
            strlen($request->business_license_issue_date) < 2
        ) {
            return $this->error('Business license issue date is missing.');
        }

        if (
            $request->business_license_validity == null ||
            strlen($request->business_license_validity) < 2
        ) {
            return $this->error('Business license validity is missing.');
        }

        if (
            $request->business_address == null ||
            strlen($request->business_address) < 2
        ) {
            return $this->error('Business address is missing.');
        }

        if (
            $request->business_phone_number == null ||
            strlen($request->business_phone_number) < 2
        ) {
            return $this->error('Business phone number is missing.');
        }

        if (
            $request->business_whatsapp == null ||
            strlen($request->business_whatsapp) < 2
        ) {
            return $this->error('Business whatsapp is missing.');
        }

        if (
            $request->business_email == null ||
            strlen($request->business_email) < 2
        ) {
            return $this->error('Business email is missing.');
        }

        $msg = "";
        $u->first_name = $request->first_name;
        $u->last_name = $request->last_name;
        $u->nin = $request->campus_id;
        $u->business_name = $request->business_name;
        $u->business_license_number = $request->business_license_number;
        $u->business_license_issue_authority = $request->business_license_issue_authority;
        $u->business_license_issue_date = $request->business_license_issue_date;
        $u->business_license_validity = $request->business_license_validity;
        $u->business_address = $request->business_address;
        $u->business_phone_number = $request->business_phone_number;
        $u->business_whatsapp = $request->business_whatsapp;
        $u->business_email = $request->business_email;
        $u->business_cover_photo = $request->business_cover_photo;
        $u->business_cover_details = $request->business_cover_details;
        $u->request_status = "Pending";
        $vet_services_ids = [];
        if (
            ($request->vet_services_ids != null) &&
            (strlen($request->vet_services_ids) > 2)
        ) {
            try {
                $vet_services_ids = json_decode($request->vet_services_ids);
            } catch (\Throwable $th) {
                $vet_services_ids = [];
            }
        }

        $images = [];
        if (!empty($_FILES)) {
            $images = Utils::upload_images_2($_FILES, false);
        }
        if (!empty($images)) {
            $u->business_logo = 'storage/images/' . $images[0];
        }

        $code = 1;
        try {
            $u->save();
            foreach ($vet_services_ids as $key => $val) {
                $hasSservice = VetHasService::where([
                    'administrator_id' => $u->id,
                    'vet_service_category_id' => $val,
                ])->first();
                if ($hasSservice != null) {
                    continue;
                }
                $hasSservice = new VetHasService();
                $hasSservice->administrator_id = $u->id;
                $hasSservice->vet_id = $u->id;
                $hasSservice->vet_service_category_id = $val;
                $hasSservice->save();
            }
            $msg = "Submitted successfully.";
            $sms_to_admin = "New vendor registration request from {$u->first_name} {$u->last_name} - {$u->phone_number}. Login to the system to review.";
            $sms_to_vendor = "Your vendor registration request has been submitted successfully. We will get back to you soon.";
            Utils::send_message('+256783204665', $sms_to_admin);
            Utils::send_message($u->business_phone_number, $sms_to_vendor);

            return $this->success(null, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
    }


    public function users_me(Request $request)
    {
        $user_id = ((int)(Utils::get_user_id($request)));
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }

        return Utils::response([
            'status' => 1,
            'data' => [$u],
            'message' => 'Success'
        ]);
    }




    public function roll_call_sessions()
    {
        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found',
                'data' => null
            ]);
        }
        $batch_sessions = BatchSession::where('administrator_id', $administrator_id)->get();
    }
    public function drug_categories_create(Request $r)
    {
        if ($r->name == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'Name is required',
                'data' => null
            ]);
        }
        //unit
        if ($r->unit == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'Unit is required',
                'data' => null
            ]);
        }
        //details
        if ($r->details == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'Details is required',
                'data' => null
            ]);
        }
        $same_cat_name = DrugCategory::where('name', $r->name)->first();
        if ($same_cat_name != null) {
            return Utils::response([
                'status' => 0,
                'message' => 'Drug category with same name already exists',
                'data' => null
            ]);
        }
        $cat = new DrugCategory();
        $cat->name = $r->name;
        $cat->unit = $r->unit;
        $cat->details = $r->details;

        try {
            $cat->save();
            return Utils::response([
                'status' => 1,
                'message' => 'Drug category created successfully.',
                'data' => $cat
            ]);
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to create drug category because ' . $th->getMessage(),
                'data' => null
            ]);
        }
    }
    public function drug_categories(Request $r)
    {
        $user_id = ((int)(Utils::get_user_id($r)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }

        $cats = [];
        foreach (DrugCategory::all() as $key => $cat) {
            $cat->avg_purchase = DrugStockBatch::where([
                'administrator_id' => $user_id,
                'drug_category_id' => $cat->id,
            ])->avg('original_quantity');
            $cat->is_managed = 'No';
            $cat->current_quantity = 0;
            $cat->current_quantity_percentage = 0;
            if ($cat->category != null) {
                $cat->category_name = $cat->category->name;
                $cat->category_unit = $cat->category->unit;
                unset($cat->category);
            }
            if ($cat->avg_purchase < 1) {
                $cats[] = $cat;
                continue;
            }
            $cat->is_managed = 'Yes';
            $cat->current_quantity = DrugStockBatch::where([
                'administrator_id' => $user_id,
                'drug_category_id' => $cat->id,
            ])->sum('current_quantity');
            $cat->current_quantity_percentage = ($cat->current_quantity / $cat->avg_purchase) * 100;
            $cats[] = $cat;
        }

        return Utils::response([
            'status' => 1,
            'data' => $cats,
            'message' => 'Success'
        ]);
    }

    public function system_users(Request $request)
    {
        $data = Administrator::all();
        return Utils::response([
            'status' => 1,
            'data' => $data,
            'message' => 'Success'
        ]);
    }

    public function routes(Request $request)
    {
        $data = [];
        foreach (MovementRoute::all() as $key => $val) {
            $val->check_points = json_encode($val->checkpoints);
            $val->checkpoints = null;
            unset($val->checkpoints);
            $data[] = $val;
        }
        return Utils::response([
            'status' => 1,
            'data' => $data,
            'message' => 'Success'
        ]);
    }

    public function user_roles(Request $request)
    {

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }

        return Utils::response([
            'status' => 1,
            'data' => $user->roles,
            'message' => 'Success'
        ]);
    }
    public function index(Request $request)
    {

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }

        $items = [];
        $filtered_items = [];
        $role = Utils::get_role($user);
        $permits = [];
        $done_ids = [];

        foreach ($user->user_roles as $_role) {
            if ($_role->role_id == 3) {
                //farmer---
                $_permits = Movement::where(['administrator_id' => $user_id])->orderBy('id', 'desc')->get();
                foreach ($_permits as $_permit) {
                    if (in_array($_permit->id, $done_ids)) {
                        continue;
                    }
                    $done_ids[] = $_permit->id;
                    $permits[] = $_permit;
                }
            }
            if ($_role->role_id == 9) {
                //checkpoint officer---
                $_permits = Movement::where(['status' => '1'])->orderBy('id', 'desc')->get();
                foreach ($_permits as $_permit) {
                    if (in_array($_permit->id, $done_ids)) {
                        continue;
                    }
                    $done_ids[] = $_permit->id;
                    $permits[] = $_permit;
                }
            }

            if (
                $user->isRole('dvo')
                || $user->isRole('sclo')
                || $user->isRole('scvo')
                || $user->isRole('slaughter')
            ) {
                //dvo 
                //$_permits = Movement::where(['district_from' => $_role->type_id])->orderBy('id','desc')->get();
                $_permits = Movement::where([])->orderBy('id', 'desc')->get();
                foreach ($_permits as $_permit) {
                    if (in_array($_permit->id, $done_ids)) {
                        continue;
                    }
                    $done_ids[] = $_permit->id;
                    $permits[] = $_permit;
                }
            }

            if (
                $user->isRole('transporter')
            ) {
                $_permits = Movement::where(['transporter_id' => $user->id])->orderBy('id', 'desc')->get();
                foreach ($_permits as $_permit) {
                    if (in_array($_permit->id, $done_ids)) {
                        continue;
                    }
                    $done_ids[] = $_permit->id;
                    $permits[] = $_permit;
                }
            }
        }

        $new_permits = [];
        foreach ($permits as $key => $p) {
            $p->movement_ids = "";
            $p->v_ids = "";
            $p->e_ids = "";
            $movement_ids = [];
            $v_ids = [];
            $e_ids = [];
            foreach ($p->animals as $an) {
                if ($an == null) {
                    continue;
                }
                $movement_ids[] = $an->id;
                $v_ids[] = $an->v_id;
                $e_ids[] = $an->e_id;
            }
            $p->movement_ids = json_encode($movement_ids);
            $p->v_ids = json_encode($v_ids);
            $p->e_ids = json_encode($e_ids);

            $new_permits[] = $p;
        }
        return Utils::response([
            'status' => 1,
            'data' => $new_permits,
            'message' => 'Success'
        ]);

        return Utils::response([
            'status' => 1,
            'data' => $permits,
            'message' => 'Success'
        ]);


        if ($user->isRole('dvo')) {
            return 'Yes';
        } else {
            return 'No';
        }
        return $user->isRole('adinub');

        if (
            $role == 'dvo' ||
            $role == 'administrator' ||
            $role == 'admin' ||
            $role == 'sclo'
        ) {
            $items = Movement::paginate(1000)->withQueryString()->items();
        } else if ($role == 'slaughter') {
            $items = Movement::where('destination_slaughter_house', '=', $user_id)->where('status', '=', 'Approved')->orderBy('id', 'desc')->get();
        } else if ($role == 'scvo') {
            //if sclo
            $items = Movement::where('sub_county_from', '=', $user->scvo)->where('status', '=', 'Approved')->orderBy('id', 'desc')->get();
        } else {
            $items = Movement::where(['administrator_id' => $user_id])->orderBy('id', 'desc')->get();
        }


        foreach ($items as $key => $value) {
            $value->created_at =  Carbon::parse($value->created_at)->toFormattedDateString();
            $value->updated_at =  Carbon::parse($value->created_at)->toFormattedDateString();
            $filtered_items[] = $value;
        }

        return $filtered_items;
    }

    public function show($id)
    {
        $item = Movement::find($id);
        if ($item == null) {
            $item = Movement::where('permit_Number', $id)->first();
        }

        if ($item == null) {
            die("{}");
        }
        $animals = $item->movement_has_movement_animals;

        $animal_list = [];

        foreach ($animals as $key => $value) {
            $an = Animal::find($value->movement_animal_id);
            if ($an == null)
                continue;

            $value->v_id = $an->v_id;
            $value->e_id = $an->e_id;
            $animal_list[] = $an;
        }
        $item->animals_count = count($animal_list);
        $item->animals_list = $animal_list;
        unset($item->movement_has_movement_animals);

        return $item;
    }

    public function create_check_record(Request $request)
    {
        if (
            $request->permit == null ||
            $request->administrator_id == null ||
            $request->animal_id == null ||
            $request->real_animal_id == null ||
            $request->latitude == null ||
            $request->longitude == null ||
            $request->found == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "All required permits must be submited.",
            ]);
        }


        $checkPoint = CheckPoint::where('administrator_id', $request->administrator_id)->first();
        if ($checkPoint == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Check point not found.",
            ]);
        }

        $user = Administrator::find($request->administrator_id);

        $record = CheckPointRecord::where([
            'movement_id' => $request->permit,
            'administrator_id' => $request->administrator_id,
        ])->first();
        if ($record == null) {
            $record = new CheckPointRecord();
        }

        $record->check_point_id = $checkPoint->id;
        $record->administrator_id = $request->administrator_id;
        $record->movement_id = $request->permit;
        $record->latitude = $request->latitude;
        $record->longitude = $request->longitude;
        $record->on_permit = $request->on_permit;

        $checked = [];
        $success = [];
        $failed = [];


        if ($record->checked != null) {
            if (strlen($record->checked) > 2) {
                $checked = json_decode($record->checked);
            }
        }

        if ($record->success != null) {
            if (strlen($record->success) > 2) {
                $success = json_decode($record->success);
            }
        }

        if ($record->failed != null) {
            if (strlen($record->failed) > 2) {
                $failed = json_decode($record->failed);
            }
        }

        if (!in_array($request->animal_id, $checked)) {
            $checked[] = $request->animal_id;
        }

        if ($request->found == 'yes') {
            if (!in_array($request->animal_id, $success)) {
                $success[] = $request->animal_id;

                $_an = Animal::where('v_id', $request->real_animal_id)->first();
                if ($_an != null) {
                    $e = new Event();
                    $e->administrator_id = $request->administrator_id;
                    $e->approved_by = $request->administrator_id;
                    $e->district_id = $user->district_id;
                    $e->sub_county_id  = $user->sub_county_id;
                    $e->animal_id = $request->real_animal_id;
                    $e->type = "Check point";
                    $e->detail = "Checked and found this animal on movement permit #{$record->movement_id}";
                    $e->save();
                }
            }
        } else {
            if (!in_array($request->animal_id, $failed)) {
                $failed[] = $request->animal_id;
                $success[] = $request->animal_id;
                $_an = Animal::where('v_id', $request->real_animal_id)->first();
                if ($_an != null) {
                    $e = new Event();
                    $e->administrator_id = $request->administrator_id;
                    $e->approved_by = $request->administrator_id;
                    $e->district_id = $user->district_id;
                    $e->sub_county_id  = $user->sub_county_id;
                    $e->animal_id = $request->real_animal_id;
                    $e->type = "Check point";
                    $e->detail = "Checked this animal but not found on movement permit #{$record->movement_id}";
                    $e->save();
                }
            }
        }



        $record->checked = json_encode($checked);
        $record->failed = json_encode($failed);
        $record->success = json_encode($success);
        $record->save();

        return Utils::response([
            'status' => 1,
            'message' => "Recorded successfully.",
            'data' => $record
        ]);
    }

    public function get_check_record(Request $request)
    {
        if (
            $request->permit == null
        ) {
            return [];
        }


        $records = CheckPointRecord::where([
            'movement_id' => $request->permit,
        ])->orderBy('id', 'desc')->get();

        $items = [];
        foreach ($records as $key => $value) {

            $check = CheckPoint::find($value->check_point_id);
            if ($check  == null) {
                continue;
            }

            $value->check_point = $check->sub_county->district->name . ", " . $check->sub_county->name;
            $value->sub_county = $check->sub_county->name;
            $value->time = Carbon::parse($value->created_at)->toFormattedDateString();

            $items[] = $value;
        }

        return $items;
    }

    public function transfer_animal(Request $r, $id)
    {
        $an = Animal::find($id);
        if ($an == null) {
            return Utils::response(['status' => 0, 'message' => "Animal not found.",]);
        }
        $sender = Administrator::find($an->administrator_id);
        if ($sender == null) {
            return Utils::response(['status' => 0, 'message' => "Sender not found.",]);
        }
        $receiver = Administrator::find($r->receiver);
        if ($receiver == null) {
            return Utils::response(['status' => 0, 'message' => "Receiver not found.",]);
        }

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
        /* 	
		
	animal_type	
disease_id	
vaccine_id	
medicine_id	
is_batch_import	
time_stamp	
import_file	
	
temperature	
e_id	
v_id	
status	
disease_text	
	
medicine_text	
medicine_quantity	
medicine_name	
medicine_batch_number	
medicine_supplier	
medicine_manufacturer	
medicine_expiry_date	
medicine_image	
vaccination	
weight	
milk	
photo	
session_id	
is_present	
	


*/
        Utils::sendNotification(
            "Your animal {$an->v_id} ownership has been transfered to {$receiver->name} - {$receiver->phone_number}.",
            $sender->id,
            $headings = 'Animal ownership transfered'
        );

        Utils::sendNotification(
            "Animal {$an->v_id} has been transfered to you by {$sender->name} - {$sender->phone_number}.",
            $receiver->id,
            $headings = 'Animal ownership received'
        );

        return Utils::response(['status' => 1, 'message' => "Animal ownership transfered successfully.",]);
    }

    public function checkpoint_session(Request $request, $id)
    {

        $mv = Movement::find($id);
        if ($mv == null) {
            return Utils::response(['status' => 0, 'message' => "Movement permit not found.",]);
        }

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);

        if ($user == null) {
            return Utils::response(['status' => 0, 'message' => "User not found.",]);
        }



        $found_animals = json_decode($request->found_animals);

        $real_found = [];
        $real_not_found = [];
        foreach ($mv->animals as $animal) {

            $found = false;
            foreach ($found_animals as $found_id) {
                $_found_id = ((int)($found_id));
                if ($animal->id == $_found_id) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $real_found[] = $animal;
            } else {
                $real_not_found[] = $animal;
            }
        }


        $session  =  new CheckpointSession();
        $session->checked_by = $user->id;
        $session->movement_id = $mv->id;
        $session->check_point_id = 1;
        $session->animals_expected = count($real_not_found) + count($real_found);
        $session->animals_checked = count($real_not_found) + count($real_found);
        $session->animals_found = count($real_found);
        $session->animals_missed = count($real_not_found);
        $session->details = $request->details;
        foreach ($user->user_roles as $role) {
            if ($role->role_type == 'check-point-officer') {
                if ($role->type_id != null) {
                    $session->check_point_id = $role->type_id;
                    break;
                }
            }
        }

        $session->save();

        return Utils::response([
            'status' => 1,
            'message' => "Session saved successfully.",
            'data' => $session
        ]);
    }

    public function checkpoint_verification(Request $r)
    {

        $movement = Movement::find($r->movement_id);
        if ($movement == null) {
            return Utils::response(['status' => 0, 'message' => "Movement permit not found.",]);
        }
        $checkPoint = CheckPoint::find($r->checkpoint_id);
        if ($checkPoint == null) {
            return Utils::response(['status' => 0, 'message' => "Checkpoint not found.",]);
        }
        $checkPoint = CheckPoint::find($r->checkpoint_id);
        if ($checkPoint == null) {
            return Utils::response(['status' => 0, 'message' => "Checkpoint not found.",]);
        }

        $user_id = ((int)(Utils::get_user_id($r)));
        $user = Administrator::find($user_id);

        if ($user == null) {
            return Utils::response(['status' => 0, 'message' => "User not found.",]);
        }


        $session  = CheckpointSession::where([
            'movement_id' => $movement->id,
            'check_point_id' => $checkPoint->id,
        ])->first();
        if ($session == null) {
            $session = new CheckpointSession();
            $session->movement_id = $movement->id;
            $session->check_point_id = $checkPoint->id;
            $session->checked_by = $user->id;
            $session->session_status = 'Pending';
            if ($movement->animals != null) {
                $session->animals_expected = count($movement->animals);
            }
            try {
                $session->save();
            } catch (\Throwable $th) {
                return Utils::response(['status' => 0, 'message' => "Session failed to save because " . $th,]);
            }
        }

        $done = [];
        $valid = [];
        try {
            $done = json_decode($r->done);
        } catch (\Throwable $th) {
            $done = [];
        }

        if ($done == null) {
            return Utils::response(['status' => 0, 'message' => "No animal found.",]);
        }

        try {
            $valid = json_decode($r->valid);
        } catch (\Throwable $th) {
            $valid = [];
        }

        $_done = [];

        if (is_array($done)) {
            foreach ($done as $key => $id) {
                $an = Animal::where('v_id', $id)
                    ->orWhere('e_id', $id)
                    ->first();
                if ($an == null) {
                    continue;
                }
                $e = new Event();
                $e->administrator_id = $an->administrator_id;
                $e->approved_by = $user->id;
                $e->district_id = $user->district_id;
                $e->sub_county_id  = $user->sub_county_id;
                $e->animal_id = $an->id;
                $e->type = "Check point";
                $e->detail = "Animal seen  at checkpoint {$checkPoint->name} on movement permit #{$movement->permit_Number}.";
                $e->description = "Animal seen  at checkpoint {$checkPoint->name}.";
                $e->save();
                $_done[] = ((int)($an->id));
            }
        }

        $_found = [];
        $_missed = [];
        foreach ($movement->animals as $an) {
            if (in_array($an->id, $_done)) {
                $_found[] = $an->id;
            } else {
                $_missed[] = $an->id;
            }
        }

        $session->session_status = 'Conducted';
        $session->animals_checked = count($_done);
        $session->animals_found = count($_found);
        $session->animals_missed = count($_missed);
        $session->details = "Movement permit checked at {$checkPoint->name} checkpoint, $session->animals_found animals found, $session->animals_missed missed.";
        $session->save();

        Utils::sendNotification(
            $session->details,
            $movement->administrator_id,
            $headings = 'Checkpoint session conducted'
        );
        Utils::sendNotification(
            $session->details,
            $user->id,
            $headings = 'Checkpoint session conducted'
        );



        return Utils::response([
            'status' => 1,
            'message' => "Submitted successfully.",
            'data' => $movement->animals
        ]);
    }
    public function slaughter_session(Request $r)
    {

        $movement = Movement::find($r->movement_id);
        if ($movement == null) {
            return Utils::response(['status' => 0, 'message' => "Movement permit not found.",]);
        }

        $user_id = ((int)(Utils::get_user_id($r)));
        $user = Administrator::find($user_id);

        if ($user == null) {
            return Utils::response(['status' => 0, 'message' => "User not found.",]);
        }


        $done = [];
        $valid = [];
        try {
            $done = json_decode($r->done);
        } catch (\Throwable $th) {
            $done = [];
        }

        if ($done == null) {
            return Utils::response(['status' => 0, 'message' => "No animal found.",]);
        }

        try {
            $valid = json_decode($r->valid);
        } catch (\Throwable $th) {
            $valid = [];
        }

        $_done = [];

        if (is_array($done)) {
            foreach ($done as $key => $id) {
                $an = Animal::where('v_id', $id)
                    ->orWhere('e_id', $id)
                    ->first();
                if ($an == null) {
                    continue;
                }
                $e = new Event();
                $e->administrator_id = $an->administrator_id;
                $e->approved_by = $user->id;
                $e->district_id = $user->district_id;
                $e->sub_county_id  = $user->sub_county_id;
                $e->animal_id = $an->id;
                $e->type = "Slaughter";
                $e->detail = "Animal on movement permit #{$movement->permit_Number} has been Slaughterd.";
                $e->description = "Animal Slaughterd.";
                $e->save();
                $_done[] = ((int)($an->id));
            }
        }

        $_found = [];
        $_missed = [];
        foreach ($movement->animals as $an) {
            if (in_array($an->id, $_done)) {
                $_found[] = $an->id;
            } else {
                $_missed[] = $an->id;
            }
        }

        $msg = count($_found) . " Animals slaughterd record on Movement Permit {$movement->permit_Number}.";

        Utils::sendNotification(
            $msg,
            $movement->administrator_id,
            $headings = 'Slaughter House Records'
        );
        Utils::sendNotification(
            $msg,
            $user->id,
            $headings = 'Slaughter House Records'
        );


        return Utils::response([
            'status' => 1,
            'message' => "Slaughter records submitted successfully.",
            'data' => null
        ]);
    }

    public function review(Request $request, $id)
    {
        $mv = Movement::find($id);
        if ($mv == null) {
            return Utils::response(['status' => 0, 'message' => "Application was not found.",]);
        }

        $mv->status = $request->status;
        $mv->reason = $request->reason;
        try {
            $mv->valid_from_Date = Carbon::parse($request->valid_from_Date)->toDateString();
            $mv->valid_to_Date = Carbon::parse($mv->valid_from_Date)->addDays(5)->toDateString();
        } catch (\Throwable $th) {
            $mv->valid_from_Date = Carbon::now()->toDateString();
            $mv->valid_to_Date = Carbon::parse($mv->valid_from_Date)->addDays(5)->toDateString();
        }
        $mv->save();

        if ($mv->status == 'Approved') {
            try {
                $checkPoints = json_decode($request->check_points_to_pass_list_ids);
                if (is_array($checkPoints)) {
                    foreach ($checkPoints as $key => $checkPointId) {
                        $checkPoint = CheckPoint::find($checkPointId);
                        if ($checkPoint == null) {
                            $mv->status .=  "$checkPointId not found";
                            continue;
                        }
                        $s = new CheckpointSession();
                        $s->session_status = 'Pending';
                        $s->checked_by = $checkPoint->administrator_id;
                        $s->check_point_id = $checkPoint->id;
                        $s->movement_id = $mv->id;
                        $s->animals_expected = 0;
                        if ($mv->animals != null) {
                            $s->animals_expected = (count($mv->animals));
                        }
                        $s->animals_checked = 0;
                        $s->animals_found = 0;
                        $s->animals_missed = 0;
                        $s->details = '';
                        $s->save();
                    }
                } else {
                }
            } catch (\Throwable $th) {
            }
        }

        return Utils::response([
            'status' => 1,
            'message' => "Movement permit $mv->status successfully.",
            'data' => null
        ]);
    }

    public function create_trip_record(Request $request)
    {

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'User account not found.'
            ]);
        }
        $trip = Trip::find($request->trip_id);
        if ($trip == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Trip not found.'
            ]);
        }

        $trip->current_latitude = $request->latitude;
        $trip->current_longitude = $request->longitude;
        $trip->current_address = $request->address;
        $trip->save();

        $record = new TripRecord();
        $record->trip_id = $trip->id;
        $record->transporter_id = $user->id;
        $record->latitude = $request->latitude;
        $record->longitude = $request->longitude;
        $record->address = $request->address;
        $record->save();

        return Utils::response([
            'status' => 1,
            'data' => null,
            'message' => 'Success'
        ]);
    }

    public function trip_end(Request $request)
    {

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'User account not found.'
            ]);
        }
        $trip = Trip::find($request->trip_id);
        if ($trip == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Trip not found.'
            ]);
        }

        $trip->has_trip_ended = 'Yes';
        $trip->save();
        try {

            $movements = Movement::where(['trip_id' => $trip->id])->get();
            foreach ($movements as $key => $mv) {
                $mv->status = 'Arrived';
                $mv->save();
            }
        } catch (\Throwable $th) {
        }


        return Utils::response([
            'status' => 1,
            'data' => null,
            'message' => 'Trip ended successfully.'
        ]);
    }
    public function create(Request $request)
    {

        $sub_county_from = Location::find($request->sub_county_from);
        if ($sub_county_from == null) {
            return Utils::response(['status' => 0, 'message' => "Subcount from was not found.",]);
        }


        $has_movements = false;
        $movement_ids = [];
        if ($request->movement_ids != null) {
            if (strlen($request->movement_ids) > 2) {
                $movement_ids = json_decode($request->movement_ids);
                if ($movement_ids == null || empty($movement_ids)) {
                } else {
                    $has_movements = true;
                }
            }
        }

        if (!$has_movements) {
            return Utils::response([
                'status' => 0,
                'message' => "There must be at least one animal in your movement permit.",
            ]);
        }


        $requirements = [
            'transporter_nin',
            'paid_method',
            'administrator_id',
            'transporter_name',
            'transportation_route',
            'vehicle',
            'destination',
        ];
        $valids = [
            'id',
            'administrator_id',
            'vehicle',
            'reason',
            'status',
            'trader_nin',
            'trader_name',
            'trader_phone',
            'transporter_name',
            'transporter_nin',
            'transporter_Phone',
            'district_from',
            'sub_county_from',
            'village_from',
            'district_to',
            'sub_county_to',
            'village_to',
            'transportation_route',
            'permit_Number',
            'valid_from_Date',
            'valid_to_Date',
            'status_comment',
            'destination',
            'destination_slaughter_house',
            'details',
            'destination_farm',
            'is_paid',
            'paid_id',
            'paid_method',
        ];
        $avaiable = [];
        foreach ($_POST as $key => $value) {
            $avaiable[] = $key;
        }

        foreach ($requirements as $key => $value) {
            if (isset($request->$value)) {
                continue;
            }
            return Utils::response([
                'status' => 0,
                'message' => "You must provide {$value}.",
                $request
            ]);
        }

        $movement = new Movement();

        $movement->administrator_id = $request->administrator_id;
        $movement->vehicle = $request->vehicle;
        $movement->reason = $request->reason;
        $movement->status = $request->status;
        $movement->trader_nin = $request->trader_nin;
        $movement->trader_name = $request->trader_name;
        $movement->trader_phone = $request->trader_phone;
        $movement->transporter_name = $request->transporter_name;
        $movement->transporter_nin = $request->transporter_nin;
        $movement->transporter_Phone = $request->transporter_Phone;
        $movement->district_from = $request->district_from;
        $movement->sub_county_from = $request->sub_county_from;
        $movement->village_from = $request->village_from;
        $movement->district_to = $request->district_to;
        $movement->sub_county_to = $request->sub_county_to;
        $movement->village_to = $request->village_to;
        $movement->transportation_route = $request->transportation_route;
        $movement->permit_Number = $request->permit_Number;
        $movement->valid_from_Date = $request->valid_from_Date;
        $movement->valid_to_Date = $request->valid_to_Date;
        $movement->status_comment = $request->status_comment;
        $movement->destination = trim($request->destination);
        $movement->destination_slaughter_house = $request->destination_slaughter_house;
        $movement->details = $request->details;
        $movement->destination_farm = $request->destination_farm;
        $movement->is_paid = $request->is_paid;
        $movement->paid_id = $request->paid_id;
        $movement->paid_method = $request->paid_method;
        $movement->paid_amount = $request->paid_amount;

        $sub_county_from = Location::find($movement->sub_county_from);
        if ($sub_county_from == null) {
            return Utils::response(['status' => 0, 'message' => "Subcount from was not found.",]);
        }

        if ($movement->destination != 'To farm' && $movement->destination != 'To slaughter' &&   $movement->destination != 'Other') {
            return Utils::response(['status' => 0, 'message' => "Destination $movement->destination type was not found.",]);
        }

        if ($movement->destination == 'To farm') {
            $destination_farm = Farm::find($movement->destination_farm);
            if ($destination_farm == null) {
                return Utils::response(['status' => 0, 'message' => "Destination $movement->destination_farm farm was not found.",]);
            }
        }
        if ($movement->destination == 'To slaughter') {
            $destination_slaughter_house = SlaughterHouse::find($movement->destination_slaughter_house);
            if ($destination_slaughter_house == null) {
                return Utils::response(['status' => 0, 'message' => "Slaughter $movement->destination_slaughter_house house was not found.",]);
            }
        }
        if ($movement->destination == 'Other') {
            $sub_county_to = Location::find($movement->sub_county_to);
            if ($sub_county_to == null) {
                return Utils::response(['status' => 0, 'message' => "Subcounty $movement->sub_county_to   was not found.",]);
            }
        }


        if ($movement->save()) {
            $movement_animal_id = $movement->id;
            foreach ($movement_ids as $key => $value) {
                $animal = Animal::find(((int)($value)));
                if ($animal == null) {
                    continue;
                }
                $move_item = new MovementHasMovementAnimal();
                $move_item->movement_id = $movement_animal_id;
                $move_item->movement_animal_id = $animal->id;
                $move_item->save();
            }
        }

        return Utils::response([
            'status' => 1,
            'message' => "Movement permit application submited successfully.",
            'data' => $movement
        ]);
    }

    public function create_v2(Request $request)
    {

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }


        $sub_county_from = Location::find($request->sub_county_from);
        if ($sub_county_from == null) {
            return Utils::response(['status' => 0, 'message' => "Subcount from was not found.",]);
        }


        $has_movements = false;
        $movement_ids = [];
        if ($request->movements_text != null) {
            if (strlen($request->movements_text) > 2) {
                $movement_ids = json_decode($request->movements_text);
                if ($movement_ids == null || empty($movement_ids)) {
                    $has_movements = false;
                } else {
                    $has_movements = true;
                }
            }
        }
        if ($request->animals_text != null) {
            if (strlen($request->animals_text) > 2) {
                $movement_ids = json_decode($request->animals_text);
                if ($movement_ids == null || empty($movement_ids)) {
                    $has_movements = false;
                } else {
                    $has_movements = true;
                }
            }
        }

        if (!$has_movements) {
            return Utils::response([
                'status' => 0,
                'message' => "There must be at least one animal in your movement permit.",
            ]);
        }

        $requirements = [
            'transporter_nin',
            'transporter_name',
            'vehicle',
            'destination',
        ];
        $valids = [
            'id',
            'administrator_id',
            'vehicle',
            'reason',
            'status',
            'trader_nin',
            'trader_name',
            'trader_phone',
            'transporter_name',
            'transporter_nin',
            'transporter_Phone',
            'district_from',
            'sub_county_from',
            'village_from',
            'district_to',
            'sub_county_to',
            'village_to',
            'transportation_route',
            'permit_Number',
            'valid_from_Date',
            'valid_to_Date',
            'status_comment',
            'destination',
            'destination_slaughter_house',
            'details',
            'destination_farm',
            'is_paid',
            'paid_id',
            'paid_method',
        ];
        $avaiable = [];
        foreach ($_POST as $key => $value) {
            $avaiable[] = $key;
        }

        foreach ($requirements as $key => $value) {
            if (isset($request->$value)) {
                continue;
            }
            return Utils::response([
                'status' => 0,
                'message' => "You must provide {$value}.",
                $request
            ]);
        }

        $movement = new Movement();
        if ($request->transporter_id != null) {
            $transporter = User::find($request->transporter_id);
            if ($transporter != null) {
                $request->transporter_name = $transporter->name;
                $request->transporter_nin = $transporter->nin;
                $request->transporter_Phone = $transporter->phone_number;
                $movement->transporter_id = $transporter->id;
            }
        }

        $movement->administrator_id = $user->id;
        $movement->vehicle = $request->vehicle;
        $movement->reason = $request->reason;
        $movement->status = $request->status;
        $movement->trader_nin = $request->trader_nin;
        $movement->trader_name = $request->trader_name;
        $movement->trader_phone = $request->trader_phone;
        $movement->transporter_name = $request->transporter_name;
        $movement->transporter_nin = $request->transporter_nin;
        $movement->transporter_Phone = $request->transporter_Phone;
        $movement->district_from = $request->district_from;
        $movement->sub_county_from = $request->sub_county_from;
        $movement->village_from = $request->village_from;
        $movement->district_to = $request->district_to;
        $movement->sub_county_to = $request->sub_county_to;
        $movement->village_to = $request->village_to;
        $movement->transportation_route = $request->transportation_route;
        $movement->permit_Number = $request->permit_Number;
        $movement->valid_from_Date = $request->valid_from_Date;
        $movement->valid_to_Date = $request->valid_to_Date;
        $movement->status_comment = $request->status_comment;
        $movement->destination = trim($request->destination);
        $movement->destination_slaughter_house = $request->destination_slaughter_house;
        $movement->details = $request->details;
        $movement->destination_farm = $request->destination_farm;
        $movement->is_paid = $request->is_paid;
        $movement->paid_id = $request->paid_id;
        $movement->paid_method = $request->paid_method;
        $movement->paid_amount = $request->paid_amount;

        $sub_county_from = Location::find($movement->sub_county_from);
        if ($sub_county_from == null) {
            return Utils::response(['status' => 0, 'message' => "Subcount from was not found.",]);
        }

        if ($movement->destination != 'To farm' && $movement->destination != 'To slaughter' &&   $movement->destination != 'Other') {
            return Utils::response(['status' => 0, 'message' => "Destination $movement->destination type was not found.",]);
        }

        if ($movement->destination == 'To farm') {
            $destination_farm = Farm::find($movement->destination_farm);
            if ($destination_farm == null) {
                return Utils::response(['status' => 0, 'message' => "Destination $movement->destination_farm farm was not found.",]);
            }
        }
        if ($movement->destination == 'To slaughter') {
            $destination_slaughter_house = SlaughterHouse::find($movement->destination_slaughter_house);
            if ($destination_slaughter_house == null) {
                return Utils::response(['status' => 0, 'message' => "Slaughter $movement->destination_slaughter_house house was not found.",]);
            }
        }
        if ($movement->destination == 'Other') {
            $sub_county_to = Location::find($movement->sub_county_to);
            if ($sub_county_to == null) {
                return Utils::response(['status' => 0, 'message' => "Subcounty $movement->sub_county_to   was not found.",]);
            }
        }




        try {
            //code...
            if ($movement->save()) {
                $movement_animal_id = $movement->id;
                foreach ($movement_ids as $key => $value) {
                    $animal = Animal::find(((int)($value->id)));
                    if ($animal == null) {
                        continue;
                    }
                    $move_item = new MovementHasMovementAnimal();
                    $move_item->movement_id = $movement_animal_id;
                    $move_item->movement_animal_id = $animal->id;
                    $move_item->save();
                }
            }
            return Utils::response([
                'status' => 1,
                'message' => "Movement permit application submited successfully.",
                'data' => $movement
            ]);
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to permit application. " . $th->getMessage(),
                'data' => null
            ]);
        }
    }


    public function trips_v2(Request $request)
    {
        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }
        $trips = Trip::where('transporter_id', $user->id)->get();

        $_permits = Movement::where(['administrator_id' => $user_id])->orderBy('id', 'desc')->get();
        foreach ($_permits as $key => $value) {
            $trip = Trip::find($value->trip_id);
            if ($trip == null) {
                continue;
            }
            $trips[] = $trip;
        }

        if (
            $user->isRole('dvo')
            || $user->isRole('sclo')
            || $user->isRole('admin')
            || $user->isRole('check-point-officer')
            || $user->isRole('scvo')
            || $user->isRole('slaughter')
        ) {
            $trips = Trip::where([])->get();
        }

        return Utils::response([
            'status' => 1,
            'message' => "Trips found.",
            'data' => $trips
        ]);
    }

    public function trip_create_v2(Request $request)
    {

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        }

        $has_movements = false;
        $movement_ids = [];
        if ($request->movements_text != null) {
            if (strlen($request->movements_text) > 2) {
                $movement_ids = json_decode($request->movements_text);
                if ($movement_ids == null || empty($movement_ids)) {
                    $has_movements = false;
                } else {
                    $has_movements = true;
                }
            }
        }

        if (!$has_movements) {
            return Utils::response([
                'status' => 0,
                'message' => "There must be at least one movement permit on a trip.",
            ]);
        }

        $trip = new Trip();
        $trip->transporter_id = $user->id;
        $trip->transporter_name = $user->name;
        $trip->transporter_nin = $user->nin;
        $trip->transporter_phone_number_1 = $user->phone_number;
        $trip->transporter_phone_number_2 = $user->phone_number_2;
        $trip->transporter_photo = $user->avatar;
        $trip->vehicle_type = $user->vehicle_registration_number;
        $trip->has_trip_started = 'Yes';
        $trip->has_trip_ended = 'No';
        $trip->trip_start_time = Carbon::now()->toDateTimeString();
        $trip->trip_end_time = null;
        $trip->start_latitude = $request->start_latitude;
        $trip->current_latitude = $request->current_latitude;
        $trip->current_longitude = $request->current_longitude;
        $trip->start_longitude = $request->start_longitude;
        $trip->trip_destination_type = $request->trip_destination_type;
        $trip->trip_destination_id = $request->trip_destination_id;
        $trip->trip_destination_latitude = $request->trip_destination_latitude;
        $trip->trip_destination_longitude = $request->trip_destination_longitude;
        $trip->trip_destination_address = $request->trip_destination_address;
        $trip->trip_destination_phone_number = $request->trip_destination_phone_number;
        $trip->trip_destination_contact_person = $request->trip_destination_contact_person;
        $trip->trip_details = $request->trip_details;

        try {
            $trip->save();
            foreach ($movement_ids as $key => $id) {
                $mv = Movement::find($id);
                if ($mv == null) {
                    continue;
                }
                $mv->status = 'On Trip';
                $mv->trip_id = $trip->id;
                $mv->save();
            }
        } catch (\Throwable $th) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to create trip. " . $th->getMessage(),
                'data' => null
            ]);
        }

        return Utils::response([
            'status' => 1,
            'message' => "Trip created successfully.",
            'data' => $trip
        ]);
    }

    public function review_movement(Request $request)
    {



        $requirements = [
            'status',
            'permit_id',
        ];
        $avaiable = [];
        foreach ($_POST as $key => $value) {
            $avaiable[] = $key;
        }

        foreach ($requirements as $key => $value) {
            if (isset($request->$value)) {
                continue;
            }
            return Utils::response([
                'status' => 0,
                'message' => "You must provide {$value}.",
                $request
            ]);
        }

        $permit_id = (int)($request->permit_id);

        $movement = Movement::find($permit_id);
        if ($movement == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Movement permit not found.",
            ]);
        }
        $movement->status = $request->status;

        if ($movement->status == 'Approved') {
            $movement->valid_from_Date = Carbon::now();
            $movement->valid_to_Date = Carbon::now()->addDays(2);
            $dates = explode(' - ', $request->valid_from_Date);
            if (count($dates) > 1) {
                $movement->valid_from_Date = $dates[0];
                $movement->valid_to_Date = $dates[1];
            }
        } else if ($movement->status == 'Halted' || $movement->status == 'Rejected') {
            $movement->valid_from_Date = null;
            $movement->valid_to_Date = null;
            $movement->reason = $request->details;
            $movement->details = $request->details;
        }

        if ($movement->save()) {
            return Utils::response([
                'status' => 1,
                'message' => "Movement permit application {$movement->status}.",
                'data' => $movement
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'message' => "Movement permit application was not {$movement->status} due to technical issue. Please try again.",
                'data' => $movement
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $Farm = Farm::findOrFail($id);
        $Farm->update($request->all());
        return $Farm;
    }

    public function delete(Request $request, $id)
    {
        $Farm = Farm::findOrFail($id);
        $Farm->delete();

        return 204;
    }
}
