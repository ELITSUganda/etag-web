<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaughter Record - {{ $record->e_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #333; background: #e8e8e8; padding: 30px 0; }
        .container { max-width: 900px; margin: 0 auto; padding: 40px 50px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        /* Header */
        .header { background: #6B3C00; color: #fff; padding: 20px 24px; margin: -40px -50px 30px -50px; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; font-weight: 600; margin: 0; }
        .header-meta { text-align: right; font-size: 12px; opacity: 0.9; }
        .header-meta span { display: block; }
        
        /* Actions */
        .actions { margin-bottom: 24px; }
        .actions button, .actions a { padding: 8px 16px; font-size: 12px; border: none; cursor: pointer; margin-right: 8px; text-decoration: none; display: inline-block; }
        .btn-print { background: #6B3C00; color: #fff; }
        .btn-pdf { background: #c0392b; color: #fff; }
        .btn-back { background: #e0e0e0; color: #333; }
        .btn-print:hover { background: #8B5A1B; }
        .btn-pdf:hover { background: #e74c3c; }
        .btn-back:hover { background: #ccc; }
        
        /* Sections */
        .section { border: 1px solid #ddd; margin-bottom: 20px; }
        .section-header { background: #f9f9f9; border-bottom: 1px solid #ddd; padding: 10px 16px; }
        .section-header h2 { font-size: 13px; font-weight: 600; color: #6B3C00; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-body { padding: 16px; }
        
        /* Info Grid */
        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .info-item { padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-item:last-child { border-bottom: none; }
        .info-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
        .info-value { font-size: 14px; font-weight: 500; color: #333; }
        .info-value.highlight { color: #6B3C00; font-weight: 600; }
        
        /* Tables */
        .container table { width: 100%; border-collapse: collapse; font-size: 12px; color: #333; }
        .container th, .container td { padding: 6px 10px; text-align: left; border: 1px solid #ddd; color: #333; background: #fff; }
        .container th { background: #6B3C00 !important; color: #fff !important; font-weight: 500; text-transform: uppercase; font-size: 11px; letter-spacing: 0.3px; }
        .container th.center, .container td.center { text-align: center; }
        .container td.label { font-weight: 500; width: 160px; color: #333 !important; background: #fff !important; }
        .container .total-row td { border-top: 2px solid #6B3C00; font-weight: 600; }
        
        /* Two Column Info */
        .info-row { display: flex; border-bottom: 1px solid #eee; }
        .info-row:last-child { border-bottom: none; }
        .info-cell { flex: 1; display: flex; padding: 8px 0; }
        .info-cell .lbl { width: 140px; font-size: 11px; color: #888; text-transform: uppercase; }
        .info-cell .val { flex: 1; font-weight: 500; }
        
        /* Footer */
        .footer { border-top: 2px solid #6B3C00; padding-top: 12px; margin-top: 30px; display: flex; justify-content: space-between; font-size: 11px; color: #888; }
        
        /* Print */
        @media print {
            body { background: #fff; padding: 0; }
            .container { padding: 20px; max-width: none; box-shadow: none; }
            .header { margin: -20px -20px 20px -20px; }
            .actions { display: none; }
            .section { break-inside: avoid; border: 1px solid #ccc; }
            .header { background: #6B3C00 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { background: #6B3C00 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div>
                    <h1>SLAUGHTER RECORD REPORT</h1>
                </div>
                <div class="header-meta">
                    <span>Record #{{ $record->id }}</span>
                    <span>{{ date('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <button class="btn-print" onclick="window.print()">Print Report</button>
            <a href="{{ admin_url('slaughter-records/' . $record->id . '/export-pdf') }}" target="_blank" class="btn-pdf">Export to PDF</a>
            <button class="btn-back" onclick="window.history.back()">Back</button>
        </div>

        <!-- Animal Identification -->
        <div class="section">
            <div class="section-header">
                <h2>Animal Identification</h2>
            </div>
            <div class="section-body">
                <div class="info-row">
                    <div class="info-cell"><span class="lbl">Visual ID (V-ID)</span><span class="val">{{ $record->v_id ?? 'N/A' }}</span></div>
                    <div class="info-cell"><span class="lbl">Electronic ID (E-ID)</span><span class="val">{{ $record->e_id ?? 'N/A' }}</span></div>
                    <div class="info-cell"><span class="lbl">LHC Number</span><span class="val">{{ $record->lhc ?? 'N/A' }}</span></div>
                </div>
                <div class="info-row">
                    <div class="info-cell"><span class="lbl">Breed</span><span class="val">{{ $record->breed ?? 'N/A' }}</span></div>
                    <div class="info-cell"><span class="lbl">Sex</span><span class="val">{{ $record->sex ?? 'N/A' }}</span></div>
                    <div class="info-cell"><span class="lbl">Date of Birth</span><span class="val">{{ $record->dob ?? 'N/A' }}</span></div>
                </div>
                <div class="info-row">
                    <div class="info-cell"><span class="lbl">Slaughter House</span><span class="val">{{ $record->destination_slaughter_house ?? 'N/A' }}</span></div>
                    <div class="info-cell"><span class="lbl">Slaughter Date</span><span class="val">{{ $record->created_at->format('d M Y') }}</span></div>
                    <div class="info-cell"><span class="lbl">Administrator</span><span class="val">{{ $record->administrator_id ?? 'N/A' }}</span></div>
                </div>
            </div>
        </div>

        <!-- Post-Slaughter Assessment -->
        <div class="section">
            <div class="section-header">
                <h2>Post-Slaughter Assessment</h2>
            </div>
            <div class="section-body">
                <div class="info-row">
                    <div class="info-cell"><span class="lbl">Carcass Weight</span><span class="val" style="color:#6B3C00;">{{ $record->post_weight ?? 0 }} KG</span></div>
                    <div class="info-cell"><span class="lbl">Available Weight</span><span class="val" style="color:#6B3C00;">{{ $record->available_weight ?? 0 }} KG</span></div>
                    <div class="info-cell"><span class="lbl">Grade</span><span class="val">{{ $record->post_grade ?? 'N/A' }}</span></div>
                </div>
                <div class="info-row">
                    <div class="info-cell"><span class="lbl">Age at Slaughter</span><span class="val">{{ $record->post_age ?? 'N/A' }}</span></div>
                    <div class="info-cell"><span class="lbl">Dentition</span><span class="val">{{ $record->post_dentition ?? 'N/A' }}</span></div>
                    <div class="info-cell"><span class="lbl">Fat Score</span><span class="val">{{ $record->post_fat ?? 'N/A' }}</span></div>
                </div>
            </div>
        </div>

        <!-- Quarter Weights -->
        <div class="section">
            <div class="section-header">
                <h2>Quarter Record</h2>
            </div>
            <div class="section-body" style="padding:0;">
                <table>
                    <thead>
                        <tr>
                            <th style="width:160px;">Quarter</th>
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
                            <td class="center" style="font-weight:600;">{{ $qTotal }}</td>
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
                <table autocomplete="off">
                    <thead>
                        <tr>
                            <th style="width:180px;">Cut Type</th>
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
                                <td style="font-weight:500;width:160px;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">{{ $cut }}</td>
                                <td class="center">{{ $flW > 0 ? $flW : '-' }}</td>
                                <td class="center">{{ $frW > 0 ? $frW : '-' }}</td>
                                <td class="center">{{ $flW + $frW }}</td>
                            </tr>
                            @endif
                        @endforeach
                        @if(!$hasForeCuts)
                        <tr><td colspan="4" class="center" style="color:#888;padding:20px;">No fore quarter cuts recorded</td></tr>
                        @else
                        <tr class="total-row">
                            <td style="font-weight:600;width:160px;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">TOTAL</td>
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
                <table>
                    <thead>
                        <tr>
                            <th style="width:180px;">Cut Type</th>
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
                                <td style="font-weight:500;width:160px;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">{{ $cut }}</td>
                                <td class="center">{{ $hlW > 0 ? $hlW : '-' }}</td>
                                <td class="center">{{ $hrW > 0 ? $hrW : '-' }}</td>
                                <td class="center">{{ $hlW + $hrW }}</td>
                            </tr>
                            @endif
                        @endforeach
                        @if(!$hasHindCuts)
                        <tr><td colspan="4" class="center" style="color:#888;padding:20px;">No hind quarter cuts recorded</td></tr>
                        @else
                        <tr class="total-row">
                            <td style="font-weight:600;width:160px;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">TOTAL</td>
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
                <table>
                    <thead>
                        <tr>
                            <th style="width:100px;">Type</th>
                            @php $offalTypes = ['Heart', 'Kidneys', 'Liver', 'Brain', 'Tongue', 'Tripe', 'Tail', 'Head', 'Other']; @endphp
                            @foreach($offalTypes as $type)
                                <th class="center">{{ $type }}</th>
                            @endforeach
                            <th class="center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:500;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">Weight (KG)</td>
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
                            <td class="center" style="font-weight:600;">{{ $offalTotal }}</td>
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
                <table>
                    <thead>
                        <tr>
                            <th style="width:140px;">Primal cut</th>
                            <th class="center" style="width:80px;">Nos packages</th>
                            @for($i = 0; $i < $maxForeWeights; $i++)
                                <th class="center" style="width:70px;">Weight {{ $i+1 }}</th>
                            @endfor
                            <th class="center" style="width:80px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($forePackages as $cutName => $data)
                        <tr>
                            <td style="font-weight:500;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">{{ $cutName }}</td>
                            <td class="center">{{ count($data['weights']) }}</td>
                            @foreach($data['weights'] as $weight)
                                <td class="center">{{ $weight }}</td>
                            @endforeach
                            @for($i = count($data['weights']); $i < $maxForeWeights; $i++)
                                <td class="center">-</td>
                            @endfor
                            <td class="center" style="font-weight:600;">{{ $data['total'] }}</td>
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
                <table>
                    <thead>
                        <tr>
                            <th style="width:140px;">Primal cut</th>
                            <th class="center" style="width:80px;">Nos packages</th>
                            @for($i = 0; $i < $maxHindWeights; $i++)
                                <th class="center" style="width:70px;">Weight {{ $i+1 }}</th>
                            @endfor
                            <th class="center" style="width:80px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hindPackages as $cutName => $data)
                        <tr>
                            <td style="font-weight:500;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">{{ $cutName }}</td>
                            <td class="center">{{ count($data['weights']) }}</td>
                            @foreach($data['weights'] as $weight)
                                <td class="center">{{ $weight }}</td>
                            @endforeach
                            @for($i = count($data['weights']); $i < $maxHindWeights; $i++)
                                <td class="center">-</td>
                            @endfor
                            <td class="center" style="font-weight:600;">{{ $data['total'] }}</td>
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
                <table>
                    <thead>
                        <tr>
                            <th style="width:140px;">Offal</th>
                            <th class="center" style="width:80px;">Nos packages</th>
                            @for($i = 0; $i < $maxOffalWeights; $i++)
                                <th class="center" style="width:70px;">Weight {{ $i+1 }}</th>
                            @endfor
                            <th class="center" style="width:80px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offalPackages as $offalName => $data)
                        <tr>
                            <td style="font-weight:500;color:#333;background:#fff;padding:6px 10px;border:1px solid #ddd;">{{ $offalName }}</td>
                            <td class="center">{{ count($data['weights']) }}</td>
                            @foreach($data['weights'] as $weight)
                                <td class="center">{{ $weight }}</td>
                            @endforeach
                            @for($i = count($data['weights']); $i < $maxOffalWeights; $i++)
                                <td class="center">-</td>
                            @endfor
                            <td class="center" style="font-weight:600;">{{ $data['total'] }}</td>
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
            <div>
                <span>Generated: {{ date('d M Y, H:i:s') }}</span>
            </div>
            <div>
                <span>Slaughter Record Management System</span>
            </div>
        </div>
    </div>
</body>
</html>
