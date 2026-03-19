<?php

namespace App\Services;

use App\Models\BlockedIp;
use App\Models\RequestLog;
use App\Models\SystemAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GuardianService
{
    /**
     * Check if an IP is blocked (cached for 60s).
     */
    public function isIpBlocked(string $ip): bool
    {
        $blockedIps = Cache::remember('guardian:blocked_ips', 60, function () {
            return BlockedIp::active()->pluck('ip_address')->toArray();
        });
        return in_array($ip, $blockedIps);
    }

    /**
     * Block an IP address.
     */
    public function blockIp(string $ip, string $reason, int $durationMinutes = null): void
    {
        BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason'       => $reason,
                'attempts'     => DB::raw('COALESCE(attempts, 0) + 1'),
                'is_permanent' => $durationMinutes === null,
                'blocked_at'   => now(),
                'expires_at'   => $durationMinutes ? now()->addMinutes($durationMinutes) : null,
            ]
        );
        Cache::forget('guardian:blocked_ips');
    }

    /**
     * Unblock an IP address.
     */
    public function unblockIp(string $ip): void
    {
        BlockedIp::where('ip_address', $ip)->delete();
        Cache::forget('guardian:blocked_ips');
    }

    /**
     * Get system metrics using PHP built-in functions.
     */
    public function getSystemMetrics(): array
    {
        $loadAvg = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $memUsage = memory_get_usage(true);
        $memPeak  = memory_get_peak_usage(true);
        $mysqlStats = $this->getMysqlStats();

        $diskFree  = @disk_free_space('/');
        $diskTotal = @disk_total_space('/');

        return [
            'cpu_load' => [
                '1min'  => round($loadAvg[0], 2),
                '5min'  => round($loadAvg[1], 2),
                '15min' => round($loadAvg[2], 2),
            ],
            'memory' => [
                'php_current_mb' => round($memUsage / 1024 / 1024, 2),
                'php_peak_mb'    => round($memPeak / 1024 / 1024, 2),
            ],
            'mysql' => $mysqlStats,
            'disk'  => [
                'free_gb'  => $diskTotal ? round($diskFree / 1024 / 1024 / 1024, 2) : 0,
                'total_gb' => $diskTotal ? round($diskTotal / 1024 / 1024 / 1024, 2) : 0,
                'used_pct' => $diskTotal ? round((1 - $diskFree / $diskTotal) * 100, 1) : 0,
            ],
        ];
    }

    private function getMysqlStats(): array
    {
        try {
            $threads = DB::select("SHOW GLOBAL STATUS LIKE 'Threads_connected'");
            $maxConn = DB::select("SHOW VARIABLES LIKE 'max_connections'");
            $uptime  = DB::select("SHOW GLOBAL STATUS LIKE 'Uptime'");
            $queries = DB::select("SHOW GLOBAL STATUS LIKE 'Questions'");
            $slowQ   = DB::select("SHOW GLOBAL STATUS LIKE 'Slow_queries'");

            return [
                'threads_connected' => (int)($threads[0]->Value ?? 0),
                'max_connections'   => (int)($maxConn[0]->Value ?? 0),
                'uptime_hours'      => round(((int)($uptime[0]->Value ?? 0)) / 3600, 1),
                'total_queries'     => (int)($queries[0]->Value ?? 0),
                'slow_queries'      => (int)($slowQ[0]->Value ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'threads_connected' => 0,
                'max_connections'   => 0,
                'uptime_hours'      => 0,
                'total_queries'     => 0,
                'slow_queries'      => 0,
            ];
        }
    }

    /**
     * Get traffic statistics.
     */
    public function getTrafficStats(): array
    {
        $now = Carbon::now();

        $lastMinute = RequestLog::where('created_at', '>=', $now->copy()->subMinute())->count();
        $lastHour   = RequestLog::where('created_at', '>=', $now->copy()->subHour())->count();
        $today      = RequestLog::where('created_at', '>=', $now->copy()->startOfDay())->count();
        $avgTime    = RequestLog::where('created_at', '>=', $now->copy()->subHour())->avg('response_time_ms');

        $totalLastHour = max($lastHour, 1);
        $errorsLastHour = RequestLog::where('created_at', '>=', $now->copy()->subHour())
            ->where('response_status', '>=', 500)->count();

        return [
            'requests_last_minute' => $lastMinute,
            'requests_last_hour'   => $lastHour,
            'requests_today'       => $today,
            'avg_response_time_ms' => round($avgTime ?? 0, 2),
            'error_rate_pct'       => round(($errorsLastHour / $totalLastHour) * 100, 2),
        ];
    }

    public function getTopIps(int $limit = 10): array
    {
        return RequestLog::where('created_at', '>=', Carbon::now()->subHour())
            ->select('ip_address', DB::raw('COUNT(*) as hits'))
            ->groupBy('ip_address')
            ->orderByDesc('hits')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTopEndpoints(int $limit = 10): array
    {
        return RequestLog::where('created_at', '>=', Carbon::now()->subHour())
            ->select('endpoint', DB::raw('COUNT(*) as hits'), DB::raw('ROUND(AVG(response_time_ms), 1) as avg_time'))
            ->groupBy('endpoint')
            ->orderByDesc('hits')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getSlowestEndpoints(int $limit = 10): array
    {
        return RequestLog::where('created_at', '>=', Carbon::now()->subHour())
            ->select('endpoint', DB::raw('ROUND(AVG(response_time_ms), 1) as avg_time'), DB::raw('COUNT(*) as hits'))
            ->groupBy('endpoint')
            ->having('hits', '>=', 3)
            ->orderByDesc('avg_time')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getRequestsPerMinuteChart(int $minutes = 60): array
    {
        $since = Carbon::now()->subMinutes($minutes);
        return RequestLog::where('created_at', '>=', $since)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%H:%i') as minute"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('minute')
            ->orderBy('minute')
            ->get()
            ->toArray();
    }

    /**
     * Create a system alert.
     */
    public function createAlert(string $type, string $severity, string $message, array $payload = []): void
    {
        SystemAlert::create([
            'type'     => $type,
            'severity' => $severity,
            'message'  => $message,
            'payload'  => $payload,
        ]);
    }

    /**
     * Check if IP is whitelisted.
     */
    public function isWhitelisted(string $ip): bool
    {
        return in_array($ip, config('guardian.ip_whitelist', []));
    }

    /**
     * Check if path is a honeypot.
     */
    public function isHoneypot(string $path): bool
    {
        foreach (config('guardian.honeypot_paths', []) as $honeypot) {
            if (str_starts_with($path, $honeypot)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Rebuild blocked IP cache from DB (called by cleanup).
     */
    public function rebuildBlockedIpCache(): void
    {
        Cache::forget('guardian:blocked_ips');
        // Re-warm the cache
        $this->isIpBlocked('__warmup__');
    }

    /**
     * Get status code distribution for the last N minutes.
     */
    public function getStatusDistribution(int $minutes = 60): array
    {
        return RequestLog::where('created_at', '>=', Carbon::now()->subMinutes($minutes))
            ->select(
                DB::raw("CASE
                    WHEN response_status < 300 THEN '2xx'
                    WHEN response_status < 400 THEN '3xx'
                    WHEN response_status < 500 THEN '4xx'
                    ELSE '5xx'
                END as status_group"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status_group')
            ->get()
            ->pluck('count', 'status_group')
            ->toArray();
    }

    /**
     * Get HTTP method distribution for the last N minutes.
     */
    public function getMethodDistribution(int $minutes = 60): array
    {
        return RequestLog::where('created_at', '>=', Carbon::now()->subMinutes($minutes))
            ->select('method', DB::raw('COUNT(*) as count'))
            ->groupBy('method')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }
}
