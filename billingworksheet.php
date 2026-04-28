<?php
require_once('mysql.php');
//echo( "d? $d " );
$drillrow = db_query_first( "select * from appuploads where type = 'drill' and schoolid = '$schoolid' and date( dateinupload ) = '$d'" );


if( isset($scid) && $scid )
    $scrow = db_query_first( "select * from appuploads where id = $scid" );
else
{
    if( isset($drillrow['id']) && $drillrow['id'] )
    {
        $tmpdrillid = db_query_first_cell( "select value from appuploaddata where uploadid = '{$drillrow['id']}' and name = 'drillid'" );
        if( $tmpdrillid )
        {
//           echo( $tmpdrillid . " is tmpdrill<br>" );
            $scid = db_query_first_cell( "select appid from servicecall where assocdrillid = '$tmpdrillid'" );
//echo( "select uploadid from appuploaddata, servicecall where servicecall.servicecallid = appuploaddata.value and appuploaddata.name = 'servicecallid' and assocdrillid = '$tmpdrillid'<br>" );
//            echo( "scid: " . $scid . "<br>" );
        }
    }
    if( isset($scid) && $scid )
    {
            // this is the one associated with the drill we're looking at...
        $scrow = db_query_first( "select * from appuploads where id = $scid" );
    }
    else
    {
        $scrow = db_query_first( "select * from appuploads where type = 'sc' and schoolid = '$schoolid' and date( dateinupload ) = '$d'" );
    }
}
$numdrills = db_query_array( "select id from appuploads where type = 'drill' and schoolid = '$schoolid' and date( dateinupload ) = '$d'", "id", "id" );
$nirow = db_query_first( "select * from appuploads where type = 'ni' and schoolid = '$schoolid' and date( dateinupload ) = '$d' order by dateuploaded desc" );
$numsc = db_query_array( "select id from appuploads where type = 'sc' and schoolid = '$schoolid' and date( dateinupload ) = '$d'", "id", "id" );
if( isset($_GET["help"]) && $_GET["help"] )
    echo( "select id from appuploads where type = 'sc' and schoolid = '$schoolid' and date( dateinupload ) = '$d'" );

if( isset($numdrills) && is_array($numdrills) && count( $numdrills ) > 1 && isOverallAdmin() )
{
//echo( "<font color='bold'>Warning! There were more than one drills uploaded for this <a href='apiuploads.php?schoolid=$schoolid&dto=$d+23:59:59&dfrom=$d&Go=1&viewarch=1'>day</a>!</font>" );	
foreach( $numdrills  as $d )
{
//echo( "<br><A href='appdrill.php?id=$d'>$d</a>" );
}
}
if( isset($numsc) && is_array($numsc) && count( $numsc ) > 1 && isOverallAdmin() )
{
//echo( "<font color='bold'>Warning! There were more than one drills uploaded for this <a href='apiuploads.php?schoolid=$schoolid&dto=$d+23:59:59&dfrom=$d&Go=1&viewarch=1'>day</a>!</font>" );	
foreach( $numsc  as $d )
{
if( isset($_GET["help"]) && $_GET["help"] )
    echo( "<br><A href='appservicecall.php?id=$d'>$d</a>" );
}
}

$nosave = true; 
$types = array( $drillrow, $scrow, $nirow );
foreach( $types as $t )
{
    if( !isset($t["type"]) || !$t["type"] ) continue;
    $urlname = "appdrill.php";
    if( $t["type"] == "sc" )
        $urlname = "appservicecall.php";
    else if( $t["type"] == "ni" )
        $urlname = "appnewinstall.php";

    $id = $t["id"] ?? '';
    if( isset($debug) && $debug )
    {
        print_r( $t );
        echo( "$urlname : $id<Br>" );
    }

    include $urlname;
}

if( isset($printable) && $printable ) { 
?>
    <script language='javascript'>
        window.print();
    </script>
<?php } ?>