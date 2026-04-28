<? 
include "mysql.php";

$res = db_query_rows( "select responderid,  firstname, lastname, clientid, buildingcode from responders_esi where deleted = 0 and buildingcode > '' and clientid in ( select id from company_esi where showsondrillreports = 1 ) order by clientid, lastname, firstname" );

$upcoming = db_query_array( "select responderid from responder_training_dates where trainingdate > '2012-05-01' ", "responderid", "responderid" );
 
$schoolscache = array();
$bccache = array();
$count = 0;
echo( "<table>" );
foreach( $res as $r )
{
    if( !$upcoming[$r[responderid]] )
    {
        $inclass = db_query_first_cell( "select classid from responder_to_class rtc, class c where responderid = $r[responderid] and c.id = rtc.classid and startdate > now()" );
        if( !$inclass )
            continue;
    }
    
	$key = $r["clientid"] . "_" . $r[buildingcode];
	if( !isset( $schoolscache[$key] ) )
	{
        $isokay = db_query_first_cell( "Select id from company_esi where id = $r[clientid] and locationcode in ( select locationcode from location_to_building where buildingcode = '$r[buildingcode]' )" );
        $schoolscache[$key] = $isokay;
	}
    

    if( !$schoolscache[$key] )
    {
        if( !$bccache[$r[clientid]] )
        {
            $bcs = db_query_first_cell( "select group_concat( buildingcode ) from location_to_building where locationcode  = ( select locationcode from company_esi where id = $r[clientid] )" );
            $bccache[$r[clientid]] = $bcs;
        }
        $count++;
        echo("<tr><td>$count. <a href='editresponder.php?responderid=$r[responderid]'>$r[firstname] $r[lastname]</a></td><td>$r[buildingcode]</td><td>".getCompanyName( $r[clientid] )." (".$bccache[$r[clientid]].")</td></tr>" );
        
    }

}



$res = db_query_rows( "select aedid,  serial, clientid, buildingcode from aed_esi where deleted = 0 and buildingcode > '' and clientid in ( select id from company_esi where showsondrillreports = 1 )  order by clientid, serial" );

$schoolscache = array();
$bccache = array();
$count = 0;
echo( "<table>" );
foreach( $res as $r )
{
	$key = $r["clientid"] . "_" . $r[buildingcode];
	if( !isset( $schoolscache[$key] ) )
	{
        $isokay = db_query_first_cell( "Select id from company_esi where id = $r[clientid] and locationcode in ( select locationcode from location_to_building where buildingcode = '$r[buildingcode]' )" );
        $schoolscache[$key] = $isokay;
	}
    

    if( !$schoolscache[$key] )
    {
        if( !$bccache[$r[clientid]] )
        {
            $bcs = db_query_first_cell( "select group_concat( buildingcode ) from location_to_building where locationcode  = ( select locationcode from company_esi where id = $r[clientid] )" );
            $bccache[$r[clientid]] = $bcs;
        }
        $count++;
        echo("<tr><td>$count. <a href='editaed.php?aedid=$r[aedid]'>$r[serial]</a></td><td>$r[buildingcode]</td><td>".getCompanyName( $r[clientid] )." (".$bccache[$r[clientid]].")</td></tr>" );
        
    }

}



?>