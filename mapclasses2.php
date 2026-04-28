<!DOCTYPE html>
<html>

<head>
    <title>Corrected Google Maps Demo</title>
    <style>
        /* Set the size of the div element that contains the map */
        #map {
            height: 400px;
            /* The height is 400 pixels */
            width: 100%;
            /* The width is the width of the web page */
        }
    </style>
    <script>
        var map;
        var InforObj = [];
        // Center the map on Uluru
        var centerCords = {
            lat: -25.344,
            lng: 131.036
        };
        var markersOnMap = [
            {
                placeName: "Australia (Uluru)",
                LatLng: [{
                    lat: -25.344,
                    lng: 131.036
                }]
            },
            {
                placeName: "Australia (Melbourne)",
                LatLng: [{
                    lat: -37.852086,
                    // Corrected Longitude for Melbourne
                    lng: 144.963058
                }]
            },
            {
                placeName: "Australia (Canberra)",
                LatLng: [{
                    lat: -35.299085,
                    // Corrected Longitude for Canberra
                    lng: 149.109615
                }]
            },
            {
                placeName: "Australia (Gold Coast)",
                LatLng: [{
                    lat: -28.013044,
                    // Corrected Longitude for Gold Coast
                    lng: 153.425586
                }]
            },
            {
                placeName: "Australia (Perth)",
                LatLng: [{
                    lat: -31.951994,
                    // Corrected Longitude for Perth
                    lng: 115.858081
                }]
            }
        ];

        window.onload = function () {
            initMap();
        };

        function addMarkerInfo() {
            for (var i = 0; i < markersOnMap.length; i++) {
                var contentString = '<div id="content"><h1>' + markersOnMap[i].placeName +
                    '</h1><p>Lorem ipsum dolor sit amet, vix mutat posse suscipit id, vel ea tantas omittam detraxit.</p></div>';

                // NOTE: Using 'const' and 'let' is better practice in modern JS but using 'var' to match original style.
                var marker = new google.maps.Marker({
                    position: markersOnMap[i].LatLng[0],
                    map: map
                });

                var infowindow = new google.maps.InfoWindow({
                    content: contentString,
                    maxWidth: 200
                });

                marker.addListener('click', function () {
                    closeOtherInfo();
                    infowindow.open(marker.get('map'), marker);
                    // Store the active infowindow
                    InforObj[0] = infowindow;
                });
                
                // Original mouseover/mouseout listeners are kept commented out
            }
        }

        function closeOtherInfo() {
            if (InforObj.length > 0) {
                /* detach the info-window from the marker ... undocumented in the API docs */
                InforObj[0].set("marker", null);
                /* and close it */
                InforObj[0].close();
                /* blank the array */
                InforObj.length = 0;
            }
        }

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                // Adjust zoom level to show all of Australia
                zoom: 4, 
                center: centerCords
            });
            addMarkerInfo();
        }
    </script>
</head>

<body>
    <h3>My Google Maps Demo (Corrected)</h3>
    <div id="map"></div>

     <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBx5KeNlRfTXCG0nBu9FkDom0NvDZ7Y9DY"></script>

</body>

</html>