<?php

namespace App\Http\Controllers;

use App\Models\AdminRoleUser;
use App\Models\DrugStockBatch;
use App\Models\Farm;
use App\Models\Location;
use App\Models\UserHasFarmPermission;
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

        foreach (
            DrugStockBatch::where([
                /*    'administrator_id' => $user_id */])
                ->where('current_quantity', '>', 0)
                ->get() as $key => $v
        ) {

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
                $data = Farm::where($where)->get();
            } else {
                //where owner is in the list of access_ids
                $data = Farm::whereIn('id', $access_ids)->get();
            }
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

    public function update_gps(Request $request)
    {
        // Get the authenticated user
        $user_id = Utils::get_user_id($request);
        $user = Administrator::find($user_id);

        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }

        // Validate required parameters
        if (!isset($request->farm_id) || empty($request->farm_id)) {
            return Utils::response([
                'status' => 0,
                'message' => "Farm ID is required."
            ]);
        }

        if (!isset($request->latitude) || !isset($request->longitude)) {
            return Utils::response([
                'status' => 0,
                'message' => "Latitude and longitude are required."
            ]);
        }

        // Find the farm
        $farm = Farm::find($request->farm_id);
        if ($farm == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Farm not found."
            ]);
        }

        // Check if user has permission to update this farm
        $has_permission = false;
        
        // Check if user is the administrator of this farm
        if ($farm->administrator_id == $user_id) {
            $has_permission = true;
        }

        // Check if user has farm permissions
        $permission = UserHasFarmPermission::where([
            'user_id' => $user_id,
            'farm_id' => $farm->id
        ])->first();
        
        if ($permission != null) {
            $has_permission = true;
        }

        // Check if user is admin or super admin
        $user_role = Utils::get_role($user);
        if (in_array($user_role, ['administrator', 'admin'])) {
            $has_permission = true;
        }

        if (!$has_permission) {
            return Utils::response([
                'status' => 0,
                'message' => "You don't have permission to update this farm's GPS location."
            ]);
        }

        // Validate latitude and longitude ranges
        $latitude = (float) $request->latitude;
        $longitude = (float) $request->longitude;

        if ($latitude < -90 || $latitude > 90) {
            return Utils::response([
                'status' => 0,
                'message' => "Latitude must be between -90 and 90 degrees."
            ]);
        }

        if ($longitude < -180 || $longitude > 180) {
            return Utils::response([
                'status' => 0,
                'message' => "Longitude must be between -180 and 180 degrees."
            ]);
        }

        // Update the farm GPS coordinates
        $farm->latitude = $request->latitude;
        $farm->longitude = $request->longitude;
        
        try {
            $farm->save();
            
            return Utils::response([
                'status' => 1,
                'message' => "Farm GPS location updated successfully.",
                'data' => [
                    'farm_id' => $farm->id,
                    'holding_code' => $farm->holding_code,
                    'latitude' => $farm->latitude,
                    'longitude' => $farm->longitude,
                    'updated_at' => $farm->updated_at
                ]
            ]);
        } catch (\Exception $e) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to update farm GPS location: " . $e->getMessage()
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
