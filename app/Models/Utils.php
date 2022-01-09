<?php

namespace App\Models;

use Encore\Admin\Grid\Model;

class Utils extends Model
{
    public static function response($data = []){
        $resp['satus'] = 1;
        $resp['message'] = "Success";
        $resp['data'] = null;
        if(isset($data['satus'] )){
            $resp['satus'] = $data['satus'];
        }
        if(isset($data['message'] )){
            $resp['message'] = $data['message'];
        }
        if(isset($data['data'] )){
            $resp['data'] = $data['data'];
        }
        return $resp;
    }
}
