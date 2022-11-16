<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Location;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiFarmController extends Controller
{
    public function locations(Request $request)
    {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $res_1 = Location::where('parent', '!=', 0)->get();
        $data = [];
        foreach ($res_1 as $key => $v) {
            $data[] = [
                'id' => $v->id,
                'text' => $v->get_name()
            ];
        }

        return [
            'data' => $data
        ];



        $data = Location::All();

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


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
            $filtered_items[] = $items[$key];
            /* if (
                ($user_role == 'administrator') ||
                ($user_role == 'admin')
            ) {
                $filtered_items[] = $items[$key];
            } else {
                if ($user_id == $items[$key]->administrator_id) {
                    unset($items[$key]->user);
                    $filtered_items[] = $items[$key];
                }
            }*/
        }

        return $filtered_items;
    }


    public function index(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        $user_role = Utils::is_admin($request);

        $data = Farm::where([
            'administrator_id' => $user_id
        ])->get();

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $data
        ]);


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
            $filtered_items[] = $items[$key];
            /* if (
                ($user_role == 'administrator') ||
                ($user_role == 'admin')
            ) {
                $filtered_items[] = $items[$key];
            } else {
                if ($user_id == $items[$key]->administrator_id) {
                    unset($items[$key]->user);
                    $filtered_items[] = $items[$key];
                }
            }*/
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


        if (
            !isset($request->administrator_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide farm owner."
            ]);
        }

        if (
            !isset($request->sub_county_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide sub_county_id."
            ]);
        }
        $administrator_id = 0;
        if (isset($request->administrator_id)) {
            $administrator_id = (int)($request->administrator_id);
        }
        $admin = Administrator::find($administrator_id);

        if ($admin == null) {
            $owner_temp_id = "";
            if (isset($request->owner_temp_id)) {
                $owner_temp_id = $request->owner_temp_id;
            }
            if (strlen($owner_temp_id) > 2) {
                $admin = Administrator::where('temp_id', $owner_temp_id)->first();
            }
        }
        if ($admin == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Farm owner not found."
            ]);
        }
        if (!isset($admin->id)) {
            return Utils::response([
                'status' => 0,
                'message' => "Farm owner ID not found."
            ]);
        }

        $f = new Farm();
        $f->administrator_id = $admin->id;
        $f->animals_count = $request->animals_count;
        $f->dfm = $request->dfm;
        $f->farm_type = $request->farm_type;
        $f->cattle_count = $request->cattle_count;
        $f->sheep_count = $request->sheep_count;
        $f->goats_count = $request->goats_count;
        $f->latitude = $request->latitude;
        $f->longitude = $request->longitude;
        $f->sub_county_id = $request->sub_county_id;
        $f->size = $request->size;
        $f->village = $request->village;

        $f->save();
        return Utils::response([
            'status' => 1,
            'message' => "Farm created successfully.",
            'data' => $f
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
