<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Hamcrest\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApiLoginController extends Controller
{
    public function update_roles(Request $r)
    {

        if (
            $r->roles == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Roles not found."
            ]);
        }

        $administrator_id = ((int) (Utils::get_user_id($r)));
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found."
            ]);
        }

        $new_roles = [];

        try {
            $new_roles = json_decode($r->roles);
        } catch (Throwable $t) {
            $new_roles = [];
        }

        if (in_array('Veterinary', $new_roles)) {
            $u->createVetProfile();
        } else {
            $u->removeVetProfile();
        }

        return Utils::response([
            'status' => 1,
            'message' => "Roles updated successfully.",
            'data' => null
        ]);
    }
    public function create_account(Request $request)
    {
        if (
            $request->name == null ||
            $request->phone_number == null ||
            $request->password == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide your name, phone number and  password."
            ]);
        }

        if (!Utils::phone_number_is_valid($request->phone_number)) {
            return Utils::response([
                'status' => 0,
                'message' => "Provide a valid phone number."
            ]);
        }

        $phone_number =  Utils::prepare_phone_number($request->phone_number);

        $u = Administrator::where('phone_number', $phone_number)->first();
        if ($u != null) {
            return Utils::response([
                'status' => 0,
                'message' => "User with same phone number already exist."
            ]);
        }

        $u = Administrator::where('username', $phone_number)->first();
        if ($u != null) {
            return Utils::response([
                'status' => 0,
                'message' => "User with same username already exist."
            ]);
        }
        $u = Administrator::where('email', $phone_number)->first();
        if ($u != null) {
            return Utils::response([
                'status' => 0,
                'message' => "User with same email address already exist."
            ]);
        }


        $user = new Administrator();

        $user->name = $request->name;
        $user->username = $phone_number;
        $user->phone_number = $phone_number;
        $user->email = '';
        $user->address = '';
        $user->password = password_hash(trim($request->password), PASSWORD_DEFAULT);
        if (!$user->save()) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to create account."
            ]);
        }

        $u = Administrator::where('phone_number', $phone_number)->first();
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to find account. Plase try agains."
            ]);
        }


        DB::table('admin_role_users')->insert([
            'user_id' => $u->id,
            'role_id' => 3
        ]);

        $u = Administrator::where('phone_number', $phone_number)->first();
        $u->role = 'farmer';


        return Utils::response([
            'status' => 1,
            'message' => "Account created successfully.",
            'data' => $u
        ]);
    }

    public function index(Request $request)
    {
        if (
            $request->password == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide password. anjane",
                'data' => $_POST
            ]);
        }

        $user = null;

        if (
            $request->username != null
        ) {

            $user = Administrator::where("username", trim($request->username))->first();
            if ($user == null) {
                $user = Administrator::where("email", trim($request->username))->first();
            }
            if ($user == null) {
                $user = Administrator::where("phone_number", Utils::prepare_phone_number(trim($request->username)))->first();
            }
        }

        if ($user == null) {
            if (
                $request->phone_number != null
            ) {

                $phone_number = Utils::prepare_phone_number($request->phone_number);

                $user = Administrator::where("phone_number", trim($phone_number))->first();
                if ($user == null) {
                    $user = Administrator::where("username", trim($phone_number))->first();
                }
            }
        }


        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'message' => "You provided wrong credentials. Please contact us to re-set your password."
            ]);
        }

        if (password_verify(trim($request->password), $user->password)) {
            unset($user->password);
            $user->role =  Utils::get_role($user);
            $user->roles = $user->roles;
            return Utils::response([
                'status' => 1,
                'message' => "Logged in successfully.",
                'data' => $user
            ]);
        }

        return Utils::response([
            'status' => 0,
            'message' => "You provided wring passwrd.",
            'data' => null
        ]);
    }
}
