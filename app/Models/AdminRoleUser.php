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
            if (isset($model->type_id_3)) {
                if ($model->type_id_3 != null) {
                    $t = (int)($model->type_id_3);
                    if ($t > 0) {
                        $model->type_id = $t;
                    }
                }
                unset($model->type_id_3);
            }
            if (isset($model->type_id_4)) {
                if ($model->type_id_4 != null) {
                    $t = (int)($model->type_id_4);
                    if ($t > 0) {
                        $model->type_id = $t;
                    }
                }
                unset($model->type_id_4);
            }
            AdminRoleUser::where([
                'user_id' => $model->user_id,
                'role_id' => $model->role_id
            ])->delete();
        });
        self::updating(function ($model) {
            if (isset($model->type_id_1)) {
                if ($model->type_id_1 != null) {
                    $t = (int)($model->type_id_1);
                    if ($t > 0) {
                        $model->type_id = $t;
                    }
                }
            }
            if (isset($model->type_id_2)) {
                if ($model->type_id_2 != null) {
                    $t = (int)($model->type_id_2);
                    if ($t > 0) {
                        $model->type_id = $t;
                    }
                }
            }
            if (isset($model->type_id_3)) {
                if ($model->type_id_3 != null) {
                    $t = (int)($model->type_id_3);
                    if ($t > 0) {
                        $model->type_id = $t;
                    }
                }
            }
            if (isset($model->type_id_4)) {
                if ($model->type_id_4 != null) {
                    $t = (int)($model->type_id_4);
                    if ($t > 0) {
                        $model->type_id = $t;
                    }
                }
                unset($model->type_id_4);
            }

            $model->type_id_1 = null;
            $model->type_id_2 = null;
            $model->type_id_3 = null;
            $model->type_id_4 = null;
            return $model;
        });
        return true;
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
