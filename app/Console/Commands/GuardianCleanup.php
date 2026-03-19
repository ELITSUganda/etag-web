<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestLog;
use App\Models\BlockedIp;
use App\Models\SystemAlert;
use App\Services\GuardianService;
use Carbon\Carbon;

class GuardianCleanup extends Command
{
    protected $signature = 'guardian:cleanup';
    protected $description = 'Clean up old Guardian logs, expired blocks, and resolved alerts';

    public function handle()
    {
        $config = config('guardian.cleanup');

        // Delete old request logs
        $logsCutoff = Carbon::now()->subDays($config['logs_older_than_days']);
        $deletedLogs = RequestLog::where('created_at', '<', $logsCutoff)->delete();
        $this->info("Deleted {$deletedLogs} request logs older than {$config['logs_older_than_days']} days.");

        // Purge expired IP blocks
        if ($config['purge_expired_blocks']) {
            $deletedBlocks = BlockedIp::where('is_permanent', false)
                ->where('expires_at', '<', Carbon::now())
                ->delete();
            $this->info("Purged {$deletedBlocks} expired IP blocks.");

            // Rebuild blocked IP cache after purge
            if ($deletedBlocks > 0) {
                app(GuardianService::class)->rebuildBlockedIpCache();
            }
        }

        // Delete old resolved alerts
        $alertsCutoff = Carbon::now()->subDays($config['alerts_older_than_days']);
        $deletedAlerts = SystemAlert::whereNotNull('read_at')
            ->where('created_at', '<', $alertsCutoff)
            ->delete();
        $this->info("Deleted {$deletedAlerts} old resolved alerts.");

        return 0;
    }
}
