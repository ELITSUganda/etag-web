<style>
    .bd-container { padding: 0; }
    .bd-stat-box {
        background: #fff;
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 12px;
        min-height: 95px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .bd-stat-box .icon {
        font-size: 28px;
        color: #6B3C00;
        margin-bottom: 8px;
    }
    .bd-stat-box .value {
        font-size: 26px;
        font-weight: 700;
        color: #333;
        line-height: 1.1;
    }
    .bd-stat-box .label {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 3px;
    }
    .bd-stat-box .sublabel {
        font-size: 10px;
        color: #888;
        margin-top: 2px;
    }
    .bd-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #6B3C00;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 20px 0 12px 0;
        padding-bottom: 6px;
        border-bottom: 2px solid #6B3C00;
    }
    .bd-panel {
        background: #fff;
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin-bottom: 15px;
    }
    .bd-panel-header {
        font-size: 12px;
        font-weight: 700;
        color: #333;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e0e0e0;
    }
    .bd-panel-header i {
        color: #6B3C00;
        margin-right: 6px;
    }
    .bd-grade-bar {
        display: flex;
        height: 28px;
        overflow: hidden;
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        margin-bottom: 10px;
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
        color: #666;
        padding: 5px 0;
    }
    .bd-legend span {
        display: inline-block;
        width: 12px;
        height: 12px;
        margin-right: 4px;
        vertical-align: middle;
        border: 1px solid rgba(0,0,0,0.1);
    }
    .bd-table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
    }
    .bd-table th {
        background: #6B3C00;
        color: #fff;
        padding: 8px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 10px;
        text-transform: uppercase;
    }
    .bd-table td {
        padding: 7px 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    .bd-table tr:hover { background: #f9f9f9; }
    .bd-alert {
        background: #fff3cd;
        border: 1px solid #e0d4a8;
        padding: 10px 12px;
        margin-bottom: 10px;
        font-size: 11px;
        color: #856404;
    }
    .bd-alert i { margin-right: 6px; }
    .bd-trend-up { color: #28a745; }
    .bd-trend-down { color: #dc3545; }
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

    <!-- TOP FACILITIES & DEMOGRAPHICS -->
    <div class="row">
        <div class="col-md-6">
            <div class="bd-panel">
                <div class="bd-panel-header">
                    <i class="fa fa-building"></i> TOP PERFORMING FACILITIES
                </div>
                @if($topFacilities->count() > 0)
                    <table class="bd-table">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th style="text-align:right;">Records</th>
                                <th style="text-align:right;">Total Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topFacilities as $facility)
                            <tr>
                                <td>{{ $facility->destination_slaughter_house }}</td>
                                <td style="text-align:right;"><strong>{{ number_format($facility->total) }}</strong></td>
                                <td style="text-align:right;">{{ number_format($facility->total_weight, 0) }} kg</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding:30px;text-align:center;color:#999;font-size:11px;">No facility data available</div>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="bd-panel">
                <div class="bd-panel-header">
                    <i class="fa fa-pie-chart"></i> ANIMAL DEMOGRAPHICS
                </div>
                <table class="bd-table">
                    <tr>
                        <td><strong>Sex Distribution</strong></td>
                        <td>Male: {{ $maleCount }} | Female: {{ $femaleCount }}
                            @if($unknownSex > 0) | Unknown: {{ $unknownSex }}@endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Average Carcass Weight</strong></td>
                        <td>{{ number_format($avgCarcassWeight, 1) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>Total Processed Weight</strong></td>
                        <td>{{ number_format($totalCarcassWeight, 0) }} kg</td>
                    </tr>
                </table>
                
                @if($ageDistribution->count() > 0)
                <div style="margin-top:15px;">
                    <div style="font-size:11px;font-weight:600;margin-bottom:8px;">AGE DISTRIBUTION</div>
                    <table class="bd-table">
                        <thead>
                            <tr>
                                <th>Age</th>
                                <th style="text-align:right;">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ageDistribution->take(5) as $age)
                            <tr>
                                <td>{{ $age->post_age }}</td>
                                <td style="text-align:right;">{{ $age->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="row" style="margin-top:15px;">
        <div class="col-md-12">
            <div style="background:#f9f9f9;border:1px solid #e0e0e0;padding:15px;text-align:center;">
                <a href="{{ admin_url('slaughter-records') }}" class="btn btn-sm" style="background:#6B3C00;color:#fff;margin:3px;">
                    <i class="fa fa-list"></i> View All Slaughter Records
                </a>
                <a href="{{ admin_url('butchery-records') }}" class="btn btn-sm" style="background:#6B3C00;color:#fff;margin:3px;">
                    <i class="fa fa-table"></i> View Butchery Records
                </a>
                <a href="{{ admin_url('packaging-records') }}" class="btn btn-sm" style="background:#6B3C00;color:#fff;margin:3px;">
                    <i class="fa fa-archive"></i> View Packaging Records
                </a>
            </div>
        </div>
    </div>
</div>
                    </li>
                    @empty
                    <li class="item">
                        <div class="product-info">
                            <span class="product-title text-muted">No prime cuts recorded yet</span>
                        </div>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Top Offal Cuts -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-heart"></i> Top Offal Cuts</h3>
            </div>
            <div class="box-body">
                <ul class="products-list product-list-in-box">
                    @forelse($topOffalCuts as $cut)
                    <li class="item">
                        <div class="product-info">
                            <a href="{{ admin_url('butcher-records?offal_cut_type=' . urlencode($cut->offal_cut_type)) }}" class="product-title">
                                {{ $cut->offal_cut_type }}
                                <span class="label label-warning pull-right">{{ $cut->total }} records</span>
                            </a>
                        </div>
                    </li>
                    @empty
                    <li class="item">
                        <div class="product-info">
                            <span class="product-title text-muted">No offal cuts recorded yet</span>
                        </div>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sales -->
<div class="row">
    <div class="col-xs-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-money"></i> Recent Sales</h3>
                <div class="box-tools pull-right">
                    <a href="{{ admin_url('butcher-records?is_sold=Yes') }}" class="btn btn-sm btn-success">View All Sales</a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date Sold</th>
                            <th>Barcode</th>
                            <th>Cut Type</th>
                            <th>Cut Name</th>
                            <th>Weight Sold (Kgs)</th>
                            <th>Price (UGX)</th>
                            <th>Buyer</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->sold_date ? \Carbon\Carbon::parse($sale->sold_date)->format('d/m/Y') : 'N/A' }}</td>
                            <td><code>{{ substr($sale->barcode, 0, 15) }}...</code></td>
                            <td>
                                @if($sale->cut_type == 'Prime Cut')
                                    <span class="label label-primary">Prime Cut</span>
                                @else
                                    <span class="label label-warning">Offal Cut</span>
                                @endif
                            </td>
                            <td>{{ $sale->cut_type == 'Prime Cut' ? $sale->prime_cut_type : $sale->offal_cut_type }}</td>
                            <td><strong>{{ number_format($sale->original_weight - $sale->current_weight, 2) }}</strong></td>
                            <td><strong>{{ number_format($sale->sold_price) }}</strong></td>
                            <td>{{ $sale->buyer_name ?? 'N/A' }}</td>
                            <td>
                                @if($sale->slaughterDistributionRecord && $sale->slaughterDistributionRecord->animal)
                                    {{ $sale->slaughterDistributionRecord->animal->source_name ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No sales recorded yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
