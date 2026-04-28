<? 
include "mysql.php" ;

$handle = fopen("/tmp/tocheck.csv", "r");
echo( "<table border=1>" );
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
    $rowcnt++;
    if( $rowcnt == 1 )
        continue;
    if( !$data[0] ) continue;
    $bcode = trim( $data[0] );
    $companypotential = db_query_rows( "select * from responders_esi where buildingcode = '$bcode' and deleted = 0 and pmsidvalidated = 1" );
    if( count( $companypotential ) ) continue;

    echo( "<tr><td valign='top'>$bcode</td><td>\n" );
    foreach( $companypotential as $c )
    {
        echo( "$c[firstname] $c[lastname]: <a href='viewresponder.php?responderid=$c[responderid]'>$c[pmsid]</a><br>\n" );
    }
    echo( "</td></tr>" );
    }
    echo( "</table>\n" );
?>
