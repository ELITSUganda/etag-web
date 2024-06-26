<?php

?><div class="modal" tabindex="-1" role="dialog" id="myModal"
    style="border-top-left-radius: 20px!important; border-top-right-radius: 20px!important;">
    <div class="modal-dialog" role="document"
        style="border-top-left-radius: 20px!important; border-top-right-radius: 20px!important;">
        <div class="modal-content rounded-top"
            style="border-top-left-radius: 20px!important; border-top-right-radius: 20px!important;">
            <div class="modal-header bg-primary text-white rounded-top"
                style="border-top-left-radius: 20px; border-top-right-radius: 20px!important;">
                <h3 class="modal-title"><b>Livestock Holding Details</b></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div id="map" style="width: 100%; height: 650px;"></div>
<script>
    //document ready

    function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: 1.37,
                lng: 32.4
            },
            zoom: 7,
            //type of map to be setellite
            mapTypeId: google.maps.MapTypeId.HYBRID, // HYBRID, ROADMAP, SATELLITE, TERRAIN
            fullscreenControl: true,
            streetViewControl: true,
            mapId: "DEMO_MAP_ID",
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_CENTER,
            },
            zoomControl: true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.LEFT_CENTER,
            },
            scaleControl: true,
        });



        var markList = [];
        //list lof famrs is json decode from php farm variable
        var farmList = {!! $farms !!};
        // Create an array to store markers
        const markers = [];

        var max = farmList.length;

        //Loop through the farm list and add markers to the map
        for (let i = 0; i < max; i++) {

            var farm = farmList[i];
            const marker = new google.maps.Marker({
                position: {
                    lat: parseFloat(farm.lat),
                    lng: parseFloat(farm.long)
                },
                map: map,
                title: `LHC: ${farm.lhc}, Subcounty: ${farm.sub}, Registered: ${farm.registered}, Farm Type: ${farm.farm_type}, Farm Size: ${farm.size} Ha. `,
                data: farm,
            });
            marker.addListener('click', function() {
                const farm = this.data;
                $('#myModal').modal('show');
                $('#modal-body').html(`
                    <h3 class="my-0 ">FARM: <b>${farm.lhc}</b></h3>
                    <hr>
                    <p class="my-0 ">Farm Type: <b>${farm.farm_type}</b></p>
                    <p class="my-0 ">Farm Size: <b>${farm.size} Ha</b></p>
                    <p class="my-0 ">Subcounty: <b>${farm.sub}</b></p>
                    <p class="my-0 ">Registered: <b>${farm.registered}</b></p>
                    <br>
                    <p><a target="_blank" href="farms/${farm.id}" class="btn btn-primary">View Farm Details</a></p>
                `);
            });

            markers.push(marker);
        }
        // // Add markers to the map
        markers.forEach((marker) => marker.setMap(map));
    }
</script>
