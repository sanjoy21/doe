<?php 
include "mysql.php" ;

echo( "<table>" );
echo( "<tr><th>Company Name</th><th>Location</th><th>Serial</th></tr>" );

// Query for AEDs located in Trailers or marked as portable/transportable
$res = db_query_rows( "SELECT clientid, serial, location, deleted, aedid FROM aed_esi WHERE location LIKE '%Trailer%' OR location LIKE '%transPortable%';" );

foreach( $res as $c )
{
    // PHP 8.2 Compliance and Security: Quoting array keys and using htmlspecialchars()
    $client_id = htmlspecialchars($c['clientid'] ?? 'N/A');
    $location = htmlspecialchars($c['location'] ?? 'N/A');
    $aed_id = htmlspecialchars($c['aedid'] ?? 'N/A');
    $serial = htmlspecialchars($c['serial'] ?? 'N/A');
    
    // Assuming getCompanyName() handles its own output safely, we pass the raw ID
    $company_name = getCompanyName( $c['clientid'] ?? null );
    
    echo( "<tr>
            <td><a href='editcompany.php?id={$client_id}'>" . $company_name . "</a></td>
            <td>{$location}</td>
            <td><a href='editaed.php?aedid={$aed_id}'>{$serial}</a></td>
          </tr>\n" );
}

echo( "</table>" );
?>