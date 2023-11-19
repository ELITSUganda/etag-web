<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Model;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zebra_Image;
use Illuminate\Support\Str;

class Utils extends Model
{

    public static function get_finance_report($u)
    {
        /* 
        $cats = FinanceCategory::where([
            'administrator_id' => $u->id,
        ])->get();

        for ($i = 0; $i < 50; $i++) {
            $t = new Transaction();
            $t->finance_category_id = $cats[rand(0, count($cats) - 1)]->id;
            $t->amount = rand(1000, 100000);
            $t->is_income = rand(0, 1);
            $t->description = "Test Transaction $i";
            $transaction_date = Carbon::now()->subDays(rand(0, 365));
            $t->transaction_date = $transaction_date;
            $t->administrator_id = $u->id;
            $t->district_id = 1;
            $t->sub_county_id = 1;
            $t->farm_id = 1;
            $t->save();
        } */


        $data = [];
        $data['total_income'] = 0;
        $data['total_expense'] = 0;
        $data['total_balance'] = 0;
        $data['total_income'] = Transaction::where([
            'is_income' => 1,
            'administrator_id' => $u->id,
        ])->sum('amount');
        $data['total_expense'] = Transaction::where([
            'is_income' => 0,
            'administrator_id' => $u->id,
        ])->sum('amount');

        $start_date = date('2023-01-01');
        $end_date = now();
        $months = Carbon::parse($start_date)->monthsUntil($end_date);
        $monthly_datas = [];
        $data['total_balance'] = $data['total_income'] + $data['total_expense'];



        foreach ($months as $key => $month) {
            $monthly_data['income'] = Transaction::where([
                'is_income' => 1,
                'administrator_id' => $u->id,
            ])->whereMonth('transaction_date', $month->month)->sum('amount');
            $monthly_data['expense'] = Transaction::where([
                'is_income' => 0,
                'administrator_id' => $u->id,
            ])->whereMonth('transaction_date', $month->month)->sum('amount');
            $monthly_data['balance'] = $monthly_data['income'] + $monthly_data['expense'];
            $monthly_data['month'] = $month->format('F');
            $monthly_data['year'] = $month->format('Y');

            //check if is current month
            if ($month->format('Y-m') == now()->format('Y-m')) {
                $data['current_month'] = $monthly_data;
            }else{
                $monthly_datas[] = $monthly_data;
            }
        }

        //reverse months $monthly_datas
        $monthly_datas = array_reverse($monthly_datas);

        $data['monthly_datas'] = $monthly_datas;
        return $data;
    }

    public static function send_message($phone_number, $message)
    {
        if (!Utils::validateUgandanPhoneNumber($phone_number)) {
            return "$phone_number is not a valid phone number.";
        }

        $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=mubaraka&passwd=muh1nd0@2023";
        $url .= "&msg=" . trim($message);
        $url .= "&numbers=" . $phone_number;
        $my_response = "";
        try {
            $result = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    /* 'content' => json_encode($m), */
                ],
            ]));
            if (str_contains($result, 'Send ok')) {
                $my_response = "";
            } else {
                $my_response = "Failed to send sms because " . ((string)$result);
            }
        } catch (\Throwable $th) {
            $my_response = $th->getMessage();
        }
        return $my_response;
    }

    public static function validateUgandanPhoneNumber($phoneNumber)
    {
        $num = Utils::prepareUgandanPhoneNumber($phoneNumber);

        if ($num == '') {
            return false;
        }
        if (strlen($num) < 13) {
            return false;
        }
        if (strlen($num) > 15) {
            return false;
        }
        return true;
    }


    public static function prepareUgandanPhoneNumber($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);
        $phoneNumber = str_replace(' ', '', $phoneNumber);
        if (substr($phoneNumber, 0, 1) == '0') {
            $phoneNumber = substr($phoneNumber, 1);
        } else if (substr($phoneNumber, 0, 3) == '256') {
            $phoneNumber = substr($phoneNumber, 3);
        } else if (substr($phoneNumber, 0, 4) == '+256') {
            $phoneNumber = substr($phoneNumber, 4);
        }
        if (strlen($phoneNumber) < 8) {
            return '';
        }
        $phoneNumber = '+256' . $phoneNumber;
        return $phoneNumber;
        // Remove any non-numeric characters from the phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if the phone number starts with "07", "256", or "+256"
        if (preg_match('/^(07|256|\+256)([1-9]\d+)$/', $phoneNumber, $matches)) {
            // Extract the numeric part
            $numericPart = $matches[2];

            // Standardize the phone number by adding "7" after "0" and "+256" at the beginning
            $standardizedNumber = '+256' . '0' . $numericPart;

            return $standardizedNumber;
        } else {
            // If the phone number does not match the expected format, return it as is
            return $phoneNumber;
        }
    }





    public static function quantity_convertor($qty, $type)
    {
        if ($type == 'Solid') {
            $val = $qty;
            if ($qty > 1000000) {
                $val = $qty / 1000000;
                return number_format($val) . "kg";
            } else if ($qty > 1000) {
                $val = $qty / 1000;
                return number_format($val) . "g";
            }
            return number_format($val) . "mg";
        } else  if ($type == 'Liquid') {
            $val = $qty;
            if ($qty > 1000) {
                $val = $qty / 1000;
                return number_format($val) . "L";
            }
            return number_format($val) . "ml";
        }
    }

    public static function systemBoot($u)
    {
        //get administartors who don't have any group
        $admins = Administrator::whereNotIn('id', function ($query) {
            $query->select('administrator_id')
                ->from('groups');
        })->get();

        //loop and create a group for each admin, make is_main_group = Yes
        foreach ($admins as $key => $admin) {
            $group = new Group();
            $group->administrator_id = $admin->id;
            $group->name = "Main Group";
            $group->is_main_group = 'Yes';
            $group->save();
        }
        //get all animals whose group_id is null
        $animals = Animal::whereNull('group_id')->get();
        //loop and create a group for each animal, make is_main_group = No
        foreach ($animals as $key => $animal) {
            //get main group of this animal administrator_id
            $group = Group::where([
                'administrator_id' => $animal->administrator_id,
                'is_main_group' => 'Yes',
            ])->first();
            if ($group == null) {
                continue;
            }
            $animal->group_id = $group->id;
            $animal->save();
        }

        $arcs = ArchivedAnimal::whereNull('administrator_id')->get();
        foreach ($arcs as $key => $val) {
            $farm = Farm::where([
                'holding_code' => $val->lhc
            ])->first();
            if ($farm == null) {
                continue;
            }
            $val->administrator_id = $farm->administrator_id;
            $val->save();
        }


        if ($u == null) {
            return;
        }
        //Utils::make_profile_pics($u);
        //Utils::transferPhotos($u);
        //Utils::prepareThumbnails();
        Utils::prepareOrders();
        Utils::prepareAverageMilk();
    }



    public static function create_thumbnail($file_path)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        if ($ext == null) {
            return null;
        }
        $ext = strtolower($ext);

        if (!in_array($ext, [
            'jpg',
            'jpeg',
            'png',
            'gif',
        ])) {
            return null;
        }
        $file_name_1 = basename($file_path);
        $file_name_2 = 'temp_' . $file_name_1;


        $image = new Zebra_Image();
        $image->handle_exif_orientation_tag = false;
        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->jpeg_quality = 15;

        $file_path_2 = str_replace($file_name_1, $file_name_2, $file_path);


        $image->auto_handle_exif_orientation = true;
        $image->source_path =  $file_path;
        $image->target_path =  $file_path_2;
        if (!$image->resize(0, 0, ZEBRA_IMAGE_CROP_CENTER)) {
            return null;
        }
        return $file_path_2;
    }


    public static function makeSlug($s)
    {
        $s = Str::slug($s, '-');
        $count = Product::where([
            'slug' => $s
        ])->count();
        if ($count > 0) {
            $s .= "-" . $count;
        }
        return $s;
    }

    public static function un_paid_order_sum($u)
    {
        $count = ProductOrder::where(
            'order_is_paid',
            '!=',
            1
        )->where([
            'customer_id' => $u->id
        ])
            ->sum('total_price');
        return $count;
    }

    public static function get_pending_orders($u)
    {
        $count = ProductOrder::where(
            'order_is_paid',
            '!=',
            1
        )->where([
            'customer_id' => $u->id
        ])
            ->sum('total_price');
        return $count;
    }

    public static function prepareThumbnails()
    {


        $imgs = Image::where([
            'thumbnail' => NULL,
        ])
            ->orderBy('id', 'Desc')
            ->get();
        if (count($imgs) < 1) {
            return;
        }


        $i = 1;
        foreach ($imgs as $key => $img) {
            $i++;
            echo "<h2>$i</h2>";
            $img->create_thumbail();
        }

        //average_milk

    }

    public static function make_profile_pics($u)
    {

        $x = 1;
        $link = $_SERVER['DOCUMENT_ROOT'] . '/storage/images/';

        $files = glob($link . '*JPG');
        foreach ($files as $file) {
            $source  = "$file";
            $e_id = $source;
            $src = str_replace($link, "", $e_id);

            if (!str_contains($src, '(m)')) {
                continue;
            }

            $e_id = str_replace('-(m).JPG', '', $src);

            $animal = Animal::where([
                'e_id' => $e_id
            ])->first();

            if ($animal == null) {
                die("Animal not found.");
            }

            $animal->photo =  'storage/images/' . $src;

            $animal->save();
            echo "<img src=\"$animal->photo\" >";


            echo $e_id . ' <hr>';
        }


        die("Romina");
    }


    public static function transferPhotos($u)
    {

        $x = 1;
        $link = $_SERVER['DOCUMENT_ROOT'] . '/storage/temp/';

        $files = glob($link . '*JPG');
        foreach ($files as $file) {
            $source  = "$file";
            $e_id = $source;
            $src = str_replace($link, "", $e_id);
            $e_id = str_replace($link, "", $e_id);
            $exp = explode(' ', $e_id);
            if (!isset($exp[1])) {
                continue;
            }
            $e_id = trim($exp[0]);
            if (strlen($e_id) < 3) {
                continue;
            }

            $animal = Animal::where([
                'e_id' => $e_id
            ])->first();

            if ($animal == null) {
                echo ("<h2>" . $e_id . " not found.</h2>");
                continue;
            }

            $target = $source;
            $target = str_replace('temp/', 'images/', $target);
            $target = str_replace(' ', '-', $target);
            $src = str_replace(" ", "-", $src);

            $img = Image::where([
                'src' => $src
            ])->first();

            if ($img != null) {
                echo ("image exists on DB <br>");
            } else {
                echo ("NEW image on DB <br>");
                $img = new Image();
                $img->administrator_id = $u->id;
                $img->src = $src;
                $img->thumbnail = null;
                $img->parent_id = $animal->id;
                $img->type = $animal->id;
                $img->type = 'animal';
                $img->note = 'Photo taken on: Sunday - December 18th, 2022';
                $img->parent_endpoint = 'Animal';
                $img->save();
            }

            if (file_exists($target)) {
                echo ("IMAGE exists in files");
            } else {
                echo ("NEW IMAGE in files");
            }

            rename($source, $target);

            echo '<hr>';
        }


        die("Romina");
    }

    public static function prepareOrders()
    {
        foreach (WholesaleOrder::where([
            'status' => 'Processing',
            'processed' => 'No',
        ])->get() as $key => $order) {

            $status = $order->validate_order();
            if ($status != null) {
                continue;
            }
            try {
                WholesaleOrder::do_process_order($order);
                $order->processed = 'Yes';
                $order->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }
    public static function prepareAverageMilk()
    {

        $animals = Animal::where([
            'sex' => 'Female',
            'average_milk' => NULL,
        ])->get();
        if (count($animals) < 1) {
            return;
        }

        foreach ($animals as $key => $animal) {
            $animal->calculateAverageMilk();
        }


        //average_milk

    }

    public static function month($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('M - Y');
    }
    public static function my_day($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M');
    }






    public static function my_time_ago($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->diffForHumans();
    }


    public static function my_date_1($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('D - d M');
    }

    public static function my_date($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y');
    }
    public static function my_date_2($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y - D');
    }

    public static function my_date_time($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y - h:m a');
    }

    public static function to_date_time($raw)
    {
        $t = Carbon::parse($raw);
        if ($t == null) {
            return  "-";
        }
        $my_t = $t->toDateString();

        return $my_t . " " . $t->toTimeString();
    }
    public static function number_format($num, $unit)
    {
        $num = (int)($num);
        $resp = number_format($num);
        if ($num < 2) {
            $resp .= " " . $unit;
        } else {
            $resp .= " " . Str::plural($unit);
        }
        return $resp;
    }


    public static function get_object($class, $id)
    {
        $data = $class::find($id);
        if ($data != null) {
            return $data;
        }
        return new $class();
    }


    public static function phone_number_is_valid($phone_number)
    {
        $phone_number = Utils::prepare_phone_number($phone_number);
        if (substr($phone_number, 0, 4) != "+256") {
            return false;
        }

        if (strlen($phone_number) != 13) {
            return false;
        }

        return true;
    }
    public static function prepare_phone_number($phone_number)
    {
        $original = $phone_number;
        //$phone_number = '+256783204665';
        //0783204665
        if (strlen($phone_number) > 10) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (substr($phone_number, 0, 1) == "0") {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }
        if (strlen($phone_number) != 9) {
            return $original;
        }
        return "+256" . $phone_number;
    }


    public static function display_alert_message()
    {
        Utils::start_session();
        if (isset($_SESSION['alerts'])) {
            if ($_SESSION['alerts'] != null) {
                foreach ($_SESSION['alerts'] as $key => $v) {
                    if (isset($v['type']) && isset($v['msg'])) {
                        echo view('components.alert', $v);
                    }
                }
            }
            $_SESSION['alerts'] == null;
            unset($_SESSION['alerts']);
        }
    }

    public static function alert_message($type, $msg)
    {
        Utils::start_session();
        $alert['type'] = $type;
        $alert['msg'] = $msg;
        $_SESSION['alerts'][] = $alert;
    }

    public static function start_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public static function get_file_url($name)
    {
        $url = url("/storage");
        if ($name == null) {
            $url .= '/default.png';
            return $url;
        }
        if ($name == null || (strlen($name) < 2)) {
        } else if (file_exists(public_path('storage/' . $name))) {
            $url .= "/" . $name;
        } else {
            $url .= '/default.png';
        }
        return $url;
    }

    public static function make_movement_qr($model)
    {
        $p_url = url("/print?id=" . $model->id);
        $data = $model->id;

        $data = "ULITS E-MOVEMENT PERMIT\n" .
            "Applicant: $model->trader_name\n" .
            "Transporter: $model->transporter_name\n" .
            "PERMIT No.: $model->permit_Number\n" .
            "PERMIT Status: $model->status\n" .
            "VERIFICATION URL: $p_url\n";
        return $data;

        Utils::make_qr([
            'file_name' => $model->id . ".png",
            'data' => $data,
        ]);
    }
    public static function make_qr($opts = [
        'file_name' => '1.png',
        'data' => 'Data',
    ])
    {
        $url = url('code_maker.php?f=png&s=qr&sf=20&ms=r&md=.8&d=' . urlencode($opts['data']));
        $data = file_get_contents($url);
        //$myfile = fopen("public/storage/codes/" . $opts['file_name'], "w");
        //fwrite($myfile, $data);
        //fclose($myfile);
    }

    public static function move_animal($transfer = [])
    {
        if (
            isset($transfer['animal_id']) &&
            isset($transfer['destination_farm_id'])
        ) {
            $animal = Animal::find($transfer['animal_id']);
            $farm = Farm::find($transfer['destination_farm_id']);
            if (
                $animal != null &&
                $farm != null
            ) {
                $animal->administrator_id = $farm->administrator_id;
                $animal->district_id = $farm->district_id;
                $animal->sub_county_id = $farm->sub_county_id;
                $animal->lhc = $farm->holding_code;
                $animal->farm_id = $farm->id;
                if ($animal->save()) {
                    $event = new Event();
                    $event->administrator_id = $animal->administrator_id;
                    $event->district_id = $animal->district_id;
                    $event->sub_county_id = $animal->sub_county_id;
                    $event->farm_id = $animal->farm_id;
                    $event->animal_id = $animal->id;
                    $event->type = "Moved";
                    $event->approved_by = Admin::user()->id;
                    $event->detail = "Animal moved to LHC " . $animal->lhc;
                    $event->disease_id = null;
                    $event->vaccine_id = null;
                    $event->medicine_id = null;
                    $event->save();
                }
            }
        }
    }

    public static function get_role($u = null)
    {
        if ($u == null) {
            return  "";
        }
        $roles = $u->roles;
        if (isset($roles[0])) {
            if (isset($roles[0]['slug'])) {
                return $roles[0]['slug'];
            }
        }
        return "";
    }
    public static function is_admin($request = null)
    {
        if ($request == null) {
            return false;
        }
        $header = (int)($request->header('user'));
        if ($header < 1) {
            return false;
        }
        $u = Administrator::find($header);
        if ($u == null) {
            return false;
        }

        $roles = $u->roles;
        if (isset($roles[0])) {
            if (isset($roles[0]['slug'])) {
                return $roles[0]['slug'];
            }
        }

        return "";

        if (!$u->isRole('veterinary')) {
            return  false;
        }

        if (!$u->isRole('veterinary')) {
            return  false;
        }

        if ($u->isRole('veterinary')) {
            return  true;
        }
        return  false;
    }

    public static function get_status($s = null)
    {
        if ($s == null) {
            return "";
        } else if ($s == "Pending") {
            return '<a href="badge badge-warning"></a>';
        }

        return "";
    }

    public static function get_drug_status($s = 0)
    {
        if ($s == 0) {
            return '<span class="badge badge-warning">pending</span>';
        } else if ($s == 1) {
            return '<span class="badge badge-success">approved</span>';
        } else {
            return '<span class="badge badge-warning">pending</span>';
        }
        return "";
    }

    public static function get_user_id($request = null)
    {
        if ($request == null) {
            return 0;
        }
        $header = (int)($request->header('user'));
        if ($header < 1) {
            $header = (int)($request->user);
        }
        if ($header < 1) {
            return 0;
        }
        return $header;
    }

    public static function response($data = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        $resp['status'] = "1";
        $resp['message'] = "Success";
        $resp['data'] = null;
        if (isset($data['status'])) {
            $resp['status'] = $data['status'] . "";
        }
        if (isset($data['message'])) {
            $resp['message'] = $data['message'];
        }
        if (isset($data['data'])) {
            $resp['data'] = $data['data'];
        }
        return $resp;
    }

    public static function archive_animal($data = [])
    {
        if (!isset($data['animal_id'])) {
            return false;
        }
        $animal_id = (int)($data['animal_id']);

        if ($animal_id < 1) {
            return false;
        }
        $animal = Animal::find($animal_id);
        if ($animal == null) {
            return false;
        }

        $ArchivedAnimal = new ArchivedAnimal();
        $ArchivedAnimal->owner = "-";
        if (isset($data['event'])) {
            $ArchivedAnimal->last_event = $data['event'];
        }
        if (isset($data['details'])) {
            $ArchivedAnimal->details = $data['details'];
        }

        if (isset($data['reason'])) {
            $ArchivedAnimal->last_event = $data['reason'];
        }


        if (($animal->farm != null)) {
            if (($animal->farm->owner() != null)) {
                try {
                    $ArchivedAnimal->owner = $animal->farm->owner()->name;
                    $ArchivedAnimal->district = $animal->farm->district->name;
                    $ArchivedAnimal->sub_county = $animal->farm->sub_county->name;
                } catch (Exception $x) {

                    $ArchivedAnimal->owner = 'N/A';
                    $ArchivedAnimal->district = '1';
                    $ArchivedAnimal->sub_county = '1';
                }
            }
        }
        $ArchivedAnimal->type = $animal->type;
        $ArchivedAnimal->e_id = $animal->e_id;
        $ArchivedAnimal->v_id = $animal->v_id;
        $ArchivedAnimal->lhc = $animal->lhc;
        $ArchivedAnimal->breed = $animal->breed;
        $ArchivedAnimal->sex = $animal->sex;
        $ArchivedAnimal->dob = $animal->dob;
        $ArchivedAnimal->administrator_id = $animal->administrator_id;

        $ArchivedAnimal->events = json_encode($animal->events);
        if ($ArchivedAnimal->save()) {
            Event::where([
                'animal_id' => $animal_id
            ])->delete();
            $animal->delete();
            return true;
        }
        return true;
    }


    public static function create_thumbail($params = array())
    {

        ini_set('memory_limit', '-1');

        if (
            !isset($params['source']) ||
            !isset($params['target'])
        ) {
            return [];
        }



        if (!file_exists($params['source'])) {
            $img = url('assets/images/cow.jpeg');
            return $img;
        }


        $image = new Zebra_Image();

        $image->auto_handle_exif_orientation = true;
        $image->source_path = "" . $params['source'];
        $image->target_path = "" . $params['target'];


        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        }

        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->handle_exif_orientation_tag = true;

        $img_size = getimagesize($image->source_path); // returns an array that is filled with info





        $image->jpeg_quality = 50;
        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        } else {
            $image->jpeg_quality = Utils::get_jpeg_quality(filesize($image->source_path));
        }
        if (!$image->resize(0, 0, ZEBRA_IMAGE_CROP_CENTER)) {
            return $image->source_path;
        } else {
            return $image->target_path;
        }
    }

    public static function get_jpeg_quality($_size)
    {
        $size = ($_size / 1000000);

        $qt = 50;
        if ($size > 5) {
            $qt = 10;
        } else if ($size > 4) {
            $qt = 10;
        } else if ($size > 2) {
            $qt = 10;
        } else if ($size > 1) {
            $qt = 11;
        } else if ($size > 0.8) {
            $qt = 11;
        } else if ($size > .5) {
            $qt = 12;
        } else {
            $qt = 15;
        }

        return $qt;
    }

    public static function process_images_in_backround()
    {
        $url = url('api/process-pending-images');
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        try {
            $data =  file_get_contents($url, null, $ctx);
            return $data;
        } catch (Exception $x) {
            return "Failed $url";
        }
    }

    public static function process_images_in_foreround()
    {
        $imgs = Image::where([
            'thumbnail' => null
        ])->get();

        foreach ($imgs as $img) {
            $thumb = Utils::create_thumbail([
                'source' => 'public/storage/images/' . $img->src,
                'target' => 'public/storage/images/thumb_' . $img->src,
            ]);
            if ($thumb != null) {
                if (strlen($thumb) > 4) {
                    $img->thumbnail = $thumb;
                    $img->save();
                }
            }
        }
    }



    public static function upload_images_2($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $destination = Utils::docs_root() . '/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }




    public static function upload_images_1($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $destination = 'public/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }


    public static function docs_root()
    {
        $r = $_SERVER['DOCUMENT_ROOT'] . "";

        if (!str_contains($r, 'home/')) {
            $r = str_replace('/public', "", $r);
            $r = str_replace('\public', "", $r);
        }

        $r = $r . "/public";

        /* 
         "/home/ulitscom_html/public/storage/images/956000011639246-(m).JPG
        
        public_html/public/storage/images
        */
        return $r;
    }

    public static function sendNotification(
        $msg,
        $receiver,
        $headings = 'U-LITS',
        $data = null,
        $url = null,
        $buttons = null,
        $schedule = null,
    ) {


        try {
            \OneSignal::addParams(
                [
                    'android_channel_id' => 'f3469729-c2b4-4fce-89da-78550d5a2dd1',
                    'large_icon' => 'https://u-lits.com/logo-1.png',
                    'small_icon' => 'logo_1',
                ]
            )
                ->sendNotificationToExternalUser(
                    $msg,
                    "$receiver",
                    $url = $url,
                    $data = $data,
                    $buttons = $buttons,
                    $schedule = $schedule,
                    $headings = $headings
                );
        } catch (\Throwable $th) {
            //throw $th;
        }


        return;
    }
    public static function getTableColumns($obj)
    {
        $table = $obj->getTable();


        $cols = DB::getSchemaBuilder()->getColumnListing($table);
        if ($cols == null) {
            $cols = [];
        }
        if (!is_array($cols)) {
            $cols = [];
        }
        return  $cols;

        // OR

        return Schema::getColumnListing($table);
    }
}
