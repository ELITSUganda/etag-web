<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packaging Label - {{ $record->package_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            color: #333;
            padding: 20mm;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2c5f2d;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18pt;
            color: #2c5f2d;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 14pt;
            color: #555;
            font-weight: normal;
        }
        
        .barcode-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .barcode-section img {
            max-width: 400px;
            height: auto;
        }
        
        .package-code {
            font-size: 16pt;
            font-weight: bold;
            color: #2c5f2d;
            margin-top: 10px;
        }
        
        .section-title {
            background-color: #2c5f2d;
            color: white;
            padding: 8px 10px;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            width: 35%;
        }
        
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .cuts-table th {
            width: 70%;
        }
        
        .cuts-table td {
            text-align: right;
            font-weight: bold;
        }
        
        .total-weight {
            background-color: #2c5f2d;
            color: white;
            font-size: 14pt;
            font-weight: bold;
            padding: 12px;
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-section {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            border: 2px dashed #2c5f2d;
        }
        
        .qr-section img {
            width: 150px;
            height: 150px;
        }
        
        .qr-section p {
            margin-top: 10px;
            font-size: 10pt;
            color: #666;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 10pt;
            font-weight: bold;
        }
        
        .badge-prime {
            background-color: #4CAF50;
            color: white;
        }
        
        .badge-offal {
            background-color: #FF9800;
            color: white;
        }
        
        .expiry-warning {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
            font-weight: bold;
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>E-TAG LIVESTOCK MANAGEMENT SYSTEM</h1>
        <h2>MEAT PACKAGING LABEL</h2>
    </div>
    
    <!-- Barcode Section -->
    <div class="barcode-section">
        @if(!empty($barcode))
            <img src="{{ $barcode }}" alt="Barcode">
        @endif
        <div class="package-code">{{ $record->package_code }}</div>
    </div>
    
    <!-- Animal Information -->
    <div class="section-title">ANIMAL INFORMATION</div>
    <table>
        <tr>
            <th>V-ID:</th>
            <td>{{ $record->v_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>E-ID:</th>
            <td>{{ $record->e_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>LHC:</th>
            <td>{{ $record->lhc ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Breed:</th>
            <td>{{ $record->breed ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Sex:</th>
            <td>{{ $record->sex ?? 'N/A' }}</td>
        </tr>
    </table>
    
    <!-- Package Details -->
    <div class="section-title">PACKAGE DETAILS</div>
    <table>
        <tr>
            <th>Package Type:</th>
            <td>
                <span class="badge {{ $record->package_type === 'Prime Cut' ? 'badge-prime' : 'badge-offal' }}">
                    {{ $record->package_type }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Package Date:</th>
            <td>{{ \Carbon\Carbon::parse($record->packaging_date)->format('d-M-Y') }}</td>
        </tr>
        <tr>
            <th>Expiry Date:</th>
            <td style="font-weight: bold; color: #d32f2f;">
                {{ \Carbon\Carbon::parse($record->expiry_date)->format('d-M-Y') }}
            </td>
        </tr>
        <tr>
            <th>Packaged By:</th>
            <td>{{ $packager->name ?? 'N/A' }}</td>
        </tr>
    </table>
    
    <!-- Expiry Warning -->
    @if($record->days_until_expiry <= 2)
    <div class="expiry-warning">
        ⚠ WARNING: This package expires in {{ $record->days_until_expiry }} day(s)! ⚠
    </div>
    @endif
    
    <!-- Meat Cut Breakdown -->
    <div class="section-title">{{ strtoupper($record->package_type) }} BREAKDOWN (kg)</div>
    <table class="cuts-table">
        @if(count($cutBreakdown) > 0)
            @foreach($cutBreakdown as $cutName => $weight)
            <tr>
                <th>{{ $cutName }}</th>
                <td>{{ number_format($weight, 2) }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2" style="text-align: center; color: #999;">No cuts recorded</td>
            </tr>
        @endif
    </table>
    
    <!-- Total Weight -->
    <div class="total-weight">
        TOTAL WEIGHT: {{ number_format($record->total_weight, 2) }} KG
    </div>
    
    <!-- QR Code Section -->
    <div class="qr-section">
        @if(!empty($qrCode))
            <img src="{{ $qrCode }}" alt="QR Code">
        @endif
        <p>Scan QR code for full traceability and product details</p>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('d-M-Y H:i:s') }}</p>
        <p>E-TAG Livestock Management System | Uganda Livestock Information & Traceability System</p>
        <p>For inquiries: info@u-lits.com | www.u-lits.com</p>
    </div>
</body>
</html>
