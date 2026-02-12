<style>
/* Butchery Record Show Page - Professional Styling */
.br-show-container {
    background: #f4f4f4;
    padding: 0;
}

.br-show-header {
    background: #ffffff;
    border-bottom: 3px solid #6B3C00;
    padding: 20px 25px;
    margin-bottom: 15px;
}

.br-show-title {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin: 0 0 5px 0;
}

.br-show-subtitle {
    font-size: 13px;
    color: #666;
    margin: 0;
}

.br-show-toolbar {
    margin-top: 15px;
}

.br-show-toolbar .btn {
    margin-right: 8px;
}

.br-show-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.br-show-col-3 {
    flex: 0 0 calc(25% - 12px);
    min-width: 250px;
}

.br-show-col-4 {
    flex: 0 0 calc(33.333% - 10px);
    min-width: 300px;
}

.br-show-col-6 {
    flex: 0 0 calc(50% - 8px);
    min-width: 350px;
}

.br-show-col-8 {
    flex: 0 0 calc(66.666% - 10px);
    min-width: 400px;
}

.br-show-col-12 {
    flex: 0 0 100%;
}

.br-show-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    margin-bottom: 15px;
}

.br-show-card-header {
    background: #f9f9f9;
    border-bottom: 2px solid #6B3C00;
    padding: 12px 15px;
}

.br-show-card-title {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.br-show-card-icon {
    color: #6B3C00;
    margin-right: 8px;
    width: 16px;
    text-align: center;
    display: inline-block;
}

.br-show-card-body {
    padding: 15px;
}

.br-info-group {
    margin-bottom: 15px;
}

.br-info-label {
    font-size: 11px;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 5px;
}

.br-info-value {
    font-size: 14px;
    color: #333;
    font-weight: 500;
    padding: 8px 10px;
    background: #f9f9f9;
    border-left: 3px solid #6B3C00;
}

.br-info-value.large {
    font-size: 18px;
    font-weight: 600;
    color: #6B3C00;
}

.br-badge {
    display: inline-block;
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    background: #6B3C00;
    color: #ffffff;
}

.br-badge.grade-a { background: #2d862d; }
.br-badge.grade-b { background: #5cb85c; }
.br-badge.grade-c { background: #f0ad4e; }
.br-badge.grade-d { background: #d9534f; }
.br-badge.grade-e { background: #c9302c; }
.br-badge.no-grade { background: #777; }

.br-stat-box {
    background: #f9f9f9;
    border-left: 3px solid #6B3C00;
    padding: 12px 15px;
    margin-bottom: 10px;
}

.br-stat-label {
    font-size: 11px;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
}

.br-stat-value {
    font-size: 20px;
    color: #6B3C00;
    font-weight: 600;
    margin-top: 3px;
}

.br-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.br-table thead {
    background: #6B3C00;
    color: #ffffff;
}

.br-table thead th {
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

.br-table tbody td {
    padding: 10px 12px;
    border-bottom: 1px solid #e0e0e0;
}

.br-table tbody tr:hover {
    background: #f9f9f9;
}

.br-table tbody tr:last-child td {
    border-bottom: none;
}

.br-table-container {
    max-height: 350px;
    overflow-y: auto;
    border: 1px solid #e0e0e0;
}

.br-empty-state {
    padding: 30px 20px;
    text-align: center;
    color: #999;
    font-size: 13px;
    font-style: italic;
}

.br-empty-state i {
    font-size: 32px;
    margin-bottom: 10px;
    display: block;
    color: #ccc;
}

.br-divider {
    height: 1px;
    background: #e0e0e0;
    margin: 15px 0;
}

.br-alert {
    padding: 12px 15px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-left-width: 3px;
    color: #856404;
    font-size: 12px;
    margin-bottom: 15px;
}

.br-alert.success {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.br-alert i {
    margin-right: 8px;
}

.br-weight-bar {
    height: 25px;
    background: #e0e0e0;
    position: relative;
    overflow: hidden;
}

.br-weight-bar-fill {
    height: 100%;
    background: #6B3C00;
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    padding: 0 10px;
}

.br-weight-bar-text {
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
}

.br-timeline {
    position: relative;
    padding-left: 30px;
}

.br-timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.br-timeline-item:before {
    content: '';
    position: absolute;
    left: -24px;
    top: 5px;
    width: 10px;
    height: 10px;
    background: #6B3C00;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 2px #6B3C00;
}

.br-timeline-item:after {
    content: '';
    position: absolute;
    left: -20px;
    top: 15px;
    width: 2px;
    height: calc(100% - 10px);
    background: #e0e0e0;
}

.br-timeline-item:last-child:after {
    display: none;
}

.br-timeline-date {
    font-size: 11px;
    color: #999;
    font-weight: 600;
}

.br-timeline-content {
    font-size: 12px;
    color: #333;
    margin-top: 3px;
}

@media (max-width: 992px) {
    .br-show-col-3,
    .br-show-col-4,
    .br-show-col-6,
    .br-show-col-8 {
        flex: 0 0 100%;
    }
}
</style>

<div class="br-show-container">
    <!-- Header -->
    <div class="br-show-header">
        <div class="br-show-title">
            <i class="fa fa-file-text-o" style="color:#6B3C00;"></i>
            Butchery Record Details - {{ $record->e_id ?? 'N/A' }}
        </div>
        <div class="br-show-subtitle">
            Slaughter Date: {{ \Carbon\Carbon::parse($record->created_at)->format('l, d F Y') }} &nbsp;|&nbsp; 
            Facility: {{ $record->slaughter_house->name ?? 'Unknown' }}
        </div>
        <div class="br-show-toolbar">
            <a href="{{ admin_url('butchery-records') }}" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ admin_url('slaughter-records/' . $record->id) }}" class="btn btn-sm btn-primary" target="_blank">
                <i class="fa fa-file-text-o"></i> Full Slaughter Report
            </a>
            <a href="{{ admin_url('slaughter-records/' . $record->id . '/export-pdf') }}" class="btn btn-sm btn-success" target="_blank">
                <i class="fa fa-file-pdf-o"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div style="padding: 0 15px 15px;">
        
        <!-- First Row: Key Statistics -->
        <div class="br-show-row">
            <div class="br-show-col-3">
                <div class="br-stat-box">
                    <div class="br-stat-label"><i class="fa fa-balance-scale"></i> Carcass Weight</div>
                    <div class="br-stat-value">{{ number_format($record->post_weight ?? 0, 1) }} kg</div>
                </div>
            </div>
            <div class="br-show-col-3">
                <div class="br-stat-box">
                    <div class="br-stat-label"><i class="fa fa-archive"></i> Available Weight</div>
                    <div class="br-stat-value">{{ number_format($record->available_weight ?? 0, 1) }} kg</div>
                </div>
            </div>
            <div class="br-show-col-3">
                <div class="br-stat-box">
                    <div class="br-stat-label"><i class="fa fa-star"></i> Carcass Grade</div>
                    <div class="br-stat-value">
                        @php
                            $grade = $record->post_grade ?? 'Not Graded';
                            $gradeClass = 'no-grade';
                            if($grade == 'A') $gradeClass = 'grade-a';
                            elseif($grade == 'B') $gradeClass = 'grade-b';
                            elseif($grade == 'C') $gradeClass = 'grade-c';
                            elseif($grade == 'D') $gradeClass = 'grade-d';
                            elseif($grade == 'E') $gradeClass = 'grade-e';
                        @endphp
                        <span class="br-badge {{ $gradeClass }}">{{ $grade }}</span>
                    </div>
                </div>
            </div>
            <div class="br-show-col-3">
                <div class="br-stat-box">
                    <div class="br-stat-label"><i class="fa fa-percent"></i> Utilization Rate</div>
                    <div class="br-stat-value">
                        @php
                            $utilization = $record->post_weight > 0 
                                ? round(($record->available_weight / $record->post_weight) * 100, 1) 
                                : 0;
                        @endphp
                        {{ $utilization }}%
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row: Animal & Carcass Info -->
        <div class="br-show-row">
            <div class="br-show-col-4">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-tag br-show-card-icon"></i> Animal Identification
                        </h3>
                    </div>
                    <div class="br-show-card-body">
                        <div class="br-info-group">
                            <div class="br-info-label">E-ID (Electronic ID)</div>
                            <div class="br-info-value large">{{ $record->e_id ?? 'N/A' }}</div>
                        </div>
                        <div class="br-info-group">
                            <div class="br-info-label">V-ID (Visual ID)</div>
                            <div class="br-info-value">{{ $record->v_id ?? 'N/A' }}</div>
                        </div>
                        <div class="br-info-group">
                            <div class="br-info-label">LHC (Last Holding Center)</div>
                            <div class="br-info-value">{{ $record->lhc ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="br-show-col-4">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-info-circle br-show-card-icon"></i> Animal Information
                        </h3>
                    </div>
                    <div class="br-show-card-body">
                        <div class="br-info-group">
                            <div class="br-info-label">Sex</div>
                            <div class="br-info-value">{{ $record->sex ?? 'N/A' }}</div>
                        </div>
                        <div class="br-info-group">
                            <div class="br-info-label">Age</div>
                            <div class="br-info-value">{{ $record->post_age ?? 'N/A' }}</div>
                        </div>
                        <div class="br-info-group">
                            <div class="br-info-label">Dentition</div>
                            <div class="br-info-value">{{ $record->post_dentition ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="br-show-col-4">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-clipboard br-show-card-icon"></i> Carcass Assessment
                        </h3>
                    </div>
                    <div class="br-show-card-body">
                        <div class="br-info-group">
                            <div class="br-info-label">Carcass Weight</div>
                            <div class="br-info-value">{{ number_format($record->post_weight ?? 0, 2) }} kg</div>
                        </div>
                        <div class="br-info-group">
                            <div class="br-info-label">Fat Measurement</div>
                            <div class="br-info-value">{{ $record->post_fat ?? 'N/A' }}</div>
                        </div>
                        <div class="br-info-group">
                            <div class="br-info-label">Grade</div>
                            <div class="br-info-value">
                                <span class="br-badge {{ $gradeClass }}">{{ $record->post_grade ?? 'Not Graded' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row: Processing Details -->
        <div class="br-show-row">
            <!-- Quarters -->
            <div class="br-show-col-6">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-th-large br-show-card-icon"></i> Quarters Breakdown
                        </h3>
                    </div>
                    <div class="br-show-card-body" style="padding:0;">
                        @php
                            $quarters = \App\Models\SlaughterDistributionRecord::where('source_id', $record->id)
                                ->whereIn('source_address', [
                                    'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                                    'Hind-1/4 - Right', 'Hind-1/4 - Left'
                                ])
                                ->get();
                            $quarterTotal = $quarters->sum('original_weight');
                        @endphp
                        
                        @if($quarters->isEmpty())
                            <div class="br-empty-state">
                                <i class="fa fa-inbox"></i>
                                No quarters have been created yet
                            </div>
                        @else
                            <table class="br-table">
                                <thead>
                                    <tr>
                                        <th>Quarter</th>
                                        <th>Weight</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quarters as $q)
                                    <tr>
                                        <td><strong>{{ $q->source_address }}</strong></td>
                                        <td>{{ number_format($q->original_weight, 2) }} kg</td>
                                        <td>{{ \Carbon\Carbon::parse($q->created_at)->format('d M Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                    <tr style="background:#f9f9f9;font-weight:600;">
                                        <td>TOTAL</td>
                                        <td>{{ number_format($quarterTotal, 2) }} kg</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Primal Cuts -->
            <div class="br-show-col-6">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-cut br-show-card-icon"></i> Primal Cuts
                        </h3>
                    </div>
                    <div class="br-show-card-body" style="padding:0;">
                        @php
                            $cuts = \App\Models\SlaughterDistributionRecord::where('source_id', $record->id)
                                ->where(function($q) {
                                    $q->where('source_address', 'like', 'Fore-%')
                                      ->orWhere('source_address', 'like', 'Hind-%');
                                })
                                ->whereNotIn('source_address', [
                                    'Fore-1/4 - Right', 'Fore-1/4 - Left', 
                                    'Hind-1/4 - Right', 'Hind-1/4 - Left'
                                ])
                                ->get();
                            $cutsTotal = $cuts->sum('original_weight');
                        @endphp
                        
                        @if($cuts->isEmpty())
                            <div class="br-empty-state">
                                <i class="fa fa-inbox"></i>
                                No primal cuts have been created yet
                            </div>
                        @else
                            <div class="br-table-container">
                                <table class="br-table">
                                    <thead>
                                        <tr>
                                            <th>Cut Type</th>
                                            <th>Weight (kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cuts as $c)
                                        <tr>
                                            <td>{{ $c->source_address }}</td>
                                            <td>{{ number_format($c->original_weight, 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr style="background:#f9f9f9;font-weight:600;">
                                            <td>TOTAL</td>
                                            <td>{{ number_format($cutsTotal, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Fourth Row: Offal & Packaging -->
        <div class="br-show-row">
            <!-- Offal -->
            <div class="br-show-col-6">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-cubes br-show-card-icon"></i> Offal Items
                        </h3>
                    </div>
                    <div class="br-show-card-body" style="padding:0;">
                        @php
                            $offal = \App\Models\SlaughterDistributionRecord::where('source_id', $record->id)
                                ->where('source_address', 'like', 'Offal%')
                                ->get();
                            $offalTotal = $offal->sum('original_weight');
                        @endphp
                        
                        @if($offal->isEmpty())
                            <div class="br-empty-state">
                                <i class="fa fa-inbox"></i>
                                No offal records available
                            </div>
                        @else
                            <table class="br-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Weight (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($offal as $o)
                                    <tr>
                                        <td>{{ $o->source_address }}</td>
                                        <td>{{ number_format($o->original_weight, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr style="background:#f9f9f9;font-weight:600;">
                                        <td>TOTAL</td>
                                        <td>{{ number_format($offalTotal, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Packaging -->
            <div class="br-show-col-6">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-archive br-show-card-icon"></i> Final Packaging
                        </h3>
                    </div>
                    <div class="br-show-card-body" style="padding:0;">
                        @php
                            $packages = \App\Models\PackagingRecord::where('slaughter_record_id', $record->id)->get();
                            $packageTotal = $packages->sum('total_weight');
                        @endphp
                        
                        @if($packages->isEmpty())
                            <div class="br-empty-state">
                                <i class="fa fa-inbox"></i>
                                No packages have been created yet
                            </div>
                        @else
                            <div class="br-table-container">
                                <table class="br-table">
                                    <thead>
                                        <tr>
                                            <th>Package Type</th>
                                            <th>Weight</th>
                                            <th>Barcode</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($packages as $p)
                                        <tr>
                                            <td>{{ $p->package_type }}</td>
                                            <td>{{ number_format($p->total_weight, 2) }} kg</td>
                                            <td>{{ $p->barcode ?? '—' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($p->packaging_date)->format('d M Y') }}</td>
                                        </tr>
                                        @endforeach
                                        <tr style="background:#f9f9f9;font-weight:600;">
                                            <td colspan="2">TOTAL</td>
                                            <td colspan="2">{{ number_format($packageTotal, 2) }} kg</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Fifth Row: Inspection Results -->
        <div class="br-show-row">
            <div class="br-show-col-12">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-shield br-show-card-icon"></i> Inspection & Compliance
                        </h3>
                    </div>
                    <div class="br-show-card-body">
                        <div class="br-show-row">
                            <div class="br-show-col-6">
                                <div class="br-info-group">
                                    <div class="br-info-label">Ante-mortem Findings</div>
                                    @php
                                        $anteFindings = $record->has_post_info;
                                        $hasAnte = !empty($anteFindings) && $anteFindings != 'No' && $anteFindings != 'null';
                                    @endphp
                                    @if($hasAnte)
                                        <div class="br-alert">
                                            <i class="fa fa-warning"></i>
                                            {{ $anteFindings }}
                                        </div>
                                    @else
                                        <div class="br-alert success">
                                            <i class="fa fa-check-circle"></i>
                                            No ante-mortem findings - Animal passed inspection
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="br-show-col-6">
                                <div class="br-info-group">
                                    <div class="br-info-label">Post-mortem Findings</div>
                                    @php
                                        $postFindings = $record->post_other;
                                        $hasPost = !empty($postFindings) && $postFindings != 'null';
                                    @endphp
                                    @if($hasPost)
                                        <div class="br-alert">
                                            <i class="fa fa-warning"></i>
                                            {{ $postFindings }}
                                        </div>
                                    @else
                                        <div class="br-alert success">
                                            <i class="fa fa-check-circle"></i>
                                            No post-mortem findings - Carcass cleared for processing
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="br-divider"></div>

                        <div class="br-info-group">
                            <div class="br-info-label">Slaughtered By</div>
                            <div class="br-info-value">
                                @php
                                    $admin = \Encore\Admin\Auth\Database\Administrator::find($record->administrator_id);
                                @endphp
                                {{ $admin ? $admin->name : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processing Summary Visualization -->
        <div class="br-show-row">
            <div class="br-show-col-12">
                <div class="br-show-card">
                    <div class="br-show-card-header">
                        <h3 class="br-show-card-title">
                            <i class="fa fa-pie-chart br-show-card-icon"></i> Weight Distribution Summary
                        </h3>
                    </div>
                    <div class="br-show-card-body">
                        @php
                            $totalWeight = $record->post_weight ?? 0;
                            $quartersWeight = $quarters->sum('original_weight');
                            $cutsWeight = $cuts->sum('original_weight');
                            $offalWeight = $offal->sum('original_weight');
                            $packagesWeight = $packages->sum('total_weight');
                            
                            $quartetsPerc = $totalWeight > 0 ? ($quartersWeight / $totalWeight) * 100 : 0;
                            $cutsPerc = $totalWeight > 0 ? ($cutsWeight / $totalWeight) * 100 : 0;
                            $offalPerc = $totalWeight > 0 ? ($offalWeight / $totalWeight) * 100 : 0;
                            $packagesPerc = $totalWeight > 0 ? ($packagesWeight / $totalWeight) * 100 : 0;
                        @endphp
                        
                        <div class="br-info-group">
                            <div class="br-info-label">
                                Quarters: {{ number_format($quartersWeight, 2) }} kg ({{ number_format($quartetsPerc, 1) }}%)
                            </div>
                            <div class="br-weight-bar">
                                <div class="br-weight-bar-fill" style="width:{{ $quartetsPerc }}%;">
                                    <span class="br-weight-bar-text">{{ number_format($quartersWeight, 1) }} kg</span>
                                </div>
                            </div>
                        </div>

                        <div class="br-info-group">
                            <div class="br-info-label">
                                Primal Cuts: {{ number_format($cutsWeight, 2) }} kg ({{ number_format($cutsPerc, 1) }}%)
                            </div>
                            <div class="br-weight-bar">
                                <div class="br-weight-bar-fill" style="width:{{ $cutsPerc }}%;">
                                    <span class="br-weight-bar-text">{{ number_format($cutsWeight, 1) }} kg</span>
                                </div>
                            </div>
                        </div>

                        <div class="br-info-group">
                            <div class="br-info-label">
                                Offal: {{ number_format($offalWeight, 2) }} kg ({{ number_format($offalPerc, 1) }}%)
                            </div>
                            <div class="br-weight-bar">
                                <div class="br-weight-bar-fill" style="width:{{ $offalPerc }}%;">
                                    <span class="br-weight-bar-text">{{ number_format($offalWeight, 1) }} kg</span>
                                </div>
                            </div>
                        </div>

                        <div class="br-info-group" style="margin-bottom:0;">
                            <div class="br-info-label">
                                Packaged: {{ number_format($packagesWeight, 2) }} kg ({{ number_format($packagesPerc, 1) }}%)
                            </div>
                            <div class="br-weight-bar">
                                <div class="br-weight-bar-fill" style="width:{{ $packagesPerc }}%;">
                                    <span class="br-weight-bar-text">{{ number_format($packagesWeight, 1) }} kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
