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

    $qr_code = $app->get_qr_code();
    if ($qr_code != null && strlen($qr_code) > 0) {
        $qr_code = public_path($qr_code);
    } else {
        $qr_code = null;
    }

@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <style>
        p {
            /*             font-size: 14px!important;
            line-height: 16px!important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif!important;
            text-align: justify!important; */
        }

        .my-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            border-collapse: collapse;
        }

        .my-table th,
        .my-table td {
            border: 1px solid black;
            padding: 2px;
            font-size: 16px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            line-height: 18px;
        }

        .my-table td {
            font-weight: 400 !important;
        }
    </style>
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
            <td class="p-2">
                <img style="width: {{ $width * 0.17 }}mm" src="{{ $qr_code }}">
            </td>
        </tr>
    </table>

    <hr style="
height: 3px;
padding-top: 0px;
margin-top: 15px;
margin-bottom: 0px;
background-color: black;
 
">
    <hr
        style="
height: 2px;
padding-top: 0px;
margin-top: 0px;
margin-bottom: 0px;
background-color: rgb(245, 216, 0);
 
">
    <hr style="
height: 2px;
padding-top: 0px;
margin-top: 0px;
margin-bottom: 0px;
background-color: red;
 
">

    <p class="text-center text-uppercase py-0 my-3" style="font-size: 20px">
        <u><b>{{ $app->application_type->message_2 }}</b></u>
    </p>

    <p class="text-right mb-3" style="font-size: 18px; font-weight: 300;">
        DATE: {{ Utils::my_date($app->created_at) }}.
    </p>

    <p style="font-size: 14px; line-height: 15px;  border: dashed 1px black; " class="p-2 mb-2"><b>NOTE:</b> In any
        correspondence on this subject please Quote No. <b>{{ $app->code }}</b>.</b>
    </p>

    {!! $app->get_content() !!}

    <hr>
    <img style="width: {{ $width * 0.15 }}mm" src="{{ public_path('assets/images/sign.png') }}">
    <p><b>Anna Rose Ademun Okurut</b></p>
    <p style="font-size: 20px;"><b>COMMISSIONER ANIMAL HEALTH</b></p>
    <br>
    <p><b>Copy to:</b></p>
    <ul>
        <li>Assistant Commissioner Veterinary Inspection and Enforcement</li>
        <li>Customs Officer- <b>Entebbe International Airport</b></li>
        <li>Veterinary Inspector- <b>Entebbe International Airport</b></li>
    </ul>

    <p class="mt-2"><b>Tel:</b>+256772504746</p>
    <p><b>Email:</b>ademunrose@yahoo.co.uk</p>
</body>
