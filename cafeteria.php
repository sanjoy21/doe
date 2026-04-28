<?php 
include "mysql.php" ;

$resp = db_query_rows( "select * from responders_esi where (title like '%cafe%' or title like '%lunch%' or title like '%kitchen%') and deleted = 0" );

echo( "<table><tr><th>Name</th><th>Title</th><th>School</th><th>Training Date</th></tr>" );

foreach( $resp as $r ) 
{    
    $dtrow = db_query_first( "select * from responder_training_dates where responderid = '" . ($r["responderid"] ?? '') . "' order by trainingdate desc limit 1" );
    echo( "<tr><td><a href='editresponder.php?responderid=" . ($r["responderid"] ?? '') . "'>" . ($r["firstname"] ?? '') . " " . ($r["lastname"] ?? '') . "</a></td><td>" . ($r["title"] ?? '') . "</td><td><a href='viewcompany.php?id=" . ($r["clientid"] ?? '') . "'>" . getCompanyName( $r["clientid"] ?? null ) . "</a></td><td>" . ($dtrow["trainingdate"] ?? 'N/A') . "</td></tr>" );
}

echo("</table>" );
?>