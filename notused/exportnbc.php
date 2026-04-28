<?
include "mysql.php";
$sql = "select * from nbcsignups order by dateadded desc ";
$result = db_query_rows( $sql );

if( $xls || 1  ) {

    require_once "Spreadsheet/Excel/Writer.php";
    $xls = new Spreadsheet_Excel_Writer();
    $sheet =& $xls->addWorksheet("NBC Signups");
    
    $filename = "nbc.xls";
    $xls->send( $filename );
    $rownum = 0;
    $colnum = 0;
    
        $sheet->write( $rownum, $colnum++, "Date Added" );
        $sheet->write( $rownum, $colnum++, "Name" );
        $sheet->write( $rownum, $colnum++, "Email" );
        $sheet->write( $rownum, $colnum++, "Building" );
        $sheet->write( $rownum, $colnum++, "Floor" );
        $sheet->write( $rownum, $colnum++, "IP Address" );
    
    foreach( $results as $row )
    { 
        $rownum++;
        $colnum = 0;
        $sheet->write( $rownum, $colnum++, $row["dateadded"] );
        $sheet->write( $rownum, $colnum++, $row["name"] );
        $sheet->write( $rownum, $colnum++, $row["email"] );
        $sheet->write( $rownum, $colnum++, $row["building"] );
        $sheet->write( $rownum, $colnum++, $row["floor"] );
        $sheet->write( $rownum, $colnum++, $row["ipaddress"] );
    }
    $xls->close();
    exit;
}

?>
<!-- not used -->

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
