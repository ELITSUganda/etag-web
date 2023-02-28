<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRoleUser extends Model
{
    use HasFactory;
    protected $primaryKey = 'id'; // or null

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (isset($model->type_id_1)) {
                if ($model->type_id_1 != null) {
                    $t = (int)($model->type_id_1);
                    if ($t > 0) {
                        $model->type_id = $t;
                    }
                }
                unset($model->type_id_1);
            }
        });
    }

    public function owner()
    {
        $r = Administrator::find($this->user_id);
        if ($r == null) {
            $this->delete();
        }
        return $this->belongsTo(Administrator::class, 'user_id');
    }
    public function role()
    {

        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    public function type()
    {
        if ($this->role_type == 'dvo' || $this->role_type == 'scvo') {
            return $this->belongsTo(Location::class, 'type_id');
        }
        return $this->belongsTo(Location::class, 'type_id');
    }
}
