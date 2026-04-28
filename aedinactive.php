<?php
include "mysql.php";

$filecont = file( "/tmp/units.csv" );
$aeds = array();
foreach( $filecont as $f )
    $aeds[] = "'" . trim( $f ) . "'";
$str = implode( ", ", $aeds );


// Execute query to find inactive AEDs matching the serial numbers in the file
$res = db_query_rows( "select serial, aedid from aed_esi where serial in ( $str ) and aedinactive = 1" );

// Output a list of inactive AEDs with links to the edit page
foreach( $res as $r )
{
    // Array keys are now quoted for PHP 8.2 compatibility
    echo( "<a href='editaed.php?aedid=" . ($r["aedid"] ?? '') . "'>" . ($r["serial"] ?? '') . "</a><br>" );
}
?>