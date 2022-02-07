<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiUserController extends Controller
{
    public function index(Request $request)
    {
        $per_page = 100;
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
        }
        return Administrator::paginate($per_page)->withQueryString()->items();
    }

    public function farm($id)
    {

        $f = Administrator::find($id);
        $f->farms_count =  count($f->farms);
        $f->farms = $f->farms;
        $f->animals_count = 0;
        unset($f->password);
        foreach ($f->farms as $key => $value) {
            $f->animals_count += count($value->animals);
        }
        return $f;
    }

    public function show($id)
    {
        return Administrator::find($id);
    }

    public function store(Request $request)
    {
        $ad = new Administrator();
        if (!isset($request->username)) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide username."
            ]);
        }
        $ad->username = $request->username;
        
        $ad->email = $request->username;
        $u = Administrator::where('username', $ad->username)->first();
        if ($u!= null) {
            return Utils::response([
                'status' => 0,
                'message' => "User with same username already exist."
            ]);
        }

        $u = Administrator::where('email', $ad->username)->first();
        if ($u != null) {
            return Utils::response([
                'status' => 0,
                'message' => "User with same email already exist."
            ]);
        }


        if (!isset($request->password)) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide password."
            ]);
        }
        $ad->password = $request->password;

        if (!isset($request->name)) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide name."
            ]);
        }
        $ad->name = $request->name;

        if (!isset($request->gender)) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide gender."
            ]);
        }
        $ad->gender = $request->gender;

        if (isset($request->details)) {
            $ad->details = $request->details;
        }

        if (isset($request->address)) {
            $ad->address = $request->address;
        }

        if (isset($request->nin)) {
            $ad->nin = $request->nin;
        }

        if (isset($request->phone_number)) {
            $ad->phone_number = $request->phone_number;
        }

        $ad->password = password_hash(trim($request->password),PASSWORD_DEFAULT);

        if(!$ad->save()){
            return Utils::response([
                'status' => 0,
                'message' => "Failed to create ACCOUNT."
            ]);
        }
 
        $u = Administrator::where('username',$request->username)->first();
        if($u === null){
            return Utils::response([
                'status' => 0,
                'message' => "User account not found. Plase try agains."
            ]);
        }

        DB::table('admin_role_users')->insert([
            'user_id' => $u->id,
            'role_id' => 3
        ]);
         
        return Utils::response([
            'status' => 1,
            'message' => "Account created successfully.",
            'data' => $u
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
