<style>
    .br-stats { margin-bottom: 15px; }
    .br-stats .row { display: flex; flex-wrap: wrap; margin: 0 -5px; }
    .br-stats .row > div { padding: 0 5px; display: flex; margin-bottom: 10px; }
    .br-stat-box {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-left: 3px solid #6B3C00;
        padding: 12px 15px;
        flex: 1;
        min-height: 75px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .br-stat-box .val {
        font-size: 22px;
        font-weight: 700;
        color: #333;
        line-height: 1.2;
    }
    .br-stat-box .lbl {
        font-size: 10px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 3px;
    }
    .br-stat-box .sub {
        font-size: 10px;
        color: #888;
        margin-top: 2px;
    }
    .br-grade-bar {
        display: flex;
        height: 24px;
        overflow: hidden;
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        margin-bottom: 8px;
    }
    .br-grade-bar .seg {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .br-grade-bar .seg:hover { opacity: 0.85; }
    .br-grade-legend {
        font-size: 10px;
        color: #666;
        padding: 5px 0;
    }
    .br-grade-legend span {
        display: inline-block;
        width: 10px;
        height: 10px;
        margin-right: 4px;
        vertical-align: middle;
        border: 1px solid rgba(0,0,0,0.1);
    }
    .br-section-title {
        font-size: 12px;
        font-weight: 700;
        color: #6B3C00;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 15px 0 10px 0;
        padding-bottom: 5px;
        border-bottom: 2px solid #6B3C00;
    }
    .br-table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
    }
    .br-table th {
        background: #6B3C00;
        color: #fff;
        padding: 8px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 10px;
        text-transform: uppercase;
    }
    .br-table td {
        padding: 6px 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    .br-table tr:hover { background: #f9f9f9; }
</style>

<div class="br-stats">
    <!-- OVERVIEW SECTION -->
    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ number_format($totalSlaughters) }}</div>
                <div class="lbl">Total Records</div>
                <div class="sub">{{ $completionRate }}% Complete</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ $todayCount }} / {{ $weekCount }} / {{ $monthCount }}</div>
                <div class="lbl">Today / Week / Month</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ number_format($totalCarcassWeight, 0) }} <small style="font-size:11px;">kg</small></div>
                <div class="lbl">Total Carcass Weight</div>
                <div class="sub">Avg: {{ $avgCarcassWeight }} kg</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ $qualityRate }}%</div>
                <div class="lbl">Quality Rate (A+B)</div>
                <div class="sub">{{ $gradeA }} Grade A</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ $inspectionRate }}%</div>
                <div class="lbl">Clear Inspection</div>
                <div class="sub">{{ $withAnteFindings + $withPostFindings }} findings</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ $utilizationRate }}%</div>
                <div class="lbl">Yield Utilization</div>
                <div class="sub">Processing efficiency</div>
            </div>
        </div>
    </div>

    <!-- GRADE DISTRIBUTION -->
    <div class="row" style="display:block; margin-top: 10px;">
        <div class="col-md-12">
            <div style="background:#fff; border: 1px solid #e0e0e0; padding: 12px 15px;">
                <div style="font-size:11px; font-weight:600; color:#333; margin-bottom:8px;">CARCASS GRADE DISTRIBUTION</div>
                @php
                    $gradeTotal = $gradeA + $gradeB + $gradeC + $gradeOther + $notGraded;
                    $pctA = $gradeTotal > 0 ? ($gradeA / $gradeTotal) * 100 : 0;
                    $pctB = $gradeTotal > 0 ? ($gradeB / $gradeTotal) * 100 : 0;
                    $pctC = $gradeTotal > 0 ? ($gradeC / $gradeTotal) * 100 : 0;
                    $pctOther = $gradeTotal > 0 ? ($gradeOther / $gradeTotal) * 100 : 0;
                    $pctNone = $gradeTotal > 0 ? ($notGraded / $gradeTotal) * 100 : 0;
                @endphp
                <div class="br-grade-bar">
                    @if($pctA > 0)
                        <div class="seg" style="width:{{ $pctA }}%;background:#6B3C00;" title="Grade A: {{ $gradeA }}">
                            @if($pctA > 8)A: {{ $gradeA }}@endif
                        </div>
                    @endif
                    @if($pctB > 0)
                        <div class="seg" style="width:{{ $pctB }}%;background:#8B5A1B;" title="Grade B: {{ $gradeB }}">
                            @if($pctB > 8)B: {{ $gradeB }}@endif
                        </div>
                    @endif
                    @if($pctC > 0)
                        <div class="seg" style="width:{{ $pctC }}%;background:#A67C3D;" title="Grade C: {{ $gradeC }}">
                            @if($pctC > 8)C: {{ $gradeC }}@endif
                        </div>
                    @endif
                    @if($pctOther > 0)
                        <div class="seg" style="width:{{ $pctOther }}%;background:#999;" title="Other Grades: {{ $gradeOther }}">
                            @if($pctOther > 8)Other: {{ $gradeOther }}@endif
                        </div>
                    @endif
                    @if($pctNone > 0)
                        <div class="seg" style="width:{{ $pctNone }}%;background:#ccc;color:#666;" title="Not Graded: {{ $notGraded }}">
                            @if($pctNone > 8)None: {{ $notGraded }}@endif
                        </div>
                    @endif
                </div>
                <div class="br-grade-legend">
                    <span style="background:#6B3C00;"></span>Grade A: {{ $gradeA }} ({{ number_format($pctA, 1) }}%) &nbsp;
                    <span style="background:#8B5A1B;"></span>Grade B: {{ $gradeB }} ({{ number_format($pctB, 1) }}%) &nbsp;
                    <span style="background:#A67C3D;"></span>Grade C: {{ $gradeC }} ({{ number_format($pctC, 1) }}%) &nbsp;
                    @if($gradeOther > 0)
                        <span style="background:#999;"></span>Other: {{ $gradeOther }} ({{ number_format($pctOther, 1) }}%) &nbsp;
                    @endif
                    @if($notGraded > 0)
                        <span style="background:#ccc;"></span>Not Graded: {{ $notGraded }} ({{ number_format($pctNone, 1) }}%)
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- PROCESSING STATISTICS -->
    <div class="br-section-title" style="margin-top:20px;">Processing Statistics</div>
    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ number_format($totalQuarters) }}</div>
                <div class="lbl">Quarters</div>
                <div class="sub">{{ number_format($quartersWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ number_format($primalCuts) }}</div>
                <div class="lbl">Primal Cuts</div>
                <div class="sub">{{ number_format($primalWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ number_format($offalCuts) }}</div>
                <div class="lbl">Offal Items</div>
                <div class="sub">{{ number_format($offalWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ number_format($totalPackages) }}</div>
                <div class="lbl">Total Packages</div>
                <div class="sub">{{ number_format($packagesWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ number_format($packagesWithBarcode) }}</div>
                <div class="lbl">Barcoded Packages</div>
                <div class="sub">{{ $totalPackages > 0 ? round(($packagesWithBarcode / $totalPackages) * 100) : 0 }}% tagged</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="br-stat-box">
                <div class="val">{{ $completedRecords }} / {{ $ongoingRecords }}</div>
                <div class="lbl">Completed / Ongoing</div>
            </div>
        </div>
    </div>

    <!-- TOP PERFORMERS & RECENT ACTIVITY -->
    <div class="row" style="margin-top:15px;">
        <!-- Most Common Cut Types -->
        <div class="col-md-6">
            <div style="background:#fff; border: 1px solid #e0e0e0; padding: 12px 15px;">
                <div style="font-size:11px; font-weight:600; color:#333; margin-bottom:10px;">
                    <i class="fa fa-cut"></i> MOST COMMON CUT TYPES
                </div>
                @if($topCutTypes->count() > 0)
                    <table class="br-table">
                        <thead>
                            <tr>
                                <th>Cut Type</th>
                                <th style="text-align:right;">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCutTypes as $cut)
                            <tr>
                                <td>{{ $cut->source_address }}</td>
                                <td style="text-align:right;"><strong>{{ number_format($cut->total) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding:20px;text-align:center;color:#999;font-size:11px;">No cut data available</div>
                @endif
            </div>
        </div>

        <!-- Top Slaughter Houses -->
        <div class="col-md-6">
            <div style="background:#fff; border: 1px solid #e0e0e0; padding: 12px 15px;">
                <div style="font-size:11px; font-weight:600; color:#333; margin-bottom:10px;">
                    <i class="fa fa-building"></i> TOP PERFORMING FACILITIES
                </div>
                @if($topHouses->count() > 0)
                    <table class="br-table">
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th style="text-align:right;">Records</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topHouses as $house)
                            <tr>
                                <td>{{ $house->destination_slaughter_house ?? 'N/A' }}</td>
                                <td style="text-align:right;"><strong>{{ number_format($house->total) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding:20px;text-align:center;color:#999;font-size:11px;">No facility data available</div>
                @endif
            </div>
        </div>
    </div>

    <!-- RECENT HIGH-GRADE CARCASSES -->
    @if($recentHighGrade->count() > 0)
    <div class="row" style="margin-top:15px;">
        <div class="col-md-12">
            <div style="background:#fff; border: 1px solid #e0e0e0; padding: 12px 15px;">
                <div style="font-size:11px; font-weight:600; color:#333; margin-bottom:10px;">
                    <i class="fa fa-star"></i> RECENT HIGH-GRADE CARCASSES (Grade A & B)
                </div>
                <table class="br-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>E-ID</th>
                            <th>Grade</th>
                            <th>Weight</th>
                            <th>Sex</th>
                            <th>Age</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentHighGrade as $record)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d M Y') }}</td>
                            <td><strong>{{ $record->e_id }}</strong></td>
                            <td><span style="color:#6B3C00;font-weight:600;">{{ $record->post_grade }}</span></td>
                            <td>{{ $record->post_weight }} kg</td>
                            <td>{{ $record->sex ?? '-' }}</td>
                            <td>{{ $record->post_age ?? '-' }}</td>
                            <td>
                                @if(strtolower($record->breed) == 'done')
                                    <span style="color:#6B3C00;">✓ Complete</span>
                                @else
                                    <span style="color:#999;">Ongoing</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
