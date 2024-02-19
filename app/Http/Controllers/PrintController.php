<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\District;
use App\Models\Movement;
use App\Models\Location;
use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PrintController extends Controller
{

    public static function get_row($t1 = "Title 1", $d1 = "Deatils 1", $t2 = "Title 2", $d2 = "Deatils 2")
    {
        return '<tr>
                    <th class="title-cell" >' . $t1 . '</th>
                    <td>' . $d1 . '</td> 
                    <th class="title-cell">' . $t2 . '</th>
                    <td>' . $d2 . '</td> 
                </tr>';
    }

    public function prepareThumbnails()
    {
        Utils::prepareThumbnails();
    }
    public function index()
    {


        $id = (int)(trim($_GET['id']));
        $m =  Movement::find($id);
        if ($m == null) {
            dd("Movement not found.");
        }

        // dd($m->village_to);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('print', [
            'm' => $m
        ]));
        return $pdf->stream($m->permit_Number . ".pdf");

        return view('print');
        return view('print');
        $id = (int)(trim($_GET['id']));
        $m =  Movement::find($id);
        if ($m == null) {
            dd("Movement not found.");
        }

        $i = 0;
        $animals = "";
        foreach ($m->movement_has_movement_animals as $key => $v) {
            if ($v->animal == null) {
                continue;
            }
            $i++;
            $animals .= '<tr>
                <th class="" >' . $i . '</th>
                    <td>' . $v->animal->lhc . '</td>
                    <td>' . $v->animal->e_id . '</td>
                    <th class="">' . $v->animal->v_id . '</th>
                    <td>' . $v->animal->sex . '</td> 
                    <td>' . $v->animal->dob . '</td> 
                </tr>';
        }


        $d =  Location::find($m->district_from);
        $district_from = "-";


        $d =  Location::find($m->district_to);
        $district_to = "-";


        $sub =  Location::find($m->sub_county_from);
        $sub_county_from = "-";
        if ($sub != null) {
            $sub_county_from = $sub->name . " ($sub->code)";
            if ($sub->district != null) {
                $district_from = $sub->district->name;
            }
        }

        $sub =  Location::find($m->sub_county_to);
        $sub_county_to = "-";
        $district_to = "-";
        if ($sub != null) {
            $sub_county_to = $sub->name . " ($sub->code)";
            if ($sub->district != null) {
                $district_to = $sub->district->name;
            }
        }



        if ($m == null) {
            dd("Permit data not found.");
        }
        $title = "E-Permit";
        $sub_title = "Ministry of Agriculture, Animal Industry and Fisheries [MAAIF]";
        $data = "
            <style>
            .title-cell{
                width: 25%;
                font-size: 12;
                background-color: #D9D9D9;
                font-family:  sans-serif;
                font-weight: 100;
            }
            table, th, td {
                font-weight: 100;
                text-align: reight;
                font-family:  sans-serif;
                font-size: 12;
                border: 1px solid black;
                border-collapse: collapse;
                padding: 4px;
            }
            table{
                  width: 100%;
              }
              </style>
        ";

        $data .= '<h2 style="text-align: center;  margin: 0; padding: 0; font-weight: 100;">' . $title . '</h2>';
        $data .= '<h3 style="text-align: center;  margin: 0; padding: 0; font-weight: 100;">' . $sub_title . '</h3>';
        $data .= '<h3 style="text-align: center; margin: 0; padding: 0; font-weight: 100;">Livestock Movement Permitt</h3>';
        $data .= "<hr>";

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Basic info</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'PERMIT NUMBER',
                $m->permit_Number,
                'Permit Status',
                $m->status
            ) .
            PrintController::get_row(
                'Valid from',
                $m->valid_from_Date,
                'Valid until',
                $m->valid_to_Date,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Applicant’s info</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'Applicant Name',
                $m->trader_name,
                'Applicant ID No.',
                $m->trader_nin
            ) .
            PrintController::get_row(
                'Applicant’s Phone',
                $m->trader_phone,
                'Applicant’s Address',
                $m->address,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Departure <b>(FROM)</b></h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'District',
                $district_from,
                'Location',
                $sub_county_from
            ) .
            PrintController::get_row(
                'Village',
                $m->village_from,
                'Address line',
                $m->address_from,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Destination<b> (TO)</b></h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'District',
                $district_to,
                'Location',
                $sub_county_to
            ) .
            PrintController::get_row(
                'Village',
                $m->village_to,
                'Address line',
                $m->address_to,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Transpotation</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'Vehicle\'s reg\' no.',
                $m->vehicle,
                'Transporter’s name',
                $m->transporter_name
            ) .
            PrintController::get_row(
                'Transporter’s ID',
                $m->transporter_nin,
                'Transporter’s Phone',
                $m->address_to,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Purpose of movement</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'Destination',
                $m->destination,
                'Details',
                $m->details
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Animals</h3>';
        $data .= '<table> 
                        <thead>' .
            '<tr>
                        <th class="" >Sn.</th>
                            <td>LHC</td>
                            <td>E-ID</td>
                            <th class="">V-ID</th>
                            <td>SEX</td> 
                            <td>Year Born</td> 
                        </tr>'
            . '</thead>
                        <tbody>' . $animals .
            '</tbody>
        </table>';


        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);
        return $pdf->stream();
    }
    public function print_vaccination($id)
    {
        $m =  Animal::find($id);
        if ($m == null) {
            die('Invalid.');
        }

        // dd($m->village_to);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('print-vaccination', [
            'm' => $m
        ]));
        return $pdf->stream('Vaccination-certiticate-' . $m->id . ".pdf");

        return view('print');
        return view('print');
        $id = (int)(trim($_GET['id']));
        $m =  Movement::find($id);
        if ($m == null) {
            dd("Movement not found.");
        }

        $i = 0;
        $animals = "";
        foreach ($m->movement_has_movement_animals as $key => $v) {
            if ($v->animal == null) {
                continue;
            }
            $i++;
            $animals .= '<tr>
                <th class="" >' . $i . '</th>
                    <td>' . $v->animal->lhc . '</td>
                    <td>' . $v->animal->e_id . '</td>
                    <th class="">' . $v->animal->v_id . '</th>
                    <td>' . $v->animal->sex . '</td> 
                    <td>' . $v->animal->dob . '</td> 
                </tr>';
        }


        $d =  Location::find($m->district_from);
        $district_from = "-";


        $d =  Location::find($m->district_to);
        $district_to = "-";


        $sub =  Location::find($m->sub_county_from);
        $sub_county_from = "-";
        if ($sub != null) {
            $sub_county_from = $sub->name . " ($sub->code)";
            if ($sub->district != null) {
                $district_from = $sub->district->name;
            }
        }

        $sub =  Location::find($m->sub_county_to);
        $sub_county_to = "-";
        $district_to = "-";
        if ($sub != null) {
            $sub_county_to = $sub->name . " ($sub->code)";
            if ($sub->district != null) {
                $district_to = $sub->district->name;
            }
        }



        if ($m == null) {
            dd("Permit data not found.");
        }
        $title = "E-Permit";
        $sub_title = "Ministry of Agriculture, Animal Industry and Fisheries [MAAIF]";
        $data = "
            <style>
            .title-cell{
                width: 25%;
                font-size: 12;
                background-color: #D9D9D9;
                font-family:  sans-serif;
                font-weight: 100;
            }
            table, th, td {
                font-weight: 100;
                text-align: reight;
                font-family:  sans-serif;
                font-size: 12;
                border: 1px solid black;
                border-collapse: collapse;
                padding: 4px;
            }
            table{
                  width: 100%;
              }
              </style>
        ";

        $data .= '<h2 style="text-align: center;  margin: 0; padding: 0; font-weight: 100;">' . $title . '</h2>';
        $data .= '<h3 style="text-align: center;  margin: 0; padding: 0; font-weight: 100;">' . $sub_title . '</h3>';
        $data .= '<h3 style="text-align: center; margin: 0; padding: 0; font-weight: 100;">Livestock Movement Permitt</h3>';
        $data .= "<hr>";

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Basic info</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'PERMIT NUMBER',
                $m->permit_Number,
                'Permit Status',
                $m->status
            ) .
            PrintController::get_row(
                'Valid from',
                $m->valid_from_Date,
                'Valid until',
                $m->valid_to_Date,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Applicant’s info</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'Applicant Name',
                $m->trader_name,
                'Applicant ID No.',
                $m->trader_nin
            ) .
            PrintController::get_row(
                'Applicant’s Phone',
                $m->trader_phone,
                'Applicant’s Address',
                $m->address,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Departure <b>(FROM)</b></h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'District',
                $district_from,
                'Location',
                $sub_county_from
            ) .
            PrintController::get_row(
                'Village',
                $m->village_from,
                'Address line',
                $m->address_from,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Destination<b> (TO)</b></h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'District',
                $district_to,
                'Location',
                $sub_county_to
            ) .
            PrintController::get_row(
                'Village',
                $m->village_to,
                'Address line',
                $m->address_to,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Transpotation</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'Vehicle\'s reg\' no.',
                $m->vehicle,
                'Transporter’s name',
                $m->transporter_name
            ) .
            PrintController::get_row(
                'Transporter’s ID',
                $m->transporter_nin,
                'Transporter’s Phone',
                $m->address_to,
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Purpose of movement</h3>';
        $data .= '<table> 
                        <tbody>' .
            PrintController::get_row(
                'Destination',
                $m->destination,
                'Details',
                $m->details
            ) .
            '</tbody>
        </table>';

        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">Animals</h3>';
        $data .= '<table> 
                        <thead>' .
            '<tr>
                        <th class="" >Sn.</th>
                            <td>LHC</td>
                            <td>E-ID</td>
                            <th class="">V-ID</th>
                            <td>SEX</td> 
                            <td>Year Born</td> 
                        </tr>'
            . '</thead>
                        <tbody>' . $animals .
            '</tbody>
        </table>';


        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($data);
        return $pdf->stream();
    }

    // 
}
