<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaughter Record Report - {{ $record->e_id }}</title>
    <link rel="stylesheet" href="{{ asset('css/slaughter-record-report.css') }}">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header Section -->
        <div class="report-header">
            <div class="logo-section">
                <h1 class="company-name">Slaughter Record Report</h1>
                <p class="report-subtitle">Comprehensive Carcass Distribution Analysis</p>
            </div>
            <div class="report-meta">
                <div class="meta-item">
                    <span class="meta-label">Report Date:</span>
                    <span class="meta-value">{{ date('d M Y, H:i') }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Record ID:</span>
                    <span class="meta-value">#{{ $record->id }}</span>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <span>🖨️</span> Print Report
            </button>
            <button onclick="window.history.back()" class="btn btn-secondary">
                <span>←</span> Back
            </button>
        </div>

        <!-- Animal Identification & Slaughter Details -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">🐄</span>
                    Animal Identification & Slaughter Details
                </h2>
            </div>
            <div class="section-body">
                <table class="info-table">
                    <tr>
                        <td class="label-col">Visual ID (V-ID)</td>
                        <td class="value-col strong">{{ $record->v_id }}</td>
                        <td class="label-col">Breed</td>
                        <td class="value-col">{{ $record->breed }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Electronic ID (E-ID)</td>
                        <td class="value-col strong">{{ $record->e_id }}</td>
                        <td class="label-col">Sex</td>
                        <td class="value-col">{{ $record->sex }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">LHC Number</td>
                        <td class="value-col strong">{{ $record->lhc }}</td>
                        <td class="label-col">Date of Birth</td>
                        <td class="value-col">{{ $record->dob }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Bar Code</td>
                        <td class="value-col">
                            @if($record->bar_code)
                                <img src="{{ asset($record->bar_code) }}" alt="Barcode" class="barcode-image">
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="label-col">Age at Slaughter</td>
                        <td class="value-col">{{ $record->post_age ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Slaughter House</td>
                        <td class="value-col">{{ $record->destination_slaughter_house }}</td>
                        <td class="label-col">Dentition</td>
                        <td class="value-col">{{ $record->post_dentition ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Slaughter Date</td>
                        <td class="value-col">{{ $record->created_at->format('d M Y') }}</td>
                        <td class="label-col">Administrator ID</td>
                        <td class="value-col">{{ $record->administrator_id }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Post-Slaughter Assessment -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">📊</span>
                    Post-Slaughter Assessment
                </h2>
            </div>
            <div class="section-body">
                <table class="info-table">
                    <tr>
                        <td class="label-col">Carcass Weight</td>
                        <td class="value-col weight-highlight">{{ $record->post_weight }} KGs</td>
                        <td class="label-col">Available Weight</td>
                        <td class="value-col weight-highlight">{{ $record->available_weight }} KGs</td>
                        <td class="label-col">Grade</td>
                        <td class="value-col">{{ $record->post_grade ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Animal Type</td>
                        <td class="value-col">{{ $record->post_animal ?? 'N/A' }}</td>
                        <td class="label-col">Fat Score</td>
                        <td class="value-col">{{ $record->post_fat ?? 'N/A' }}</td>
                        <td class="label-col">Additional Notes</td>
                        <td class="value-col">{{ $record->post_other ?? 'None' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Quarter Record Section -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">🥩</span>
                    Quarter Record - EID: {{ $record->e_id }}
                </h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header-primary">
                                <th>Quarter Type</th>
                                <th class="text-center">Fore Right (KGs)</th>
                                <th class="text-center">Fore Left (KGs)</th>
                                <th class="text-center">Hind Right (KGs)</th>
                                <th class="text-center">Hind Left (KGs)</th>
                                <th class="text-center total-col">Total (KGs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="row-label">Quarters</td>
                                <td class="text-center"><span class="badge badge-primary">{{ $foreRightWeight }}</span></td>
                                <td class="text-center"><span class="badge badge-primary">{{ $foreLeftWeight }}</span></td>
                                <td class="text-center"><span class="badge badge-success">{{ $hindRightWeight }}</span></td>
                                <td class="text-center"><span class="badge badge-success">{{ $hindLeftWeight }}</span></td>
                                <td class="text-center total-col"><span class="total-value">{{ $qTotal }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Fore Quarters - Primal Cuts -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">🔪</span>
                    Fore Quarters - Primal Cuts Record
                </h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header-warning">
                                <th>Primal Cut</th>
                                <th class="text-center">Fore Left (KGs)</th>
                                <th class="text-center">Fore Right (KGs)</th>
                                <th class="text-center total-col">Total (KGs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $foreCuts = ['Beef boneless', 'Beef Stew', 'Bones', 'Brisket', 'Chops', 'Chuck ribs', 'Family Steak', 'Fore rib', 'Leg Cut'];
                                $totalFL = 0;
                                $totalFR = 0;
                            @endphp
                            @foreach($foreCuts as $cut)
                                @php
                                    $fl = $primeCuts->where('source_address', 'like', "%Fore%Left%$cut%")->first();
                                    $fr = $primeCuts->where('source_address', 'like', "%Fore%Right%$cut%")->first();
                                    $flW = $fl ? $fl->original_weight : 0;
                                    $frW = $fr ? $fr->original_weight : 0;
                                    $totalFL += $flW;
                                    $totalFR += $frW;
                                @endphp
                                <tr>
                                    <td class="row-label">{{ $cut }}</td>
                                    <td class="text-center">
                                        @if($flW > 0)
                                            <span class="badge badge-info">{{ $flW }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($frW > 0)
                                            <span class="badge badge-info">{{ $frW }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $flW + $frW }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td class="row-label"><strong>TOTALS</strong></td>
                                <td class="text-center"><span class="total-value">{{ $totalFL }}</span></td>
                                <td class="text-center"><span class="total-value">{{ $totalFR }}</span></td>
                                <td class="text-center total-col"><span class="total-value">{{ $totalFL + $totalFR }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Hind Quarters - Primal Cuts -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">🥓</span>
                    Hind Quarters - Primal Cuts Record
                </h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header-danger">
                                <th>Primal Cut</th>
                                <th class="text-center">Hind Left (KGs)</th>
                                <th class="text-center">Hind Right (KGs)</th>
                                <th class="text-center total-col">Total (KGs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $hindCuts = ['Fillet', 'Oxtail', 'Rib eye', 'Rolled loin', 'Rump', 'Silver side', 'Sirloin / Striploin', 'T Bone', 'Topside /Beef Roast', 'Veal Steak'];
                                $totalHL = 0;
                                $totalHR = 0;
                            @endphp
                            @foreach($hindCuts as $cut)
                                @php
                                    $hl = $primeCuts->where('source_address', 'like', "%Hind%Left%$cut%")->first();
                                    $hr = $primeCuts->where('source_address', 'like', "%Hind%Right%$cut%")->first();
                                    $hlW = $hl ? $hl->original_weight : 0;
                                    $hrW = $hr ? $hr->original_weight : 0;
                                    $totalHL += $hlW;
                                    $totalHR += $hrW;
                                @endphp
                                <tr>
                                    <td class="row-label">{{ $cut }}</td>
                                    <td class="text-center">
                                        @if($hlW > 0)
                                            <span class="badge badge-success">{{ $hlW }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($hrW > 0)
                                            <span class="badge badge-success">{{ $hrW }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $hlW + $hrW }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td class="row-label"><strong>TOTALS</strong></td>
                                <td class="text-center"><span class="total-value">{{ $totalHL }}</span></td>
                                <td class="text-center"><span class="total-value">{{ $totalHR }}</span></td>
                                <td class="text-center total-col"><span class="total-value">{{ $totalHL + $totalHR }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Offals Record -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">🫀</span>
                    Offals Record - EID: {{ $record->e_id }}
                </h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="data-table offals-table">
                        <thead>
                            <tr class="table-header-success">
                                <th>Offal Type</th>
                                @php
                                    $offalTypes = ['Heart', 'Kidneys', 'Liver', 'Brain', 'Tongue', 'Tripe', 'Tail', 'Head', 'Other'];
                                @endphp
                                @foreach($offalTypes as $type)
                                    <th class="text-center">{{ $type }}</th>
                                @endforeach
                                <th class="text-center total-col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="row-label">Weight (KGs)</td>
                                @php
                                    $offalTotal = 0;
                                @endphp
                                @foreach($offalTypes as $type)
                                    @php
                                        $offal = $offalCuts->where('source_address', 'like', "%$type%")->first();
                                        $weight = $offal ? $offal->original_weight : 0;
                                        $offalTotal += $weight;
                                    @endphp
                                    <td class="text-center">
                                        @if($weight > 0)
                                            <span class="badge badge-warning">{{ $weight }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center total-col"><span class="total-value">{{ $offalTotal }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Report Footer -->
        <div class="report-footer">
            <div class="footer-line"></div>
            <div class="footer-content">
                <div class="footer-left">
                    <p class="footer-text">Generated on {{ date('d M Y, H:i:s') }}</p>
                    <p class="footer-text">Slaughter Record Management System</p>
                </div>
                <div class="footer-right">
                    <p class="footer-text">Report ID: #{{ $record->id }}</p>
                    <p class="footer-text">Page 1 of 1</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
