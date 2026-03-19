<style>
    .guardian-box {
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.12);
        margin-bottom: 20px;
        padding: 20px;
    }
    .guardian-box .box-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #3c8dbc;
        padding-bottom: 8px;
    }
    .metric-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
        border-radius: 4px;
        padding: 15px;
        text-align: center;
        margin-bottom: 15px;
        border-left: 4px solid #3c8dbc;
    }
    .metric-card.warning { border-left-color: #f39c12; }
    .metric-card.danger { border-left-color: #dd4b39; }
    .metric-card.success { border-left-color: #00a65a; }
    .metric-card .metric-value {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        line-height: 1.2;
    }
    .metric-card .metric-label {
        font-size: 12px;
        color: #777;
        text-transform: uppercase;
        margin-top: 4px;
    }
    .metric-card .metric-sub {
        font-size: 11px;
        color: #999;
        margin-top: 2px;
    }
    .guardian-table {
        width: 100%;
        font-size: 13px;
    }
    .guardian-table th {
        background: #f5f5f5;
        padding: 8px 10px;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
    }
    .guardian-table td {
        padding: 6px 10px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    .guardian-table tbody tr:hover {
        background: #f9f9f9;
    }
    .badge-severity {
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
    }
    .badge-severity.critical { background: #dd4b39; }
    .badge-severity.warning { background: #f39c12; }
    .badge-severity.info { background: #00c0ef; }
    .badge-type {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        color: #fff;
        background: #666;
    }
    .badge-type.rate_limit { background: #e74c3c; }
    .badge-type.spike { background: #e67e22; }
    .badge-type.bot { background: #9b59b6; }
    .badge-type.resource { background: #2980b9; }
    .badge-type.slow_endpoint { background: #f39c12; }
    .badge-type.honeypot { background: #c0392b; }
    .btn-block-ip {
        padding: 2px 8px;
        font-size: 11px;
        border-radius: 3px;
        cursor: pointer;
    }
    .traffic-stat {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .traffic-stat:last-child { border-bottom: none; }
    .traffic-stat .stat-label { color: #777; font-size: 13px; }
    .traffic-stat .stat-value { font-weight: 600; color: #333; font-size: 14px; }
    .status-bar {
        display: flex;
        height: 30px;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .status-bar .segment {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
    }
    .manual-block-form {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
    }
    .manual-block-form input {
        flex: 1;
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 13px;
    }
    .alert-unread { background: #fffde7; }
</style>

{{-- ROW 1: System Metrics --}}
<div class="row">
    <div class="col-md-2">
        <div class="metric-card {{ $systemMetrics['cpu_load']['1min'] >= 4 ? 'danger' : '' }}">
            <div class="metric-value" id="cpu-load">{{ $systemMetrics['cpu_load']['1min'] }}</div>
            <div class="metric-label">CPU Load (1m)</div>
            <div class="metric-sub">5m: {{ $systemMetrics['cpu_load']['5min'] }} | 15m: {{ $systemMetrics['cpu_load']['15min'] }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card">
            <div class="metric-value">{{ $systemMetrics['memory']['php_current_mb'] }}<small>MB</small></div>
            <div class="metric-label">PHP Memory</div>
            <div class="metric-sub">Peak: {{ $systemMetrics['memory']['php_peak_mb'] }}MB</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card {{ $systemMetrics['mysql']['threads_connected'] >= 100 ? 'warning' : '' }}">
            <div class="metric-value" id="mysql-conn">{{ $systemMetrics['mysql']['threads_connected'] }}</div>
            <div class="metric-label">MySQL Connections</div>
            <div class="metric-sub">Max: {{ $systemMetrics['mysql']['max_connections'] }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card success">
            <div class="metric-value" id="req-per-min">{{ $trafficStats['requests_last_minute'] }}</div>
            <div class="metric-label">Requests/Min</div>
            <div class="metric-sub">Hour: {{ number_format($trafficStats['requests_last_hour']) }}</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card">
            <div class="metric-value">{{ $trafficStats['avg_response_time_ms'] }}<small>ms</small></div>
            <div class="metric-label">Avg Response</div>
            <div class="metric-sub">Errors: {{ $trafficStats['error_rate_pct'] }}%</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card {{ $unreadAlertCount > 0 ? 'danger' : 'success' }}">
            <div class="metric-value">{{ $unreadAlertCount }}</div>
            <div class="metric-label">Unread Alerts</div>
            <div class="metric-sub">Today: {{ number_format($trafficStats['requests_today']) }} req</div>
        </div>
    </div>
</div>

{{-- ROW 2: Traffic Chart + Stats --}}
<div class="row">
    <div class="col-md-8">
        <div class="guardian-box">
            <div class="box-title">Requests Per Minute (Last 60 Minutes)</div>
            <canvas id="guardianTrafficChart" style="width: 100%; height: 280px;"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="guardian-box">
            <div class="box-title">HTTP Status Distribution (Last Hour)</div>
            <div class="status-bar">
                @php
                    $total = array_sum(array_column($statusDist, 'count')) ?: 1;
                    $colors = ['2' => '#00a65a', '3' => '#00c0ef', '4' => '#f39c12', '5' => '#dd4b39'];
                    $labels = ['2' => '2xx', '3' => '3xx', '4' => '4xx', '5' => '5xx'];
                @endphp
                @foreach($colors as $group => $color)
                    @php $count = $statusDist[$group]['count'] ?? 0; $pct = round(($count / $total) * 100, 1); @endphp
                    @if($pct > 0)
                        <div class="segment" style="width: {{ max($pct, 5) }}%; background: {{ $color }};">
                            {{ $labels[$group] }} {{ $pct }}%
                        </div>
                    @endif
                @endforeach
            </div>
            <div style="margin-top: 15px;">
                <div class="traffic-stat">
                    <span class="stat-label">Requests/Minute</span>
                    <span class="stat-value" id="stat-rpm">{{ $trafficStats['requests_last_minute'] }}</span>
                </div>
                <div class="traffic-stat">
                    <span class="stat-label">Requests/Hour</span>
                    <span class="stat-value">{{ number_format($trafficStats['requests_last_hour']) }}</span>
                </div>
                <div class="traffic-stat">
                    <span class="stat-label">Requests Today</span>
                    <span class="stat-value">{{ number_format($trafficStats['requests_today']) }}</span>
                </div>
                <div class="traffic-stat">
                    <span class="stat-label">Avg Response Time</span>
                    <span class="stat-value">{{ $trafficStats['avg_response_time_ms'] }}ms</span>
                </div>
                <div class="traffic-stat">
                    <span class="stat-label">Error Rate (5xx)</span>
                    <span class="stat-value" style="color: {{ $trafficStats['error_rate_pct'] > 5 ? '#dd4b39' : '#00a65a' }}">{{ $trafficStats['error_rate_pct'] }}%</span>
                </div>
                <div class="traffic-stat">
                    <span class="stat-label">Disk Usage</span>
                    <span class="stat-value">{{ $systemMetrics['disk']['used_pct'] }}% ({{ $systemMetrics['disk']['free_gb'] }}GB free)</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: Top Tables --}}
<div class="row">
    <div class="col-md-4">
        <div class="guardian-box">
            <div class="box-title">Top 10 IPs (Last Hour)</div>
            <table class="guardian-table">
                <thead><tr><th>IP Address</th><th>Hits</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($topIps as $ip)
                    <tr>
                        <td><code>{{ $ip['ip_address'] }}</code></td>
                        <td><strong>{{ number_format($ip['hits']) }}</strong></td>
                        <td><button class="btn btn-xs btn-danger btn-block-ip" onclick="blockIp('{{ $ip['ip_address'] }}')">Block</button></td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#999;">No data yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="guardian-box">
            <div class="box-title">Top 10 Endpoints (Last Hour)</div>
            <table class="guardian-table">
                <thead><tr><th>Endpoint</th><th>Hits</th><th>Avg Time</th></tr></thead>
                <tbody>
                @forelse($topEndpoints as $ep)
                    <tr>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $ep['endpoint'] }}">{{ $ep['endpoint'] }}</td>
                        <td><strong>{{ number_format($ep['hits']) }}</strong></td>
                        <td>{{ $ep['avg_time'] }}ms</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#999;">No data yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="guardian-box">
            <div class="box-title">Slowest Endpoints (Last Hour)</div>
            <table class="guardian-table">
                <thead><tr><th>Endpoint</th><th>Avg Time</th><th>Hits</th></tr></thead>
                <tbody>
                @forelse($slowestEndpoints as $ep)
                    <tr>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $ep['endpoint'] }}">{{ $ep['endpoint'] }}</td>
                        <td style="color: {{ $ep['avg_time'] > 3000 ? '#dd4b39' : ($ep['avg_time'] > 1000 ? '#f39c12' : '#333') }}"><strong>{{ $ep['avg_time'] }}ms</strong></td>
                        <td>{{ $ep['hits'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#999;">No data yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ROW 4: Security --}}
<div class="row">
    <div class="col-md-7">
        <div class="guardian-box">
            <div class="box-title">Blocked IPs ({{ $blockedIps->count() }} active)</div>
            <div class="manual-block-form">
                <input type="text" id="manual-block-ip" placeholder="Enter IP to block (e.g. 192.168.1.1)">
                <input type="text" id="manual-block-reason" placeholder="Reason" style="flex: 0.8;">
                <select id="manual-block-duration" style="padding: 6px; border: 1px solid #ddd; border-radius: 3px;">
                    <option value="15">15 min</option>
                    <option value="60" selected>1 hour</option>
                    <option value="1440">24 hours</option>
                    <option value="10080">7 days</option>
                    <option value="0">Permanent</option>
                </select>
                <button class="btn btn-sm btn-danger" onclick="manualBlockIp()">Block</button>
            </div>
            <table class="guardian-table">
                <thead><tr><th>IP</th><th>Reason</th><th>Attempts</th><th>Blocked At</th><th>Expires</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($blockedIps as $blocked)
                    <tr>
                        <td><code>{{ $blocked->ip_address }}</code></td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $blocked->reason }}">{{ $blocked->reason }}</td>
                        <td>{{ $blocked->attempts }}</td>
                        <td>{{ $blocked->blocked_at ? $blocked->blocked_at->format('M d H:i') : '-' }}</td>
                        <td>
                            @if($blocked->is_permanent)
                                <span style="color:#dd4b39; font-weight:600;">Permanent</span>
                            @else
                                {{ $blocked->expires_at ? $blocked->expires_at->format('M d H:i') : '-' }}
                            @endif
                        </td>
                        <td><button class="btn btn-xs btn-success" onclick="unblockIp('{{ $blocked->ip_address }}')">Unblock</button></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center; color:#999;">No blocked IPs</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-5">
        <div class="guardian-box">
            <div class="box-title">MySQL Server Stats</div>
            <div class="traffic-stat">
                <span class="stat-label">Threads Connected</span>
                <span class="stat-value">{{ $systemMetrics['mysql']['threads_connected'] }} / {{ $systemMetrics['mysql']['max_connections'] }}</span>
            </div>
            <div class="traffic-stat">
                <span class="stat-label">Uptime</span>
                <span class="stat-value">{{ $systemMetrics['mysql']['uptime_hours'] }} hours</span>
            </div>
            <div class="traffic-stat">
                <span class="stat-label">Total Queries</span>
                <span class="stat-value">{{ number_format($systemMetrics['mysql']['total_queries']) }}</span>
            </div>
            <div class="traffic-stat">
                <span class="stat-label">Slow Queries</span>
                <span class="stat-value" style="color: {{ $systemMetrics['mysql']['slow_queries'] > 0 ? '#f39c12' : '#00a65a' }}">{{ number_format($systemMetrics['mysql']['slow_queries']) }}</span>
            </div>
            <div class="traffic-stat">
                <span class="stat-label">CPU Load</span>
                <span class="stat-value">{{ $systemMetrics['cpu_load']['1min'] }} / {{ $systemMetrics['cpu_load']['5min'] }} / {{ $systemMetrics['cpu_load']['15min'] }}</span>
            </div>
            <div class="traffic-stat">
                <span class="stat-label">Disk Free</span>
                <span class="stat-value">{{ $systemMetrics['disk']['free_gb'] }}GB / {{ $systemMetrics['disk']['total_gb'] }}GB</span>
            </div>
        </div>
    </div>
</div>

{{-- ROW 5: Alerts Feed --}}
<div class="row">
    <div class="col-md-12">
        <div class="guardian-box">
            <div class="box-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>System Alerts ({{ $unreadAlertCount }} unread)</span>
                <span>
                    <button class="btn btn-xs btn-primary" id="btn-run-analysis" onclick="runAnalysis()"><i class="fa fa-bolt"></i> Run Analysis</button>
                    @if($unreadAlertCount > 0)
                        <button class="btn btn-xs btn-default" onclick="markAllAlertsRead()">Mark All Read</button>
                    @endif
                </span>
            </div>
            <table class="guardian-table">
                <thead><tr><th style="width:140px;">Time</th><th style="width:90px;">Type</th><th style="width:70px;">Severity</th><th>Message</th><th style="width:70px;">Action</th></tr></thead>
                <tbody>
                @forelse($recentAlerts as $alert)
                    <tr class="{{ !$alert->read_at ? 'alert-unread' : '' }}">
                        <td>{{ $alert->created_at->format('M d H:i:s') }}</td>
                        <td><span class="badge-type {{ $alert->type }}">{{ str_replace('_', ' ', $alert->type) }}</span></td>
                        <td><span class="badge-severity {{ $alert->severity }}">{{ $alert->severity }}</span></td>
                        <td>{{ $alert->message }}</td>
                        <td>
                            @if(!$alert->read_at)
                                <button class="btn btn-xs btn-default" onclick="markAlertRead({{ $alert->id }}, this)">Dismiss</button>
                            @else
                                <span style="color:#aaa; font-size:11px;">Read</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center; color:#999;">No alerts</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(function () {
    // Requests/min chart
    var ctx = document.getElementById('guardianTrafficChart').getContext('2d');
    var chartData = @json($requestsChart);
    var trafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(function(d) { return d.minute; }),
            datasets: [{
                label: 'Requests/min',
                data: chartData.map(function(d) { return d.count; }),
                borderColor: '#3c8dbc',
                backgroundColor: 'rgba(60,141,188,0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                pointHoverRadius: 5,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{ ticks: { beginAtZero: true } }],
                xAxes: [{ ticks: { maxTicksLimit: 15 } }]
            },
            legend: { display: false }
        }
    });

    // Auto-refresh every 30 seconds
    setInterval(function() {
        $.get('{{ admin_url("guardian/api/live-metrics") }}', function(resp) {
            if (resp.code == '1') {
                $('#cpu-load').text(resp.data.system.cpu_load['1min']);
                $('#mysql-conn').text(resp.data.system.mysql.threads_connected);
                $('#req-per-min').text(resp.data.traffic.requests_last_minute);
                $('#stat-rpm').text(resp.data.traffic.requests_last_minute);

                // Update chart
                if (resp.data.chart && resp.data.chart.length > 0) {
                    trafficChart.data.labels = resp.data.chart.map(function(d) { return d.minute; });
                    trafficChart.data.datasets[0].data = resp.data.chart.map(function(d) { return d.count; });
                    trafficChart.update();
                }
            }
        });
    }, 30000);
});

function blockIp(ip) {
    if (!confirm('Block IP ' + ip + ' for 1 hour?')) return;
    $.post('{{ admin_url("guardian/block-ip") }}', {
        _token: LA.token,
        ip: ip,
        reason: 'Manually blocked from dashboard',
        duration: 60
    }, function(resp) {
        toastr.success(resp.message);
        location.reload();
    }).fail(function() { toastr.error('Failed to block IP'); });
}

function manualBlockIp() {
    var ip = $('#manual-block-ip').val().trim();
    var reason = $('#manual-block-reason').val().trim() || 'Manually blocked by admin';
    var duration = $('#manual-block-duration').val();
    if (!ip) { toastr.warning('Please enter an IP address'); return; }
    $.post('{{ admin_url("guardian/block-ip") }}', {
        _token: LA.token,
        ip: ip,
        reason: reason,
        duration: duration
    }, function(resp) {
        if (resp.code == '1') {
            toastr.success(resp.message);
            location.reload();
        } else {
            toastr.error(resp.message);
        }
    }).fail(function() { toastr.error('Failed to block IP'); });
}

function unblockIp(ip) {
    if (!confirm('Unblock IP ' + ip + '?')) return;
    $.post('{{ admin_url("guardian/unblock-ip") }}', {
        _token: LA.token,
        ip: ip
    }, function(resp) {
        toastr.success(resp.message);
        location.reload();
    }).fail(function() { toastr.error('Failed to unblock IP'); });
}

function markAlertRead(id, btn) {
    $.post('{{ admin_url("guardian/alert/read") }}', {
        _token: LA.token,
        id: id
    }, function(resp) {
        $(btn).closest('tr').removeClass('alert-unread');
        $(btn).replaceWith('<span style="color:#aaa; font-size:11px;">Read</span>');
    });
}

function markAllAlertsRead() {
    $.post('{{ admin_url("guardian/alerts/read-all") }}', {
        _token: LA.token
    }, function(resp) {
        toastr.success(resp.message);
        location.reload();
    });
}

function runAnalysis() {
    var btn = $('#btn-run-analysis');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Analyzing...');
    $.post('{{ admin_url("guardian/api/run-analysis") }}', {
        _token: LA.token
    }, function(resp) {
        if (resp.code == '1') {
            toastr.success(resp.message);
            location.reload();
        } else {
            toastr.error(resp.message || 'Analysis failed');
            btn.prop('disabled', false).html('<i class="fa fa-bolt"></i> Run Analysis');
        }
    }).fail(function() {
        toastr.error('Failed to run analysis');
        btn.prop('disabled', false).html('<i class="fa fa-bolt"></i> Run Analysis');
    });
}
</script>
