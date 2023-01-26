<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckpointSession extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();


        self::created(function ($model) {
            $m = Movement::find($model->movement_id);

            if ($m != null) {

                $u = Administrator::find($m->administrator_id);
                $name = "";
                if ($u != null) {
                    $name = "Hello {$u->name}, ";
                }
                $sub_county_from = Location::find($m->sub_county_from);



                if ($sub_county_from != null) {
                    $rs = AdminRoleUser::where([
                        'role_type' => 'dvo',
                        'type_id' => $sub_county_from->parent,
                    ])->get();
                    foreach ($rs as $v) {
                        Utils::sendNotification(
                            "Movement permit #{$m->id} has been checked at {$model->check_point->name}. $model->animals_found animals found, $model->animals_missed animals missed. ",
                            $v->user_id,
                            $headings = 'Checkpoint'
                        );
                    }
                }

                //$items = Movement::where('sub_county_from', '=', $user->scvo)->where('status', '=', 'Approved')->get();  

                Utils::sendNotification(
                    "Your movement permit #{$m->id} has been checked at {$model->check_point->name}. $model->animals_found animals found, $model->animals_missed animals missed. ",
                    $m->administrator_id,
                    $headings = $model->name . ' Checkpoint'
                );
            }
        });
    }

    public function check_point()
    {
        return $this->belongsTo(CheckPoint::class);
    }
}
