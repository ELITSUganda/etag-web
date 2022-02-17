<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{ 

    protected $fillable = ['name', 'detail']; 

    public static function set_dvo($dis){
        if($dis == null){
            return false;
        }
        if($dis->administrator_id == null){
            return false;
        }
        $u = Administrator::find($dis->administrator_id);
        $u->dvo = $dis->id;
        if($u == null){
            return false;
        }
        $u->save();

    }

    public static function boot()
    {
        parent::boot();
        
        self::created(function ($dis) {
            self::set_dvo($dis);
        });
    
        self::updated(function ($dis) {
            self::set_dvo($dis);
        });

    }

}
