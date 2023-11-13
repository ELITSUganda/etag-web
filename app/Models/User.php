<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{


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


    //boot
    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            if ($m->user_type == 'Vendor') {
                if ($this->user_type == 'Vendor') {
                    if ($m->request_status == 'Active') {
                        $message_to_vendor = "Congratulations! Your account has been approved as a vendor. You can now login to your account and start selling your products.";
                        Utils::send_message($m->business_phone_number, $message_to_vendor);
                    }
                }
            }
        });
    }

    //getter for business_cover_photo
    /*   public function getBusinessCoverPhotoAttribute($x)
    {
        if ($x == null) {
            return asset('images/placeholder.png');
        }
        return asset('storage/' . $x);
    } */
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
