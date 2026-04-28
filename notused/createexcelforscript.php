<?
$nologinrequired = true;
include "mysql.php";
require_once "Spreadsheet/Excel/Writer.php";

$filename = "/tmp/responders.xls" ;
$xls = new Spreadsheet_Excel_Writer( $filename );

// Add a worksheet to the file, returning an object to add data to
$sheet =& $xls->addWorksheet("Responders");

$sheet->write( 0, 0, "School Code" );
$sheet->write( 0, 1, "First Name" );
$sheet->write( 0, 2, "Last Name" );
$sheet->write( 0, 3, "File No" );
$sheet->write( 0, 4, "Exp Date" );
$sheet->write( 0, 5, "ESI School ID" );
$rownum = 0;


$resi = mysqli_query($link,  "select companyname, r.clientid, filenumber, pmsid, firstname, lastname, responderid, schoolcode from responders_esi r, company_esi c where c.deleted = 0 and r.deleted = 0 and c.id = r.clientid" );
while( $row = mysql_fetch_array( $res ) )
{
    $mostcurrent = db_query_first_cell( "Select responder_training_dates.trainingdate from responder_training_dates left join class on classid = class.id where responderid = $row[responderid] order by trainingdate desc" );
	$twoyears = 24*60*365*2*60;
    $tm = strtotime( $mostcurrent ) + $twoyears;
    if( $tm < time() )
        continue;
    $rownum++;
    $colnum = 0;
    $sheet->write( $rownum, $colnum++, $row["schoolcode"] );
    $sheet->write( $rownum, $colnum++, $row["firstname"] );
    $sheet->write( $rownum, $colnum++, $row["lastname"] );
    $sheet->write( $rownum, $colnum++, getIdentifier( $attendee ) );
    $sheet->write( $rownum, $colnum++, date( "Y-m-d", $tm ) );
    $sheet->write( $rownum, $colnum++, $row["clientid"] );
    $sheet->write( $rownum, $colnum++, $row["companyname"] );
}
$xls->close(); 



$filename = "/tmp/aeds.xls" ;
$xls = new Spreadsheet_Excel_Writer( $filename );

// Add a worksheet to the file, returning an object to add data to
$sheet =& $xls->addWorksheet("Responders");

$sheet->write( 0, 0, "School Code" );
$sheet->write( 0, 1, "Serial" );
$sheet->write( 0, 2, "Location" );
$sheet->write( 0, 3, "ESI School ID" );
$sheet->write( 0, 3, "School Name" );
$rownum = 0;

$res = 
mysqli_query($link,  "select companyname, r.clientid, serial, location, schoolcode from aed_esi r, company_esi c where c.deleted = 0 and r.deleted = 0 and c.id = r.clientid" );
while( $row = mysql_fetch_array( $res ) )
{
    $rownum++;
    $colnum = 0;
    $sheet->write( $rownum, $colnum++, $row["schoolcode"] );
    $sheet->write( $rownum, $colnum++, $row["serial"] );
    $sheet->write( $rownum, $colnum++, $row["location"] );
    $sheet->write( $rownum, $colnum++, $row["clientid"] );
    $sheet->write( $rownum, $colnum++, $row["companyname"] );
}
$xls->close(); 


?>
