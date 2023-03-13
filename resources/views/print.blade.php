<?php
use App\Models\Location;

$sub = Location::find($m->sub_county_from);
$sub_county_from = '-';
if ($sub != null) {
    $sub_county_from = $sub->name . " ($sub->code)";
    if ($sub->district != null) {
        $district_from = $sub->district->name;
    }
}
$sub = Location::find($m->sub_county_to);
$sub_county_to = '-';
if ($sub != null) {
    $sub_county_to = $sub->name . " ($sub->code)";
    if ($sub->district != null) {
        $district_to = $sub->district->name;
    }
}
$district_to = '-';
$sub = Location::find($m->sub_county_to);
$sub_county_to = '-';

if ($sub != null) {
    $sub_county_to = $sub->name . " ($sub->code)";
    if ($sub->district != null) {
        $district_to = $sub->district->name;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <link type="text/css" href="{{ public_path('css/print.css') }}" rel="stylesheet" />

</head>

<body>

    <table>
        <tr>
            <td style="width: 30%;">
                <p class="mb-2">Permit status.: <b class="">APPROVED</b></p>
                <p class="mb-2">Permit no.: <b class="">132322323</b></p>
            </td>
            <td class="text-center">
                <img style="width: 75%;" src="{{ public_path('assets/images/coat_of_arms-min.png') }}">

            </td>
            <td style="width: 45%;" class="pl-3">
                <p><b>MINISTRY OF AGRICULTURE, ANIMAL INDUSTRY AND FISHERIES.</b></p>
                <p class="mb-2"><b>DEPARTMENT OF ANIMAL HEALTH</b></p>
                <p>P.O. Box 513, ENTEBBE, UGANDA</p>
                <p><b>E-MAIL:</b> animalhealth@agriculture.co.ug</p>
                <p><b>TELEPHONE:</b> +256 0414 320 627, 320166, 320376</p>
            </td>
        </tr>
    </table>
    <h2 class="text-center mt-3">INTER-DISTRICT VETERINARY HEALTH CERTIFICATE PERMITTING THE MOVEMENT OF SLAUGHTER
        ANIMALS (ALL SPECIES)
        <u>WITHIN UGANDA ONLY</u>
    </h2>
    <p class="text-center my-2 text-secondary"><i>(Issued under the animal disease Act Chapter 38)</i></p>

    <h5 class="mb-1">i. Identification of animals</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <td class="">Sn.</td>
                <td class="text-uppercase"><b>LHC</b></td>
                <td class="text-uppercase"><b>E-ID</b></td>
                <td class="text-uppercase"><b>V-ID</b></td>
                <td class="text-uppercase"><b>SEX</b></td>
                <td class="text-uppercase"><b>Date Born</b></td>
            </tr>
        </thead>
        <tbody>
            @foreach ($m->movement_has_movement_animals as $key => $v)
                <?php
                if ($v->animal == null) {
                    continue;
                }
                ?>
                <tr>
                    <th class="p-1">{{ $key + 1 }}</th>
                    <td class="p-1">{{ $v->animal->lhc }}</td>
                    <td class="p-1">{{ $v->animal->e_id }}</td>
                    <td class="p-1">{{ $v->animal->v_id }}</td>
                    <td class="p-1">{{ $v->animal->sex }}</td>
                    <td class="p-1">{{ $v->animal->dob }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
    <h5 class="mb-1">ii. Place of origin for the animals</h5>
    <p>Name of owner / farm / ranch /unit <u> {{ $m->trader_name }} </u> is permitted to move animals within <u>
            {{ $m->valid_from_Date }} </u> - <u> {{ $m->valid_to_Date }} </u> days From the Sub-county / Division Of
        <u>
            {{ $m->village_from }}</u> in the District of <u>{{ $sub_county_from }}, $district_from</u>.
    </p>
    <h5 class="mb-1 mt-2">iii. Destination of animals</h5>
    <p>To the District Of <u>{{ $m->village_to }} </u> in the Sub-county / Division of <u>{{ $sub_county_to }},
            {{ $district_to }} </u>
        Specifically to the following slaughter or processing place/s: <u>{{ $m->destination }}</u>
        Means of transport, and stock-routes to be used <u>{{ $m->transportation_route }} </u>

    <h5 class="mb-1 mt-2">iv. Zoo-sanitary information and attestation</h5>
    <p>I, the undersigned authorized state veterinary officer certifies to the best of my knowledge that the animals
        described above and examined on this day come from a disease free area / area not under any animal quarantine
        restrictions and:</p>

    <p class="mt-1">a. Show no sign of disease, vectors or pests</p>
    <p>b. Satisfy the required animal health standards of the final destination district</p>
    <p class="mb-1">c. Are fit for Slaughter purposes</p>
    <br>
    <p>Issued at (name of district and date): .........................................................</p>
    <h4>Name, rank, address and telephone of the authorized state veterinary officer:<h4>
            <p>....................................................................................................
            </p>
            <p>Signature and stamp / seal:
                ....................................................................................................
            </p>

            {{--    <table>
                <tbody>

                    <tr>
                        <td width="25%">' . $qr_code_image . '</td>
                        <td>
                            <p>
                                Animals are to remain under isolation and none to be removed or added in transit up to
                                the final designated slaughter / processing places
                                Emergency Slaughters to be supervised by authorized veterinary personnel and a report
                                submitted to the final destination veterinary authorities,
                                Animals ( revention of Cruetty) Chapter 39 must be guaranteed by the certifying, loading
                                and stock-route inspection officers. Animals here mean
                                domestic mammalian, birds and wildlife animals of terrestrial -aquatic origin. The
                                Certificate is issued in triplicate and for only a single consignment.
                                Slaughter animals shall be identified by the SL mark on the left jaw and or centrally
                                serialized Red ear tags. Extra inf; should be attached.
                                This is not a revenue collection receipt.
                            </p>
                        </td>
                    </tr>

                </tbody>
            </table>'; --}}

</body>


</html>
