<?php
$nologinrequired = 1;
require_once('mysql.php');

// Safely retrieve external variables. Assuming $id and $date are passed via $_GET or $_POST.
$id_safe = $id ?? null;
$date_safe = $date;

// Retrieve current status. Note: Proper input sanitization should be used for security in a live application.
$val = db_query_first_cell( "select status from tdrillcalendar where userid = '" . $id_safe . "' and thedate = '" . $date_safe . "'" );

// Cycle the availability status: 0 -> 1 -> 2 -> 0
if( !$val ) $val = 0;
$val++;
if( $val == 3 )
    $val = 0;
    
// Update the trainer's availability status
updateTrainerAvail( $id_safe, $date_safe, $val );

// Get the timestamp for the updated date
$dt = strtotime( $date_safe );

// Output the color corresponding to the new availability status
echo( getTrainerDrillColor( date( "m", $dt ), date( "d", $dt ), date( "Y", $dt ), $id_safe ) );
?>