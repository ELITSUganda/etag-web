<?php

namespace App\Http\Controllers;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiUserController extends Controller
{
    public function index(Request $request)
    {
        $per_page = 100;
        if(isset($request->per_page)){
            $per_page = $request->per_page;
        }
        return Administrator::paginate($per_page)->withQueryString()->items();
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
