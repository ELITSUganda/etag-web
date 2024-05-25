<?php
//rominah P
namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Image;
use App\Models\Location;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class V2ApiMainController extends Controller
{
    use ApiResponser;

    public function v2_farms_create(Request $r)
    {
        $farm = Farm::find($r->id);
        $owner = null;
        $isNew = false;
        if ($farm == null) {
            $farm = Farm::where([
                'registered_id' => $r->registered_id,
                'local_id' => $r->local_id,
            ])->first();
            if ($farm == null) {
                $farm = Farm::find($r->id);
                if ($farm == null) {
                    $farm = new Farm();
                    $isNew = true;
                }
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
        if (strlen($r->farm_owner_nin) > 4) {
            $owner = User::where([
                'nin' => $r->farm_owner_nin
            ])->first();
        }

        if ($owner == null) {
            $owner = User::where([
                'phone_number' => $phone
            ])->first();
        }

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

            $owner->address = $r->village;
            $owner->nin = $r->farm_owner_nin;
            $owner->sub_county_id = $r->sub_county_id;
            $owner->save();
            $owner = User::find($owner->id);
            $farm->administrator_id = $owner->id;
        }

        if ($owner == null) {
            return $this->error("Owner not found.");
        }

        if ($isNew) {
            if ($r->farm_owner_is_new == 'Yes') {
            } else {
                $owner = User::find($r->administrator_id);
                if ($owner == null) {
                    return $this->error("Farm owner with id #" . $r->administrator_id . " not found.");
                }
            }
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
            if ($isNew) {
                $download_ulits_app_url = url('/app');
                $message_to_farmer = "Dear " . $owner->name . ", your farm has been successfully registered. Your Farm LHC is " . $farm->id . ". Download the ULITS app to access your farm data. LINK: " . $download_ulits_app_url . " Thank you.";
                try {
                    Utils::send_message($phone, $message_to_farmer);
                } catch (\Exception $e) {
                }
            }
            return $this->success("Farm saved successfully.");
        } catch (\Exception $e) {
            return $this->error("Failed to save farm because " . $e->getMessage());
        }
    }


    public function v2_animals_create(Request $r)
    {
        if ($r->task != 'Update' && $r->task != 'New') {
            return $this->error("Invalid task.");
        }

        //local_id
        if ($r->local_id == null || strlen($r->local_id) < 3) {
            return $this->error("Invalid local id.");
        }

        $isNew = false;
        if ($r->task == 'Update') {
            $animal = Animal::find($r->id);
            if ($animal == null) {
                return $this->error("Animal not found.");
            }
            $isNew = false;
        } else {

            $animal = Animal::where([
                'registered_by_id' => $r->registered_by_id,
                'local_id' => $r->local_id,
            ])->first();

            if ($animal == null) {
                $animal = new Animal();
                $isNew = true; //new changes
            }
        }

        $registered_by = User::find($r->registered_by_id);
        if ($registered_by == null) {
            return $this->error("Registered by not found.");
        }

        if ($animal == null) {
            $animal = Animal::where([
                'registered_by_id' => $r->registered_by_id,
                'local_id' => $r->local_id,
            ])->first();
            if ($animal == null) {
                $animal = Animal::find($r->id);
                if ($animal == null) {
                    $animal = new Animal();
                    $isNew = true;
                }
            }
        }

        if ($animal == null) {
            return $this->error("Animal not created.");
        }
        $farm = Farm::find($r->farm_id);
        if ($farm == null) {
            return $this->error("Farm not found.");
        }
        $animal->administrator_id = $farm->administrator_id;
        $animal->district_id = $farm->district_id;
        $animal->sub_county_id = $farm->sub_county_id;
        $animal->parish_id = $farm->sub_county_id;
        $animal->status = 1;
        $animal->type = $r->type;
        $animal->e_id = $r->e_id;
        $animal->v_id = $r->v_id;
        $animal->lhc = $farm->holding_code;
        $animal->origin_latitude = $farm->latitude;
        $animal->origin_longitude = $farm->longitude;
        $animal->breed = $r->breed;
        $animal->sex = $r->sex;
        $animal->dob = $r->dob;
        $animal->color = $r->color;
        $animal->local_id = $r->local_id;
        $animal->farm_id = $r->farm_id;
        $animal->fmd = $r->fmd;
        $animal->details = $r->details;
        $animal->has_parent = $r->has_parent;
        $animal->parent_id = $r->parent_id;
        $animal->parent_id = $r->parent_id;
        $animal->photo = null;
        $animal->stage = $r->stage;
        $animal->registered_by_id = $r->registered_by_id;
        $animal->local_id = $r->local_id;

        $resp_msg = 'Animal updated successfully.';
        try {
            $animal->save();
            if ($isNew) {
                $resp_msg = 'Animal created successfully.';
            }
            $animal  = Animal::find($animal->id);
        } catch (\Exception $e) {
            return $this->error("Failed to save animal because " . $e->getMessage());
        }



        if ($animal != null) {
            //profile photo
            $img = Image::where([
                'local_id' => $r->local_id,
                'registered_by_id' => $r->registered_by_id,
            ])->first();
            if ($img != null) {
                if (strlen($img->thumbnail) < 3) {
                    $animal->photo = 'storage/images/' . $img->src;
                } else {
                    $animal->photo = 'storage/images/' . $img->thumbnail;
                }
                $animal->save();
                $img->parent_id = $animal->id;
                $img->product_id = $animal->id;
                $img->administrator_id = $animal->administrator_id;
                $img->parent_endpoint = 'Animal';
                $img->note = 'Profile photo';
                $img->save();
            }
            $animal->save();
        }

        $animal  = Animal::find($animal->id);
        return $this->success($animal, $resp_msg);
    }



    public function v2_post_media_upload(Request $request)
    {

        $administrator_id = $request->registered_by_id;
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        if (
            !isset($request->local_id) ||
            $request->local_id == null ||
            strlen($request->local_id) < 3
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Local ID is missing.",
            ]);
        }

        if (
            !isset($request->parent_endpoint) ||
            $request->parent_endpoint == null ||
            (strlen(($request->parent_endpoint))) < 2
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
            $img->thumbnail =  null;
            $img->parent_endpoint =  $request->parent_endpoint;
            $img->parent_id =  $request->parent_id;
            $img->local_id =  $request->local_id;

            if ($img->local_id == null || strlen($img->local_id) < 3) {
                $img->local_id =  $request->parent_id;
            }
            $img->registered_by_id =  $request->registered_by_id;
            $img->size = 0;
            $img->note = $request->note;
            if (
                isset($request->note)
            ) {
                $img->note =  $request->note;
                $msg .= "Note not set. ";
            }
            $img->save();

            $type = strtolower($img->type);
            $parent_endpoint = strtolower($img->parent_endpoint);

            if (
                $type == 'animal' ||
                $parent_endpoint == 'animal'
            ) {
                $animal = Animal::find($img->parent_id);
                if ($animal == null) {
                    $animal = Animal::find($img->online_parent_id);
                }
                if ($animal == null) {
                    $animal = Animal::where([
                        'local_id' => $request->local_id,
                        'registered_by_id' => $administrator_id,
                    ])->first();
                    $animal = Animal::where([
                        'local_id' => $request->local_parent_id,
                        'registered_by_id' => $administrator_id,
                    ])->first();
                }
                if ($animal != null) {
                    $img->product_id = $animal->id;
                    $img->parent_id = $animal->id;
                    $img->save();
                    if ($animal->photo == null || strlen($animal->photo) < 3) {
                        $animal->photo = 'storage/images/' . $img->thumbnail;
                        $animal->save();
                    }
                }
            }
            $_images[] = $img;
        }
        //Utils::process_images_in_backround();
        return Utils::response([
            'status' => 1,
            'data' => json_encode($_POST),
            'message' => "File uploaded successfully.",
        ]);
    }
}
