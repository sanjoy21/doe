<? 
include "mysql.php" ;

$buildings = db_query_rows( "select * from buildings" );

$dts = array();
$dts[] = array( ( "2014-07-01" ), ( "2015-07-01" ) );
$dts[] = array( ( "2015-07-01" ), ( "2016-07-01" ) );
$dts[] = array( ( "2016-07-01" ), ( "2017-07-01" ) );
$dts[] = array( ( "2017-07-01" ), ( "2018-07-01" ) );
$dts[] = array( ( "2018-07-01" ), ( "2019-07-01" ) );



if( !$onscreen )
    {
require_once "Spreadsheet/Excel/Writer.php";
$xls = new Spreadsheet_Excel_Writer();
$filename = "data.xls";
$xls->send( $filename );
$sheet =& $xls->addWorksheet("Report");

$colnum = 0; $rownum = 0;
$sheet->write( $rownum, $colnum++, "Year" );
$sheet->write( $rownum, $colnum++, "# Schools" );
$sheet->write( $rownum, $colnum++, "# Classes" );
$sheet->write( $rownum, $colnum++, "# Responders" );
$sheet->write( $rownum, $colnum++, "# Drills" );


foreach( $dts as $dtarr )
{
$colnum = 0; $rownum++;
    $numresponders = db_query_first_cell( "select count(*) from responders_esi r, responder_training_dates rtd, company_esi c where c.id = r.clientid and r.responderid = rtd.responderid and rtd.trainingdate >= '$dtarr[0]'  and rtd.trainingdate < '$dtarr[1]'  and iscorp = 0 " );
    
    $numdrills = db_query_first_cell( "select count(*) from drill r, company_esi c where c.id = r.companyid and r.drilldate >= '$dtarr[0]'  and r.drilldate < '$dtarr[1]' and iscorp = 0 " );

    $numclasses = db_query_first_cell( "select count(*) from class r, company_esi c where c.id = r.companyid and r.startdate >= '$dtarr[0]'  and r.startdate < '$dtarr[1]' and iscorp = 0 and r.deleted = 0 " );
    $numschools = db_query_first_cell( "select count(*) from company_esi c where `date` <= '$dtarr[0]' and iscorp = 0 " );
    $sheet->write( $rownum, $colnum++, "$dtarr[0] - $dtarr[1]" );
    $sheet->write( $rownum, $colnum++, $numschools );
    $sheet->write( $rownum, $colnum++, "$numclasses" );
    $sheet->write( $rownum, $colnum++, "$numresponders" );
    $sheet->write( $rownum, $colnum++, "$numdrills" );
}
$xls->close();
exit;

    }
else
    {

	echo( "<table border=1 cellpadding=2 cellspacing=0><tr>" );
echo("<th>Year</th>" );
echo("<th># Schools</th>" );
echo("<th># Classes</th>" );
echo("<th># Responders</th>" );
echo("<th># Drills</th>" );
echo( "</tr>" );

foreach( $dts as $dtarr )
{
echo( "<tr>" );
    $numresponders = db_query_first_cell( "select count(*) from responders_esi r, responder_training_dates rtd, company_esi c where c.id = r.clientid and r.responderid = rtd.responderid and rtd.trainingdate >= '$dtarr[0]'  and rtd.trainingdate < '$dtarr[1]'  and iscorp = 0 " );
    
    $numdrills = db_query_first_cell( "select count(*) from drill r, company_esi c where c.id = r.companyid and r.drilldate >= '$dtarr[0]'  and r.drilldate < '$dtarr[1]' and iscorp = 0 " );

    $numclasses = db_query_first_cell( "select count(*) from class r, company_esi c where c.id = r.companyid and r.startdate >= '$dtarr[0]'  and r.startdate < '$dtarr[1]' and iscorp = 0 and r.deleted = 0 " );
    $numschools = db_query_first_cell( "select count(*) from company_esi c where `date` <= '$dtarr[0]' and iscorp = 0 " );
    
    echo("<td>$dtarr[0] - $dtarr[1]</td>" );
    echo("<td>$numschools</td>" );
    echo("<td>$numclasses</td>" );
    echo("<td>$numresponders</td>" );
    echo("<td>$numdrills</td>" );
echo( "</tr>" );
}
$xls->close();
	
    }
?>
