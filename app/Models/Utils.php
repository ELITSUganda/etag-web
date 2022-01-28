<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Grid\Model;

class Utils extends Model
{
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

        if (!$u->isRole('veterinary')) {
            return  false;
        }

        if ($u->isRole('veterinary')) {
            return  true;
        }
        return  false;
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
        $resp['status'] = 1;
        $resp['message'] = "Success";
        $resp['data'] = null;
        if (isset($data['status'])) {
            $resp['status'] = $data['status'];
        }
        if (isset($data['message'])) {
            $resp['message'] = $data['message'];
        }
        if (isset($data['data'])) {
            $resp['data'] = $data['data'];
        }
        return $resp;
    }
}
