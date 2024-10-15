<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Application;
use App\Models\District;
use App\Models\Movement;
use App\Models\Location;
use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Svg\Tag\Rect;

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

    public function application_print(Request $r)
    {

        $m = new Animal();
        $pdf = App::make('dompdf.wrapper');
        $app = Application::find($r->id);
    
        $pdf->loadHTML(view('application-form-print', [
            'animal' => $m,
            'app' => $app
        ]));
        /* 
           "id" => 1
    "created_at" => "2024-10-09 18:12:44"
    "updated_at" => "2024-10-09 18:12:44"
    "applicant_id" => 709
    "inspector_1_id" => null
    "inspector_2_id" => null
    "inspector_3_id" => null
    "application_type_id" => null
    "stage" => "Pending"
    "payment_status" => "Pending"
    "payment_prn" => "Pending"
    "payment_prn_status" => "Pending"
    "stage_message" => null
    "applicant_remarks" => null
    "applicant_name" => "Ahmed Rivas"
    "applicant_occupation" => null
    "applicant_phone" => "+1 (391) 225-8662"
    "applicant_id_type" => null
    "applicant_id_number" => null
    "applicant_address" => "Quas magnam est vel"
    "applicant_tin" => "Sapiente laboris lor"
    "applicant_region" => null
    "applicant_district_id" => null
    "applicant_subcounty_id" => null
    "applicant_county_id" => null
    "applicant_parish_id" => null
    "applicant_village" => null
    "applicant_business_name" => null
    "applicant_business_address" => null
    "applicant_business_region" => null
    "applicant_business_district_id" => null
    "applicant_business_subcounty_id" => null
    "applicant_business_parish_id" => null
    "applicant_photo" => null
    "applicant_proof_of_payment_photo" => null
    "applicant_recommendation" => null
    "applicant_nationality" => null
    "applicant_national_insurance_number" => null
    "applicant_has_been_convicted" => null
    "applicant_conviction_details" => null
    "applicant_conviction_date" => null
    "type_of_skin" => null
    "animal_name" => null
    "animal_species" => "Bovine/Cattle"
    "animal_breed" => "Elit voluptatem dol"
    "animal_age" => "6"
    "animal_sex" => "0"
    "animal_e_id" => "Tempor deserunt cons"
    "animal_v_id" => "Nemo blanditiis cons"
    "animal_color" => "Expedita provident"
    "animal_dob" => null
    "animal_weight" => "21"
    "animal_quantity" => "578"
    "animal_identification_remarks" => "Sed sit provident"
    "package_hs_code" => "Dolores id aut sint"
    "package_type" => "Vehicle"
    "package_wight" => "2"
    "package_number" => "751"
    "package_purpose" => "Qui mollitia ut omni"
    "package_goods_description" => "Et possimus volupta"
    "package_monetry_value" => "12"
    "package_currency" => "Autem eu qui quasi t"
    "origin_owner_name" => "May Oconnor"
    "origin_address" => "Quae voluptatem Qui"
    "origin_subscount_id" => null
    "origin_district_id" => null
    "destination_country_id" => null
    "destination_district_id" => null
    "destination_subcounty_id" => null
    "destination_address" => "Quae commodo quia na"
    "destination_importer_name" => "Xandra Castillo"
    "port_of_exit" => "Quis aut placeat di"
    "movement_route" => "Repellendus Duis pl"
    "movement_transport_means" => "Dicta sunt cupidatat"
    "movement_quarantine" => null
    "has_buyer_licence" => null
    "buyer_license_number" => null
    "buyer_license_expiry" => null
    "buyer_tin" => null
    "buyer_nin" => null
    "operation_location_of_premise" => null
    "operation_floor_space_of_the_store" => null
    "operation_district_id" => null
    "operation_capacity_of_press" => null
    "operation_sub_country_id" => null
    "operation_director_of_company_of_staffing" => null
    "feed_type" => null
    "feed_quantity" => null
    "feed_description" => null
    "feed_batch_no" => null
    "invoice_number" => null
    "invoice_value" => null
    "invoice_currency" => null
    "file_inspection_report" => null
    "file_objection_letter" => null
    "file_laboratory_results" => null
    "file_invoice" => null
  ]
        */
        return $pdf->stream($m->id . ".pdf");
    }

    public function animal_profile()
    {
        //animal-profile

        $id = (int)(trim($_GET['id']));
        $m =  Animal::find($id);
        if ($m == null) {
            dd("Movement not found.");
        }

        // dd($m->village_to);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('animal-profile-export', [
            'animal' => $m
        ]));
        return $pdf->stream($m->permit_Number . ".pdf");
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
