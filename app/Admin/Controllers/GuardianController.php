<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use App\Services\GuardianService;
use App\Models\BlockedIp;
use App\Models\SystemAlert;
use App\Models\RequestLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuardianController extends AdminController
{
    public function index(Content $content)
    {
        Admin::js('/vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js');

        $guardian = app(GuardianService::class);

        $systemMetrics    = $guardian->getSystemMetrics();
        $trafficStats     = $guardian->getTrafficStats();
        $topIps           = $guardian->getTopIps(10);
        $topEndpoints     = $guardian->getTopEndpoints(10);
        $slowestEndpoints = $guardian->getSlowestEndpoints(10);
        $requestsChart    = $guardian->getRequestsPerMinuteChart(60);
        $blockedIps       = BlockedIp::active()->orderByDesc('blocked_at')->get();
        $recentAlerts     = SystemAlert::orderByDesc('created_at')->limit(50)->get();
        $unreadAlertCount = SystemAlert::unread()->count();

        $statusDist = RequestLog::where('created_at', '>=', Carbon::now()->subHour())
            ->select(DB::raw('FLOOR(response_status/100) as status_group'), DB::raw('COUNT(*) as count'))
            ->groupBy('status_group')
            ->get()
            ->keyBy('status_group')
            ->toArray();

        return $content
            ->title('System Guardian')
            ->description('Real-time system monitoring, security & performance')
            ->body(view('admin.guardian-dashboard', compact(
                'systemMetrics', 'trafficStats', 'topIps', 'topEndpoints',
                'slowestEndpoints', 'requestsChart', 'blockedIps',
                'recentAlerts', 'unreadAlertCount', 'statusDist'
            )));
    }

    public function blockIp(Request $request)
    {
        $ip       = $request->input('ip');
        $reason   = $request->input('reason', 'Manually blocked by admin');
        $duration = $request->input('duration', 60);

        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return response()->json(['code' => '0', 'message' => 'Invalid IP address']);
        }

        $guardian = app(GuardianService::class);
        $guardian->blockIp($ip, $reason, $duration == 0 ? null : (int)$duration);

        return response()->json(['code' => '1', 'message' => "IP {$ip} blocked successfully"]);
    }

    public function unblockIp(Request $request)
    {
        $ip = $request->input('ip');
        $guardian = app(GuardianService::class);
        $guardian->unblockIp($ip);

        return response()->json(['code' => '1', 'message' => "IP {$ip} unblocked successfully"]);
    }

    public function markAlertRead(Request $request)
    {
        $id = $request->input('id');
        $alert = SystemAlert::find($id);
        if ($alert) {
            $alert->markAsRead();
        }
        return response()->json(['code' => '1', 'message' => 'Alert marked as read']);
    }

    public function markAllAlertsRead()
    {
        SystemAlert::unread()->update(['read_at' => now()]);
        return response()->json(['code' => '1', 'message' => 'All alerts marked as read']);
    }

    public function liveMetrics()
    {
        $guardian = app(GuardianService::class);
        return response()->json([
            'code' => '1',
            'data' => [
                'system'  => $guardian->getSystemMetrics(),
                'traffic' => $guardian->getTrafficStats(),
                'chart'   => $guardian->getRequestsPerMinuteChart(60),
            ],
        ]);
    }

    public function runAnalysis()
    {
        \Illuminate\Support\Facades\Artisan::call('guardian:analyze');
        $output = \Illuminate\Support\Facades\Artisan::output();

        return response()->json(['code' => '1', 'message' => 'Analysis complete', 'output' => trim($output)]);
    }
}
