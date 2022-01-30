<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiAnimalController extends Controller
{
    public function index(Request $request)
    { 
        $s = $request->s;
        $items = [];
        
        if($s != null){
            if(strlen($s)>0){

                if(str_contains($s,'UG')){
                    $f = Farm::where("holding_code",$s)->first();
                    if($f != null){
                        $items = $f->animals;
                    }
                }else{
                    $items = Animal::where(
                        'e_id', 'like', '%'.trim($request->s).'%',
                    )->paginate(1000)->withQueryString()->items();
                }

                if(empty($items)){ 
                    return [];
                }
            }
        }

        if(empty($items)){
            $per_page = 1000;
            if(isset($request->per_page)){
                $per_page = $request->per_page;
            }
            $items = Animal::paginate($per_page)->withQueryString()->items();            
        }
 
        foreach ($items as $key => $value) {
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
        }
        return $items;
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

    
}
