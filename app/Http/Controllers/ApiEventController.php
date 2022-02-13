<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Event;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiEventController extends Controller
{
    public function index(Request $request)
    {
        $per_page = 100;
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        }
        $items = Event::paginate($per_page)->withQueryString()->items();
        foreach ($items as $key => $value) {
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

        }

        return $items;
    }


    public function show($id)
    {
        $item = Event::find($id); 
        $item->owner_name  = ""; 

        $item->owner_name = "";
        $item->district_name = "";
        $item->created = Carbon::parse($item->created)->toFormattedDateString();

        $item->e_id  = "";
        $item->v_id  = "";
        $item->lhc  = "";
        if ($item->animal != null) {
            if ($item->animal->e_id != null) {
                $item->e_id  = $item->animal->e_id;
                $item->v_id  = $item->animal->v_id;
                $item->lhc  = $item->animal->lhc;
            }
            unset($item->animal);
        } 




        return $item;
    }





    public function store(Request $request)
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

        if($request->type == null){
            return Utils::response([
                'status' => 0,
                'message' => "Event type must be provided.",
            ]);
        }

        return Utils::response([
            'status' => 0,
            'message' => "We are gooD!",
        ]);
        

  
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
