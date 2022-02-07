<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiLoginController extends Controller
{
    public function create_account(Request $request)
    {
        if (
            $request->username == null ||
            $request->phone_number == null ||
            $request->name == null ||
            $request->password == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide username, password, name and phone number."
            ]);
        }

        $u = Administrator::where('username',$request->username)->first();
        if($u != null){
            return Utils::response([
                'status' => 0,
                'message' => "User with same username already exist."
            ]);
        }
        $u = Administrator::where('email',$request->username)->first();
        if($u != null){
            return Utils::response([
                'status' => 0,
                'message' => "User with same email address already exist."
            ]);
        }
        $u = Administrator::where('phone_number',$request->phone_number)->first();
        if($u != null){
            return Utils::response([
                'status' => 0,
                'message' => "User with same phone number already exist."
            ]);
        }

        $user = new Administrator();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->phone_number = $request->phone_number;
        $user->password = password_hash(trim($request->password),PASSWORD_DEFAULT);
        if(!$user->save()){
            return Utils::response([
                'status' => 0,
                'message' => "Failed to create ACCOUNT."
            ]);
        }
         
        $u = Administrator::where('username',$request->username)->first();
        if($u === null){
            return Utils::response([
                'status' => 0,
                'message' => "Failed to create account. Plase try agains."
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

    public function index(Request $request)
    {
        if (
            $request->username == null ||
            $request->password == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide both username and password."
            ]);
        }

        $user = Administrator::where("username", trim($request->username))->first();
        if ($user == null) {
            $user = Administrator::where("email", trim($request->username))->first();
        }
        if ($user == null) {
            $user = Administrator::where("phone_number", trim($request->phone_number))->first();
        }

        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'message' => "You provided wrong username or email or phone number."
            ]);
        }

        if (password_verify(trim($request->password), $user->password)) {
            unset($user->password);
            $user->is_admin = false;
            if($user->isRole('veterinary')){
                $user->is_admin = true;
            }
            $user->roles = $user->roles; 
            return Utils::response([
                'status' => 1,
                'message' => "Logged in successfully.",
                'data' => $user
            ]);
        }

        return Utils::response([
            'status' => 0,
            'message' => "You entered a wrong password. => "
        ]);
    }
}
