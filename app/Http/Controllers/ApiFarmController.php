<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Utils;
use Illuminate\Http\Request;

class ApiFarmController extends Controller
{
    public function index(Request $request)
    {
        $has_search = false;
        if(isset($request->s)){
            if($request->s !=null){
                if(strlen($request->s)>0){
                    $has_search = true; 
                }
            }
        }
        
        $items = [];
        if($has_search){
            $items = Farm::where(
                'holding_code', 'like', '%'.trim($request->s).'%',
            )->paginate(1000)->withQueryString()->items();
        }else{
            $items = Farm::paginate(1000)->withQueryString()->items();
        }

        foreach ($items as $key => $value) {
            $items[$key]->owner_name = "";
            $items[$key]->district_name = "";
            if($value->user!=null){
                $items[$key]->owner_name = $value->user->name;
            }
            if($value->district!=null){
                $items[$key]->district_name = $value->district->name;
            }
            if($value->sub_county!=null){
                $items[$key]->sub_county_name = $value->sub_county->name;
            }
        }

        return $items;
    }
 
    public function show($id)
    {
        $item = Farm::find($id);
        if($item ==null){
            return '{}';
        }
        $item->owner_name = "";
        $item->district_name = "";
        if($item->user!=null){
            $item->owner_name = $item->user->name;
        }
        if($item->district!=null){
            $item->district_name = $item->district->name;
        }
        if($item->sub_county!=null){
            $item->sub_county_name = $item->sub_county->name;
        }

        return $item;
    }

    public function create(Request $request) 
    {


        if (
            !isset($request->administrator_id )
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide farm owner."
            ]);
        } 

        if (
            !isset($request->parish_id)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide parish_id."
            ]);
        } 
        
        $f = new Farm(); 
        $f->administrator_id = $request->administrator_id;
        $f->animals_count = $request->animals_count;
        $f->dfm = $request->dfm;
        $f->farm_type = $request->farm_type;
        $f->latitude = $request->latitude;
        $f->longitude = $request->longitude;
        $f->parish_id = $request->parish_id;
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
