<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Movement;
use App\Models\SubCounty;
use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PrintController2 extends Controller
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

    public function index()
    {
        $id = (int)(trim($_GET['id']));
        $m =  Movement::find($id);
        if ($m == null) {
            dd("Movement not found.");
        }

        if(strlen($m->permit_Number)<2){
            $m->permit_Number = "################";
        } 
        
        Utils::make_movement_qr($m);
        $code_link =  url('storage/codes/'.$m->id.".png");
        $i = 0;
        $animals = "";
        foreach ($m->movement_has_movement_animals as $key => $v) {
            if($v->animal == null){
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


        $d =  District::find($m->district_from);
        $district_from = "-";
        

        $d =  District::find($m->district_to);
        $district_to = "-";
         

        $sub =  SubCounty::find($m->sub_county_from);
        $sub_county_from = "-";
        if ($sub != null) {
            $sub_county_from = $sub->name . " ($sub->code)";
            if($sub->district != null){
                $district_from = $sub->district->name;
            }
        }

        $sub =  SubCounty::find($m->sub_county_to);
        $sub_county_to = "-";
        if ($sub != null) {
            $sub_county_to = $sub->name . " ($sub->code)";
            if($sub->district != null){
                $district_to = $sub->district->name;
            }
        }


        if ($m == null) {
            dd("Permit data not found.");
        }

        $font_roboto_link =  ('public/assets/fonts/Roboto/Roboto-Light.ttf');
        $logo_link =  url('assets/images/coat_of_arms-min.png');
 
        $title = "E-Permit";
        $sub_title = "INTER-DISTRICT VETERINARY HEALTH CERTIFICATE PERMITTING THE MOVEMENT
        OF SLAUGHTER ANIMALS (ALL SPECIES) WITHIN UGANDA ONLY";
        $data = "
            <style>
            @font-face {
                font-family: 'Roboto-Regular';
                font-weight: normal;
                font-weight: 400; 
                font-style: normal;
                font-variant: normal;
                src: url('$font_roboto_link');
            }
            
            p{
                font-size: 12px;
                padding: 0;
                margin: 0;
                font-family: Roboto-Regular;
            }
            .title-cell{
                width: 25%;
                font-family: Roboto-Regular;
                font-size: 12px;
                background-color: #D9D9D9;
                font-family:  sans-serif;
                font-weight: 100;
            }
           
            table, th, td {
                font-weight: 100;
                text-align: reight;
                font-family:  sans-serif;
                font-size: 12px;
                border-collapse: collapse;
                padding: 4px;
            }
            .bordered-table,.bordered-table td{
                border: 1px solid black;
            }
            table{
                  width: 100%;
              }
              </style>
        ";


        $data .= '<table  class="no-border">
                    <tr class="no-border">
                        <td width="35%">
                        <p>Permit status.: '.$m->status.'  
                        <p>Permit no.: '.$m->permit_Number.'  
                        </td>
                        <td width="20%"><img width="70%" src="'.$logo_link.'"/></td>
                        <td width="45%">
                        <p>MINISTRY OF AGRICULTURE, ANIMAL INDUSTRY AND FISHERIES.</p> 
                        <p>DEPARTMENT OF ANIMAL HEALTH</p><br>
                        <p>P.O. Box 513, ENTEBBE, UGANDA</p> 
                        <p>E-MAIL: animalhealth@agriculture.co.ug</p>
                        <p>TELEPHONE: +256 0414 320 627, 320166, 320376</p>
                        </td>
                    </tr>
                </table>';
                
                $data .= '<br><h3 style="text-align: center;  margin: 0; padding: 0; font-weight: 100; font-size:15px;">' . $sub_title . '</h3>';
        $data .= '<br><h2 style="text-align: center;  margin: 0; padding: 0; font-weight: 100;">' . $title . '</h2>';
        $data .= '<h3 style="text-align: center; margin: 0; padding: 0; font-weight: 100; color: grey;">NOT TO MOVE AT NIGHT</h3>';
        $data .= "<br>";


        $data .= '<h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">i. Identification of animals </h3>';
        $data .= '<table class="bordered-table" > 
                        <thead>' .
            '<tr>
                        <td class="" >Sn.</td>
                            <td>LHC</td>
                            <td>E-ID</td>
                            <td>V-ID</td>
                            <td>SEX</td> 
                            <td>Year Born</td> 
                        </tr>'
            . '</thead>
                        <tbody>' . $animals .
            '</tbody>
        </table>';

        


        $data .= '<br><h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">ii. Place of origin for the animals</h3>';
        $data .= "<p>Name of owner / farm / ranch /unit <u> $m->trader_name </u> is permitted to move animals within  <u> $m->valid_from_Date </u> - <u> $m->valid_to_Date </u>  days
        From the Sub-county / Division Of <u>{$m->village_from}</u> in the District of <u>{$sub_county_from}, {$district_from}</u>.</p><br>";
         
        $data .= '<br><h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">iii. Destination of animals</h3>';
        $data .= "<p>To the District Of  <u>{$m->village_to} </u> in the Sub-county / Division of <u>{$sub_county_to}, {$district_to} </u>
        Specifically to the following slaughter or processing place/s: <u>{$m->destination}</u>
        Means of transport, and stock-routes to be used <u>{$m->transportation_route} </u> ";

        $data .= '<br><br><br><h3 style="text-align: left; margin: 0; padding: 0; font-weight: 100;">iv. Zoo-sanitary information and attestation:</h3>';
        $data .= '<p>I, the undersigned authorized state veterinary officer certifies to the best of my knowledge that the animals
                    described above and examined on this day come from a disease free area / area not under any animal quarantine
                    restrictions and:</p>
                <ol>
                    <li>a) Show no sign of disease, vectors or pests</li>
                    <li>Satisfy the required animal health standards of the final destination district</li>
                    <li>c) Are fit for Slaughter purposes</li>
                </ol>
                <p>Issued at (name of district and date): .........................................................</p>   
                <h4>Name, rank, address and telephone of the authorized state veterinary officer:<h4>
                <p>..................................................................................................................</p>   
                <p>Signature and stamp / seal: ..................................................................................................................</p>
                <br>

                <table>
                    <tbody>

                        <tr>
                        <td width="20%" ><img width="100%" src="'.$code_link.'"/></td>
                        <td>
                        <p>
                            Animals are to remain under isolation and none to be removed or added in transit up to the final designated slaughter / processing places 
                            Emergency Slaughters to be supervised by authorized veterinary personnel and a report submitted to the final destination veterinary authorities,
                            Animals ( revention of Cruetty) Chapter 39 must be guaranteed by the certifying, loading and stock-route inspection officers. Animals here mean
                            domestic mammalian, birds and wildlife animals of terrestrial -aquatic origin. The Certificate is issued in triplicate and for only a single consignment. 
                            Slaughter animals shall be identified by the SL mark on the left jaw and or centrally serialized Red ear tags. Extra inf; should be attached. 
                            This is not a revenue collection receipt.
                            </p>
                        </td>
                        </tr>

                    </tbody>
                </table>
            

                ';
                
        
       
        


        $pdf = App::make('dompdf.wrapper');
        
        $pdf->loadHTML($data);
        return $pdf->stream();
    }

    // 
}
