<style>
    .bd-container { 
        padding: 0;
        background: linear-gradient(135deg, #f9f6f3 0%, #ffffff 100%);
    }
    .bd-stat-box {
        background: linear-gradient(135deg, #ffffff 0%, #faf8f6 100%);
        border: 2px solid #6B3C00;
        border-left: 5px solid #6B3C00;
        padding: 15px;
        margin-bottom: 12px;
        min-height: 95px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(107, 60, 0, 0.1);
        transition: all 0.3s ease;
    }
    .bd-stat-box:hover {
        box-shadow: 0 4px 12px rgba(107, 60, 0, 0.2);
        transform: translateY(-2px);
        border-left-width: 8px;
    }
    .bd-stat-box .icon {
        font-size: 32px;
        color: #6B3C00;
        margin-bottom: 8px;
        text-shadow: 0 2px 4px rgba(107, 60, 0, 0.1);
    }
    .bd-stat-box .value {
        font-size: 28px;
        font-weight: 700;
        color: #6B3C00;
        line-height: 1.1;
    }
    .bd-stat-box .label {
        font-size: 11px;
        color: #6B3C00;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 3px;
        font-weight: 600;
    }
    .bd-stat-box .sublabel {
        font-size: 10px;
        color: #8B5A1B;
        margin-top: 2px;
    }
    .bd-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        background: #6B3C00;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin: 20px 0 15px 0;
        padding: 12px 15px;
        box-shadow: 0 2px 8px rgba(107, 60, 0, 0.2);
    }
    .bd-panel {
        background: #fff;
        border: 2px solid #6B3C00;
        border-top: 5px solid #6B3C00;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(107, 60, 0, 0.1);
    }
    .bd-panel-header {
        font-size: 12px;
        font-weight: 700;
        color: #6B3C00;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #6B3C00;
    }
    .bd-panel-header i {
        color: #6B3C00;
        margin-right: 8px;
        font-size: 14px;
    }
    .bd-grade-bar {
        display: flex;
        height: 32px;
        overflow: hidden;
        background: #f5f5f5;
        border: 2px solid #6B3C00;
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(107, 60, 0, 0.1);
    }
    .bd-grade-bar .seg {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
    }
    .bd-legend {
        font-size: 10px;
        color: #6B3C00;
        padding: 8px 0;
        font-weight: 600;
    }
    .bd-legend span {
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 5px;
        vertical-align: middle;
        border: 2px solid #6B3C00;
    }
    .bd-table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
        border: 2px solid #6B3C00;
    }
    .bd-table th {
        background: #6B3C00;
        color: #fff;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bd-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #d4b89c;
        background: #fff;
    }
    .bd-table tr:hover td { 
        background: #faf8f6;
    }
    .bd-alert {
        background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
        border: 2px solid #6B3C00;
        border-left: 5px solid #6B3C00;
        padding: 12px 15px;
        margin-bottom: 10px;
        font-size: 11px;
        color: #6B3C00;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(107, 60, 0, 0.1);
    }
    .bd-alert i { 
        margin-right: 8px;
        font-size: 14px;
    }
    .bd-trend-up { 
        color: #28a745;
        font-weight: 700;
    }
    .bd-trend-down { 
        color: #dc3545;
        font-weight: 700;
    }
    .bd-trend-neutral { color: #888; }
</style>

<div class="bd-container">
    <!-- KEY PERFORMANCE INDICATORS -->
    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-database"></i></div>
                <div class="value">{{ number_format($totalSlaughters) }}</div>
                <div class="label">Total Records</div>
                <div class="sublabel">{{ number_format($totalCarcassWeight, 0) }} kg total</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-calendar"></i></div>
                <div class="value">{{ $todayCount }} / {{ $weekCount }} / {{ $monthCount }}</div>
                <div class="label">Today / Week / Month</div>
                <div class="sublabel">{{ $yearCount }} this year</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-certificate"></i></div>
                <div class="value">{{ $qualityRate }}%</div>
                <div class="label">Quality Rate (A+B)</div>
                <div class="sublabel">{{ $gradeA + $gradeB }} high-grade</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-check-circle"></i></div>
                <div class="value">{{ $clearRate }}%</div>
                <div class="label">Clear Inspections</div>
                <div class="sublabel">{{ $clearInspections }} passed</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-industry"></i></div>
                <div class="value">{{ $utilizationRate }}%</div>
                <div class="label">Yield Utilization</div>
                <div class="sublabel">Processing efficiency</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-barcode"></i></div>
                <div class="value">{{ $traceabilityRate }}%</div>
                <div class="label">Traceability</div>
                <div class="sublabel">{{ $packagesWithBarcode }} tagged</div>
            </div>
        </div>
    </div>

    <!-- MONTHLY TREND INDICATOR -->
    @if($monthChange != 0)
    <div class="row">
        <div class="col-md-12">
            <div class="bd-alert">
                <i class="fa fa-info-circle"></i>
                <strong>Monthly Trend:</strong> 
                {{ $currentMonthCount }} records this month vs {{ $lastMonthCount }} last month
                @if($monthChange > 0)
                    (<span class="bd-trend-up"><i class="fa fa-arrow-up"></i> +{{ $monthChange }}% increase</span>)
                @elseif($monthChange < 0)
                    (<span class="bd-trend-down"><i class="fa fa-arrow-down"></i> {{ $monthChange }}% decrease</span>)
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- GRADE DISTRIBUTION ANALYSIS -->
    <div class="bd-section-title">NATIONAL CARCASS QUALITY DISTRIBUTION</div>
    <div class="row">
        <div class="col-md-12">
            <div class="bd-panel">
                @php
                    $gradeTotal = $gradeA + $gradeB + $gradeC + $gradeD + $gradeE + $notGraded;
                    $pctA = $gradeTotal > 0 ? ($gradeA / $gradeTotal) * 100 : 0;
                    $pctB = $gradeTotal > 0 ? ($gradeB / $gradeTotal) * 100 : 0;
                    $pctC = $gradeTotal > 0 ? ($gradeC / $gradeTotal) * 100 : 0;
                    $pctD = $gradeTotal > 0 ? ($gradeD / $gradeTotal) * 100 : 0;
                    $pctE = $gradeTotal > 0 ? ($gradeE / $gradeTotal) * 100 : 0;
                    $pctNone = $gradeTotal > 0 ? ($notGraded / $gradeTotal) * 100 : 0;
                @endphp
                <div class="bd-grade-bar">
                    @if($pctA > 0)
                        <div class="seg" style="width:{{ $pctA }}%;background:#6B3C00;">
                            @if($pctA > 5)A: {{ $gradeA }}@endif
                        </div>
                    @endif
                    @if($pctB > 0)
                        <div class="seg" style="width:{{ $pctB }}%;background:#8B5A1B;">
                            @if($pctB > 5)B: {{ $gradeB }}@endif
                        </div>
                    @endif
                    @if($pctC > 0)
                        <div class="seg" style="width:{{ $pctC }}%;background:#A67C3D;">
                            @if($pctC > 5)C: {{ $gradeC }}@endif
                        </div>
                    @endif
                    @if($pctD > 0)
                        <div class="seg" style="width:{{ $pctD }}%;background:#C4A068;">
                            @if($pctD > 5)D: {{ $gradeD }}@endif
                        </div>
                    @endif
                    @if($pctE > 0)
                        <div class="seg" style="width:{{ $pctE }}%;background:#999;">
                            @if($pctE > 5)E: {{ $gradeE }}@endif
                        </div>
                    @endif
                    @if($pctNone > 0)
                        <div class="seg" style="width:{{ $pctNone }}%;background:#ccc;color:#666;">
                            @if($pctNone > 5)N/A: {{ $notGraded }}@endif
                        </div>
                    @endif
                </div>
                <div class="bd-legend">
                    <span style="background:#6B3C00;"></span>Grade A: {{ $gradeA }} ({{ number_format($pctA, 1) }}%) - Avg: {{ number_format($gradeAAvg, 1) }} kg &nbsp;&nbsp;
                    <span style="background:#8B5A1B;"></span>Grade B: {{ $gradeB }} ({{ number_format($pctB, 1) }}%) - Avg: {{ number_format($gradeBAvg, 1) }} kg &nbsp;&nbsp;
                    <span style="background:#A67C3D;"></span>Grade C: {{ $gradeC }} ({{ number_format($pctC, 1) }}%) - Avg: {{ number_format($gradeCAvg, 1) }} kg &nbsp;&nbsp;
                    @if($gradeD > 0)<span style="background:#C4A068;"></span>Grade D: {{ $gradeD }} ({{ number_format($pctD, 1) }}%) &nbsp;&nbsp;@endif
                    @if($gradeE > 0)<span style="background:#999;"></span>Grade E: {{ $gradeE }} ({{ number_format($pctE, 1) }}%) &nbsp;&nbsp;@endif
                    @if($notGraded > 0)<span style="background:#ccc;"></span>Not Graded: {{ $notGraded }} ({{ number_format($pctNone, 1) }}%)@endif
                </div>
            </div>
        </div>
    </div>

    <!-- PROCESSING & WORKFLOW STATUS -->
    <div class="bd-section-title">PROCESSING PIPELINE STATUS</div>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ number_format($totalQuarters) }}</div>
                <div class="label">Quarters Created</div>
                <div class="sublabel">{{ number_format($quartersWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ number_format($totalPrimalCuts) }}</div>
                <div class="label">Primal Cuts</div>
                <div class="sublabel">{{ number_format($primalWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ number_format($totalOffal) }}</div>
                <div class="label">Offal Items</div>
                <div class="sublabel">{{ number_format($offalWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ number_format($totalPackages) }}</div>
                <div class="label">Total Packages</div>
                <div class="sublabel">{{ number_format($packagesWeight, 0) }} kg</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ $carcassesWithQuarters }}</div>
                <div class="label">Carcasses → Quarters</div>
                <div class="sublabel">{{ $totalSlaughters > 0 ? round(($carcassesWithQuarters / $totalSlaughters) * 100) : 0 }}% processed</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ $carcassesWithCuts }}</div>
                <div class="label">Carcasses → Cuts</div>
                <div class="sublabel">{{ $totalSlaughters > 0 ? round(($carcassesWithCuts / $totalSlaughters) * 100) : 0 }}% processed</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ $carcassesWithPackaging }}</div>
                <div class="label">Carcasses → Packaged</div>
                <div class="sublabel">{{ $totalSlaughters > 0 ? round(($carcassesWithPackaging / $totalSlaughters) * 100) : 0 }}% packaged</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="bd-stat-box">
                <div class="value">{{ $completionRate }}%</div>
                <div class="label">Completion Rate</div>
                <div class="sublabel">{{ $completedRecords }} / {{ $totalSlaughters }} done</div>
            </div>
        </div>
    </div>

    <!-- INSPECTION FINDINGS & HEALTH SURVEILLANCE -->
    <div class="bd-section-title">INSPECTION & HEALTH SURVEILLANCE</div>
    <div class="row">
        <div class="col-md-4">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-stethoscope"></i></div>
                <div class="value">{{ $withAnteFindings }}</div>
                <div class="label">Ante-mortem Findings</div>
                <div class="sublabel">{{ $totalSlaughters > 0 ? round(($withAnteFindings / $totalSlaughters) * 100, 1) : 0 }}% of total</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-microscope"></i></div>
                <div class="value">{{ $withPostFindings }}</div>
                <div class="label">Post-mortem Findings</div>
                <div class="sublabel">{{ $totalSlaughters > 0 ? round(($withPostFindings / $totalSlaughters) * 100, 1) : 0 }}% of total</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bd-stat-box">
                <div class="icon"><i class="fa fa-shield"></i></div>
                <div class="value">{{ $clearInspections }}</div>
                <div class="label">Clear Inspections</div>
                <div class="sublabel">{{ $clearRate }}% pass rate</div>
            </div>
        </div>
    </div>

    @if($recentWithFindings->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="bd-panel">
                <div class="bd-panel-header">
                    <i class="fa fa-exclamation-triangle"></i> RECENT INSPECTION FINDINGS (Last 10 Records)
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <table class="bd-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>E-ID</th>
                                <th>Grade</th>
                                <th>Ante-mortem</th>
                                <th>Post-mortem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentWithFindings as $record)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d M Y') }}</td>
                                <td><strong>{{ $record->e_id }}</strong></td>
                                <td>{{ $record->post_grade ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $ante = $record->has_post_info;
                                        $hasAnte = !empty($ante) && $ante != 'No' && $ante != 'null';
                                    @endphp
                                    @if($hasAnte)
                                        <span style="color:#d9534f;">✓ Findings</span>
                                    @else
                                        <span style="color:#999;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $post = $record->post_other;
                                        $hasPost = !empty($post) && $post != 'null';
                                    @endphp
                                    @if($hasPost)
                                        <span style="color:#d9534f;">✓ Findings</span>
                                    @else
                                        <span style="color:#999;">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- QUICK ACTIONS -->
    <div class="row" style="margin-top:20px;">
        <div class="col-md-12">
            <div style="background:linear-gradient(135deg, #6B3C00 0%, #8B5A1B 100%);border:3px solid #6B3C00;padding:20px;text-align:center;box-shadow: 0 4px 12px rgba(107, 60, 0, 0.3);">
                <a href="{{ admin_url('slaughter-records') }}" class="btn btn-sm" style="background:#fff;color:#6B3C00;margin:5px;padding:10px 20px;font-weight:600;border:2px solid #fff;">
                    <i class="fa fa-list"></i> View All Slaughter Records
                </a>
                <a href="{{ admin_url('butchery-records') }}" class="btn btn-sm" style="background:#fff;color:#6B3C00;margin:5px;padding:10px 20px;font-weight:600;border:2px solid #fff;">
                    <i class="fa fa-table"></i> View Butchery Records
                </a>
                <a href="{{ admin_url('packaging-records') }}" class="btn btn-sm" style="background:#fff;color:#6B3C00;margin:5px;padding:10px 20px;font-weight:600;border:2px solid #fff;">
                    <i class="fa fa-archive"></i> View Packaging Records
                </a>
            </div>
        </div>
    </div>
</div>
