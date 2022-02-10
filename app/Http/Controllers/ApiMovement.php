<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Movement;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiMovement extends Controller
{
    public function index(Request $request)
    {

        $is_admin = Utils::is_admin($request);
        $user_id = Utils::get_user_id($request);

        $has_search = false;
        if (isset($request->s)) {
            if ($request->s != null) {
                if (strlen($request->s) > 0) {
                    $has_search = true;
                }
            }
        }

        $items = [];
        if ($has_search) {
            $items = Farm::where(
                'holding_code',
                'like',
                '%' . trim($request->s) . '%',
            )->paginate(1000)->withQueryString()->items();
        } else {
            $items = Farm::paginate(1000)->withQueryString()->items();
        }


        $filtered_items = [];
        foreach ($items as $key => $value) {
            $items[$key]->owner_name = "";
            $items[$key]->district_name = "";
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();
            if ($value->user != null) {
                $items[$key]->owner_name = $value->user->name;
            }
            if ($value->district != null) {
                $items[$key]->district_name = $value->district->name;
            }
            if ($value->sub_county != null) {
                $items[$key]->sub_county_name = $value->sub_county->name;
            }
            unset($items[$key]->farm);
            unset($items[$key]->district);
            unset($items[$key]->sub_county);
            if ($is_admin) {
                $filtered_items[] = $items[$key];
            } else {
                if ($user_id == $items[$key]->administrator_id) {
                    unset($items[$key]->user);
                    $filtered_items[] = $items[$key];
                }
            }
        }

        return $filtered_items;
    }

    public function show($id)
    {
        $item = Farm::find($id);
        if ($item == null) {
            return '{}';
        }
        $item->owner_name = "";
        $item->district_name = "";
        $item->created = $item->created;
        if ($item->user != null) {
            $item->owner_name = $item->user->name;
        }
        if ($item->district != null) {
            $item->district_name = $item->district->name;
        }
        if ($item->sub_county != null) {
            $item->sub_county_name = $item->sub_county->name;
        }

        return $item;
    }

    public function create(Request $request)
    {
        $requirements = [
            'administrator_id',
            'paid_method',
            'transporter_nin',
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
            'status_comment',
            'valid_to_Date',
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
            if (in_array($value, $avaiable)) {
                continue;
            }
            return Utils::response([
                'status' => 0,
                'message' => "You must provide {$value}.",
                $_POST
            ]);
        }

        $movement = new Movement();
        foreach ($_POST as $key => $value) {
            if (in_array($key, $valids)) {
                $movement->$key = $value;
            }
        }

        $movement->save();

        return Utils::response([
            'status' => 0,
            'message' => "Movement permit application submited successfully.",
            'data' => $movement
        ]);
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
