<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slaughter Record - {{ $record->e_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; line-height: 1.4; color: #333; padding: 15mm; }
        
        /* Header */
        .header { background-color: #6B3C00; color: #fff; padding: 15px 20px; margin-bottom: 20px; }
        .header h1 { font-size: 16pt; font-weight: bold; margin: 0; }
        .header-meta { font-size: 9pt; margin-top: 5px; opacity: 0.9; }
        
        /* Sections */
        .section { border: 1px solid #ddd; margin-bottom: 15px; page-break-inside: avoid; }
        .section-header { background-color: #f5f5f5; border-bottom: 1px solid #ddd; padding: 8px 12px; }
        .section-header h2 { font-size: 10pt; font-weight: bold; color: #6B3C00; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-body { padding: 12px; }
        
        /* Info Tables */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 6px 8px; vertical-align: top; }
        .info-table .lbl { font-size: 8pt; color: #888; text-transform: uppercase; letter-spacing: 0.3px; }
        .info-table .val { font-size: 10pt; font-weight: 500; color: #333; }
        .info-table .val.highlight { color: #6B3C00; font-weight: bold; }
        
        /* Data Tables */
        table.data-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        table.data-table th { background-color: #6B3C00; color: #fff; padding: 6px 8px; text-align: left; font-weight: 500; text-transform: uppercase; font-size: 8pt; letter-spacing: 0.3px; border: 1px solid #6B3C00; }
        table.data-table td { padding: 5px 8px; text-align: left; border: 1px solid #ddd; background-color: #fff; color: #333; }
        table.data-table th.center, table.data-table td.center { text-align: center; }
        table.data-table td.label { font-weight: 500; }
        table.data-table tr.total-row td { border-top: 2px solid #6B3C00; font-weight: bold; }
        table.data-table .empty-msg { text-align: center; color: #888; padding: 15px; }
        
        /* Footer */
        .footer { border-top: 2px solid #6B3C00; padding-top: 10px; margin-top: 20px; font-size: 8pt; color: #888; }
        .footer-table { width: 100%; }
        .footer-table td { padding: 0; }
        .footer-table td:last-child { text-align: right; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>SLAUGHTER RECORD REPORT</h1>
        <div class="header-meta">
            Record #{{ $record->id }} | {{ date('d M Y, H:i') }}
        </div>
    </div>

    <!-- Animal Identification -->
    <div class="section">
        <div class="section-header">
            <h2>Animal Identification</h2>
        </div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td width="33%">
                        <div class="lbl">Visual ID (V-ID)</div>
                        <div class="val">{{ $record->v_id ?? 'N/A' }}</div>
                    </td>
                    <td width="33%">
                        <div class="lbl">Electronic ID (E-ID)</div>
                        <div class="val">{{ $record->e_id ?? 'N/A' }}</div>
                    </td>
                    <td width="34%">
                        <div class="lbl">LHC Number</div>
                        <div class="val">{{ $record->lhc ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="lbl">Breed</div>
                        <div class="val">{{ $record->breed ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="lbl">Sex</div>
                        <div class="val">{{ $record->sex ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="lbl">Date of Birth</div>
                        <div class="val">{{ $record->dob ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="lbl">Slaughter House</div>
                        <div class="val">{{ $record->destination_slaughter_house ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="lbl">Slaughter Date</div>
                        <div class="val">{{ $record->created_at->format('d M Y') }}</div>
                    </td>
                    <td>
                        <div class="lbl">Administrator</div>
                        <div class="val">{{ $record->administrator_id ?? 'N/A' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Post-Slaughter Assessment -->
    <div class="section">
        <div class="section-header">
            <h2>Post-Slaughter Assessment</h2>
        </div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td width="33%">
                        <div class="lbl">Carcass Weight</div>
                        <div class="val highlight">{{ $record->post_weight ?? 0 }} KG</div>
                    </td>
                    <td width="33%">
                        <div class="lbl">Available Weight</div>
                        <div class="val highlight">{{ $record->available_weight ?? 0 }} KG</div>
                    </td>
                    <td width="34%">
                        <div class="lbl">Grade</div>
                        <div class="val">{{ $record->post_grade ?? 'N/A' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="lbl">Age at Slaughter</div>
                        <div class="val">{{ $record->post_age ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="lbl">Dentition</div>
                        <div class="val">{{ $record->post_dentition ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="lbl">Fat Score</div>
                        <div class="val">{{ $record->post_fat ?? 'N/A' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Quarter Weights -->
    <div class="section">
        <div class="section-header">
            <h2>Quarter Record</h2>
        </div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:120px;">Quarter</th>
                        <th class="center">Fore Right</th>
                        <th class="center">Fore Left</th>
                        <th class="center">Hind Right</th>
                        <th class="center">Hind Left</th>
                        <th class="center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Weight (KG)</td>
                        <td class="center">{{ $foreRightWeight > 0 ? $foreRightWeight : '-' }}</td>
                        <td class="center">{{ $foreLeftWeight > 0 ? $foreLeftWeight : '-' }}</td>
                        <td class="center">{{ $hindRightWeight > 0 ? $hindRightWeight : '-' }}</td>
                        <td class="center">{{ $hindLeftWeight > 0 ? $hindLeftWeight : '-' }}</td>
                        <td class="center" style="font-weight:bold;">{{ $qTotal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fore Quarters - Prime Cuts -->
    <div class="section">
        <div class="section-header">
            <h2>Fore Quarters - Prime Cuts</h2>
        </div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:140px;">Cut Type</th>
                        <th class="center">Fore Left (KG)</th>
                        <th class="center">Fore Right (KG)</th>
                        <th class="center">Total (KG)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $foreCuts = ['Beef boneless', 'Beef Stew', 'Bones', 'Brisket', 'Chops', 'Chuck ribs', 'Family Steak', 'Fore rib', 'Leg Cut', 'Middle rib', 'Minced meat', 'Neck', 'Ossubucco', 'Ribs', 'Shin', 'Staff Meat'];
                        $totalFL = 0;
                        $totalFR = 0;
                        $hasForeCuts = false;
                    @endphp
                    @foreach($foreCuts as $cut)
                        @php
                            $fl = $primeCuts->filter(function($item) use ($cut) {
                                return str_contains($item->source_address, 'Fore Left') && str_contains($item->source_address, $cut);
                            })->first();
                            $fr = $primeCuts->filter(function($item) use ($cut) {
                                return str_contains($item->source_address, 'Fore Right') && str_contains($item->source_address, $cut);
                            })->first();
                            $flW = $fl ? floatval($fl->original_weight) : 0;
                            $frW = $fr ? floatval($fr->original_weight) : 0;
                            $totalFL += $flW;
                            $totalFR += $frW;
                            if($flW > 0 || $frW > 0) $hasForeCuts = true;
                        @endphp
                        @if($flW > 0 || $frW > 0)
                        <tr>
                            <td class="label">{{ $cut }}</td>
                            <td class="center">{{ $flW > 0 ? $flW : '-' }}</td>
                            <td class="center">{{ $frW > 0 ? $frW : '-' }}</td>
                            <td class="center">{{ $flW + $frW }}</td>
                        </tr>
                        @endif
                    @endforeach
                    @if(!$hasForeCuts)
                    <tr><td colspan="4" class="empty-msg">No fore quarter cuts recorded</td></tr>
                    @else
                    <tr class="total-row">
                        <td class="label">TOTAL</td>
                        <td class="center">{{ $totalFL }}</td>
                        <td class="center">{{ $totalFR }}</td>
                        <td class="center">{{ $totalFL + $totalFR }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hind Quarters - Prime Cuts -->
    <div class="section">
        <div class="section-header">
            <h2>Hind Quarters - Prime Cuts</h2>
        </div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:140px;">Cut Type</th>
                        <th class="center">Hind Left (KG)</th>
                        <th class="center">Hind Right (KG)</th>
                        <th class="center">Total (KG)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $hindCuts = ['Fillet', 'Oxtail', 'Rib eye', 'Rolled loin', 'Rump', 'Silver side', 'Sirloin / Striploin', 'T Bone', 'Topside /Beef Roast', 'Veal Steak', 'Beef boneless', 'Beef Stew', 'Bones', 'Minced meat', 'Staff Meat'];
                        $totalHL = 0;
                        $totalHR = 0;
                        $hasHindCuts = false;
                    @endphp
                    @foreach($hindCuts as $cut)
                        @php
                            $hl = $primeCuts->filter(function($item) use ($cut) {
                                return str_contains($item->source_address, 'Hind Left') && str_contains($item->source_address, $cut);
                            })->first();
                            $hr = $primeCuts->filter(function($item) use ($cut) {
                                return str_contains($item->source_address, 'Hind Right') && str_contains($item->source_address, $cut);
                            })->first();
                            $hlW = $hl ? floatval($hl->original_weight) : 0;
                            $hrW = $hr ? floatval($hr->original_weight) : 0;
                            $totalHL += $hlW;
                            $totalHR += $hrW;
                            if($hlW > 0 || $hrW > 0) $hasHindCuts = true;
                        @endphp
                        @if($hlW > 0 || $hrW > 0)
                        <tr>
                            <td class="label">{{ $cut }}</td>
                            <td class="center">{{ $hlW > 0 ? $hlW : '-' }}</td>
                            <td class="center">{{ $hrW > 0 ? $hrW : '-' }}</td>
                            <td class="center">{{ $hlW + $hrW }}</td>
                        </tr>
                        @endif
                    @endforeach
                    @if(!$hasHindCuts)
                    <tr><td colspan="4" class="empty-msg">No hind quarter cuts recorded</td></tr>
                    @else
                    <tr class="total-row">
                        <td class="label">TOTAL</td>
                        <td class="center">{{ $totalHL }}</td>
                        <td class="center">{{ $totalHR }}</td>
                        <td class="center">{{ $totalHL + $totalHR }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Offals -->
    <div class="section">
        <div class="section-header">
            <h2>Offal Record</h2>
        </div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:80px;">Type</th>
                        @php $offalTypes = ['Heart', 'Kidneys', 'Liver', 'Brain', 'Tongue', 'Tripe', 'Tail', 'Head', 'Other']; @endphp
                        @foreach($offalTypes as $type)
                            <th class="center">{{ $type }}</th>
                        @endforeach
                        <th class="center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Weight (KG)</td>
                        @php $offalTotal = 0; @endphp
                        @foreach($offalTypes as $type)
                            @php
                                $offal = $offalCuts->filter(function($item) use ($type) {
                                    return str_contains($item->source_address, $type);
                                })->first();
                                $weight = $offal ? floatval($offal->original_weight) : 0;
                                $offalTotal += $weight;
                            @endphp
                            <td class="center">{{ $weight > 0 ? $weight : '-' }}</td>
                        @endforeach
                        <td class="center" style="font-weight:bold;">{{ $offalTotal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if(count($forePackages) > 0 || count($hindPackages) > 0 || count($offalPackages) > 0)
    <!-- Primal Cut Packages Fore Quarters -->
    @if(count($forePackages) > 0)
    <div class="section">
        <div class="section-header">
            <h2>Primal Cut Packages Fore Quarters</h2>
        </div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:100px;">Primal cut</th>
                        <th class="center" style="width:50px;">Nos packages</th>
                        @for($i = 0; $i < $maxForeWeights; $i++)
                            <th class="center" style="width:45px;">Wt {{ $i+1 }}</th>
                        @endfor
                        <th class="center" style="width:55px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forePackages as $cutName => $data)
                    <tr>
                        <td class="label">{{ $cutName }}</td>
                        <td class="center">{{ count($data['weights']) }}</td>
                        @foreach($data['weights'] as $weight)
                            <td class="center">{{ $weight }}</td>
                        @endforeach
                        @for($i = count($data['weights']); $i < $maxForeWeights; $i++)
                            <td class="center">-</td>
                        @endfor
                        <td class="center" style="font-weight:bold;">{{ $data['total'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Primal Cut Packages Hind Quarters -->
    @if(count($hindPackages) > 0)
    <div class="section">
        <div class="section-header">
            <h2>Primal Cut Packages Hind Quarters</h2>
        </div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:100px;">Primal cut</th>
                        <th class="center" style="width:50px;">Nos packages</th>
                        @for($i = 0; $i < $maxHindWeights; $i++)
                            <th class="center" style="width:45px;">Wt {{ $i+1 }}</th>
                        @endfor
                        <th class="center" style="width:55px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hindPackages as $cutName => $data)
                    <tr>
                        <td class="label">{{ $cutName }}</td>
                        <td class="center">{{ count($data['weights']) }}</td>
                        @foreach($data['weights'] as $weight)
                            <td class="center">{{ $weight }}</td>
                        @endforeach
                        @for($i = count($data['weights']); $i < $maxHindWeights; $i++)
                            <td class="center">-</td>
                        @endfor
                        <td class="center" style="font-weight:bold;">{{ $data['total'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Offals Packages -->
    @if(count($offalPackages) > 0)
    <div class="section">
        <div class="section-header">
            <h2>Offals Packages</h2>
        </div>
        <div class="section-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:100px;">Offal</th>
                        <th class="center" style="width:50px;">Nos packages</th>
                        @for($i = 0; $i < $maxOffalWeights; $i++)
                            <th class="center" style="width:45px;">Wt {{ $i+1 }}</th>
                        @endfor
                        <th class="center" style="width:55px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offalPackages as $offalName => $data)
                    <tr>
                        <td class="label">{{ $offalName }}</td>
                        <td class="center">{{ count($data['weights']) }}</td>
                        @foreach($data['weights'] as $weight)
                            <td class="center">{{ $weight }}</td>
                        @endforeach
                        @for($i = count($data['weights']); $i < $maxOffalWeights; $i++)
                            <td class="center">-</td>
                        @endfor
                        <td class="center" style="font-weight:bold;">{{ $data['total'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif

    <!-- Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Generated: {{ date('d M Y, H:i:s') }}</td>
                <td>Slaughter Record Management System</td>
            </tr>
        </table>
    </div>
</body>
</html>
