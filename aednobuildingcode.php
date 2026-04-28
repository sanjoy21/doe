<?php
include "mysql.php";

$sql = ( "select a.*, companyname, schoolcode, locationcode from aed_esi a, company_esi c where a.clientid = c.id and buildingcode = '' and iscorp = 0 and donotinclude = 0 and a.deleted = 0 and c.deleted = 0 and aedretired = 0 and aedstolen=0 and location <> 'PSAL' and location <> 'PSAL-' and location not like '%portable%' and location not like '%loaner%' and location not like '%msal%' and aedmissing = 0 and schoolcode not like '84-%' " );
//echo( $sql );
$schools = db_query_rows( $sql );
$i = 1;
echo( "<table border=1>" );
if( !isset($minnum) || !$minnum )
    $minnum = "0";
echo( "<tr><td></td><td>AED Serial</td><td>AED Location</td><td>School Code</td><td>BCs?</td><td>School Name</td></tr>" );
foreach( $schools as $s )
{
    $location_lower = isset($s['location']) ? strtolower($s['location']) : '';
    $companyname_lower = isset($s['companyname']) ? strtolower($s['companyname']) : '';
    
    if( strpos( $location_lower, "psal" ) !== false ) continue; 
    if( strpos( $companyname_lower, "psal" ) !== false ) continue; 
    if( strpos( $location_lower, "adaptive" ) !== false ) continue; 
    if( strpos( $companyname_lower, "emergency skills" ) !== false ) continue; 
    if( strpos( $location_lower, "champs" ) !== false ) continue; 
    if( strpos( $location_lower, "msbl" ) !== false ) continue;
    
    $locationcode = isset($s['locationcode']) ? $s['locationcode'] : '';
    $bc = db_query_first_cell( "select group_concat( buildingcode ) from location_to_building where locationcode = '$locationcode'" );
    
    $aedid = isset($s['aedid']) ? $s['aedid'] : '';
    $serial = isset($s['serial']) ? $s['serial'] : '';
    $location = isset($s['location']) ? $s['location'] : '';
    $clientid = isset($s['clientid']) ? $s['clientid'] : '';
    $companyname = isset($s['companyname']) ? $s['companyname'] : '';
    $schoolcode = isset($s['schoolcode']) ? $s['schoolcode'] : '';
    
    echo( "<tr><td>{$i}</td><td><a href='viewserial.php?aedid=$aedid'>$serial</a></td><td>$location</td><td><a href='viewcompany.php?id=$clientid'>$companyname</a></td><td>$bc</td><td>$schoolcode</td>" );
    echo( "</tr>" );
    $i++;
} 
?>
</table>