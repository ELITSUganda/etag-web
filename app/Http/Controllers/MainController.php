<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }


    function process_photos()
    {
        set_time_limit(-1);
        $i = 1;
        $dir = public_path("storage/images/"); // replace with your directory path
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != ".." && $file != '.DS_Store') {
                        $original_file = $dir . $file;
                        if (!file_exists($original_file)) {
                            continue;
                        }
                        $isImage = false;
                        try {
                            $image_data =  getimagesize($original_file);
                            if ($image_data == null) {
                                $isImage = false;
                            }
                            if (
                                isset($image_data[0]) &&
                                isset($image_data[1]) &&
                                isset($image_data[2]) &&
                                isset($image_data[3])
                            ) {
                                $isImage = true;
                            }

                            if (!$isImage) {
                                continue;
                            }

                            $fileSizeInBytes = 0;
                            try {
                                $fileSizeInBytes = filesize($original_file);
                                $fileSizeInBytes = $fileSizeInBytes / 1000000;
                            } catch (\Throwable $th) {
                            }
                            if ($fileSizeInBytes < 1) {
                                continue;
                            }


                            $thumb =  Utils::create_thumbnail($original_file);
                            if ($thumb == null) {
                                continue;
                            } 

                            if (!file_exists($thumb)) {
                                echo "========THUMB DNE!============";
                                continue;
                            }



                            echo  $i . '<=== <img src="' . url('storage/images/' . $file) . '" width="300" /><br>';
                            $i++;
                            rename($thumb, $original_file);

                            if (file_exists($thumb)) {
                                unlink($thumb); 
                                continue;
                            }

                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                }
                closedir($dh);
            }
        }

        die("done");
    }




    public function create_account_save(Request $request)
    {


        if (
            $request->phone_number == null ||
            $request->name == null ||
            $request->password == null
        ) {

            return back()->withInput()->withErrors([
                'name' => "You must provide username, password, name and phone number.",
            ]);
        }

        if ($request->password != $request->password_2) {
            return back()->withInput()->withErrors([
                'password' => "Passwords did not match.",
            ]);
        }

        if (!Utils::phone_number_is_valid($request->phone_number)) {
            return back()->withInput()->withErrors([
                'phone_number' => "Please enter valid phone number.",
            ]);
        }

        $request->phone_number = Utils::prepare_phone_number($request->phone_number);
        $request->username = $request->phone_number;


        $u = Administrator::where('username', $request->username)->first();
        if ($u != null) {
            return back()->withInput()->withErrors([
                'phone_number' => "User with same username already exist.",
            ]);
        }

        $u = Administrator::where('email', $request->username)->first();
        if ($u != null) {
            return back()->withInput()->withErrors([
                'phone_number' => "User with same phone number already exist.",
            ]);
        }

        $u = Administrator::where('phone_number', $request->phone_number)->first();
        if ($u != null) {
            return back()->withInput()->withErrors([
                'phone_number' => "User with same phone number already exist.",
            ]);
        }

        $user = new Administrator();

        $user->name = $request->name;
        $user->username = $request->username;
        $user->phone_number = $request->phone_number;
        $user->address = $request->address;
        $user->password = password_hash(trim($request->password), PASSWORD_DEFAULT);
        if (!$user->save()) {
            return back()->withInput()->withErrors([
                'name' => "Failed to create account. Please try again.",
            ]);
        }

        $u = Administrator::where('username', $request->username)->first();
        if ($u === null) {
            return back()->withInput()->withErrors([
                'name' => "Failed to create account. Plase try agains.",
            ]);
        }


        DB::table('admin_role_users')->insert([
            'user_id' => $u->id,
            'role_id' => 12
        ]);



        $remember = $request->get('remember', true);

        $credentials = $request->only(['username', 'password']);
        if ($this->guard()->attempt($credentials, $remember)) {
            return redirect(admin_url('/'));
        }

        return redirect(admin_url('auth/login'));
    }

    public function create_account_page(Request $request)
    {
        return view('main.register');
        if (
            $request->username == null ||
            $request->password == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide both username and password. anjane",
                'data' => $_POST
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
            'message' => "You entered a wrong password. => "
        ]);
    }
}
