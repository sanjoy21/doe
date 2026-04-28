<?
$nologinrequired = true;
include "mysql.php" ;
// DO NOT DELETE
if( !$datetorun ) $datetorun = "2016-12-01";
?>
<form method='get'>
<input type='text' name='datetorun' value='<?=$datetorun?>'>
<input type='submit' value='Go'>
</form>
<?


if( $datetorun )
{
$schools = db_query_rows ( "select * from company_esi where iscorp = 0 and deleted = 0 and retired = 0 and showsondrillreports = 1 and schoolcode = '29-Q-052'  " );//and id = 12159

$classes = db_query_array( "select id from class where accepted = 1 and startdate > '$datetorun' and canceldate is null", "id", "id" );

$classes = implode( ",", $classes );

$h = fopen( "safetyplanreport.csv", "w" );
echo( "<table border=1 cellspacing=0 cellpadding=2><tr><th>Name</th><th>School Code</th><th>Address</th><th>Building Code</th><th>Num current</th><th>Num Upcoming</th><th>Latest training Date</th><th>Next Training Date</th></tr>" );
fputcsv($h, array( "Name", "School Code", "Address", "Building Code", "Num current", "Num Upcoming", "Last Exp Date", "Next Training Date" ) );
//fwrite( $h, "<table border=1 cellspacing=0 cellpadding=2><tr><th>Name</th><th>School Code</th><th>Address</th><th>Building Code</th><th>Num current</th><th>Num Upcoming</th><th>Last Exp Date</th><th>Next Training Date</th></tr>\n" );

foreach( $schools as $r )
{
    $buildingcodes = db_query_array( "select buildingcode from location_to_building where locationcode = '$r[locationcode]'", "buildingcode", "buildingcode" );
//    print_r( $buildingcodes );
    $addr = getCompanyAddress( $r[id], $r );
    foreach( $buildingcodes as $b )
    {
    
        $responders = db_query_array( "select responderid from responders_esi where buildingcode = '$b' ", "responderid", "responderid" );
echo( "select responderid from responders_esi where buildingcode = '$b'<br>" );
        $responders[]= -1;
        $responders = implode( ",", $responders );

        
//echo( "select count(*) from responder_training_dates where responderid in ( $responders ) and trainingdate > '2016-11-30'" );
        $count = db_query_first_cell( "select count(*) from responder_training_dates where responderid in ( $responders ) and trainingdate >= '$datetorun'" );
//        echo( "select count(*) from responder_training_dates where responderid in ( $responders ) and trainingdate >= '$datetorun-01'<br>" );
        
        if( $count < 2 ) 
        {
            $count2 = db_query_first_cell( "select count(*) from responder_to_class where responderid in ( $responders ) and classid in ( $classes ) " );
//echo( "select count(*) from responder_to_class where responderid in ( $responders ) and classid in ( $classes ) " );
            
            if( ($count2  + $count) < 2 )
            {
                $max = db_query_first_cell( "select max(trainingdate) from responder_training_dates where responderid in ( $responders ) " );
                if( $max)
                {
//                    $max = date( "Y-m-d", strtotime( "$max + 2 years" ) );
                }
                $nexttd = db_query_first_cell( "select max(startdate) from class where companyid = '$r[id]'  and startdate > now()" );
                echo( "<tr><td><a href='viewcompany.php?id=$r[id]#resps'>".$r[companyname]."</a></td><td>$r[schoolcode]</td><td>$addr</td><td>$b</td><td>$count</td><td>$count2</td><td>$max</td><td>$nexttd</td></tr>" );
                $arr = array( "<a href='viewcompany.php?id=$r[id]#resps'>".$r[companyname]."</a>","$r[schoolcode]","$addr","$b","$count","$count2","$max","$nexttd" );
                fputcsv( $h, $arr );
        }
        }
    }

    if( !count( $buildingcodes ) )
    {
        $responders = db_query_array( "select responderid from responders_esi where clientid = '$r[id]'", "responderid", "responderid" );
        $responders[]= -1;
        $responders = implode( ",", $responders );
        
        $count = db_query_first_cell( "select count(*) from responder_training_dates where responderid in ( $responders ) and trainingdate >= '$datetorun'" );
        
        if( $count < 2 ) 
        {
            $count2 = db_query_first_cell( "select count(*) from responder_to_class where responderid in ( $responders ) and classid in ( $classes ) " );
            if( ($count2  + $count) < 2 )
            {
            $max = db_query_first_cell( "select max(trainingdate) from responder_training_dates where responderid in ( $responders ) " );
            $nexttd = db_query_first_cell( "select max(startdate) from class where companyid = '$r[id]' and startdate > now() " );

            if( $max)
            {
//                $max = date( "Y-m-d", strtotime( "$max + 2 years" ) );
            }
                echo( "<tr><td><a href='viewcompany.php?id=$r[id]#resps'>".$r[companyname]."</a></td><td>$r[schoolcode]</td><td>$addr</td><td>no bc</td><td>$count</td><td>$count2</td><td>$max</td><td>$nexttd</td</tr>" );
                $arr = array( "a href='viewcompany.php?id=$r[id]#resps'>".$r[companyname]."</a>","$r[schoolcode]","$addr","no bc","$count","$count2","$max","$nexttd" );
                fputcsv( $h, $arr );
//                fwrite( $h,"<tr><td><a href='viewcompany.php?id=$r[id]#resps'>".$r[companyname]."</a></td><td>$r[schoolcode]</td><td>$addr</td><td>no bc</td><td>$count</td><td>$count2</td><td>$max</td><td>$nexttd</td</tr>\n" );
        }
        }
    }
}

echo( "</table>" );
echo( "<a href='exp.csv'>Download Here</a>" );
fclose( $h );
exit;
}
?>
