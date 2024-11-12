<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
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

    public function buy_now_post(Request $r, $product_id)
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
                ->withErrors(['first_name' => 'Enter your valid last name.'])
                ->withInput();
        }


        if (!Utils::phone_number_is_valid($r->phone_number)) {
            return back()
                ->withErrors(['phone_number' => 'Enter a valid uganda phone number.'])
                ->withInput();
        }

        $phone_number_2 = "";
        $phone_number = Utils::prepare_phone_number($r->phone_number);
        if (Utils::phone_number_is_valid($r->phone_number_2)) {
            $phone_number_2 = Utils::prepare_phone_number($r->phone_number_2);
        }

        $u = Auth::user();
        if (!empty($phone_number_2)) {
            $u->phone_number_2 = $phone_number_2;
        }

        if (!empty($phone_number_2)) {
            $u->phone_number_2 = $phone_number_2;
        }
        if (!empty($r->address)) {
            $u->address = $r->address;
        }
        $u->save();

        if (Validator::make($_POST, [
            'address' => 'required|string|min:4'
        ])->fails()) {
            return back()
                ->withErrors(['address' => 'Enter a correct address.'])
                ->withInput();
        }
        $product = Product::findOrFail($product_id);
        $order = new ProductOrder();
        $order->name = $r->first_name . " " . $order->last_name;
        $order->phone_number = $phone_number;
        $order->phone_number_2 = $phone_number_2;
        $order->address = $r->address;
        $order->note = $r->order_note;
        $order->status = 1;
        $order->latitude = 0.;
        $order->product_id = $product_id;
        $order->customer_id = Auth::user()->id;
        $order->customer_data = json_encode($product);
        $order->total_price = $product->price;
        $order->save();

        Utils::alert_message('success', 'Account created successfully');
        return redirect(route('account-orders'));
    }
    public function buy_now(Request $r, $id)
    {
        if (!Auth::guard()->check()) {
            return redirect(route('m-register'));
        }

        $product = Product::findOrFail($id);

        return  view('market.buy-now', [
            'isPjax' => false,
            'product' => $product,
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

        try {
            $user->save();
        } catch (\Throwable $th) {
            return back()->withInput()->withErrors([
                'phone_number' => "Failed to create account because " . $th->getMessage(),
            ]);
        }

        $u = User::where('phone_number', $phone_number)->first();
        if ($u == null) {
            return back()->withInput()->withErrors([
                'phone_number' => "Failed to create account for $phone_number.",
            ]);
        }


        DB::table('admin_role_users')->insert([
            'user_id' => $u->id,
            'role_id' => 15
        ]);

        $credentials['phone_number'] = $phone_number;
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
            'phone_number' => "Account created but failed to login with  password $r->password.",
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


    function generate_variables()
    {
        $data = ' created_at
        administrator_id
        business_subcounty_id
        business_district_id
        verified
        business_name
        business_cover_photo
        business_logo
        business_phone_number_1
        business_phone_number_2
        business_email
        business_address
        business_about
        license
        ';

        $recs = preg_split('/\r\n|\n\r|\r|\n/', $data);


        MarketController::fromJson($recs);
        MarketController::from_json($recs);

        MarketController::create_table($recs, 'logged_in_user');

        //MainController::to_json($recs);
        // MainController::generate_vars($recs);
    }


    function fromJson($recs)
    {

        $_data = "";

        foreach ($recs as $v) {
            $key = trim($v);

            if ($key == 'id') {
                $_data .= "obj.{$key} = Utils.int_parse(m['{$key}']);<br>";
            } else {
                $_data .= "obj.{$key} = Utils.toStr(m['{$key}'],'');<br>";
            }
        }

        print_r($_data);
        die("");
    }



    function create_table($recs, $table_name)
    {

        $_data = "CREATE TABLE  IF NOT EXISTS  $table_name (  ";
        $i = 0;
        $len = count($recs);
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 2) {
                continue;
            }

            if ($key == 'id') {
                $_data .= 'id INTEGER PRIMARY KEY';
            } else {
                $_data .= " $key TEXT";
            }

            $i++;
            if ($i != $len) {
                $_data .= ',';
            }
        }

        $_data .= ')';
        print_r($_data);
        die("");
    }


    function from_json($recs)
    {

        $_data = "";
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 2) {
                continue;
            }
            $_data .= '"' . $key . '"' . " : $key,<br>";
        }

        echo "<pre>";
        print_r($_data);
        die("");
    }


    function to_json($recs)
    {
        $_data = "";
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 2) {
                continue;
            }
            $_data .= "'$key' : $key,<br>";
        }

        echo "<pre>";
        print_r($_data);
        die("");
    }

    function generate_vars($recs)
    {

        $_data = "";
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 2) {
                continue;
            }
            $_data .= "String $key = \"\";<br>";
        }

        echo "<pre>";
        print_r($_data);
        die("");
    }
}
