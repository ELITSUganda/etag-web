<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiAnimalController extends Controller
{
    public function index(Request $request)
    { 
        $per_page = 100;
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        }
        $items = Animal::paginate($per_page)->withQueryString()->items();
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


        }

        return $items;
    }
 
    public function farm($id){
    
        $f = Administrator::find($id);
        $f->farms_count =  count($f->farms);
        $f->farms = $f->farms;
        $f->animals_count = 0;
        unset($f->password);
        foreach ($f->farms as $key => $value) {
            $f->animals_count+=count($value->animals);
        }
        return $f;
    }
 
    public function show($id)
    {
        return Administrator::find($id);
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
