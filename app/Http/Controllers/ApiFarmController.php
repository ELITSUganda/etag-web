<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Utils;
use Illuminate\Http\Request;

class ApiFarmController extends Controller
{
    public function index()
    {
        return Farm::paginate(1000)->withQueryString()->items();
    }
 
    public function show($id)
    {
        return Farm::find($id);
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
