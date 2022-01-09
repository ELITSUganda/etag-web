<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
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
