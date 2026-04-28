<?
$nologinrequired = true;
include "mysql.php" ;
// DO NOT DELETE

echo( "<form method='post'>School Code: <input type='text' name='schoolcode' value='$schoolcode'> <input type='Submit' name='Go' value='Go'></form>" );

if( $schoolcode )
{
$schools = db_query_rows ( "select * from company_esi where iscorp = 0 and deleted = 0 and retired = 0 and showsondrillreports = 1  and schoolcode = '$schoolcode' " );//and id = 12159

$classes = db_query_array( "select id from class where accepted = 1 and startdate > '2014-04-04' and canceldate is null", "id", "id" );

$classes = implode( ",", $classes );

echo( "<table border=1 cellspacing=0 cellpadding=2><tr><th>Name</th><th>School Code</th><th>Address</th><th>Building Code</th><th>Num current</th><th>Num Upcoming</th><th>Last Exp Date</th><th>Next Training Date at this school</th><th>Current Responders in this building</th><th>Responders with upcoming classes</th></tr>" );

foreach( $schools as $r )
{
    $buildingcodes = db_query_array( "select buildingcode from location_to_building where locationcode = '$r[locationcode]'", "buildingcode", "buildingcode" );
    $addr = getCompanyAddress( $r[id], $r );
    foreach( $buildingcodes as $b )
    {
    
        $responders = db_query_array( "select responderid from responders_esi where buildingcode = '$b' ", "responderid", "responderid" );
//        print_r( $responders );
        $responders[]= -1;
        $responders = implode( ",", $responders );

        
//echo( "select count(*) from responder_training_dates where responderid in ( $responders ) and trainingdate > '2012-11-30'" );
        $dates = db_query_rows( "select * from responder_training_dates where responderid in ( $responders ) and trainingdate > '2012-11-30'" );
        
        $classes = db_query_rows( "select * from responder_to_class where responderid in ( $responders ) and classid in ( $classes ) " );
        $count = count( $dates );
        $count2 = count( $classes );

        $rrows = "<table>";
        foreach( $classes as $c )
        {
            $rrow = getResponderRow( $c["responderid"] );
            $rrows .= "<tr><td>$rrow[firstname] $rrow[lastname]</td><td>$rrow[buildingcode]</td><td>".getCompanyLink( $rrow[clientid] )."</td><td>".getClassLink( $c[classid] )."</td></tr>" ;
        }
        $rrows .= "</table>";

        $currents = "<table>";
        foreach( $dates as $c )
        {
            $rrow = getResponderRow( $c["responderid"] );
            $currents .= "<tr><td>$rrow[firstname] $rrow[lastname]</td><td>$rrow[buildingcode]</td><td>".getCompanyLink( $rrow[clientid] )."</td><td>".getFormattedDate( $c[trainingdate] )."</td></tr>" ;
        }
        $currents .= "</table>";

        
        
//echo( "select count(*) from responder_to_class where responderid in ( $responders ) and classid in ( $classes ) " );
        
        $max = db_query_first_cell( "select max(trainingdate) from responder_training_dates where responderid in ( $responders ) " );
        $nexttd = db_query_first_cell( "select max(startdate) from class where companyid = '$r[id]'  and startdate > now()" );
        echo( "<tr><td><a href='viewcompany.php?id=$r[id]#resps'>".$r[companyname]."</a></td><td>$r[schoolcode]</td><td>$addr</td><td>$b</td><td>$count</td><td>$count2</td><td>$max</td><td>$nexttd</td><td>$currents</td><td>$rrows</td></tr>" );
    }

    if( !count( $buildingcodes ) )
    {
        $responders = db_query_array( "select responderid from responders_esi where clientid = '$r[id]'", "responderid", "responderid" );
        $responders[]= -1;
        $responders = implode( ",", $responders );
        
        $dates = db_query_rows( "select count(*) from responder_training_dates where responderid in ( $responders ) and trainingdate > '2012-11-30'" );
        
		$classes = db_query_rows( "select * from responder_to_class where responderid in ( $responders ) and classid in ( $classes ) " );
        $count = count( $dates );
        $count2 = count( $classes );
        
        $max = db_query_first_cell( "select max(trainingdate) from responder_training_dates where responderid in ( $responders ) " );
        $nexttd = db_query_first_cell( "select max(startdate) from class where companyid = '$r[id]' and startdate > now() " );
        
        echo( "<tr><td><a href='viewcompany.php?id=$r[id]#resps'>".$r[companyname]."</a></td><td>$r[schoolcode]</td><td>$addr</td><td>no bc</td><td>$count</td><td>$count2</td><td>$max</td><td>$nexttd</td><td>$rrows</td></tr>" );
    }
}

echo( "</table>" );
}

?>
