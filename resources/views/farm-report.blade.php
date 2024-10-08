@php
    // import Utils class from models
    use App\Models\Utils;

    $animal_owner = 'N/A';

    $sub_county_text = 'N/A';

    $is_export = false;
    if (isset($_GET['export']) && $_GET['export'] == 'true') {
        $is_export = true;
    } else {
        $is_export = false;
    }

    $a4_width = 210;
    //width of without margin
    $width = 210 - 10;

@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
</head>

<body class=" p-0 m-0">
    <style>
        .start-on-top {
            margin-top: 0;
            vertical-align: top;
        }

        .title {
            font-weight: bold;
        }

        p {
            padding: 0;
            margin: 0%;
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>


    <table class="p-0 m-0" style="width: 100%">

        <tr>
            <td class="" style="width: {{ $width * 0.15 }}mm">
                <img style="width: {{ $width * 0.15 }}mm" src="{{ public_path('assets/images/coat_of_arms-min.png') }}">
            </td>
            <td colspan="3" class="text-center pt-0">
                <p class="text-center py-0 my-0" style="font-size: 16px"><b>MINISTRY OF AGRICULTURE, ANIMAL INDUSTRY AND
                        FISHERIES
                    </b></p>
                <p class="mb-0" class="text-center" style="font-size: 12px"><b>P.O. Box 513, ENTEBBE, UGANDA</b></p>
                <p class="mb-0" class="text-center" style="font-size: 12px"><b>E-MAIL:</b>
                    animalhealth@agriculture.co.ug</p>
                <p class="mb-0" class="text-center" style="font-size: 12px;"><b>TELEPHONE:</b> +256 0414 320 627,
                    320166, 320376</p>
            </td>
            <td class="" style="width: {{ $width * 0.15 }}mm; ">
                {{--  --}}
            </td>
        </tr>
    </table>

    <hr style="
height: 4px;
padding-top: 0px;
margin-top: 5px;
margin-bottom: 0px;
background-color: black;
 
">
    <hr
        style="
height: 4px;
padding-top: 0px;
margin-top: 0px;
margin-bottom: 0px;
background-color: rgb(245, 216, 0);
 
">
    <hr style="
height: 4px;
padding-top: 0px;
margin-top: 0px;
margin-bottom: 0px;
background-color: red;
 
">

    <p class="text-center text-uppercase py-0 my-0 mt-2" style="font-size: 22px">
        <u><b>{{ $report->title }}</b></u>
    </p>

    <div class="row p-0 m-0">

        <div class="col-12 p-0 m-0">
            <table class="w-100">
                <tr>
                    <td>
                        <h2 class="p-0 m-0 mt-3" style="font-size: 18px;"><b>FARM KPIs</b></h2>
                        <hr class="p-0 m-0 mt-2 mb-2"
                            style="
                            background-color: #6B3B01;
                            height: 2px;
                        ">
                        @include('components.text-detail', [
                            't' => 'Pregnancy Rate',
                            'v' => $report->pregnancy_rate . '%' ?? 'N/A',
                        ])

                        @include('components.text-detail', [
                            't' => 'Abortion Rate',
                            'v' => $report->abortion_rate . '%' ?? 'N/A',
                        ])

                        @include('components.text-detail', [
                            't' => 'CALVING RATE',
                            'v' => $report->calving_rate . '%' ?? 'N/A',
                        ])

                        @include('components.text-detail', [
                            't' => 'Weaning Rate',
                            'v' => $report->weaning_rate . '%',
                        ])
                        @include('components.text-detail', [
                            't' => 'Average Weaning weight',
                            'v' => $report->weaning_weight . ' KGs',
                        ])
                        @include('components.text-detail', [
                            't' => 'Weaning weight range',
                            'v' => $report->min_weaning_weight . ' - ' . $report->max_weaning_weight . ' (KGs)',
                        ])



                    </td>
                    <td>
                        <div class="start-on-top mt-2">
                            <div class="ml-2 pl-3 pt-2 mt-0 start-on-top pb-2" style="border: #6B3B01 2px solid;">
                                <h2 class="p-0 m-0 text-uppercase" style="font-size: 18px;"><b>Summary</b></h2>
                                <hr class="p-0 m-0 mt-2 mb-2"
                                    style="
                            background-color: #6B3B01;
                            height: 2px;
                        ">



                                @include('components.text-detail', [
                                    't' => 'LHC',
                                    'v' => $report->farm->holding_code,
                                ])

                                @include('components.text-detail', [
                                    't' => 'Location',
                                    'v' => $report->farm->sub_county_text,
                                ])

                                @include('components.text-detail', [
                                    't' => 'Total Cattle',
                                    'v' => $report->animals->count(),
                                ])
                                @include('components.text-detail', [
                                    't' => 'Female Cattle',
                                    'v' => $report->female_animals->count(),
                                ])
                                @include('components.text-detail', [
                                    't' => 'Male Cattle',
                                    'v' => $report->animals->count() - $report->female_animals->count(),
                                ])



                            </div>
                        </div>
                    </td>

                </tr>
            </table>

        </div>

    </div>


    <div class="row mt-2">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>Pregnancy Overview</b></h2>
            <hr class="p-0 m-0 mt-1 mb-2"
                style="
                        background-color: #6B3B01;
                        height: 2px;
                    ">
            {{-- Total Pregnancies Recorded --}}
            @include('components.text-detail', [
                't' => 'Total Animals Serviced',
                'v' => $report->serviced_animals->count(),
            ])

            {{-- Total Pregnancies Recorded --}}
            @include('components.text-detail', [
                't' => 'Total Pregnancies Recorded',
                'v' => $report->got_pregnant_animals->count(),
            ])

            {{-- Total Pregnancies in Progress --}}
            @include('components.text-detail', [
                't' => 'Pregnancies in Progress',
                'v' => $report->pregnancies_in_progress->count(),
            ])

            @include('components.text-detail', [
                't' => 'Calves born',
                'v' => $report->animals_that_produced->count(),
            ])

            @include('components.text-detail', [
                't' => 'Calves died',
                'v' => $report->calves_that_died->count(),
            ])

        </div>
    </div>



    <div class="row mt-3">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>Fertilization and Conception
                    Data</b></h2>
            <hr class="p-0 m-0 mt-2 mb-1"
                style="
                        background-color: #6B3B01;
                        height: 2px;
                    ">


            <table class="table table-bordered">
                <tr>
                    <th class="p-1">Method</th>
                    <th class="p-1 text-center">Artifitial Inseminations</th>
                    <th class="p-1 text-center">Natural Mating</th>
                    <th class="p-1 text-right">TOTAL</th>
                </tr>
                <tr>
                    <th class="p-1 m-0">
                        Serviced Animals
                    </th>
                    <td class="text-center  p-1">
                        {{ $report->serviced_animals->count() }}
                    </td>
                    <td class="text-center p-1">
                        {{ $report->natural_mating->count() }}
                    </td>
                    <td class="text-right p-1 m-0">
                        {{ $report->serviced_animals->count() + $report->natural_mating->count() }}
                    </td>
                </tr>
                {{-- •	Conception Rate --}}
                <tr>
                    <th class="p-1 m-0">
                        Conception Rate
                    </th>
                    <td class="text-center  p-1">
                        {{ $report->ai_conception_rate . '%' }}
                    </td>
                    <td class="text-center p-1">
                        {{ $report->natural_conception_rate . '%' }}
                    </td>
                    <td class="text-right p-1 m-0">
                        {{ $report->natural_conception_rate + $report->ai_conception_rate . '%' }}
                    </td>
                </tr>
                {{-- •	Abortion Rate --}}
                <tr>
                    <th class="p-1 m-0">
                        Abortion Rate
                    </th>
                    <td class="text-center  p-1">
                        {{ $report->ai_abortion_rate . '%' }}
                    </td>
                    <td class="text-center p-1">
                        {{ $report->natural_abortion_rate . '%' }}
                    </td>
                    <td class="text-right p-1 m-0">
                        {{ $report->natural_abortion_rate + $report->ai_abortion_rate . '%' }}
                    </td>
                </tr>
                {{-- Gestation Period --}}
                <tr>
                    <th class="p-1 m-0">
                        Gestation Period
                    </th>
                    <td class="text-center  p-1">
                        {{ $report->ai_gestation_length . ' Days' }}
                    </td>
                    <td class="text-center p-1">
                        {{ $report->natural_gestation_length . ' days' }}
                    </td>
                    <td class="text-right p-1 m-0">
                        {{ $report->natural_gestation_length + $report->ai_gestation_length }} Days
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <div class="row mt-2">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>Weaning Statistics</b></h2>
            <hr class="p-0 m-0 mt-1 mb-2"
                style="
                        background-color: #6B3B01;
                        height: 2px;
                    ">

            {{-- Total Pregnancies Recorded --}}
            @include('components.text-detail', [
                't' => 'Total Calves Weaned',
                'v' => $report->total_calves_weaned->count(),
            ])
            @include('components.text-detail', [
                't' => 'Average Weaning Weight',
                'v' => $report->average_weaning_weight,
            ])
            @include('components.text-detail', [
                't' => 'Average Weaning Age',
                'v' => $report->average_weaning_age . ' Days',
            ])
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>Health and Complications</b></h2>
            <hr class="p-0 m-0 mt-1 mb-2" style="background-color: #6B3B01;
                        height: 2px; ">

            {{-- Total Pregnancies Recorded --}}
            @include('components.text-detail', [
                't' => 'Total Complications Recorded',
                'v' => $report->total_complications_recorded->count(),
            ])
            <h5><u><b>Abortion Reasons Breakdown:</b></u></h5>
            <ul>
                <li>
                    @include('components.text-detail', [
                        't' => 'Disease',
                        'v' => $report->reason_for_animal_abort_disease->count(),
                    ])
                </li>
                <li>
                    {{-- reason_for_animal_abort_accident --}}
                    @include('components.text-detail', [
                        't' => 'Accident',
                        'v' => $report->reason_for_animal_abort_accident->count(),
                    ])
                </li>
                <li>
                    {{-- reason_for_animal_abort --}}
                    @include('components.text-detail', [
                        't' => 'Other',
                        'v' => $report->reason_for_animal_abort_other->count(),
                    ])
                </li>
            </ul>
        </div>
    </div>


    <div class="row mt-2">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>
                    Recommendations and Action Items
                </b></h2>
            <hr class="p-0 m-0 mt-1 mb-2" style="background-color: #6B3B01;
                        height: 2px; ">
            <ul>
                <li>Areas for Improvement: [List of Areas]</li>
                <li>Proposed Actions: [List of Actions]</li>
            </ul>

            <b>Notes:</b>
            • [Additional notes or observations]
        </div>
    </div>




</body>
