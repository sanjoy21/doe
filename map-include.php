<?php
// Initialize globals that the functions rely on
global $curlcount, $curlhandle;
$curlcount = $curlcount ?? 0;
// $curlhandle is initialized inside the function

/**
 * Executes a cURL request to fetch file contents, supporting POST fields.
 * @param string $URL The URL to fetch.
 * @param string $params POST data string.
 * @return string|bool The content of the URL on success, or FALSE on failure.
 */
function curl_get_file_contents_2($URL, $params = "")
{
    global $curlcount, $curlhandle;
    $curlcount++;

    $curlhandle = curl_init();
    curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlhandle, CURLOPT_FOLLOWLOCATION, true);

    if ($params) {
        curl_setopt($curlhandle, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curlhandle, CURLOPT_POSTFIELDS, $params);
    }
    
    // speed up?
    curl_setopt($curlhandle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    curl_setopt($curlhandle, CURLOPT_URL, $URL);
    $contents = curl_exec($curlhandle);
    
    // Cleanup should ideally happen, but we follow the original logic by leaving it commented.
    // curl_close($curlhandle);

    if ($contents) {
        return $contents;
    } else {
        // Log cURL error for debugging
        // error_log("cURL Error: " . curl_error($curlhandle));
        return FALSE;
    }
}

/**
 * Retrieves Latitude and Longitude for an address, using a cache table or MapQuest API.
 * @param string $address Street address.
 * @param string $city City.
 * @param string $state State.
 * @return array|bool Array with Latitude and Longitude, or FALSE.
 */
function getLatLong($address, $city = "", $state = "", $zip = "")
{
    // MapQuest API key (assumed to be a known key)
    $key = "hk6RCj2cIoRJ7ChduYEsT0sox3CMsRLH"; // username is emergencyskills
    
    // Check local cache first (assumes escMe() and db_query_first() are defined)
    $res = db_query_first("SELECT Latitude, Longitude FROM address_to_latitude WHERE address = '" . escMe($address) . "' AND city = '" . escMe($city) . "' AND state = '" . escMe($state) . "' AND latitude > -1");

    if (!$res) {
        $taddress = urlencode($address);
        $ext = "";
        
        if ($state) {
            $ext .= "&state=" . urlencode($state);
        }
        if ($city) {
            $ext .= "&city=" . urlencode($city);
        }
        if ($zip) {
            $ext .= "&postalCode=" . urlencode($zip);
        }

        $url = "http://www.mapquestapi.com/geocoding/v1/address?key={$key}&street={$taddress}{$ext}&outFormat=json";
        
        // Log API call URL (original script used /tmp/rc)
        // file_put_contents("/tmp/rc", $url . "\n", FILE_APPEND);
        
        $tmp = curl_get_file_contents_2($url);
        $contents = json_decode($tmp);
        
        $latitude = -1;
        $longitude = -1;
        $restype = "";

        if (isset($contents->results[0]->locations) && is_array($contents->results[0]->locations)) {
            // Priority: Find a location specifically matching state "NY"
            foreach ($contents->results[0]->locations as $tmplocation) {
                if (
                    isset($tmplocation->adminArea3) && 
                    trim(strtolower($tmplocation->adminArea3)) == trim(strtolower("NY"))
                ) {
                    $latitude = $tmplocation->latLng->lat ?? -1;
                    $longitude = $tmplocation->latLng->lng ?? -1;
                    $restype = "fullmatch";
                    break;
                }
            }
        }
        
        // Secondary: Use the default location if no "NY" match was found, but a result exists
        if ($latitude == -1 && $longitude == -1 && isset($contents->results[0]->locations[0])) {
             if (
                isset($contents->results[0]->locations[0]->adminArea3) &&
                trim(strtolower($contents->results[0]->locations[0]->adminArea3)) == trim(strtolower("NY"))
            ) {
                $latitude = $contents->results[0]->locations[0]->latLng->lat ?? -1;
                $longitude = $contents->results[0]->locations[0]->latLng->lng ?? -1;
                $restype = "default";
            }
        }

        // Cache the result (even if it's -1 to avoid repeated lookups)
        if ($latitude != 0) { // Condition ensures we cache invalid (-1) or valid (> -1) results
            $sql = "INSERT INTO address_to_latitude 
                    (address, city, state, zip, latitude, longitude, restype) 
                    VALUES (
                        '" . escMe($address) . "', 
                        '" . escMe($city) . "', 
                        '" . escMe($state) . "', 
                        '" . escMe($zip) . "', 
                        '" . escMe((string)$latitude) . "', 
                        '" . escMe((string)$longitude) . "', 
                        '" . escMe($restype) . "'
                    )";
            db_query($sql);
            
            // Re-query to return consistent structure and potentially handle transaction commits
            $res = db_query_first("SELECT Latitude, Longitude FROM address_to_latitude WHERE address = '" . escMe($address) . "' AND city = '" . escMe($city) . "' AND state = '" . escMe($state) . "' AND zip = '" . escMe($zip) . "'");
        } else {
             // If latitude is 0 (which is valid for Ghana/Equator, but unlikely here), we still want to return it.
             // If $latitude and $longitude are -1, $res will be null, and we return that.
             $res = ['Latitude' => $latitude, 'Longitude' => $longitude];
        }
    }
    
    return $res;
}

// --- Main Script Execution ---

$locs = [];
// $alreadydisplayed and getTrainingAddress() are assumed to be defined externally
$alreadydisplayed = $alreadydisplayed ?? [];

foreach ($alreadydisplayed as $crow) {
    // Safely retrieve properties, defaulting to empty string if not set
    $crow_id = $crow['id'] ?? null;
    $crow_startdate = $crow['startdate'] ?? '';
    $crow_remote = $crow['remote'] ?? false;
    $crow_location = $crow['training_location'] ?? '';
    $crow_city = $crow['training_city'] ?? '';
    $crow_state = $crow['training_state'] ?? '';

    $locarr = [
        "classid" => $crow_id, 
        "classdate" => date("m/d/Y h:i a", strtotime($crow_startdate)),
    ];
    
    // getTrainingAddress() is assumed to be an external function
    $locarr["address"] = getTrainingAddress($crow);
    
    if ($crow_remote) continue;
    if (!$crow_location) continue;

    $latlong = getLatLong($crow_location, $crow_city, $crow_state);
    
    if (isset($latlong["Latitude"]) && $latlong["Latitude"] > -1) {
        $locarr["latitude"] = $latlong["Latitude"];
        $locarr["longitude"] = $latlong["Longitude"];

        $locs[] = $locarr;
    }
}

// Function to safely escape strings for JavaScript JSON output
function safe_json_encode($data) {
    $data = json_encode($data);
    // Escape single quotes for use inside single-quoted JS strings
    return str_replace("'", "\'", $data);
}

// Function to safely escape strings for use inside double-quoted JS strings
function safe_js_string($string) {
    return addslashes(trim($string));
}

// --- HTML and JavaScript Output ---
?>

<div id="map" class="map">
</div><div id="backme">
</div><script src="https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.js"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v1.12.0/mapbox-gl.css" rel="stylesheet" />

<script>
    var maxzoom = 6;
    // Mapbox access token
    mapboxgl.accessToken = 'pk.eyJ1IjoicmM1d3JjNXciLCJhIjoiY2tsdjZxMG54Mm5ycDJ3cG02MmVmdzc4aSJ9.TqoYgdD5C2jfAlRCWIpcbQ'; 
    
    var mydata = {
        "type": "FeatureCollection",
        "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
        "features": [
            <?php 
            $any = false;
            foreach ($locs as $locarr) {
                if ($any) {
                    echo ", \n ";
                }
                $any = true;
                
                // Ensure values are safely outputted to JavaScript
                $classid = intval($locarr["classid"]);
                $classdate = safe_js_string($locarr["classdate"] ?? '');
                $address = safe_js_string($locarr["address"] ?? '');
                $longitude = floatval($locarr["longitude"] ?? 0.0);
                $latitude = floatval($locarr["latitude"] ?? 0.0);
            ?>
            { "type": "Feature", "properties": { "classid": <?php echo $classid; ?>, "description": "<?php echo $classid; ?>", "classdate": "<?php echo $classdate; ?>", "address": "<?php echo $address; ?>" }, "geometry": { "type": "Point", "coordinates": [ <?php echo $longitude; ?>, <?php echo $latitude; ?>, 0.0 ] } } 
            <?php 
            } 
            ?>
        ]
    };

    var map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v11', // replace this with your style URL
        center: [-74.0060, 40.7128], // Default to NYC coordinates
        zoom: 10.7
    });

    // code from the next step will go here

    map.on('load', function() {
        map.addControl(new mapboxgl.NavigationControl());

        map.addSource("classes", {
            type: "geojson",
            data: mydata,
        });

        map.loadImage("/icon_mapmarker.png", function(error, image) {
            if (error) throw error;
            map.addImage("custom-marker", image);
            
            map.addLayer({
                id: "unclustered-point",
                type: "symbol",
                source: "classes",
                filter: ["!", ["has", "point_count"]],
                layout: {
                    "icon-image": "custom-marker",
                    "icon-allow-overlap": true // Allow markers to stack
                }
            });

        });
        
        // Calculate bounds and fit map
        var bounds = null;
        for(i = 0; i < mydata.features.length; i++ ) {
            var coord = mydata.features[i].geometry.coordinates;
            
            if (coord[0] === 0.0 && coord[1] === 0.0) continue; // Skip bad geocodes

            if (bounds == null) {
                bounds = new mapboxgl.LngLatBounds(coord, coord);
            }
            bounds.extend(coord);
        }
        
        <?php if (count($locs) > 0) { ?>
        
            if (bounds) {
                map.fitBounds(bounds, {
                    padding: 20,
                    // maxZoom: maxzoom // If maxzoom is desired
                });
            }

            // Click listener for markers
            map.on('click', 'unclustered-point', function (e) {
                var features = e.features;
                
                if (!features || features.length === 0) return;

                var props = features[0].properties;
                var confirmationMessage = "Go to Class # " + props.classid + 
                                          "\n" + props.classdate + 
                                          "\n" + props.address + 
                                          "\n?";
                
                var val = confirm(confirmationMessage);
                
                if (val) {
                    document.location.href = "#class" + props.classid;
                }
            });
            
            // Change the cursor to a pointer when the mouse is over the unclustered-point layer.
            map.on('mouseenter', 'unclustered-point', function () {
                map.getCanvas().style.cursor = 'pointer';
            });
            
            // Change it back to a pointer when it leaves.
            map.on('mouseleave', 'unclustered-point', function () {
                map.getCanvas().style.cursor = '';
            });
            
        <?php } ?>
        
    });
</script>