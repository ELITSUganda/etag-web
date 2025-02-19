@php
    use App\Models\Utils;
    $a4_width = 210;
    $width = 200;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }}</title>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .section-title {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 12px 0 6px;
            text-align: center;
        }
        .table-bordered {
            width: 85%;
            margin: 0 auto 12px auto;
            border-collapse: collapse;
            font-size: 12px;
            border-radius: 4px;
            overflow: hidden;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
            vertical-align: middle;
        }
        .table-bordered thead {
            background-color: #f8f8f8;
        }
        .header-text {
            text-align: center;
            margin-bottom: 0;
            font-size: 12px;
        }
        hr {
            height: 3px;
            margin: 4px 0;
            border: none;
        }
        .divider-black {
            background-color: #000;
        }
        .divider-yellow {
            background-color: rgb(245, 216, 0);
        }
        .divider-red {
            background-color: red;
        }
        .small-text {
            font-size: 11px;
        }
        .summary-item {
            margin: 3px 0;
        }
        .main-title {
            text-align: center;
            font-size: 18px;
            text-transform: uppercase;
            margin: 10px 0 5px;
        }
        .sub-title {
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .spacer {
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <table style="width: 100%;">
        <tr>
            <td style="width: {{ $width * 0.15 }}mm;">
                <img src="{{ public_path('assets/images/logo.png') }}" style="width: {{ $width * 0.15 }}mm;">
            </td>
            <td colspan="3" style="text-align: center;">
                <p style="font-size: 14px; text-transform: uppercase;">
                    <strong>Uganda Livestock Identification Traceability System</strong>
                </p>
                <p class="header-text"><strong>P.O. Box 513, ENTEBBE, UGANDA</strong></p>
                <p class="header-text"><strong>E-MAIL:</strong> animalhealth@agriculture.co.ug</p>
                <p class="header-text"><strong>TELEPHONE:</strong> +256 0414 320 627, 320166, 320376</p>
            </td>
            <td style="width: {{ $width * 0.15 }}mm;"></td>
        </tr>
    </table>

    <hr class="divider-black">
    <hr class="divider-yellow">
    <hr class="divider-red">

    <p class="main-title">
        <u><strong>{{ $report->title }}</strong></u>
    </p>
    <p class="sub-title">
        Period: {{ Utils::my_date($report->start_date) }} – {{ Utils::my_date($report->end_date) }}
    </p>

    <div class="spacer">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: top; width: 50%;">
                    <div class="section-title">Farm Summary</div>
                    <p class="summary-item"><strong>Farm ID:</strong> {{ $report->farm_id }}</p>
                    <p class="summary-item"><strong>Sub County:</strong> {{ $report->farm->sub_county_text ?? 'N/A' }}</p>
                    <p class="summary-item"><strong>Total Animals:</strong> {{ $data['total_animals'] }}</p>
                    <p class="summary-item"><strong>Cattle:</strong> {{ $data['total_cattle'] }}</p>
                    <p class="summary-item"><strong>Goats:</strong> {{ $data['total_goats'] }}</p>
                    <p class="summary-item"><strong>Sheep:</strong> {{ $data['total_sheep'] }}</p>
                </td>
                <td style="vertical-align: top; width: 50%;">
                    <div class="section-title">Financial Summary</div>
                    <p class="summary-item">
                        <strong>Total Amount Spent on Drugs:</strong> UGX {{ number_format($data['total_amount_spent'], 2) }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="spacer">
        <div class="section-title">Drug Stock Summary</div>
        
        <h4 style="font-size: 13px; text-align: center; text-decoration: underline; margin: 10px 0;">
            Drugs In Stock
        </h4>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Avg. Original Qty</th>
                    <th>Current Qty</th>
                    <th>% Remaining</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['drugs_in_stock'] as $drug)
                <tr>
                    <td>{{ $drug['category'] }}</td>
                    <td>{{ $drug['total_avergae'] }}</td>
                    <td>{{ $drug['current_quantity'] }}</td>
                    <td>{{ $drug['percentage'] }}%</td>
                    <td>{{ $drug['unit'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <h4 style="font-size: 13px; text-align: center; text-decoration: underline; margin: 10px 0;">
            Drugs Running Out
        </h4>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Avg. Original Qty</th>
                    <th>Current Qty</th>
                    <th>% Remaining</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['drugs_running_out'] as $drug)
                <tr>
                    <td>{{ $drug['category'] }}</td>
                    <td>{{ $drug['total_avergae'] }}</td>
                    <td>{{ $drug['current_quantity'] }}</td>
                    <td>{{ $drug['percentage'] }}%</td>
                    <td>{{ $drug['unit'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <h4 style="font-size: 13px; text-align: center; text-decoration: underline; margin: 10px 0;">
            Drugs Out of Stock
        </h4>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Avg. Original Qty</th>
                    <th>Current Qty</th>
                    <th>% Remaining</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['drugs_out_of_stock'] as $drug)
                <tr>
                    <td>{{ $drug['category'] }}</td>
                    <td>{{ $drug['total_avergae'] }}</td>
                    <td>{{ $drug['current_quantity'] }}</td>
                    <td>{{ $drug['percentage'] }}%</td>
                    <td>{{ $drug['unit'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="spacer">
        <div class="section-title">Most Invested Drugs</div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th>Drug Category</th>
                    <th>Total Amount Invested</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['most_invested_drugs'] as $drug)
                <tr>
                    <td>{{ $drug['category'] }}</td>
                    <td>UGX {{ number_format($drug['total_amount_invested'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="spacer">
        <div class="section-title">Recent Drug Purchases</div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th>Drug Name</th>
                    <th>Manufacturer</th>
                    <th>Batch Number</th>
                    <th>Expiry Date</th>
                    <th>Original Qty</th>
                    <th>Current Qty</th>
                    <th>Selling Price</th>
                    <th>Last Activity</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['recent_purchases'] as $purchase)
                <tr>
                    <td>{{ $purchase->name }}</td>
                    <td>{{ $purchase->manufacturer }}</td>
                    <td>{{ $purchase->batch_number }}</td>
                    <td>{{ date('d-M-Y', strtotime($purchase->expiry_date)) }}</td>
                    <td>{{ $purchase->original_quantity }}</td>
                    <td>{{ $purchase->current_quantity }}</td>
                    <td>{{ number_format($purchase->selling_price, 2) }}</td>
                    <td>{{ $purchase->last_activity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="spacer">
        <div class="section-title">Top 10 Animals by Treatment Cost</div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th>Animal ID</th>
                    <th>Type / Breed</th>
                    <th>Total Drug Cost Spent</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['most_teated_animals'] as $item)
                <tr>
                    <td>{{ $item['animal']->local_id ?? $item['animal']->id }}</td>
                    <td>{{ $item['animal']->type }} / {{ $item['animal']->breed }}</td>
                    <td>UGX {{ number_format($item['total_amount'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="spacer">
        <div class="section-title">Recommendations and Action Items</div>
        <ul style="width: 85%; margin: 0 auto;">
            <li>Review and reorder drugs that are running out to avoid treatment gaps.</li>
            <li>Monitor expiry dates closely; arrange for proper disposal of expired stocks.</li>
            <li>Evaluate drug spending trends and consider cost-effective alternatives.</li>
            <li>Improve stock tracking by updating records regularly.</li>
        </ul>
        <p style="width: 85%; margin: 0 auto;">
            <strong>Notes:</strong> Ensure a balanced inventory and timely reordering to maintain effective livestock treatment.
        </p>
    </div>

</body>
</html>
