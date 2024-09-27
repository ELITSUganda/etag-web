@php
    // import Utils class from models
    use App\Models\Utils;

    $owner = $animal->owner;
    $animal_owner = 'N/A';
    if ($owner != null) {
        $animal_owner = $owner->name;
    }
    $sub_county_text = 'N/A';
    if ($animal->sub_county != null) {
        $sub_county_text = $animal->sub_county->name_text;
    }

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
                <p class="text-center py-0 my-0" style="font-size: 16px"><b>
                    </b></p>
                <p class="mb-0" class="text-center" style="font-size: 12px"><b>P.O. Box 513, ENTEBBE, UGANDA</b></p>
                <p class="mb-0" class="text-center" style="font-size: 12px"><b>E-MAIL:</b>
                    animalhealth@agriculture.co.ug</p>
                <p class="mb-0" class="text-center" style="font-size: 12px;"><b>TELEPHONE:</b> +256 0414 320 627,
                    320166, 320376</p>
            </td>
            <td class="" style="width: {{ $width * 0.15 }}mm; ">
                <img style="width: {{ $width * 0.17 }}mm" src="{{ Utils::img($animal->photo, $is_export) }}">
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

    <p class="text-center text-uppercase py-0 my-0" style="font-size: 22px">
        <u><b>Animal's Profile</b></u>
    </p>

    <div class="row p-0 m-0">

        <div class="col-12 p-0 m-0">
            <table class="w-100">
                <tr>
                    <td>
                        <h2 class="p-0 m-0" style="font-size: 18px;"><b>BIO DATA</b></h2>
                        <hr class="p-0 m-0 mt-2 mb-2"
                            style="
                            background-color: #6B3B01;
                            height: 2px;
                        ">
                        @include('components.text-detail', ['t' => 'E-ID', 'v' => $animal->e_id])
                        @include('components.text-detail', ['t' => 'V-ID', 'v' => $animal->v_id])
                        @include('components.text-detail', ['t' => 'species', 'v' => $animal->type])
                        @include('components.text-detail', ['t' => 'sex', 'v' => $animal->sex])
                        @include('components.text-detail', ['t' => 'breed', 'v' => $animal->breed])
                        @include('components.text-detail', ['t' => 'color', 'v' => $animal->color])
                        @include('components.text-detail', [
                            't' => 'Animal Name',
                            'v' => $animal->details,
                        ])
                        @include('components.text-detail', [
                            't' => 'Date of birth',
                            'v' => Utils::my_date($animal->dob),
                        ])

                        {{-- purchase_from --}}
                        @include('components.text-detail', [
                            't' => 'Purchased From',
                            'v' =>
                                $animal->purchase_from == null || strlen($animal->purchase_from) < 3
                                    ? 'N/A'
                                    : $animal->purchase_from,
                        ])
                        {{-- purchase_price in ugx --}}
                        @include('components.text-detail', [
                            't' => 'Purchase Price',
                            'v' =>
                                $animal->purchase_price == 0 || $animal->purchase_price == null
                                    ? 'N/A'
                                    : 'UGX ' . number_format($animal->purchase_price),
                        ])

                        {{-- weight_at_birth --}}
                        @include('components.text-detail', [
                            't' => 'Weight at Birth',
                            'v' =>
                                $animal->weight_at_birth == 0 || $animal->weight_at_birth == null
                                    ? 'N/A'
                                    : $animal->weight_at_birth . ' KG',
                        ])

                        {{-- conception --}}
                        @include('components.text-detail', [
                            't' => 'Conception',
                            'v' =>
                                $animal->conception == null || strlen($animal->conception) < 2
                                    ? 'N/A'
                                    : $animal->conception,
                        ])

                        {{-- genetic_donor --}}
                        @include('components.text-detail', [
                            't' => 'Genetic Donor',
                            'v' =>
                                $animal->genetic_donor == null || strlen($animal->genetic_donor) < 2
                                    ? 'N/A'
                                    : $animal->genetic_donor,
                        ])

                        {{-- group_text --}}
                        @include('components.text-detail', [
                            't' => 'Group',
                            'v' =>
                                $animal->group_text == null || strlen($animal->group_text) < 2
                                    ? 'N/A'
                                    : $animal->group_text,
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
                                    't' => 'Age',
                                    'v' => Utils::get_cattle_age($animal->dob),
                                ])

                                @include('components.text-detail', [
                                    't' => 'Stage',
                                    'v' => Utils::get_cattle_stage($animal->dob, $animal->sex),
                                ])


                                @include('components.text-detail', [
                                    't' => 'Has been vaccinated against FMD',
                                    'v' => $animal->has_fmd ? 'Yes' : 'No',
                                ])
                                @include('components.text-detail', [
                                    't' => 'FMD Vaccination Date',
                                    'v' => Utils::my_date($animal->fmd_date),
                                ])

                                {{-- animal weight --}}
                                @include('components.text-detail', [
                                    't' => 'Weight',
                                    'v' =>
                                        $animal->weight == 0 || $animal->weight == null
                                            ? 'N/A'
                                            : $animal->weight . ' KG',
                                ])


                                {{-- average_milk production --}}
                                @include('components.text-detail', [
                                    't' => 'Average Milk Production',
                                    'v' =>
                                        $animal->average_milk_production == 0 ||
                                        $animal->average_milk_production == null
                                            ? 'N/A'
                                            : $animal->average_milk_production . ' Litres',
                                ])

                                {{-- current_price in ugx --}}
                                @include('components.text-detail', [
                                    't' => 'Animal Worth (Current Price)',
                                    'v' =>
                                        $animal->current_price == 0 || $animal->current_price == null
                                            ? 'N/A'
                                            : 'UGX ' . number_format($animal->current_price),
                                ])

                                @include('components.text-detail', [
                                    't' => 'Date registered',
                                    'v' => Utils::my_date($animal->created_at),
                                ])
                                @include('components.text-detail', [
                                    't' => 'Last Updated',
                                    'v' => Utils::my_date($animal->updated_at),
                                ])
                                @include('components.text-detail', [
                                    't' => 'LHC',
                                    'v' => $animal->lhc,
                                ])@include('components.text-detail', [
                                    't' => 'LHC Address',
                                    'v' => $sub_county_text,
                                ])
                                @include('components.text-detail', [
                                    't' => 'Animal Owner',
                                    'v' => $animal_owner,
                                ])
                                {{-- was_purchases --}}
                                @include('components.text-detail', [
                                    't' => 'Was Purchased',
                                    'v' => $animal->was_purchased ? 'Yes' : 'No',
                                ])
                                {{-- purchase_date --}}
                                @include('components.text-detail', [
                                    't' => 'Purchase Date',
                                    'v' =>
                                        $animal->purchase_date == null || strlen($animal->purchase_date) < 3
                                            ? 'N/A'
                                            : Utils::my_date($animal->purchase_date),
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

            <h2 class="p-0 m-0 text-uppercase text-center" style="font-size: 18px;"><b>Recent Events</b></h2>
            <hr class="p-0 m-0 mt-1 mb-2"
                style="
                        background-color: #6B3B01;
                        height: 2px;
                    ">
        </div>
    </div>
    <div class="row p-0 m-0">
        <div class="col-12 p-0 m-0 ">
            <p class=" p-0 m-0 text-uppercase" style="font-size: 16px;"><b><u>RECENT Sanitary Event</u></b></p>

            @php
                $sanirary_events = $animal->getRecentSanitaryEvents();
                $x = 0;
            @endphp

            @if (count($sanirary_events) == 0)
                <div class="alert alert-dark text-center m-2">
                    No recent sanitary events.
                </div>
            @else
                @foreach ($sanirary_events as $event)
                    @php
                        $x++;
                    @endphp
                    <p>
                        {{ $x }}.
                        <b>{{ Utils::my_date($event->created_at) }}</b> -
                        {{ $event->type }} Event:
                        @if ($event->detail != null && strlen($event->detail) > 3)
                            {{ $event->detail }}.
                        @else
                            {{ $event->description }}.
                        @endif
                    </p>
                @endforeach
            @endif
        </div>
    </div>

    <div class="row p-0 m-0 mt-2">
        <div class="col-12 p-0 m-0 ">
            <p class=" p-0 m-0 text-uppercase" style="font-size: 16px;"><b><u>RECENT Production Event</u></b></p>

            @php
                $sanirary_events = $animal->getRecentProductionEvents();
                $x = 0;
            @endphp

            @if (count($sanirary_events) == 0)
                <div class="alert alert-dark text-center m-2">
                    No recent production events.
                </div>
            @else
                @foreach ($sanirary_events as $event)
                    @php
                        $x++;
                    @endphp
                    <p>
                        {{ $x }}.
                        <b>{{ Utils::my_date($event->created_at) }}</b> -
                        {{ $event->type }} Event:
                        @if ($event->detail != null && strlen($event->detail) > 3)
                            {{ $event->detail }}.
                        @else
                            {{ $event->description }}.
                        @endif
                    </p>
                @endforeach
            @endif
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
        $photos = $animal->getRecentPhotos();
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
                'v' => $animal->is_pregnant ? 'Yes' : 'No',
            ])

            {{-- weight_change weigt gain/loss --}}
            @include('components.text-detail', [
                't' => 'Weight Gain/Loss',
                'v' => $animal->weight_change == null ? 'N/A' : $animal->weight_change . ' KG',
            ])

            {{-- weight_at_last_calving --}}
            @include('components.text-detail', [
                't' => 'Weight at Last Calving',
                'v' =>
                    $animal->weight_at_last_calving == 0 || $animal->weight_at_last_calving == null
                        ? 'N/A'
                        : $animal->weight_at_last_calving . ' KG',
            ])

            {{-- has_produced_before --}}
            @include('components.text-detail', [
                't' => 'Has Produced Before',
                'v' => $animal->has_produced_before ? 'Yes' : 'No',
            ])

            {{-- number_of_calves --}}
            @include('components.text-detail', [
                't' => 'Number of Calves',
                'v' =>
                    $animal->number_of_calves == 0 || $animal->number_of_calves == null
                        ? 'N/A'
                        : $animal->number_of_calves,
            ])

            {{-- age_at_first_calving --}}
            @include('components.text-detail', [
                't' => 'Age at First Calving',
                'v' =>
                    $animal->age_at_first_calving == 0 || $animal->age_at_first_calving == null
                        ? 'N/A'
                        : $animal->age_at_first_calving,
            ])

            {{-- weight_at_first_calving --}}
            @include('components.text-detail', [
                't' => 'Weight at First Calving',
                'v' =>
                    $animal->weight_at_first_calving == 0 || $animal->weight_at_first_calving == null
                        ? 'N/A'
                        : $animal->weight_at_first_calving . ' KG',
            ])

            {{-- has_been_inseminated --}}
            @include('components.text-detail', [
                't' => 'Has Been Inseminated',
                'v' => $animal->has_been_inseminated ? 'Yes' : 'No',
            ])

            {{-- age_at_first_insemination --}}
            @include('components.text-detail', [
                't' => 'Age at First Insemination',
                'v' =>
                    $animal->age_at_first_insemination == 0 || $animal->age_at_first_insemination == null
                        ? 'N/A'
                        : $animal->age_at_first_insemination,
            ])

            {{-- weight_at_first_insemination --}}
            @include('components.text-detail', [
                't' => 'Weight at First Insemination',
                'v' =>
                    $animal->weight_at_first_insemination == 0 || $animal->weight_at_first_insemination == null
                        ? 'N/A'
                        : $animal->weight_at_first_insemination . ' KG',
            ])

            {{-- inter_calving_interval --}}
            @include('components.text-detail', [
                't' => 'Inter Calving Interval',
                'v' =>
                    $animal->inter_calving_interval == 0 || $animal->inter_calving_interval == null
                        ? 'N/A'
                        : $animal->inter_calving_interval . ' Days',
            ])

            {{-- calf_mortality_rate --}}
            @include('components.text-detail', [
                't' => 'Calf Mortality Rate',
                'v' =>
                    $animal->calf_mortality_rate == 0 || $animal->calf_mortality_rate == null
                        ? 'N/A'
                        : $animal->calf_mortality_rate . ' %',
            ])

            {{-- weight_gain_per_day --}}
            @include('components.text-detail', [
                't' => 'Weight Gain Per Day',
                'v' =>
                    $animal->weight_gain_per_day == 0 || $animal->weight_gain_per_day == null
                        ? 'N/A'
                        : $animal->weight_gain_per_day . ' KG',
            ])

            {{-- number_of_isms_per_conception --}}
            @include('components.text-detail', [
                't' => 'Number of ISMs Per Conception',
                'v' =>
                    $animal->number_of_isms_per_conception == 0 || $animal->number_of_isms_per_conception == null
                        ? 'N/A'
                        : $animal->number_of_isms_per_conception,
            ])

            {{-- is_a_calf --}}
            @include('components.text-detail', [
                't' => 'Is a Calf',
                'v' => $animal->is_a_calf ? 'Yes' : 'No',
            ])

            {{-- is_weaned_off --}}
            @include('components.text-detail', [
                't' => 'Is Weaned Off',
                'v' => $animal->is_weaned_off ? 'Yes' : 'No',
            ])

            {{-- birth_position --}}
            @include('components.text-detail', [
                't' => 'Birth Position',
                'v' =>
                    $animal->birth_position == 0 || $animal->birth_position == null
                        ? 'N/A'
                        : $animal->birth_position,
            ])
        </div>
    </div>


</body>
