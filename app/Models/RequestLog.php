<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip_address', 'user_agent', 'method', 'endpoint',
        'response_status', 'response_time_ms', 'memory_usage_mb',
        'query_count', 'user_id', 'created_at',
    ];

    protected $casts = [
        'response_time_ms' => 'float',
        'memory_usage_mb'  => 'float',
        'query_count'      => 'integer',
        'response_status'  => 'integer',
        'created_at'       => 'datetime',
    ];
}
