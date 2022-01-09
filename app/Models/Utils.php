<?php

namespace App\Models;

use Encore\Admin\Grid\Model;

class Utils extends Model
{
    public static function response($data = []){
        $resp['status'] = 1;
        $resp['message'] = "Success";
        $resp['data'] = null;
        if(isset($data['status'] )){
            $resp['status'] = $data['status'];
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
