<?php 
$nologinrequired = true;
include "mysql.php";
require_once "services.php";

// $rrow = db_query_first( "select * from responders_esi where responderid = 791257" );
// file_put_contents( "/tmp/rctemp", $rrow[firstname] . " " . $rrow[lastname] );
// exit;

// Fetch records where firstname or lastname contains a non-breaking space (U+00A0)
// Note: '' is used to represent the non-breaking space character in the original code.
$rrows = db_query_rows( "select * from responders_esi where firstname like '% %' or lastname like '% %' " );

foreach( $rrows as $r )
{
    // Safely access quoted array keys for output
    $firstname_safe = $r["firstname"] ?? '';
    $lastname_safe = $r["lastname"] ?? '';
    $responderid_safe = $r["responderid"] ?? 0;
    
    echo( $firstname_safe . ", ". $lastname_safe . "<br>" );
    
    // Update the database to remove the non-breaking space character from both name fields
    db_query( "update responders_esi 
               set lastname = replace( lastname, ' ', '' ), 
                   firstname = replace( firstname, ' ', '' ) 
               where responderid = '" . (int)$responderid_safe . "'" );
}

?>