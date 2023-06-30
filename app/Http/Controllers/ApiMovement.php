<?php
//rominah P
namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\CheckPoint;
use App\Models\CheckPointRecord;
use App\Models\CheckpointSession;
use App\Models\DrugCategory;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Location;
use App\Models\Movement;
use App\Models\MovementHasMovementAnimal;
use App\Models\MovementRoute;
use App\Models\SlaughterHouse;
use App\Models\Utils;
use Carbon\Carbon;
use COM;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiMovement extends Controller
{
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

            if ($user->isRole('dvo') || $user->isRole('sclo') || $user->isRole('scvo')) {
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
        }



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
                $checkPoints = json_decode($request->check_points_to_pass_list);
                if (is_array($checkPoints)) {
                    foreach ($checkPoints as $key => $checkPointId) {
                        $checkPoint = CheckPoint::find($checkPointId);
                        if ($checkPoint == null) {
                            continue;
                        }
                        $s = new CheckpointSession();
                        $s->session_status = 'Pending';
                        $s->checked_by = $checkPoint->administrator_id;
                        $s->check_point_id = $checkPoint->id;
                        $s->movement_id = $mv->id;
                        $s->animals_expected = $mv->animals->count();
                        $s->animals_checked = 0;
                        $s->animals_found = 0;
                        $s->animals_missed = 0;
                        $s->details = '';
                        $s->save();
                    }
                }
            } catch (\Throwable $th) {
            }
        }

        return Utils::response([
            'status' => 1,
            'message' => "Movement permit $mv->status successfully.",
            'data' => $mv
        ]);
    }
    public function create(Request $request)
    {

        $sub_county_from = Location::find($request->sub_county_from);
        if ($sub_county_from == null) {
            return Utils::response(['status' => 0, 'message' => "Subcount from was not found.",]);
        }


        $has_animals = false;
        $animal_ids = [];
        if ($request->animal_ids != null) {
            if (strlen($request->animal_ids) > 2) {
                $animal_ids = json_decode($request->animal_ids);
                if ($animal_ids == null || empty($animal_ids)) {
                } else {
                    $has_animals = true;
                }
            }
        }

        if (!$has_animals) {
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
            foreach ($animal_ids as $key => $value) {
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
