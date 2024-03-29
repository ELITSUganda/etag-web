<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Parish;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index()
    {
        return District::paginate(1000)->withQueryString()->items();
    }

    public function parishes()
    {
        return Parish::paginate(10000)->withQueryString()->items();
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
