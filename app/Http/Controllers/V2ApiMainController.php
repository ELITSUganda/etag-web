<?php
//rominah P
namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\FarmReport;
use App\Models\Image;
use App\Models\Location;
use App\Models\PregnantAnimal;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class V2ApiMainController extends Controller
{
    use ApiResponser;

    public function v2_farm_report_create(Request $r)
    {
        $farm = Farm::find($r->farm_id);
        if ($farm == null) {
            return $this->error("Farm not found.");
        }
        $start_date = null;
        try {
            $start_date = Carbon::parse($r->start_date);
        } catch (\Throwable $th) {
            return $this->error("Invalid start date.");
        }
        $end_date = null;
        try {
            $end_date = Carbon::parse($r->end_date);
        } catch (\Throwable $th) {
            return $this->error("Invalid end date.");
        }
        //check if days is less than 3
        $diffDays = $start_date->diffInDays($end_date);
        if ($diffDays < 3) {
            return $this->error("Report must be atleast 3 days.");
        }
        $report = new FarmReport();
        $report->farm_id = $farm->id;
        $report->user_id = $farm->administrator_id;
        $report->start_date = $start_date;
        $report->end_date = $end_date;
        $report->pdf = null;

        try {
            $report->save();
        } catch (\Throwable $th) {
            return $this->error("Failed to create farm report because " . $th->getMessage());
        }
        $r = FarmReport::find($report->id);
        if ($r == null) {
            return $this->error("Failed to create farm report.");
        }
        $r = FarmReport::do_process($r);
        $r->save();
        return $this->success($r, "Farm report created successfully.");



        /*
start_date
end_date
farm_id
user_id
pdf
pdf_prepared
pdf_prepare_date */
    }
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

        if ($r->created_at == 'UPDATE_FARM') {
            $farm = Farm::find($r->updated_at);
            if ($farm == null) {
                return $this->error("Farm to update not found.");
            }
            $farm->is_processed = 'No';
            $farm->sub_county_id = ($r->sub_county_id != null && (strlen($r->sub_county_id) > 0)) ? $r->sub_county_id : $farm->sub_county_id;
            $farm->farm_type = ($r->farm_type != null && (strlen($r->farm_type) > 0)) ? $r->farm_type : $farm->farm_type;
            $farm->size = ($r->size != null && (strlen($r->size) > 0)) ? $r->size : $farm->size;
            // $farm->latitude = ($r->latitude != null && (strlen($r->latitude) > 0)) ? $r->latitude : $farm->latitude;
            // $farm->longitude = ($r->longitude != null && (strlen($r->longitude) > 0)) ? $r->longitude : $farm->longitude;
            $farm->dfm = ($r->dfm != null && (strlen($r->dfm) > 0)) ? $r->dfm : $farm->dfm;
            //name
            $farm->name = ($r->name != null && (strlen($r->name) > 0)) ? $r->name : $farm->name;
            //village
            $farm->village = ($r->village != null && (strlen($r->village) > 0)) ? $r->village : $farm->village;
            //animals_count
            $farm->animals_count = ($r->animals_count != null && (strlen($r->animals_count) > 0)) ? $r->animals_count : $farm->animals_count;
            //sheep_count
            $farm->sheep_count = ($r->sheep_count != null && (strlen($r->sheep_count) > 0)) ? $r->sheep_count : $farm->sheep_count;
            //goats_count
            $farm->goats_count = ($r->goats_count != null && (strlen($r->goats_count) > 0)) ? $r->goats_count : $farm->goats_count;
            //cattle_count
            $farm->cattle_count = ($r->cattle_count != null && (strlen($r->cattle_count) > 0)) ? $r->cattle_count : $farm->cattle_count;
            //has_fmd
            $farm->has_fmd = ($r->has_fmd != null && (strlen($r->has_fmd) > 0)) ? $r->has_fmd : $farm->has_fmd;
            //farm_owner_name
            $farm->farm_owner_name = ($r->farm_owner_name != null && (strlen($r->farm_owner_name) > 0)) ? $r->farm_owner_name : $farm->farm_owner_name;
            //farm_owner_nin
            $farm->farm_owner_nin = ($r->farm_owner_nin != null && (strlen($r->farm_owner_nin) > 0)) ? $r->farm_owner_nin : $farm->farm_owner_nin;
            //farm_owner_phone_number
            $farm->farm_owner_phone_number = ($r->farm_owner_phone_number != null && (strlen($r->farm_owner_phone_number) > 0)) ? $r->farm_owner_phone_number : $farm->farm_owner_phone_number;
            //pigs_count
            $farm->pigs_count = ($r->pigs_count != null && (strlen($r->pigs_count) > 0)) ? $r->pigs_count : $farm->pigs_count;
            //local_id
            $farm->local_id = ($r->local_id != null && (strlen($r->local_id) > 0)) ? $r->local_id : $farm->local_id;
            //registered_id
            $farm->registered_id = ($r->registered_id != null && (strlen($r->registered_id) > 0)) ? $r->registered_id : $farm->registered_id;
            //duplicate_checked
            $farm->duplicate_checked = ($r->duplicate_checked != null && (strlen($r->duplicate_checked) > 0)) ? $r->duplicate_checked : $farm->duplicate_checked;
            //duplicate_results
            $farm->duplicate_results = ($r->duplicate_results != null && (strlen($r->duplicate_results) > 0)) ? $r->duplicate_results : $farm->duplicate_results;
            try {
                $farm->save();
                return $this->success("Farm updated successfully.");
            } catch (\Exception $e) {
                return $this->error("Failed to update farm because " . $e->getMessage());
            } //end
        }
        /* 	
			
local_id	
registered_id	
duplicate_checked	
duplicate_results	
	
Edit Edit
Copy Copy

        */


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

        $animal = Animal::find($r->id);

        if ($animal == null) {
            //local_id
            if ($r->local_id == null || strlen($r->local_id) < 3) {
                return $this->error("Invalid local id.");
            }
        }

        $isNew = false;
        if ($r->task == 'Update') {
            $animal = Animal::find($r->id);
            if ($animal == null && $r->local_id != null && strlen($r->local_id) > 4) {
                $animal = Animal::where([
                    'local_id' => $r->local_id,
                ])->first();
            }
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

        $farm = null;
        if ($animal == null) {
            return $this->error("Animal not created.");
            $farm = Farm::find($r->farm_id);
        } else {
            $farm = Farm::find($animal->farm_id);
            if ($farm == null) {
                $farm = Farm::find($r->farm_id);
            }
        }

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
        $animal->farm_id = $farm->id;
        $animal->fmd = $r->fmd;
        $animal->details = $r->details;
        $animal->has_parent = $r->has_parent;
        $animal->parent_id = $r->parent_id ?? null;
        $animal->stage = $r->stage;
        $animal->registered_by_id = $r->registered_by_id;
        $animal->local_id = $r->local_id;
        $animal->group_id = $r->group_id ?? null;
        $animal->details = $r->details ?? null;
        $animal->has_parent = $r->has_parent ?? null;
        $animal->has_more_info = $r->has_more_info ?? null;
        $animal->was_purchases = $r->was_purchases ?? null;
        $animal->purchase_date = $r->purchase_date ?? null;
        $animal->purchase_from = $r->purchase_from ?? null;
        $animal->purchase_price = $r->purchase_price ?? null;
        $animal->current_price = $r->current_price ?? null;
        $animal->weight_at_birth = $r->weight_at_birth ?? null;
        $animal->conception = $r->conception ?? null;
        $animal->genetic_donor = $r->genetic_donor ?? null;
        $animal->group_id = $r->group_id ?? null;
        $animal->has_produced_before = $r->has_produced_before ?? null;
        $animal->age_at_first_calving = $r->age_at_first_calving ?? null;
        $animal->weight_at_first_calving = $r->weight_at_first_calving ?? null;
        $animal->has_been_inseminated = $r->has_been_inseminated ?? null;
        $animal->age_at_first_insemination = $r->age_at_first_insemination ?? null;
        $animal->weight_at_first_insemination = $r->weight_at_first_insemination ?? null;
        $animal->is_a_calf = $r->is_a_calf ?? null;
        $animal->is_weaned_off = $r->is_weaned_off ?? null;
        $animal->wean_off_weight = $r->wean_off_weight ?? null;
        $animal->wean_off_age = $r->wean_off_age ?? null;
        $animal->birth_position = $r->birth_position ?? null;

        if ($r->last_profile_update_date != null) {
            if (strlen($r->last_profile_update_date) > 3) {
                $last_profile_update_date = null;
                try {
                    $last_profile_update_date = Carbon::parse($r->last_profile_update_date);
                } catch (\Throwable $th) {
                    $last_profile_update_date = null;
                }
                if ($last_profile_update_date != null) {
                    $animal->last_profile_update_date = $last_profile_update_date;
                    $animal->profile_updated = 'Yes';
                }
            }
        }
        $animal->last_profile_update_date = $r->last_profile_update_date ?? null;



        // $animal->photo = null;
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
                    $animal->photo = 'images/' . $img->src;
                } else {
                    $animal->photo = 'images/' . $img->thumbnail;
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

                if (($animal != null) && ($img->note == 'ProfilePhoto')) {
                    $img->product_id = $animal->id;
                    $img->parent_id = $animal->id;
                    $img->save();
                    $animal->photo = 'images/' . $img->src;
                    $animal->save();
                }
            }
            $_images[] = $img;
        }

        return Utils::response([
            'status' => 1,
            'data' => $images,
            'message' => "File uploaded successfully.",
        ]);
    }

    //v2-pregnant-animals GET
    public function v2_pregnant_animals_create(Request $r)
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
        $animal = Animal::find($r->animal_id);
        if ($animal == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Animal not found'
            ]);
        }
        $record = PregnantAnimal::find($r->id);
        $isEdit = false;
        if ($record == null) {
            $record = new PregnantAnimal();
        } else {
            $isEdit = true;
        }

        if (!$isEdit) {
            //check if animal is already pregnant
            $pregnant = PregnantAnimal::where([
                'animal_id' => $animal->id
            ])->first();
            if ($pregnant != null) {
                return Utils::response([
                    'status' => 0,
                    'data' => null,
                    'message' => 'Animal is already pregnant'
                ]);
            }
        }

        $record->administrator_id = $user_id;
        $record->animal_id = $r->animal_id;
        $record->district_id = $animal->district_id;
        $record->sub_county_id = $animal->sub_county_id;
        $record->original_status = $r->original_status;
        $record->current_status = $r->current_status;
        $record->fertilization_method = $r->fertilization_method;
        $record->expected_sex = $r->expected_sex;
        $record->details = $r->details;
        $record->pregnancy_check_method = $r->pregnancy_check_method;

        $msg = '';

        try {
            $record->save();
            if ($isEdit) {
                $msg = 'Pregnant animal updated successfully.';
            } else {
                $msg = 'Pregnant animal created successfully.';
            }
        } catch (\Exception $e) {
            $msg = 'Failed because ' . $e->getMessage();
        }

        $record = PregnantAnimal::find($record->id);
        return $this->success($record, $msg);
    }

    public function v2_pregnant_animals_list(Request $r)
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

        $pregnant_animals = PregnantAnimal::where([
            'administrator_id' => $user_id
        ])->get();
        return $this->success($pregnant_animals);
    }

    public function v2_farm_reports(Request $r)
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

        $pregnant_animals = FarmReport::where([
            'user_id' => $user_id
        ])->get();
        return $this->success($pregnant_animals);
    }
}
