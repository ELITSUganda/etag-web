<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;


class MapController extends Controller
{
    public function charts(Content $content)
    {

        $content->row(view('charts'));
        return $content;
        return view('charts');
    }
    public function index(Content $content)
    {

        $farms = [];
        foreach (Farm::where([
            'duplicate_results' => 'NEW'
        ])->get() as $farm) {
            if ($farm->latitude == null || $farm->longitude == null) {
                continue;
            }
            //check length of lat and long
            if (strlen($farm->latitude) < 3 || strlen($farm->longitude) < 3) {
                continue;
            }
            $farms[] = [
                'id' => $farm->id,
                'lhc' => $farm->holding_code,
                'lat' => $farm->latitude,
                'long' => $farm->longitude,
                'registered' => Utils::my_date_time($farm->created_at),
                'size' => $farm->size,
                'sub' => $farm->sub_county_text,
                'farm_type' => $farm->farm_type,

            ];
        }

        $data = json_encode($farms);
        $content->title('Farms (' . number_format(count($farms)) . ')');
        $content->row(view('map', [
            'farms' => $data
        ]));
        return $content;

        //get all enterprises
        $farms = Farm::where([
            'duplicate_results' => 'NEW'
        ])->get();
        $markers = '';
        $icon_path = '';
        foreach ($farms as $farm) {
            $status = '';
            //check has closed farm
            if (!$farm->running) {
                $status = 'Closed';
                $icon_path = "'/assets/icons/pin-closed.png'";
            } else {
                $status = 'Operational';
                $icon_path = "'/assets/icons/pin-open.png'";
            }
            //check both lat and long are numeric
            if (is_numeric($farm->latitude) && is_numeric($farm->longitude)) {
                $markers .= 'new L.marker([' . $farm->latitude . ',' . $farm->longitude . '],{icon:L.icon({iconUrl:' . $icon_path . ',
                    iconSize:     [40, 50], // size of the icon
                    iconAnchor:   [22, 94], // point of the icon which will correspond to marker\'s location
                    popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
                        })}).addTo(mymap).bindPopup("<a href=\"/admin/farms/' . $farm->id . '\">' . $farm->name . '</a><br>' . $status . '").openPopup();';
            }
        }
        $content->title('Farm maps (' . count($farms) . ')');
        $content->row('<div id="mapid" style="width: 100%; height: 500px;"></div>');

        Admin::script(
            "
            //use leafletjs
            var mymap = L.map('mapid').setView([0.347596,32.582520], 8);
         
            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoiZGFsaWhpbGxhcnkiLCJhIjoiY2s1c2ZhYnp1MDF2NDNsbDd0bTNjM3RzNCJ9._wzQ6YFFVtt5c_KAbsd1XA', {
                attribution: 'Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors, <a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, Imagery © <a href=\"https://www.mapbox.com/\">Mapbox</a>',
                maxZoom: 18,
                id: 'mapbox/streets-v11',
                accessToken: 'pk.eyJ1IjoiZGFsaWhpbGxhcnkiLCJhIjoiY2s1c2ZhYnp1MDF2NDNsbDd0bTNjM3RzNCJ9._wzQ6YFFVtt5c_KAbsd1XA'
            }).addTo(mymap);" . $markers

        );



        return $content;
    }
}
