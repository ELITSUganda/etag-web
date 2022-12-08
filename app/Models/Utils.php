<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Model;
use Exception;
use Illuminate\Support\Facades\DB;
use Zebra_Image;
use Illuminate\Support\Str;

class Utils extends Model
{



    public static function systemBoot($u){
        if($u == null){
            return;
        }
        Utils::prepareAverageMilk();
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

        if (strlen($phone_number) == 14) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = str_replace("256", "", $phone_number);
        }


        if (strlen($phone_number) > 11) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (strlen($phone_number) == 10) {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }


        if (strlen($phone_number) != 9) {
            return $phone_number;
        }

        $phone_number = "+256" . $phone_number;
        return $phone_number;
    }



    public static function display_alert_message()
    {
        Utils::start_session();
        if (isset($_SESSION['alerts'])) {
            if ($_SESSION['alerts'] != null) {
                foreach ($_SESSION['alerts'] as $key => $v) {
                    if (isset($v['type']) && isset($v['msg'])) {
                        if ($v['type'] == 'danger') {
                            admin_error('Warning', $v['msg']);
                        } else if ($v['type'] == 'success') {
                            admin_success('Success!', $v['msg']);
                        } else {
                            admin_info('Alert', $v['msg']);
                        }
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

        if (($animal->farm != null)) {
            if (($animal->farm->owner() != null)) {
                $ArchivedAnimal->owner = $animal->farm->owner()->name;
                $ArchivedAnimal->district = $animal->farm->district->name;
                $ArchivedAnimal->sub_county = $animal->farm->sub_county->name;
            }
        }
        $ArchivedAnimal->type = $animal->type;
        $ArchivedAnimal->e_id = $animal->e_id;
        $ArchivedAnimal->v_id = $animal->v_id;
        $ArchivedAnimal->lhc = $animal->lhc;
        $ArchivedAnimal->breed = $animal->breed;
        $ArchivedAnimal->sex = $animal->sex;
        $ArchivedAnimal->dob = $animal->dob;
        $ArchivedAnimal->events = json_encode($animal->events);
        if ($ArchivedAnimal->save()) {
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

        $image->auto_handle_exif_orientation = false;
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

        $width = 300;
        $heigt = 300;

        if (isset($img_size[0]) && isset($img_size[1])) {
            $width = $img_size[0];
            $heigt = $img_size[1];
        }
        //dd("W: $width \n H: $heigt");

        if ($width < $heigt) {
            $heigt = $width;
        } else {
            $width = $heigt;
        }

        if (isset($params['width'])) {
            $width = $params['width'];
        }

        if (isset($params['heigt'])) {
            $width = $params['heigt'];
        }

        $image->jpeg_quality = 50;
        $image->jpeg_quality = Utils::get_jpeg_quality(filesize($image->source_path));
        if (!$image->resize($width, $heigt, ZEBRA_IMAGE_CROP_CENTER)) {
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
            $qt = 13;
        } else if ($size > 2) {
            $qt = 15;
        } else if ($size > 1) {
            $qt = 17;
        } else if ($size > 0.8) {
            $qt = 50;
        } else if ($size > .5) {
            $qt = 80;
        } else {
            $qt = 90;
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


    public static function docs_root($params = array())
    {
        $r = $_SERVER['DOCUMENT_ROOT'] . "";
        $r = str_replace('/public', "", $r);
        $r = $r . "/public";
        return $r;
    }
}
