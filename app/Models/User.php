<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    public function farms()
    {
        return $this->hasMany(Farm::class, 'administrator_id');
    }


    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admin_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];





    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {


            $phone_number = Utils::prepare_phone_number($m->phone_number);
            $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
            if ($phone_number_is_valid) {
                $m->phone_number = $phone_number;
                $m->username = $phone_number;
            } else {
                if ($m->email != null) {
                    $m->username = $m->email;
                }
            }

            return $m;
        });

        self::created(function ($model) {
            //$pro['user_id'] = $model->id;
            //Profile::create($pro);
        });

        self::updating(function ($m) {

            $phone_number = Utils::prepare_phone_number($m->phone_number);
            $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
            if ($phone_number_is_valid) {
                $m->phone_number = $phone_number;
                $m->username = $phone_number;
                $users = User::where([
                    'username' => $phone_number
                ])->orWhere([
                    'phone_number' => $phone_number
                ])->get();

                foreach ($users as $u) {
                    if ($u->id != $m->id) {
                        $u->delete();
                        continue;
                        $_resp = Utils::response([
                            'status' => 0,
                            'message' => "This phone number $m->phone_number is already used by another account",
                            'data' => null
                        ]);
                    }
                }
            }
            return $m;
        });
    }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
