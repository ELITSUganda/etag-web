<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDrugSeller extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::updating(function ($m) {
            if ($m->status == 1) {

                AdminRoleUser::where([
                    'role_id' => 11,
                    'user_id' => $m->applicant_id,
                ])->delete();
                $role = new AdminRoleUser();
                $role->role_id = 11;
                $role->user_id = $m->applicant_id;
                $role->save();
            } else {
                AdminRoleUser::where([
                    'role_id' => 11,
                    'user_id' => $m->applicant_id,
                ])->delete();
            }
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y g:i A');
    }

    public function sub_county()
    {
        return $this->belongsTo(SubCounty::class);
    }

    public function applicant()
    {
        return $this->belongsTo(Administrator::class, 'applicant_id');
    }
}
