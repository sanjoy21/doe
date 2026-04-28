<?
include "mysql.php";
$responderids = db_query_array( "select responderid, trainingdate from responder_training_dates where trainingdate > '2015-10-31' order by trainingdate ", "responderid", "trainingdate" );
//print_r( $responderids )
$str = implode( ", ", array_keys( $responderids ) );

$sql = "select * from responders_esi r, company_esi c where c.id = r.clientid and c.iscorp = 0 and c.deleted = 0 and r.deleted = 0 and responderid in ( $str ) order by lastname, firstname ";
$result = db_query_rows( $sql );

if( $xls || 1  ) {

    require_once "Spreadsheet/Excel/Writer.php";
    $xls = new Spreadsheet_Excel_Writer();
    $sheet =& $xls->addWorksheet("Report");
    
    $filename = "bbp.xls";
    $xls->send( $filename );
    $rownum = 0;
    $colnum = 0;
    
        $sheet->write( $rownum, $colnum++, "PMS ID" );
        $sheet->write( $rownum, $colnum++, "First Name" );
        $sheet->write( $rownum, $colnum++, "Last Name" );
        $sheet->write( $rownum, $colnum++, "School Name" );
        $sheet->write( $rownum, $colnum++, "DBN" );
        $sheet->write( $rownum, $colnum++, "Responder ID" );
        $sheet->write( $rownum, $colnum++, "Training Expiration Date" );
    
    while($row = mysql_fetch_array( $result ) ) 
    { 
        $dt = date( "Y-m-d", strtotime( $responderids[$row[responderid]] . " + 2 years" ) );
        $rownum++;
        $colnum = 0;
        $sheet->writeString( $rownum, $colnum++, getIdentifier( $row, 0 ) );
        $sheet->write( $rownum, $colnum++, $row["firstname"] );
        $sheet->write( $rownum, $colnum++, $row["lastname"] );
        $sheet->write( $rownum, $colnum++, $row["companyname"] );
        $sheet->write( $rownum, $colnum++, $row["schoolcode"] );
        $sheet->write( $rownum, $colnum++, $row["responderid"] );
        $sheet->write( $rownum, $colnum++, $dt );
    }
    $xls->close();
    exit;
}



?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Report</title>
</head>

<body bgcolor="#ffffff">

<table cellpadding="3" cellspacing="0" border="1" width="100%">
	<tr>
<th><?=getSchoolStr( "PMS ID" )?></th>
<th>First Name</th>
<th>Last Name</th>
<th>School Name</th>
<th>DBN</th>
<th>Responder ID</th>        
<th>Training Expiration Date</th>        
</tr>

<? 
while($row = mysql_fetch_array( $result ) ) 
{ 
    $dt = date( "Y-m-d", strtotime( $responderids[$row[responderid]] . " + 2 years" ) );
?>
	<tr>
    <td> <?=getIdentifier( $row, 0 )?></td>
<td><?=$row["firstname"]?></td>
<td><?=$row[lastname]?></td>
<td><?=$row[companyname]?></td>
<td><?=$row[schoolcode]?></td>
<td><?=$row[responderid]?></td>
<td><?=$dt?></td>
	</tr>
    	<? } ?>
</table>
