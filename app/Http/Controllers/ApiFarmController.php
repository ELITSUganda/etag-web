<?php

namespace App\Http\Controllers;

use App\Models\AdminRoleUser;
use App\Models\DrugStockBatch;
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

        $data = [];
        foreach (Location::All() as $v) {
            $d['id'] = $v->id;
            $d['parent'] = $v->parent;
            $d['name_text'] = $v->name_text;
            $data[] = $d;
        }

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


    public function my_drugs(Request $request)
    {
        $drugs = [];
        $user_id = Utils::get_user_id($request);

        foreach (DrugStockBatch::where([
            /*    'administrator_id' => $user_id */])
            ->where('current_quantity', '>', 0)
            ->get() as $key => $v) {

            $unit = "";
            if ($v->category != null) {
                $unit = " - {$v->category->unit}";
            }

            $v->name_text =  $v->name . " - Available QTY: {$v->current_quantity} {$unit}";
            $drugs[] = $v;
        }

        return Utils::response([
            'status' => 1,
            'message' => "Success.",
            'data' => $drugs
        ]);
    }
    public function index(Request $request)
    {

        $user_id = Utils::get_user_id($request);
        $user_role = Utils::is_admin($request);
        $u = Administrator::find($user_id);
        $where = [
            'administrator_id' => $user_id
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

        $data = Farm::where($where)->get();

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

        $user_id = Utils::get_user_id($request);

        $user = Administrator::find($user_id);

        if (
            $user == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Farm owner not found."
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


        $user->sub_county_id = $request->sub_county_id;
        $user->save();

        $f = new Farm();
        $f->administrator_id = $user->id;
        $f->animals_count = 0;
        $f->dfm = '1-1-2020';
        $f->farm_type = $request->farm_type;
        $f->sheep_count = 0;
        $f->goats_count = 0;
        $f->latitude = $request->latitude;
        $f->longitude = $request->longitude;
        $f->sub_county_id = $request->sub_county_id;
        $f->size = $request->size;
        $f->village = $request->village;
        $f->cattle_count = ((int)($request->cattle_count));

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
