<!DOCTYPE html>
<html>

<head>
    <title>Random Markers in Uganda</title>
    
</head>

<body>
    <div id="map" style="width: 100%; height: 600px;"></div>
    <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: 1.37,
                    lng: 32.4
                },
                zoom: 5,
                fullscreenControl: false,
                streetViewControl: false,
                mapId: "DEMO_MAP_ID",
            });


            // Uganda's approximate latitude and longitude ranges
            const minLat = -0.375;
            const maxLat = 4.22;
            const minLng = 29.63;
            const maxLng = 35.01;

            // Create an array to store markers
            const markers = [];

            // Generate 10 random markers within Uganda's borders
            for (let i = 0; i < 10; i++) {
                const lat = Math.random() * (maxLat - minLat) + minLat;
                const lng = Math.random() * (maxLng - minLng) + minLng;

                const marker = new google.maps.Marker({
                    position: {
                        lat,
                        lng
                    },
                    map: map,
                });

                markers.push(marker);
            }
            // // Add markers to the map
            markers.forEach((marker) => marker.setMap(map));
        }
    </script>
</body>

</html>
