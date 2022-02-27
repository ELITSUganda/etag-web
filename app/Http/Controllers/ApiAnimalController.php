<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\SlaughterRecord;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiAnimalController extends Controller
{
    public function create_slaughter(Request $request)
    {
 
        if($request->animal_ids == null){
            return Utils::response([
                'status' => 0,
                'message' => "Animals must be provided.",
            ]);
        }

       
        
        $details =  ((String)($request->details));

        $user_id = Utils::get_user_id($request);

        if($user_id < 1){
            return Utils::response([
                'status' => 0,
                'message' => "Slaugter house ID not found.",
            ]);
        }

        $u = Administrator::find($user_id); 
        if($u  == null){
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter house not found.",
            ]);
        }
        $animal = json_decode($request->animal_ids);
        
        if($animal == null || empty($animal)){
            return Utils::response([
                'status' => 0,
                'message' => "No animals found.",
            ]);
        }
        $i = 0;  
        foreach ($animal as $key => $id) {
            $_id = ((int)($id));
            if($_id < 1){
                continue;
            }
            $an = Animal::find($_id);
            if($an == null){
                continue;
            }

            $sr = new SlaughterRecord();
            $sr->lhc = $an->lhc;
            $sr->v_id = $an->v_id;
            $sr->administrator_id = $user_id;
            $sr->e_id = $an->e_id;
            $sr->breed = $an->breed;
            $sr->sex = $an->sex;
            $sr->dob = $an->dob;
            $sr->fmd = $an->fmd; 
            $sr->details = "Slautered by ".$u->name.", ID ".$u->id.". ".$details; 
            $sr->destination_slaughter_house = $u->name;
            
            if($sr->save()){
                Utils::archive_animal([
                    'animal_id' => $_id,
                    'details' => $sr->details,
                    'event' => 'Slautered',
                ]);
            }
            $i++; 
        }

        return Utils::response([
            'status' => 1,
            'message' => "{$i} Slauhter records have been created successfully.",
        ]);

    }
  
  
    public function create_sale(Request $request)
    {
        if($request->animal_ids == null){
            return Utils::response([
                'status' => 0,
                'message' => "Animals must be provided.",
            ]);
        }

        if($request->trader == null){
            return Utils::response([
                'status' => 0,
                'message' => "Trader ID must be provided.",
            ]);
        }
        
        $trader =  ((int)($request->trader));
        if($trader < 1){
            return Utils::response([
                'status' => 0,
                'message' => "Trader ID not found.",
            ]);
        }
        $t = Administrator::find($trader);
        if($t  == null){
            return Utils::response([
                'status' => 0,
                'message' => "Trader not found.",
            ]);
        }
        $animal = json_decode($request->animal_ids);
        
        if($animal == null || empty($animal)){
            return Utils::response([
                'status' => 0,
                'message' => "No animals found.",
            ]);
        }
        $i = 0;
        foreach ($animal as $key => $id) {
            $_id = ((int)($id));
            if($_id < 1){
                continue;
            }
            $an = Animal::find($_id);
            if($an == null){
                continue;
            }
            $i++;
            $an->trader = $trader;
            $an->save();
        }

        return Utils::response([
            'status' => 1,
            'message' => "{$i} Animals were assigned to trader successfully.",
        ]);

    }


    public function store_event(Request $request)
    {
        if($request->animal_id == null){
            return Utils::response([
                'status' => 0,
                'message' => "Animal ID must be provided.",
            ]);
        }

        if($request->type == null){
            return Utils::response([
                'status' => 0,
                'message' => "Event type must be provided.",
            ]);
        }
 
        $animal = Animal::find(((int)($request->animal_id)));
        if($animal == null){
            return Utils::response([
                'status' => 0,
                'message' => "Animal not found on our database.",
            ]);
        }

        $event = new Event();
        $event->animal_id = (int)($request->animal_id);
        $event->detail = $request->detail;
        $event->type = $request->type;
        $event->disease_id = $request->disease;
        $event->medicine_id = $request->treatment;
        $event->vaccine_id = $request->vaccination;
        $event->approved_by = 1; 
        
        if($event->save()){
            return Utils::response([
                'status' => 1,
                'message' => "Event was created successfully.",
                'data' => $event
            ]);
        }
        
        return Utils::response([
            'status' => 0,
            'message' => "Failed to save event on database.",
        ]);
  
    }



    public function index(Request $request)
    {  
        $administrator_id = Utils::get_user_id($request);
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id); 
        $role = Utils::get_role($u);
         
        $s = $request->s;
        $items = [];
        $_items = [];
        
        if($s != null){
            if(strlen($s)>0){ 

                $f = Farm::where("holding_code",$s)->first();
                if($f != null){
                    $items = $f->animals;
                }

                $_items = Animal::where(
                    'e_id', 'like', '%'.trim($request->s).'%',
                )->paginate(1000)->withQueryString()->items();
                
                $__items = Animal::where(
                    'v_id', 'like', '%'.trim($request->s).'%',
                )->paginate(1000)->withQueryString()->items();
                
                $items_ids = [];
                $___items = [];

                foreach ($items as $key => $v) {
                    if(!in_array($v->id,$items_ids)){
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }

                foreach ($_items as $key => $v) {
                    if(!in_array($v->id,$items_ids)){
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }

                foreach ($__items as $key => $v) {
                    if(!in_array($v->id,$items_ids)){
                        $items_ids[] = $v->id;
                        $___items[] = $v;
                    }
                }


                return $___items;
            }
        }

        if(empty($items)){
            $per_page = 1000;
            if(isset($request->per_page)){
                $per_page = $request->per_page;
            }
            if($role == 'slaughter'){
                $moves = Movement::where('destination_slaughter_house', '=', $user_id)->where('status', '=', 'Approved')->get();
                foreach ($moves as $key => $value) {
                    if($value->movement_has_movement_animals!=null){
                        foreach ($value->movement_has_movement_animals as $_value) {
                            if($_value->movement_animal_id!=null){
                                $__an = Animal::find($_value->movement_animal_id);
                                if($__an != null){
                                    $items[] = $__an;
                                }
                            }
                        }
                    }
                }                
            }else{
                $items = Animal::paginate($per_page)->withQueryString()->items();            
            }
        }

        foreach ($items as $key => $value) { 
            if($role == 'farmer'){
                if($value->administrator_id != $administrator_id){ 
                    continue;
                }
            }else if($role == 'trader'){
                if($u->id != $value->trader){  
                    continue;
                }
            }else if($role == 'dvo'){
                if($u->dvo != $value->district_id){ 
                    continue;
                }
            }

            $items[$key]->owner_name  = "";
            if($items[$key]->farm!=null){  
                if($items[$key]->farm->user !=null){
                    $items[$key]->owner_name = $items[$key]->farm->user->name;
                }
            } 

            $items[$key]->owner_name = "";
            $items[$key]->district_name = "";
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString(); 
            if($value->district!=null){
                $items[$key]->district_name = $value->district->name;
            }
            if($value->sub_county!=null){
                $items[$key]->sub_county_name = $value->sub_county->name;
            }
            unset($items[$key]->farm);
            unset($items[$key]->district); 
            unset($items[$key]->sub_county); 
            $_items[] = $items[$key];
        }
        return $_items;
    }



    public function slaughters(Request $request)
    {  
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id); 
        if($u == null){
            return [];
        }
        
        $items = [];
        $_items = [];
        
        $items = SlaughterRecord::where('administrator_id', $user_id)->get();
        foreach ($items as $key => $value) {  
            $value->created = Carbon::parse( $value->created_at)->toFormattedDateString();
            $_items[] = $value;
        }
        return $_items;
    }


    public function show($id)
    {
        $item = Animal::find($id);

        $item->owner_name  = "";
        if($item->farm!=null){  
            if($item->farm->user !=null){
                $item->owner_name = $item->farm->user->name;
            }
        } 

        $item->owner_name = "";
        $item->district_name = "";
        $item->created = Carbon::parse($item->created)->toFormattedDateString(); 
        if($item->district!=null){
            $item->district_name = $item->district->name;
        }
        if($item->sub_county!=null){
            $item->sub_county_name = $item->sub_county->name;
        }
        unset($item->farm);
        unset($item->district); 
        unset($item->sub_county);


        return $item;
    }


    
 
  

    public function store(Request $request)
    {
        return Administrator::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $Administrator = Administrator::findOrFail($id);
        $Administrator->update($request->all());

        return $Administrator;
    }

    public function delete(Request $request, $id)
    {
        $Administrator = Administrator::findOrFail($id);
        $Administrator->delete();

        return 204;
    }

    public function create(Request $request) 
    { 
        if (
            !isset($request->farm_id )
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide farm."
            ]);
        }  

        if (
            !isset($request->e_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide e_id."
            ]);
        } 

        if (
            !isset($request->type)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide type."
            ]);
        } 

        if (
            !isset($request->e_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide e_id."
            ]);
        } 

        if (
            !isset($request->breed)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide breed."
            ]);
        } 

        if (
            !isset($request->sex)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide sex."
            ]);
        } 

        if (
            !isset($request->fmd)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide fmd."
            ]);
        } 
        
        $f = new Animal(); 
        $f->e_id = $request->e_id;
        $f->farm_id = $request->farm_id; 
        $f->type = $request->type;
        $f->v_id = $request->v_id;
        $f->lhc = $request->lhc;
        $f->breed = $request->breed;
        $f->sex = $request->sex;
        $f->dob = $request->dob;
        $f->fmd = $request->fmd;
        $f->status = 'live';
         

        $f->save();
        return Utils::response([
            'status' => 1,
            'message' => "Animal created successfully.",
            'data' => $f
        ]);
    }

    public function events(Request $request)
    {
        $per_page = 100;
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        }
        
        $administrator_id = Utils::get_user_id($request);
        $user_id = Utils::get_user_id($request);
        $u = Administrator::find($user_id); 
        if($u ==null){
            return [];
        } 
        $role = Utils::get_role($u);
        
        $is_search = false;
        $items = [];
        $s = $request->s; 
        if($s != null){
            if(strlen($s)>0){ 
                $is_search = true;

                $an = Animal::where("e_id",$s)->first();
                if($an == null){
                    $an = Animal::where("v_id",$s)->first();
                }
                if($an==null){ 
                    return [];
                }
                if(!isset($an->id)){
                    return [];
                }

                $items = Event::where("animal_id",$an->id)->get(); 
                if(empty($items)){ 
                    return [];
                }
            }
        }
        
        if(!$is_search){
            $items = Event::paginate($per_page)->withQueryString()->items();
        }
        

        $_items = [];
        foreach ($items as $key => $value) {

            if($role == 'farmer'){
                if($value->administrator_id != $administrator_id){ 
                    continue;
                }
            }else if($role == 'scvo'){
                if($u->scvo != $value->sub_county_id){ 
                    continue;
                }
            }

            $items[$key]->e_id  = "";
            $items[$key]->v_id  = "";
            $items[$key]->lhc  = "";
            if ($items[$key]->animal != null) {
                if ($items[$key]->animal->e_id != null) {
                    $items[$key]->e_id  = $items[$key]->animal->e_id;
                    $items[$key]->v_id  = $items[$key]->animal->v_id;
                    $items[$key]->lhc  = $items[$key]->animal->lhc;
                }
                unset($items[$key]->animal);
            }
            $items[$key]->created = Carbon::parse($value->created)->toFormattedDateString();  
            $_items[] = $items[$key];
        }
        return $_items;
    }

    

}
