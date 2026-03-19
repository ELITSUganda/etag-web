<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BlockedIp extends Model
{
    protected $fillable = [
        'ip_address', 'reason', 'attempts', 'is_permanent',
        'blocked_at', 'expires_at',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'blocked_at'   => 'datetime',
        'expires_at'   => 'datetime',
        'attempts'     => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_permanent', true)
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    public function isExpired(): bool
    {
        if ($this->is_permanent) return false;
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function isBlocked(string $ip): bool
    {
        return static::where('ip_address', $ip)->active()->exists();
    }
}
