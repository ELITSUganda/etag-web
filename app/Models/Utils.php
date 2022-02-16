<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Model;

class Utils extends Model
{
    
    public static function make_movement_qr($model){
        $p_url = url("/print?id=".$model->id);
            $data = "ULITS E-MOVEMENT PERMIT\n".
                    "Applicant: $model->trader_name\n".
                    "Transporter: $model->transporter_name\n".
                    "PERMIT No.: $model->permit_Number\n".
                    "PERMIT Status: $model->status\n".
                    "VERIFICATION URL: $p_url\n";
                
            Utils::make_qr([
                'file_name' => $model->id.".png",
                'data' => $data,
            ]);
    }
    public static function make_qr($opts = [
        'file_name' => '1.png',
        'data' => 'Data',
    ]){
        $url = url('code_maker.php?f=png&s=qr&sf=20&ms=r&md=.8&d='.urlencode($opts['data']));
        $data = file_get_contents($url);
        $myfile = fopen("public/storage/codes/".$opts['file_name'], "w");
        fwrite($myfile, $data); 
        fclose($myfile); 
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

    public static function get_role($u = null){
        $roles = $u->roles;
        if(isset($roles[0])){
            if(isset($roles[0]['slug'])){
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
        if(isset($roles[0])){
            if(isset($roles[0]['slug'])){
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

    public static function get_user_id($request = null)
    {
        if ($request == null) {
            return 0;
        }
        $header = (int)($request->header('user'));
        if ($header < 1) {
            return 0;
        }
        return $header;
    }

    public static function response($data = [])
    {
        $resp['status'] = "1";
        $resp['message'] = "Success";
        $resp['data'] = null;
        if (isset($data['status'])) {
            $resp['status'] = $data['status']."";
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
        if(!isset($data['animal_id'])){
            return false;
        }
        $animal_id = (int)($data['animal_id']);
        
        if($animal_id<1){
            return false;
        }
        $animal = Animal::find($animal_id);
        if($animal==null){
            return false;
        }

        $ArchivedAnimal = new ArchivedAnimal();
        $ArchivedAnimal->owner = "-";
        if(isset($data['event'])){
            $ArchivedAnimal->last_event = $data['event'];
        }
        if(isset($data['details'])){
            $ArchivedAnimal->details = $data['details'];
        }

        if(($animal->farm!=null)){
            if(($animal->farm->owner() !=null)){
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
        if($ArchivedAnimal->save()){
            $animal->delete();
            return true;
        }
        return true;
    }
}
