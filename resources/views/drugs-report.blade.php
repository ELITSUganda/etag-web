@php
    use App\Models\Utils; 
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <title>Drug Stock Report</title>
    <style>
        body {
            margin: 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }
        .header-table {
            width: 100%;
        }
        .header-table img {
            width: 100%;
        }
        h2, h3 {
            font-size: 16px;
            margin: 10px 0 5px;
        }
        .section-title {
            font-size: 18px;
            display: block;
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .separator {
            background-color: #6B3B01;
            height: 2px;
            margin: 5px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 5px;
            vertical-align: middle;
        }
        .table-sm th, .table-sm td {
            padding: 3px;
        }
        .text-right {
            text-align: right;
        }
        .details-box {
            border: 2px solid #6B3B01;
            padding: 10px;
            margin-left: 10px;
        }
        .title-underline {
            height: 4px;
            margin: 1px 0;
        }
        .hr-black {
            background-color: black;
        }
        .hr-yellow {
            background-color: rgb(245, 216, 0);
        }
        .hr-red {
            background-color: red;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 15%">
                <img src="{{ public_path('assets/images/logo.png') }}">
            </td>
            <td colspan="3" class="text-center">
                <p class="text-uppercase" style="font-size: 16px; margin-bottom: 2px;"><b>Uganda Livestock Identification Traceability System</b></p>
                <p style="font-size: 12px; margin-bottom: 2px;"><b>P.O. Box 513, ENTEBBE, UGANDA</b></p>
                <p style="font-size: 12px; margin-bottom: 2px;"><b>E-MAIL:</b> animalhealth@agriculture.co.ug</p>
                <p style="font-size: 12px; margin-bottom: 2px;"><b>TELEPHONE:</b> +256 0414 320 627</p>
            </td>
            <td style="width: 15%"></td>
        </tr>
    </table>
    <hr class="title-underline hr-black">
    <hr class="title-underline hr-yellow">
    <hr class="title-underline hr-red">

    <p class="text-center" style="font-size: 20px; margin: 5px 0;"><u><b>{{ $report->title }}</b></u></p>

    <div>
        <table style="width: 100%;">
            <tr>
                <td>
                    <h2>FARM SUMMARY</h2>
                    <div class="separator"></div>
                    @include('components.text-detail', [ 't' => 'LHC', 'v' => $report->farm->holding_code ])
                    @include('components.text-detail', [ 't' => 'Location', 'v' => $report->farm->sub_county_text ])
                    @include('components.text-detail', [ 't' => 'Total Animals', 'v' => $data['total_animals'] ])
                    @include('components.text-detail', [ 't' => 'Total Cattle', 'v' => $data['total_cattle'] ])
                    @include('components.text-detail', [ 't' => 'Total Sheep', 'v' => $data['total_sheep'] ])
                    @include('components.text-detail', [ 't' => 'Total Goats', 'v' => $data['total_goats'] ])
                </td>
                <td>
                    <div class="details-box">
                        <h2 class="text-uppercase" style="font-size: 16px; margin: 0;"><b>Drug Usage Summary</b></h2>
                        <div class="separator"></div>
                        @include('components.text-detail', [
                            't' => 'Total Amount Spent on Drugs',
                            'v' => Utils::money($data['total_amount_spent']),
                        ])
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <span class="section-title">Drug Stock Overview</span>
        <div class="separator"></div>
        @if (count($data['drugs_in_stock']) > 0)
            <h3 class="text-center">Drugs In Stock</h3>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Drug Category</th>
                        <th class="text-right">Average Quantity</th>
                        <th class="text-right">Current Quantity</th>
                        <th class="text-right">Percentage (%)</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data['drugs_in_stock'] as $item)
                    <tr>
                        <td>{{ $item['category'] }}</td>
                        <td class="text-right">{{ number_format($item['total_avergae'], 2). ' '. $item['unit'] }}</td>
                        <td class="text-right">{{ number_format($item['current_quantity'], 2). ' '. $item['unit'] }}</td>
                        <td class="text-right">{{ $item['percentage'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if (count($data['drugs_running_out']) > 0)
            <h3 class="text-center" style="margin-top: 15px;">Drugs Running Out</h3>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Drug Category</th>
                        <th class="text-right">Average Quantity</th>
                        <th class="text-right">Current Quantity</th>
                        <th class="text-right">Percentage (%)</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data['drugs_running_out'] as $item)
                    <tr>
                        <td>{{ $item['category'] }}</td>
                        <td class="text-right">{{ number_format($item['total_avergae'], 2). ' '. $item['unit'] }}</td>
                        <td class="text-right">{{ number_format($item['current_quantity'], 2). ' '. $item['unit'] }}</td>
                        <td class="text-right">{{ $item['percentage'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if (count($data['drugs_out_of_stock']) > 0)
            <h3 class="text-center" style="margin-top: 15px;">Drugs Out of Stock</h3>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Drug Category</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data['drugs_out_of_stock'] as $item)
                    <tr>
                        <td>{{ $item['category'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div style="margin-top: 20px;">
        <span class="section-title">Drug Investment Analysis</span>
        <div class="separator"></div>
        @if (count($data['most_invested_drugs']) > 0)
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Drug Category</th>
                        <th class="text-right">Total Amount Invested</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data['most_invested_drugs'] as $item)
                    <tr>
                        <td>{{ $item['category'] }}</td>
                        <td class="text-right">{{ Utils::money($item['total_amount_invested']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center">No drug investment data available for this period.</p>
        @endif
    </div>

    <div style="margin-top: 20px;">
        <span class="section-title">Recent Drug Purchases</span>
        <div class="separator"></div>
        @if (count($data['recent_purchases']) > 0)
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Drug Name</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th class="text-right">Original Quantity</th>
                        <th class="text-right">Current Quantity</th>
                        <th class="text-right">Selling Price</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data['recent_purchases'] as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->batch_number }}</td>
                        <td>{{ Utils::my_date($item->expiry_date) }}</td>
                        <td class="text-right">{{ $item->original_quantity }}</td>
                        <td class="text-right">{{ $item->current_quantity }}</td>
                        <td class="text-right">{{ Utils::money($item->selling_price) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center">No recent drug purchases found for this period.</p>
        @endif
    </div>

    <div style="margin-top: 20px;">
        <span class="section-title">Most Treated Animals</span>
        <div class="separator"></div>
        @if (count($data['most_teated_animals']) > 0)
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Animal ID</th>
                        <th>Animal Type</th>
                        <th class="text-right">Total Amount Spent</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($data['most_teated_animals'] as $item)
                    <tr>
                        <td>{{ $item['animal']->e_id }}</td>
                        <td>{{ $item['animal']->type }}</td>
                        <td class="text-right">{{ Utils::money($item['total_amount']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center">No animal treatment data available for this period.</p>
        @endif
    </div>
</body>
</html>