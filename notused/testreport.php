<?
include "mysql.php";
//mysql_select_db( "doe_community20081215" );
$mydt = strtotime( $dt );
$dt2 = date( "Y-m-d", $mydt );
$dt = date( "Y-m-d", mktime( 0,0,0,date("m", $mydt ), date( "d", $mydt ), date( "Y", $mydt ) - 2 ) );

require_once "Spreadsheet/Excel/Writer.php";
$xls = new Spreadsheet_Excel_Writer( );
$filename = "report_responders_$dt.xls";
$sheet =& $xls->addWorksheet("Report");
$xls->send( $filename );


//$ext = " and id = 2150";
    $sql =  ( "select id, companyname, schoolcode, campusid  from company_esi c  where iscorp = '$session_iscorp' and c.deleted = 0 $ext order by companyname" );
$result = mysql_query( $sql ) or die( mysql_error() . $sql );

     $rownum = 0;
     $colnum = 0;
$sheet->write( $rownum, $colnum++, "School" );
$sheet->write( $rownum, $colnum++, "SchoolCode" );
while($row = mysql_fetch_array( $result ) ) 
{
    $rownum++;
    $colnum = 0;
    if( $row[campusid] )
    {
        $schoolid = db_query_first_cell( "select concat( id ) from company_esi where campusid = $row[campusid]" );
    }
    else
    {
        $schoolid = $row[id];
    }
    $sql = ( " select r.firstname, r.lastname, r.responderid, count( rt.id ) as numclasses, max( rt.trainingdate ) as trainingdate from responders_esi r left join responder_training_dates rt on (r.responderid = rt.responderid and rt.trainingdate >  '$dt' and rt.trainingdate <= '$dt2') where r.deleted = 0 and clientid in ( $schoolid ) group by responderid having numclasses > 0 " );
    $res = db_query_rows( $sql );
    $num = count( $res );
    $resps = "";
    foreach( $res as $r )
    {	     
    $resps .= "$r[lastname]: ($r[trainingdate]) $r[responderid] , ";
    }
    
    $sheet->write( $rownum, $colnum++, $row["companyname"] );
    $sheet->write( $rownum, $colnum++, $row["schoolcode"] );
    $sheet->write( $rownum, $colnum++, $num );
    $sheet->write( $rownum, $colnum++, $resps );
}
$xls->close();

?>
