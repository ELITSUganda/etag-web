<?php
//rominah P
namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Location;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class V2ApiMainController extends Controller
{
    use ApiResponser;

    public function v2_farms_create(Request $r)
    {
        $farm = Farm::find($r->id);
        $isNew = false;
        if ($farm == null) {
            $farm = Farm::where([
                'registered_id' => $r->registered_id,
                'local_id' => $r->local_id,
            ])->first();
            if ($farm == null) {
                $farm = new Farm();
                $isNew = true;
            }
        }

        if ($farm != null) {
            $owner = User::find($farm->administrator_id);
        }

        $registerd_by = User::find($r->registered_id);
        if ($registerd_by == null) {
            return $this->error("Registered by not found.");
        }
        $farm->registered_id = $r->registered_id;
        $phone = Utils::prepare_phone_number($r->farm_owner_phone_number);
        if (!Utils::phone_number_is_valid($phone)) {
            return $this->error("Invalid phone number.");
        }
        $owner = null;
        if ($isNew) {
            if ($r->farm_owner_is_new == 'Yes') {
                $owner = User::where([
                    'phone_number' => $phone
                ])->first();
                if ($owner == null) {
                    $owner = User::where([
                        'username' => $phone
                    ])->first();
                }
                if ($owner == null) {
                    $owner = User::where([
                        'email' => $phone
                    ])->first();
                }

                if ($owner == null) {
                    $new_farmer = new User();
                    $new_farmer->username = $phone;
                    $new_farmer->phone_number = $phone;
                    $new_farmer->email = $phone;
                    $new_farmer->address = $r->village;
                    $new_farmer->nin = $r->farm_owner_nin;
                    $new_farmer->sub_county_id = $r->sub_county_id;
                    $new_farmer->user_type = 'Farmer';
                    $new_farmer->status = 1;
                    $new_farmer->first_name = $r->farm_owner_name;
                    $new_farmer->last_name = $r->farm_owner_name;
                    $pass = '1234';
                    $new_farmer->password = password_hash($pass, PASSWORD_DEFAULT);
                    $new_farmer->name = $r->farm_owner_name;
                    $new_farmer->save();
                    $farm->administrator_id = $new_farmer->id;
                    $owner = User::find($new_farmer->id);
                } else {
                    $farm->administrator_id = $owner->id;
                    $owner = User::find($owner->id);
                }
            }else{
                $owner = User::find($r->administrator_id);
                if ($owner == null) {
                    return $this->error("Farm Owner not found.");
                }
            }
        }

        if ($owner == null) {
            return $this->error("Owner not found.");
        }
        $sub = Location::find($r->sub_county_id);
        if ($sub == null) {
            return $this->error("Sub county not found.");
        }
        $farm->farm_owner_name = $r->farm_owner_name;
        $farm->farm_owner_nin = $r->farm_owner_nin;
        $farm->farm_owner_phone_number = $r->farm_owner_phone_number;
        $farm->district_id = $r->parent;
        $farm->sub_county_id = $sub->id;
        $farm->farm_type = $r->farm_type;
        $farm->size = $r->size;
        $farm->latitude = $r->latitude;
        $farm->longitude = $r->longitude;
        $farm->dfm = $r->dfm;
        $farm->name = $r->name;
        $farm->village = $r->village;
        $farm->animals_count = $r->animals_count;
        $farm->sheep_count = $r->sheep_count;
        $farm->goats_count = $r->goats_count;
        $farm->cattle_count = $r->cattle_count;
        $farm->pigs_count = $r->pigs_count;
        $farm->has_fmd = $r->has_fmd;
        $farm->local_id = $r->local_id;
        $farm->registered_id = $r->registered_id;
        $farm->is_processed = 'Yes';
        try {
            $farm->save();
            return $this->success("Farm saved successfully.");
        } catch (\Exception $e) {
            return $this->error("Failed to save farm because " . $e->getMessage());
        }
    }
}
