<?php 

include "mysql.php"; 

$row = getCompanyRow( $id ?? null );

$addr = $row["address"] ?? '';

// Replace various cardinal direction abbreviations for normalization
$addr = str_replace( array(" W.", " West" ), " W", $addr );
$addr = str_replace( array(" N.", " North" ), " N", $addr );
$addr = str_replace( array(" E.", " East" ), " E", $addr );
$addr = str_replace( array(" S.", " South" ), " S", $addr );

// Construct the full address string
$addy = "$addr, " . ($row["city"] ?? '') . ", " . ($row["state"] ?? '') . ", " . ($row["zip"] ?? '');

// URL encode the address for the API call
$urladdy = str_replace( ",", "%2C", $addy );
$urladdy = str_replace( " ", "%20", $urladdy );

// Construct the full ArcGIS geocode suggestion URL
$fullurl = "https://utility.arcgis.com/usrsvcs/servers/9d812b2197984f1bba2bd565a7f25c10/rest/services/World/GeocodeServer/suggest?f=json&text=$urladdy&maxSuggestions=6&searchExtent=%7B%22spatialReference%22%3A%7B%22latestWkid%22%3A3857%2C%22wkid%22%3A102100%7D%2C%22xmin%22%3A-8279238.194446411%2C%22ymin%22%3A4928315.257300768%2C%22xmax%22%3A-8188736.752956826%2C%22ymax%22%3A5014230.477093264%7D&location=%7B%22spatialReference%22%3A%7B%22latestWkid%22%3A3857%2C%22wkid%22%3A102100%7D%2C%22x%22%3A-8233987.473701619%2C%22y%22%3A4971272.867197016%7D";

// Log the URL and output (assuming file_put_contents and curl_get_file_contents exist)
file_put_contents( "hotspotlookups", $fullurl . "\n", FILE_APPEND);
$output = curl_get_file_contents( $fullurl );
file_put_contents( "hotspotlookups", "output: $output\n", FILE_APPEND );

// Decode the JSON response
$vals = json_decode( $output );

// Process the suggestion if available
if( is_array( $vals->suggestions ?? null ) && count($vals->suggestions) > 0 )
{
    $addr = $vals->suggestions[0]->text;
    
    // Clean up the address from the geocoder
    $addr = str_replace( ", USA", "" , $addr );
    
    // Double URL encode the address for the final redirect URL
    $addr = str_replace( " ", "%2520", $addr );
    $addr = str_replace( ",", "%252C", $addr );
}
else
{
    // Use the original address if geocoding fails
    $addr = str_replace( " ", "%2520", $addy ); // Ensure original is also encoded
    $addr = str_replace( ",", "%252C", $addr );
}

// Redirect the user to the NYC map lookup application
Header( "Location: https://nycgov.maps.arcgis.com/apps/instant/lookup/index.html?appid=021940a41da04314827e2782d3d1986f&find=$addr" );
exit;
?>