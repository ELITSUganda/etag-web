<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;


class MapController extends Controller
{
    public function index(Content $content)
    {
        $content->title('Farm maps');
        $content->row(view('map'));
        return $content;

        //get all enterprises
        $farms = \App\Models\Farm::get();
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
        $content->title('Farm maps');
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
