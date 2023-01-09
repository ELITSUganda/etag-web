<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MarketController extends Controller
{
    public function index(Request $r)
    {
        $isPjax = false;
        if ($r->headers->get('X-PJAX') != null) {
            $isPjax = true;
        }
        $products = Product::all();
        return  view('market.index', [
            'isPjax' => $isPjax,
            'products' => $products,
        ]);
    }

    public function buy_now(Request $r)
    {

        if (!Auth::guard()->check()) {
            return redirect(route('m-register'));
        }

        return  view('market.buy-now', [
            'isPjax' => false,
            'layoutsParams' => [
                'disableSidebar' => false,
            ]
        ]);
    }

    public function account_orders(Request $r)
    {

        if (!Auth::guard()->check()) {
            return redirect(route('m-register'));
        }

        return  view('market.account-orders', [
            'layoutsParams' => [
                'disableSidebar' => false,
            ]
        ]);
    }

    public function account_logout(Request $r)
    {
        Auth::logout();
        Utils::alert_message('success', 'Account logged out successfully');
        return redirect(route('market'));
    }
    public function register(Request $r)
    {
        if (Auth::guard()->check()) {
            return redirect(route('account-orders'));
        }

        if ($_SERVER['HTTP_REFERER'] != null && strlen($_SERVER['HTTP_REFERER']) > 5) {
            if (!str_contains($_SERVER['HTTP_REFERER'], 'register')) {
                if (!str_contains($_SERVER['HTTP_REFERER'], 'login')) {
                    Utils::start_session();
                    $pending_for_redirect = $_SERVER['HTTP_REFERER'];
                    $_SESSION['pending_for_redirect'] = $pending_for_redirect;
                }
            }
        }


        return  view('market.register', [
            'layoutsParams' => [
                'disableSidebar' => true
            ]
        ]);
    }

    public function register_post(Request $r)
    {


        if (Validator::make($_POST, [
            'first_name' => 'required|string|min:4'
        ])->fails()) {
            return back()
                ->withErrors(['first_name' => 'Enter your valid first name.'])
                ->withInput();
        }

        if (Validator::make($_POST, [
            'last_name' => 'required|string|min:4'
        ])->fails()) {
            return back()
                ->withErrors(['last_name' => 'Enter your valid last name.'])
                ->withInput();
        }

        if (Validator::make($_POST, [
            'phone_number' => 'required',
        ])->fails()) {
            return back()
                ->withErrors(['phone_number' => 'Enter a valid phone number.'])
                ->withInput();
        }

        if (Validator::make($_POST, [
            'password' => 'required|min:2'
        ])->fails()) {
            return back()
                ->withErrors(['password' => 'Enter password with more than 3 chracters.'])
                ->withInput();
        }

        if (Validator::make($_POST, [
            'password_1' => 'required|min:2'
        ])->fails()) {
            return back()
                ->withErrors(['password_1' => 'Enter password with more than 3 chracters.'])
                ->withInput();
        }

        if ($r->password != $r->password_1) {
            return back()
                ->withErrors(['password_1' => 'Confirmation password did not match.'])
                ->withInput();
        }


        if (!Utils::phone_number_is_valid($r->phone_number)) {
            return back()
                ->withErrors(['phone_number' => 'Enter a valid uganda phone number.'])
                ->withInput();
        }

        $phone_number = Utils::prepare_phone_number($r->phone_number);



        $u = Administrator::where([
            'phone_number' => $phone_number
        ])->orwhere([
            'username' => $phone_number
        ])->first();

        if ($u != null) {
            return back()
                ->withErrors(['phone_number' => 'User with same phone number already exist. Please login if you already have account.'])
                ->withInput();
        }

        $user = new Administrator();

        $user->name = $r->first_name . " " . $r->last_name;
        $user->first_name = $r->first_name;
        $user->last_name = $r->last_name;
        $user->username = $phone_number;
        $user->phone_number = $phone_number;
        $user->address = '';
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        if (!$user->save()) {
            return back()->withInput()->withErrors([
                'phone_number' => "Failed to create account. Please try again.",
            ]);
        }

        $u = Administrator::where('phone_number', $phone_number)->first();
        if ($u == null) {
            return back()->withInput()->withErrors([
                'phone_number' => "Failed to create account. Plase try agains.",
            ]);
        }


        DB::table('admin_role_users')->insert([
            'user_id' => $u->id,
            'role_id' => 15
        ]);

        $credentials['username'] = $phone_number;
        $credentials['password'] = $r->password;


        if (Auth::attempt($credentials, true)) {

            Utils::start_session();

            Utils::alert_message('success', 'Account created successfully');

            $pending_for_redirect = $_SESSION['pending_for_redirect'];
            if (strlen($pending_for_redirect) > 5) {
                $_SESSION['pending_for_redirect'] = null;
                unset($_SESSION['pending_for_redirect']);
                return redirect($pending_for_redirect);
            }


            return redirect(route('account-orders'));
        }

        return back()->withInput()->withErrors([
            'phone_number' => "Failed to login your account. Plase try agains.",
        ]);
    }



    public function product(Request $r, $slug)
    {

        $animal = Product::where([
            'slug' => $slug
        ])->first();

        $isPjax = false;
        if ($r->headers->get('X-PJAX') != null) {
            $isPjax = true;
        }

        if ($animal != null) {
            return  view('market.product', [
                'isPjax' => $isPjax,
                'product' => $animal,
            ]);
        }
        die("Page not found.");
    }
}
