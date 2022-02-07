<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Parish;
use App\Models\SubCounty;
use Illuminate\Http\Request;

class UtilsController extends Controller
{
    public function sub_counties()
    {
        $data = [];
        foreach (SubCounty::paginate(1000)->withQueryString()->items() as $key => $value) {
            unset($value->created_at);
            unset($value->updated_at);
            unset($value->detail);
            $value->name .= ", ".$value->name.", ".$value->district->name;
  
            $data[] = $value;
        }
        return $data;
    }

    public function index()
    {
        return District::paginate()->withQueryString()->items();
    }
 
    public function show($id)
    {
        return District::find($id);
    }

    public function store(Request $request)
    {
        return District::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $District = District::findOrFail($id);
        $District->update($request->all());

        return $District;
    }

    public function delete(Request $request, $id)
    {
        $District = District::findOrFail($id);
        $District->delete();

        return 204;
    }
    
}
