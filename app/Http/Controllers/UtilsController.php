<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Parish;
use Illuminate\Http\Request;

class UtilsController extends Controller
{
    public function parishes()
    {
        $data = [];
        foreach (Parish::paginate(1000)->withQueryString()->items() as $key => $value) {
            unset($value->created_at);
            unset($value->updated_at);
            unset($value->detail);
            $value->name .= ", ".$value->sub_county->name.", ".$value->sub_county->district->name;
            unset($value->sub_county);
            unset($value->sub_county_id);
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
