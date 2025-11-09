<?php
//rominah P
namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\DistrictTagDistributionBatch;
use App\Models\FamerTagsOrder;
use App\Models\Farm;
use App\Models\FarmReport;
use App\Models\Image;
use App\Models\Location;
use App\Models\PersonalSetting;
use App\Models\PregnantAnimal;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Faker\Provider\ar_EG\Person;
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
        FarmReport::do_process($r);
        $r = FarmReport::find($report->id);
        return $this->success($r, "Farm report created successfully.");
    }


    public function v2_personal_settings(Request $r){
        $user_id = ((int)(Utils::get_user_id($r)));
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        } 

        if($u->user_type == 'Worker'){
            $user_id = $u->temp_id;
            $u = Administrator::find($user_id);
            if ($u == null) {
                return Utils::response([
                    'status' => 0,
                    'data' => null,
                    'message' => 'Failed'
                ]);
            }
        }

        $settings = PersonalSetting::where([
            'user_id' => $u->id
        ])->first();
        
        if($settings == null){
            $settings = new PersonalSetting();
            $settings->user_id = $u->id;
            try {
                $settings->save();
                $settings = PersonalSetting::find($settings->id);
            } catch (\Throwable $th) {
                return $this->error("Failed to create personal settings because " . $th->getMessage());
            }
        }
        $recs = [$settings];
        return $this->success($recs, "Personal settings retrieved successfully.");
    }


    public function v2_farmer_tags_orders(Request $r){
        $user_id = ((int)(Utils::get_user_id($r)));
        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => 'Failed'
            ]);
        } 
        $orders = FamerTagsOrder::where([
            'famer_id' => $user_id
        ])->get();
        return $this->success($orders);

    }
    public function animal_connect_parent(Request $r)
    {
        /* 
                 "animal_id": widget.item.id,
          "parent_id": widget.item.parent_id,
          "birth_position": widget.item.birth_position,
          "has_parent": widget.item.has_parent, */
        $animal = Animal::find($r->animal_id);
        if ($animal == null) {
            return $this->error("Animal not found.");
        }

        $msg = '';
        if ($r->has_parent != 'Yes') {
            $animal->has_parent = 'No';
            $animal->parent_id = null;
            $animal->birth_position = null;
            $msg = 'Animal disconnected from parent.';
        } else {
            $parent = Animal::find($r->parent_id);
            if ($parent == null) {
                return $this->error("Parent not found.");
            }
            $animal->has_parent = 'Yes';
            $animal->parent_id = $parent->id;
            $animal->birth_position = $r->birth_position;
            $msg = 'Animal connected to parent.';
        }
        try {
            $animal->save();
            $animal = Animal::find($animal->id);
            if ($animal == null) {
                return $this->error("Failed to connect animal to parent.");
            }
            return $this->success($animal, $msg);
        } catch (\Throwable $th) {
            return $this->error("Failed to connect animal to parent because " . $th->getMessage());
        }
    }

    //v2_personal_settings_update
    public function v2_personal_settings_update(Request $r){
        $rec = PersonalSetting::find($r->id);
        if ($rec == null) {
            return $this->error("Personal settings not found.");
        }
        if ($r->sms_phone_number == null || strlen($r->sms_phone_number) < 5) {
            return $this->error("Invalid phone number.");
        }  
        $rec->enable_sms_notification = $r->enable_sms_notification;
        $rec->sms_phone_number = $r->sms_phone_number;
        $rec->paid_for_sms_notification = $r->paid_for_sms_notification;
        $rec->enable_email_notification = $r->enable_email_notification;
        $rec->email_address = $r->email_address;
        $rec->farm_worker_can_view_data = $r->farm_worker_can_view_data;
        $rec->farm_worker_can_edit_data = $r->farm_worker_can_edit_data;
        $rec->farm_worker_can_add_data = $r->farm_worker_can_add_data;
        $rec->farm_worker_can_delete_data = $r->farm_worker_can_delete_data;
        $rec->enable_automated_reports = $r->enable_automated_reports;
        $rec->report_frequency = $r->report_frequency;
        $rec->enable_milk_production_report = $r->enable_milk_production_report;
        $rec->enable_animal_health_report = $r->enable_animal_health_report;
        $rec->enable_animal_sales_report = $r->enable_animal_sales_report;
        $rec->enable_animal_birth_report = $r->enable_animal_birth_report;
        $rec->enable_animal_death_report = $r->enable_animal_death_report;
        $rec->enable_animal_movement_report = $r->enable_animal_movement_report;
        $rec->enable_animal_treatment_report = $r->enable_animal_treatment_report;
        $rec->enable_animal_vaccination_report = $r->enable_animal_vaccination_report;
        $rec->enable_animal_weighing_report = $r->enable_animal_weighing_report;
        $rec->enable_animal_tagging_report = $r->enable_animal_tagging_report;
        $rec->enable_animal_breeding_report = $r->enable_animal_breeding_report;
        $rec->enable_financial_report = $r->enable_financial_report;
        $rec->enable_milk_sales_report = $r->enable_milk_sales_report;
        try {
            $rec->save();
            $rec = PersonalSetting::find($rec->id);
            if ($rec == null) {
                return $this->error("Failed to update personal settings.");
            }
            return $this->success($rec, "Personal settings updated successfully.");
        } catch (\Throwable $th) {
            return $this->error("Failed to update personal settings because " . $th->getMessage());
        }
    }
    public function v2_farmer_tags_order_check_payment_status(Request $r){
        $rec = FamerTagsOrder::find($r->id);
        if ($rec == null) {
            return $this->error("Order not found.");
        }

        try {
            $rec->is_order_paid();
        } catch (\Throwable $th) {
            return $this->error("Failed to check payment status because " . $th->getMessage());
        }
        
        $rec = FamerTagsOrder::find($rec->id);
        if ($rec == null) {
            return $this->error("Failed to create order. Order not found again.");
        }
        return $this->success($rec, "Payment link generated successfully.");
    }


    public function v2_farmer_tags_order_generate_payment_link_create(Request $r){

        $rec = FamerTagsOrder::find($r->id);
        if ($rec == null) {
            return $this->error("Order not found.");
        }

        $user = User::find($rec->famer_id);
        if ($user == null) {
            return $this->error("Farmer not found.");
        }
        
        $rec->flutterwave_phone_number = $r->flutterwave_phone_number;
        $rec->name = $r->name;
        if($rec->name == null || strlen($rec->name) < 3){
           $rec->name = $user->name;
        }

        if($rec->name == null || strlen($rec->name) < 3){
           $rec->name = $user->first_name . " " . $user->last_name;
        } 
        
        $rec->phone_number_type = $r->phone_number_type;

        try {
            $rec->save();
        } catch (\Throwable $th) {
            return $this->error("Failed to save order because " . $th->getMessage());
        }

        try {
            $rec->get_flutterwave_link();
        } catch (\Throwable $th) {
            return $this->error("Failed to generate payment link because " . $th->getMessage());
        }
    
        $rec = FamerTagsOrder::find($rec->id);
    
        if ($rec == null) {
            return $this->error("Failed to create order. Order not found again.");
        }

        if ($rec->flutterwave_link == null || strlen($rec->flutterwave_link) < 10) {
            return $this->error("Failed to create order. Payment link not found.");
        }

        $rec->flutterwave_link = $rec->flutterwave_link;
        return $this->success($rec, "Payment link generated successfully.");

    }
/**
 * Handles the creation or updating of a farmer tags order.
 *
 * This function first validates the existence of the farm and its owner based on the 
 * provided farm ID. It then checks the validity of the total tags ordered quantity 
 * and the delivery address. If an existing order ID is provided, the function updates 
 * the order; otherwise, it creates a new order. The order details are populated 
 * including district ID and total tags ordered amount. The function attempts to save 
 * the order and returns an appropriate success or error message.
 *
 * @param Request $r The HTTP request object containing order details such as farm ID, 
 *                   total tags ordered quantity, delivery address, and optionally, 
 *                   an order ID.
 * 
 * @return \Illuminate\Http\Response A success response with the created or updated order 
 *                                   details, or an error response if any validation or 
 *                                   process fails.
 */

    public function v2_farmer_tags_order_create(Request $r)
    {
        $farm = Farm::find($r->farm_id);
        $owner = null;
        $isNew = false;
        if ($farm == null) {
            return $this->error("Farm not found. #" . $r->farm_id);
        }
        $owner = User::find($farm->administrator_id);
        if ($owner == null) {
            return $this->error("Farm owner not found.");
        }
        $total_tags_ordered_quantity = (int)$r->total_tags_ordered_quantity;
        if ($total_tags_ordered_quantity < 1) {
            return $this->error("Invalid tags ordered quantity.");
        }
        $delivery_address = $r->delivery_address;
        if ($delivery_address == null || strlen($delivery_address) < 3) {
            return $this->error("Invalid delivery address.");
        }
        $farmer_message = $r->farmer_message;
        $order = null;
        $isCraeating = false;
        if (isset($r->id)) {
            $order = FamerTagsOrder::find($r->id);
            $isCraeating = true;
        }

        if ($order == null) {
            $order = new FamerTagsOrder();
        }
        $district = Location::find($farm->district_id);
        if ($district != null) {
            $order->district_id = $district->id;
        } else {
            return $this->error("District not found.");
        }
        if($isCraeating){
            $batch = DistrictTagDistributionBatch::find($r->district_tag_distribution_batch_id);
            if ($batch != null) {
                $order->district_tag_distribution_batch_id = $batch->id;
                $order->total_tags_ordered_amount = $batch->selling_price * $total_tags_ordered_quantity;
            }else{
                $order->total_tags_ordered_amount = 1000 * $total_tags_ordered_quantity;
            }
            $order->delivery_address = $delivery_address;
            $order->farmer_message = $farmer_message;
            $order->order_status = 'Pending';
            $order->farm_id = $farm->id;
        }

        if($isCraeating){
            try {
                $order->save();
                $order = FamerTagsOrder::find($order->id);
                if ($order == null) {
                    return $this->error("Failed to create order.");
                }
                return $this->success($order, "Order created successfully.");
            } catch (\Throwable $th) {
                return $this->error("Failed to create order because " . $th->getMessage());
            }
        }else{
            try {
                $order->save();
                $order = FamerTagsOrder::find($order->id);
                if ($order == null) {
                    return $this->error("Failed to create order.");
                }
                return $this->success($order, "Order updated successfully.");
            } catch (\Throwable $th) {
                return $this->error("Failed to create order because " . $th->getMessage());
            }
        }

        return $this->error("Failed to create order.");
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

            if(isset($r->permissions)){
                $farm->permissions = $r->permissions;
            }
            
            try {
                $farm->save();
                return $this->success("Farm updated successfully.");
            } catch (\Exception $e) {
                return $this->error("Failed to update farm because " . $e->getMessage());
            } //end
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
        if(isset($r->permissions)){
            $farm->permissions = $r->permissions;
        }
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
    /**
     * Legacy animal view - returns basic animal data only
     * Kept for backward compatibility
     */
    public function animal_view(Request $r)
    {
        $an = Animal::find($r->id);
        if ($an == null) {
            $an = Animal::where([
                'local_id' => $r->id
            ])->first();
        }
        if ($an == null) {
            $an = Animal::where([
                'v_id' => $r->id
            ])->first();
        }
        if ($an == null) {
            $an = Animal::where([
                'e_id' => $r->id
            ])->first();
        }
        if ($an == null) {
            return $this->error("Animal not found  for id #" . $r->id);
        }
        return $this->success($an);
    }

    /**
     * 360° Animal Detail View
     * Returns comprehensive animal information including:
     * - Basic info
     * - Relationships (mother, sire, offspring)
     * - Photos & Documents
     * - Events & Health Records
     * - Movements & Transfers
     * - Performance metrics
     * 
     * GET /api/animals/{id}/detail
     */
    public function animal_detail(Request $r, $id)
    {
        // Find animal by ID, local_id, v_id, or e_id
        $animal = Animal::find($id);
        if ($animal == null) {
            $animal = Animal::where('local_id', $id)
                ->orWhere('v_id', $id)
                ->orWhere('e_id', $id)
                ->first();
        }

        if ($animal == null) {
            return $this->error("Animal not found");
        }

        // Load farm, district, and sub-county names
        $farm = null;
        $district = null;
        $subCounty = null;
        
        if ($animal->farm_id > 0) {
            $farm = \App\Models\Farm::find($animal->farm_id);
        }
        
        if ($animal->district_id > 0) {
            $district = \App\Models\Location::find($animal->district_id);
        }
        
        if ($animal->sub_county_id > 0) {
            $subCounty = \App\Models\Location::find($animal->sub_county_id);
        }

        // Prepare comprehensive response
        $data = [
            // Basic Information
            'basic_info' => [
                'id' => $animal->id,
                'local_id' => $animal->local_id,
                'e_id' => $animal->e_id,
                'v_id' => $animal->v_id,
                'lhc' => $animal->lhc,
                'type' => $animal->type,
                'breed' => $animal->breed,
                'sex' => $animal->sex,
                'status' => $animal->status,
                'dob' => $animal->dob,
                'age' => $animal->age,
                'stage' => $animal->stage,
                'color' => $animal->color,
                'weight' => $animal->weight,
                'weight_text' => $animal->weight_text,
                'photo' => $animal->photo ? url($animal->photo) : null,
                'created_at' => $animal->created_at,
                'updated_at' => $animal->updated_at,
            ],

            // Farm & Location
            'location' => [
                'farm_id' => $animal->farm_id,
                'farm_text' => $farm ? $farm->name : ($animal->farm_text ?: $animal->lhc),
                'district_id' => $animal->district_id,
                'district_text' => $district ? $district->name : $animal->district_text,
                'sub_county_id' => $animal->sub_county_id,
                'sub_county_text' => $subCounty ? $subCounty->name : $animal->sub_county_text,
                'group_id' => $animal->group_id,
                'group_text' => $animal->group_text,
            ],

            // Relationships
            'relationships' => $this->getAnimalRelationships($animal),

            // Photos & Media
            'photos' => $this->getAnimalPhotos($animal),

            // Events & Records
            'events' => $this->getAnimalEvents($animal),

            // Health & Vaccinations  
            'health' => $this->getAnimalHealth($animal),

            // Performance Metrics
            'performance' => $this->getAnimalPerformance($animal),

            // Additional Info
            'additional_info' => [
                'conception' => $animal->conception,
                'fmd' => $animal->fmd,
                'has_produced_before' => $animal->has_produced_before,
                'is_weaned_off' => $animal->is_weaned_off,
                'weight_at_birth' => $animal->weight_at_birth,
                'purchase_date' => $animal->purchase_date,
                'purchase_price' => $animal->purchase_price,
                'current_price' => $animal->current_price,
                'comments' => $animal->comments,
            ],
        ];

        return $this->success($data, "Animal details retrieved successfully");
    }

    /**
     * Get animal relationships (mother, sire, offspring)
     */
    private function getAnimalRelationships($animal)
    {
        $relationships = [
            'has_parent' => $animal->has_parent == 'Yes',
            'mother' => null,
            'sire' => null,
            'offspring' => [],
        ];

        // Mother (parent_id)
        if ($animal->parent_id) {
            $mother = Animal::find($animal->parent_id);
            if ($mother) {
                $relationships['mother'] = [
                    'id' => $mother->id,
                    'e_id' => $mother->e_id,
                    'v_id' => $mother->v_id,
                    'breed' => $mother->breed,
                    'photo' => $mother->photo ? url($mother->photo) : null,
                ];
            }
        }

        // Sire (genetic_donor / sire_id if available)
        if (isset($animal->sire_id) && $animal->sire_id) {
            $sire = Animal::find($animal->sire_id);
            if ($sire) {
                $relationships['sire'] = [
                    'id' => $sire->id,
                    'e_id' => $sire->e_id,
                    'v_id' => $sire->v_id,
                    'breed' => $sire->breed,
                    'photo' => $sire->photo ? url($sire->photo) : null,
                ];
            }
        }

        // Offspring (animals where this animal is the parent)
        $offspring = Animal::where('parent_id', $animal->id)
            ->select('id', 'e_id', 'v_id', 'breed', 'sex', 'dob', 'photo', 'status')
            ->limit(50)
            ->get();

        $relationships['offspring'] = $offspring->map(function($child) {
            return [
                'id' => $child->id,
                'e_id' => $child->e_id,
                'v_id' => $child->v_id,
                'breed' => $child->breed,
                'sex' => $child->sex,
                'dob' => $child->dob,
                'status' => $child->status,
                'photo' => $child->photo ? url($child->photo) : null,
            ];
        })->toArray();

        $relationships['offspring_count'] = count($relationships['offspring']);

        return $relationships;
    }

    /**
     * Get animal photos
     */
    private function getAnimalPhotos($animal)
    {
        $photos = Image::where('parent_id', $animal->id)
            ->where('parent_endpoint', 'api/animals')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return $photos->map(function($photo) {
            return [
                'id' => $photo->id,
                'thumbnail' => url($photo->thumbnail),
                'src' => url($photo->src),
                'size' => $photo->size,
                'created_at' => $photo->created_at,
            ];
        })->toArray();
    }

    /**
     * Get animal events (milking, weight checks, etc.)
     */
    private function getAnimalEvents($animal)
    {
        $events = \App\Models\Event::where('animal_id', $animal->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $grouped = [
            'milking' => [],
            'weight' => [],
            'breeding' => [],
            'treatment' => [],
            'other' => [],
        ];

        foreach ($events as $event) {
            $eventData = [
                'id' => $event->id,
                'type' => $event->type,
                'detail' => $event->detail,
                'created_at' => $event->created_at,
                'milk' => $event->milk,
                'weight' => $event->weight,
            ];

            if ($event->type == 'Milking') {
                $grouped['milking'][] = $eventData;
            } elseif ($event->type == 'Weight check') {
                $grouped['weight'][] = $eventData;
            } elseif (in_array($event->type, ['Breeding', 'Pregnancy', 'Birth'])) {
                $grouped['breeding'][] = $eventData;
            } elseif (in_array($event->type, ['Treatment', 'Vaccination', 'Disease'])) {
                $grouped['treatment'][] = $eventData;
            } else {
                $grouped['other'][] = $eventData;
            }
        }

        return [
            'total_count' => $events->count(),
            'by_category' => $grouped,
            'latest' => $events->take(10)->map(function($event) {
                return [
                    'id' => $event->id,
                    'type' => $event->type,
                    'detail' => $event->detail,
                    'created_at' => $event->created_at,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get animal health records
     */
    private function getAnimalHealth($animal)
    {
        // Get vaccination records
        $vaccinations = \App\Models\Event::where('animal_id', $animal->id)
            ->where('type', 'Vaccination')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get disease records  
        $diseases = \App\Models\Event::where('animal_id', $animal->id)
            ->where('type', 'Disease')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Check if animal is in sick_animals table
        $sickRecord = \App\Models\SickAnimal::where('animal_id', $animal->id)
            ->where('current_results', 'Positive')
            ->first();

        // Check if animal is pregnant
        $pregnancyRecord = PregnantAnimal::where('animal_id', $animal->id)
            ->whereIn('current_status', ['Pregnant', 'Confirmed'])
            ->first();

        return [
            'is_sick' => $sickRecord != null,
            'is_pregnant' => $pregnancyRecord != null,
            'pregnancy' => $pregnancyRecord ? [
                'status' => $pregnancyRecord->current_status,
                'conception_date' => $pregnancyRecord->conception_date,
                'expected_calving_date' => $pregnancyRecord->expected_calving_date,
                'fertilization_method' => $pregnancyRecord->fertilization_method,
            ] : null,
            'vaccinations' => $vaccinations->map(function($v) {
                return [
                    'id' => $v->id,
                    'detail' => $v->detail,
                    'date' => $v->created_at,
                ];
            })->toArray(),
            'diseases' => $diseases->map(function($d) {
                return [
                    'id' => $d->id,
                    'detail' => $d->detail,
                    'date' => $d->created_at,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get animal performance metrics
     */
    private function getAnimalPerformance($animal)
    {
        // Milk production
        $milkEvents = \App\Models\Event::where('animal_id', $animal->id)
            ->where('type', 'Milking')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalMilk = $milkEvents->sum('milk');
        $avgMilk = $milkEvents->count() > 0 ? $totalMilk / $milkEvents->count() : 0;

        // Weight tracking
        $weightEvents = \App\Models\Event::where('animal_id', $animal->id)
            ->where('type', 'Weight check')
            ->orderBy('created_at', 'desc')
            ->get();

        $latestWeight = $weightEvents->first();
        $firstWeight = $weightEvents->last();
        $weightGain = 0;
        if ($latestWeight && $firstWeight && $latestWeight->id != $firstWeight->id) {
            $weightGain = $latestWeight->weight - $firstWeight->weight;
        }

        return [
            'milk_production' => [
                'total_records' => $milkEvents->count(),
                'total_liters' => round($totalMilk, 2),
                'average_per_session' => round($avgMilk, 2),
                'latest' => $milkEvents->take(5)->map(function($m) {
                    return [
                        'liters' => $m->milk,
                        'date' => $m->created_at,
                    ];
                })->toArray(),
            ],
            'weight_tracking' => [
                'total_records' => $weightEvents->count(),
                'current_weight' => $latestWeight ? $latestWeight->weight : $animal->weight,
                'weight_gain' => round($weightGain, 2),
                'history' => $weightEvents->take(5)->map(function($w) {
                    return [
                        'weight' => $w->weight,
                        'date' => $w->created_at,
                    ];
                })->toArray(),
            ],
        ];
    }
}
