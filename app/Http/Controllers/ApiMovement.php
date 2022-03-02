<?php
//rominah P
namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\MovementHasMovementAnimal;
use App\Models\Utils;
use Carbon\Carbon;
use COM;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiMovement extends Controller
{
    public function index(Request $request)
    {

        $user_id = ((int)(Utils::get_user_id($request)));
        $user = Administrator::find($user_id);
        if ($user == null) {
            return [];
        }

        $items = [];
        $filtered_items = [];
        $role = Utils::get_role($user);


        if (
            $role == 'dvo' ||
            $role == 'administrator' ||
            $role == 'admin' ||
            $role == 'sclo'
        ) {
            $items = Movement::paginate(1000)->withQueryString()->items();
        } else if ($role == 'slaughter') {
            $items = Movement::where('destination_slaughter_house', '=', $user_id)->where('status', '=', 'Approved')->get();
        } else if ($role == 'scvo') {
            //if sclo
            $items = Movement::where('sub_county_from', '=', $user->scvo)->where('status', '=', 'Approved')->get();
        } else {
            $items = Movement::where(['administrator_id' => $user_id])->get();
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
            $item = Movement::where('permit_Number',$id)->first();
        }
        
        if ($item == null) {
            die("{}");
        }
        $animals = $item->movement_has_movement_animals;
        $item->animals_count = count($animals);
        $item->animals_list = $animals;
        unset($item->movement_has_movement_animals);

        return $item;
    }

    public function create(Request $request)
    {
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
        $movement->destination = $request->destination;
        $movement->destination_slaughter_house = $request->destination_slaughter_house;
        $movement->details = $request->details;
        $movement->destination_farm = $request->destination_farm;
        $movement->is_paid = $request->is_paid;
        $movement->paid_id = $request->paid_id;
        $movement->paid_method = $request->paid_method;


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
        }else{ 
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
