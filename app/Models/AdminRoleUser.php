<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRoleUser extends Model
{
    use HasFactory;

    public function owner(){
        return $this->belongsTo(Administrator::class,'user_id');
    }
}
