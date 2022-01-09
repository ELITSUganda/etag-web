<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class ApiLoginController extends Controller
{
    public function index(Request $request)
    {
        if (
            $request->username == null ||
            $request->password == null
        )
            return Utils::response([
                'status' => 0,
                'message' => "You must provide both username and password."
            ]);

        $user = Administrator::where("username", trim($request->username))->first();
        if ($user == null) {
            $user = Administrator::where("email", trim($request->username))->first();
        }
        if ($user == null) {
            $user = Administrator::where("phone_number", trim($request->username))->first();
        }

        if ($user == null) {
            return Utils::response([
                'status' => 0,
                'message' => "You provided wrong username or email or phone number."
            ]);
        }

        if(password_verify($request->password,$user->password)){
            unset($user->password);
            $user->roles = $user->roles;
            return Utils::response([
                'status' => 1,
                'message' => "Logged in successfully.",
                'data' => $user
            ]);
        }

        return Utils::response([
            'status' => 0,
            'message' => "You entered a wrong password."
        ]);
    }
}
