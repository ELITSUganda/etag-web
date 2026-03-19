<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RequestLog;
use App\Services\GuardianService;
use Illuminate\Support\Facades\DB;

class LogRequestMiddleware
{
    /**
     * Handle the incoming request (runs BEFORE response).
     * Lightweight: whitelist check, blocked-IP check, honeypot check, start timing.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('guardian.enabled', true)) {
            return $next($request);
        }

        $ip = $request->ip();

        // Whitelisted IPs bypass everything
        if (in_array($ip, config('guardian.ip_whitelist', []))) {
            return $next($request);
        }

        // Check blocked IPs (cached lookup)
        try {
            $guardian = app(GuardianService::class);
            if ($guardian->isIpBlocked($ip)) {
                abort(403, 'Your IP address has been blocked.');
            }
        } catch (\Exception $e) {
            // Guardian must never break the app
        }

        // Honeypot — instant block for scanners (skip admin paths to avoid false positives)
        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/' . config('admin.route.prefix', 'admin'))) {
            foreach (config('guardian.honeypot_paths', []) as $honeypot) {
                if (str_starts_with($path, $honeypot)) {
                    try {
                        $guardian = $guardian ?? app(GuardianService::class);
                        $guardian->blockIp($ip, 'Honeypot path accessed: ' . $path, 1440);
                        $guardian->createAlert('honeypot', 'warning', "Honeypot triggered by {$ip}: {$path}", ['ip' => $ip, 'path' => $path]);
                    } catch (\Exception $e) {}
                    abort(403, 'Access Denied');
                }
            }
        }

        // Start timing
        $request->attributes->set('guardian_start_time', microtime(true));
        $request->attributes->set('guardian_start_memory', memory_get_usage(true));

        // Enable query counting
        DB::enableQueryLog();

        return $next($request);
    }

    /**
     * Terminate (runs AFTER response is sent to client).
     * This is where we log without adding latency.
     */
    public function terminate($request, $response)
    {
        if (!config('guardian.enabled', true)) {
            return;
        }

        // Skip logging for whitelisted IPs
        if (in_array($request->ip(), config('guardian.ip_whitelist', []))) {
            DB::disableQueryLog();
            return;
        }

        $path = $request->getPathInfo();
        foreach (config('guardian.logging.exclude_paths', []) as $pattern) {
            if (preg_match($pattern, $path)) {
                DB::disableQueryLog();
                return;
            }
        }

        $startTime   = $request->attributes->get('guardian_start_time', microtime(true));
        $startMemory = $request->attributes->get('guardian_start_memory', 0);

        $responseTime = (microtime(true) - $startTime) * 1000;
        $peakMemory   = (memory_get_peak_usage(true) - $startMemory) / 1024 / 1024;
        $queryCount   = count(DB::getQueryLog());

        DB::disableQueryLog();

        try {
            RequestLog::create([
                'ip_address'       => $request->ip(),
                'user_agent'       => substr($request->userAgent() ?? '', 0, 500),
                'method'           => $request->method(),
                'endpoint'         => substr($path, 0, 500),
                'response_status'  => $response->getStatusCode(),
                'response_time_ms' => round($responseTime, 2),
                'memory_usage_mb'  => round(max($peakMemory, 0), 2),
                'query_count'      => $queryCount,
                'user_id'          => $request->user() ? $request->user()->id : null,
                'created_at'       => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Guardian: Failed to log request: ' . $e->getMessage());
        }
    }
}
