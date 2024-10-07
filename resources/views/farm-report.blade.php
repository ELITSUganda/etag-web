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
                            't' => 'Pregnant Animals',
                            'v' => '$animal->v_id',
                        ])
                        @include('components.text-detail', ['t' => 'Calving Rate', 'v' => '$animal->v_id'])
                        @include('components.text-detail', [
                            't' => 'Abortion Rate',
                            'v' => '$animal->v_id',
                        ])
                        @include('components.text-detail', ['t' => 'Weaning Rate', 'v' => '$animal->v_id'])

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
                                {{--                                 @include('components.text-detail', [
                                    't' => 'Female Cattle',
                                    'v' => $report->farm->female_cattle->count(),
                                ])

                                @include('components.text-detail', [
                                    't' => 'Male Cattle',
                                    'v' => $report->farm->animals->count(),
                                ]) --}}

                            </div>
                        </div>
                    </td>

                </tr>
            </table>

        </div>

    </div>


    <div class="row mt-2">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>Recent Events</b></h2>
            <hr class="p-0 m-0 mt-1 mb-2"
                style="
                        background-color: #6B3B01;
                        height: 2px;
                    ">
        </div>
    </div>



    <div class="row mt-3">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>Recent Photos</b></h2>
            <hr class="p-0 m-0 mt-1 mb-3"
                style="
                        background-color: #6B3B01;
                        height: 2px;
                    ">
        </div>
    </div>

    @php
        $photos = [];
        $x = 0;
    @endphp

    @if (count($photos) == 0)
        <div class="alert alert-dark text-center mt-2 mr-4">
            No recent photos.
        </div>
    @else
        @foreach ($photos as $photo)
            @php
                $x++;
            @endphp
            <img class="mt-2" src="{{ Utils::img($photo->src, $is_export) }}" alt="Photo" style="width: 220px;">
        @endforeach
    @endif
    </div>




    <div class="row mt-2">
        <div class="col-12 p-0">

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>More Information</b></h2>
            <hr class="p-0 m-0 mt-1 mb-2"
                style="
                        background-color: #6B3B01;
                        height: 2px;
                    ">
        </div>
    </div>

    <div class="row pb-4">
        <div class="col-12">
            {{-- is_pregnant --}}
            @include('components.text-detail', [
                't' => 'Is Pregnant',
                'v' => 'as',
            ])


        </div>
    </div>


</body>
