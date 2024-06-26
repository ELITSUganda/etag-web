<!DOCTYPE html>
<html>
<head>
    <title>Random GPS Markers in Uganda</title>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9Mdls_ETVjOb_u5bjcqavSI4E8S1D2Vs&callback=Function.prototype"></script>
    

    <script>
        // Function to initialize the map
        function initMap() {
            // Center the map on Uganda
            var uganda = {lat: 1.3733, lng: 32.2903};
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 6,
                center: uganda
            });

            // Generate and place 10 random markers within Uganda
            for (var i = 0; i < 10; i++) {
                var lat = getRandomInRange(-1.5, 4, 6);
                var lng = getRandomInRange(29.5, 35, 6);
                var markerElement = document.createElement('div');
                markerElement.textContent = '📍';
                markerElement.style.fontSize = '24px';
                markerElement.style.position = 'absolute';
                markerElement.style.transform = 'translate(-50%, -50%)';

                new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: {lat: lat, lng: lng},
                    content: markerElement
                });
            }
        }

        // Function to get a random number within a range
        function getRandomInRange(from, to, fixed) {
            return (Math.random() * (to - from) + from).toFixed(fixed) * 1;
        }
    </script>
</head>
<body>
    <h1>Random GPS Markers in Uganda</h1>
    <div id="map" style="height: 500px; width: 100%;"></div>
</body>
</html>
``
