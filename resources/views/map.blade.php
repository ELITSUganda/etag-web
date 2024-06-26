<!DOCTYPE html>
<html>

<head>
    <title>Random Markers in Uganda</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9Mdls_ETVjOb_u5bjcqavSI4E8S1D2Vs&callback=initMap" async></script>
</head>

<body>
    <div id="map" style="width: 100%; height: 400px;"></div>
    <script>
        function initMap() {
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

            // Replace 'YOUR_API_KEY' with your actual Google Maps API key
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 7,
                center: {
                    lat: 1.37,
                    lng: 32.4
                }, // Center on Uganda
            });

            // Add markers to the map
            markers.forEach((marker) => marker.setMap(map));
        }
    </script>
</body>

</html>
