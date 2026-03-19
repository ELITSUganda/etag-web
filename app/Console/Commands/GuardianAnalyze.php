<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestLog;
use App\Models\BlockedIp;
use App\Models\SystemAlert;
use App\Services\GuardianService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GuardianAnalyze extends Command
{
    protected $signature = 'guardian:analyze';
    protected $description = 'Analyze request patterns and auto-defend against threats';

    public function handle()
    {
        if (!config('guardian.enabled', true)) {
            $this->info('Guardian is disabled.');
            return 0;
        }

        $guardian = app(GuardianService::class);
        $now = Carbon::now();

        $this->checkRateLimits($guardian, $now);
        $this->checkSpikes($guardian, $now);
        $this->checkSlowEndpoints($guardian, $now);
        $this->checkBadBots($guardian, $now);
        $this->checkResourceThresholds($guardian);

        $this->info('Guardian analysis complete at ' . $now->toDateTimeString());
        return 0;
    }

    private function checkRateLimits(GuardianService $guardian, Carbon $now): void
    {
        $config = config('guardian.rate_limits');
        $whitelist = config('guardian.ip_whitelist', []);
        $oneMinuteAgo = $now->copy()->subMinute();

        $violators = RequestLog::where('created_at', '>=', $oneMinuteAgo)
            ->whereNotIn('ip_address', $whitelist)
            ->select('ip_address', DB::raw('COUNT(*) as hits'))
            ->groupBy('ip_address')
            ->having('hits', '>', $config['requests_per_minute'])
            ->get();

        foreach ($violators as $v) {
            if (!BlockedIp::isBlocked($v->ip_address)) {
                $guardian->blockIp(
                    $v->ip_address,
                    "Rate limit exceeded: {$v->hits} requests/min (limit: {$config['requests_per_minute']})",
                    $config['block_duration_minutes']
                );
                $guardian->createAlert(
                    'rate_limit', 'warning',
                    "Blocked IP {$v->ip_address}: {$v->hits} req/min",
                    ['ip' => $v->ip_address, 'hits' => $v->hits]
                );
                $this->warn("Blocked {$v->ip_address}: {$v->hits} req/min");
            }
        }
    }

    private function checkSpikes(GuardianService $guardian, Carbon $now): void
    {
        $config = config('guardian.spike_detection');
        $oneMinuteAgo = $now->copy()->subMinute();
        $totalLastMinute = RequestLog::where('created_at', '>=', $oneMinuteAgo)->count();

        if ($totalLastMinute > $config['requests_per_minute_threshold']) {
            // Avoid duplicate spike alerts within 10 min
            $recentSpike = SystemAlert::where('type', 'spike')
                ->where('created_at', '>=', $now->copy()->subMinutes(10))
                ->exists();
            if ($recentSpike) return;

            $guardian->createAlert(
                'spike', 'critical',
                "Traffic spike: {$totalLastMinute} requests in last minute (threshold: {$config['requests_per_minute_threshold']})",
                ['total' => $totalLastMinute, 'threshold' => $config['requests_per_minute_threshold']]
            );
            $this->error("SPIKE: {$totalLastMinute} req/min");
        }
    }

    private function checkSlowEndpoints(GuardianService $guardian, Carbon $now): void
    {
        $config = config('guardian.heavy_endpoints');
        $fiveMinutesAgo = $now->copy()->subMinutes(5);

        $slowEndpoints = RequestLog::where('created_at', '>=', $fiveMinutesAgo)
            ->select('endpoint', DB::raw('AVG(response_time_ms) as avg_time'), DB::raw('COUNT(*) as hits'))
            ->groupBy('endpoint')
            ->having('avg_time', '>', $config['slow_threshold_ms'])
            ->having('hits', '>=', 3)
            ->orderByDesc('avg_time')
            ->limit(5)
            ->get();

        foreach ($slowEndpoints as $ep) {
            // Avoid duplicate alerts: skip if same slow_endpoint alert exists within last 30 min
            $recentAlert = SystemAlert::where('type', 'slow_endpoint')
                ->where('message', 'LIKE', "Slow endpoint: {$ep->endpoint}%")
                ->where('created_at', '>=', $now->copy()->subMinutes(30))
                ->exists();
            if ($recentAlert) continue;

            $guardian->createAlert(
                'slow_endpoint', 'info',
                "Slow endpoint: {$ep->endpoint} avg " . round($ep->avg_time) . "ms ({$ep->hits} hits)",
                ['endpoint' => $ep->endpoint, 'avg_time' => round($ep->avg_time, 1), 'hits' => $ep->hits]
            );
        }
    }

    private function checkBadBots(GuardianService $guardian, Carbon $now): void
    {
        $config = config('guardian.bad_bots');
        $whitelist = config('guardian.ip_whitelist', []);
        $tenMinutesAgo = $now->copy()->subMinutes(10);

        // Check suspicious user agents
        foreach ($config['suspicious_agents'] as $pattern) {
            // Extract regex core from PHP pattern like /nmap/i → nmap
            if (preg_match('#^/(.+)/[a-z]*$#', $pattern, $m)) {
                $mysqlPattern = $m[1];
            } else {
                $mysqlPattern = $pattern;
            }
            try {
                $suspiciousIps = RequestLog::where('created_at', '>=', $tenMinutesAgo)
                    ->whereNotIn('ip_address', $whitelist)
                    ->where('user_agent', 'REGEXP', $mysqlPattern)
                    ->select('ip_address')
                    ->distinct()
                    ->get();

                foreach ($suspiciousIps as $row) {
                    if (!BlockedIp::isBlocked($row->ip_address)) {
                        $guardian->blockIp($row->ip_address, "Suspicious user agent matching: {$mysqlPattern}", 1440);
                        $guardian->createAlert(
                            'bot', 'warning',
                            "Bad bot blocked: {$row->ip_address} (agent matched: {$mysqlPattern})",
                            ['ip' => $row->ip_address, 'pattern' => $pattern]
                        );
                        $this->warn("Bad bot blocked: {$row->ip_address}");
                    }
                }
            } catch (\Exception $e) {
                // Skip invalid regex patterns
            }
        }

        // Check 404 scanners
        $scanners = RequestLog::where('created_at', '>=', $tenMinutesAgo)
            ->whereNotIn('ip_address', $whitelist)
            ->where('response_status', 404)
            ->select('ip_address', DB::raw('COUNT(*) as count_404'))
            ->groupBy('ip_address')
            ->having('count_404', '>', $config['max_404_per_10_min'])
            ->get();

        foreach ($scanners as $s) {
            if (!BlockedIp::isBlocked($s->ip_address)) {
                $guardian->blockIp(
                    $s->ip_address,
                    "404 scanning: {$s->count_404} not-found requests in 10 min",
                    config('guardian.rate_limits.block_duration_minutes')
                );
                $guardian->createAlert(
                    'bot', 'warning',
                    "404 scanner blocked: {$s->ip_address} ({$s->count_404} 404s in 10 min)",
                    ['ip' => $s->ip_address, 'count_404' => $s->count_404]
                );
                $this->warn("404 scanner blocked: {$s->ip_address}");
            }
        }
    }

    private function checkResourceThresholds(GuardianService $guardian): void
    {
        $config = config('guardian.resource_thresholds');
        $metrics = $guardian->getSystemMetrics();

        // Skip if a resource alert was created within last 15 min
        $recentResourceAlert = SystemAlert::where('type', 'resource')
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->exists();
        if ($recentResourceAlert) return;

        $cpuLoad = $metrics['cpu_load']['1min'];
        if ($cpuLoad >= $config['cpu_load_critical']) {
            $guardian->createAlert('resource', 'critical', "CPU load critical: {$cpuLoad}", $metrics['cpu_load']);
        } elseif ($cpuLoad >= $config['cpu_load_warning']) {
            $guardian->createAlert('resource', 'warning', "CPU load high: {$cpuLoad}", $metrics['cpu_load']);
        }

        $mysqlConn = $metrics['mysql']['threads_connected'];
        if ($mysqlConn >= $config['mysql_connections_critical']) {
            $guardian->createAlert('resource', 'critical', "MySQL connections critical: {$mysqlConn}", $metrics['mysql']);
        } elseif ($mysqlConn >= $config['mysql_connections_warning']) {
            $guardian->createAlert('resource', 'warning', "MySQL connections high: {$mysqlConn}", $metrics['mysql']);
        }
    }
}
