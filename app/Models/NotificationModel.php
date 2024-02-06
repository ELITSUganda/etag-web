<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationModel extends Model
{
    use HasFactory;
    protected $fillable = [
        'created_at',
        'updated_at',
        'title',
        'message',
        'data',
        'reciever_id',
        'status',
        'type',
        'image',
        'animal_id',
        'animal_ids',
        'event_id',
        'event_ids',
        'notification_id',
        'notification_ids',
        'session_id',
        'session_ids',
        'url',
    ];
}
