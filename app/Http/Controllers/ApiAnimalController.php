<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Farm;
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
                $f = Farm::where("holding_code",$s)->first();
                if($f != null){
                    $items = $f->animals;
                }
                if(empty($items)){
                    //
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
    
}
