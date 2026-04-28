<?
include "mysql.php";
if( $xls )
{
require_once "Spreadsheet/Excel/Writer.php";
$xls = new Spreadsheet_Excel_Writer( );
$filename = "report_responders.xls";
$sheet =& $xls->addWorksheet("Report");
$xls->send( $filename );
}

if( $nbc )
    $whr = "and ( buildingcode = '' or buildingcode is null ) ";
else
    $whr = "and buildingcode > ''  ";

    $sql =  ( "select r.*, region, companyname, schoolcode, iscorp  from responders_esi r, company_esi c  where iscorp = '$session_iscorp' and c.deleted = 0 and r.deleted = 0 and (r.clientid = c.id ) and ( lastupdateresult is not null and lastupdateresult <> 'Success' ) and pmsid > '' $whr" ); 
//echo ( $sql );
$result = mysqli_query( $link, $sql ) or die( mysql_error() . $sql );
	if( !$xls && !$csv )
{
?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>

<html>
<head>
	<title>AED Report</title>

<link rel='stylesheet' href='../css/style.css'>
</head>

<body bgcolor='#ffffff'>
<table cellpadding="3" cellspacing="0" border="1" width="100%">
<tr><td colspan='8'><?=$companyname?></td></tr>
	<tr>
		<td valign="top"><span class="copy"><strong>Last Name</strong></span></td>
		<td valign="top"><span class="copy"><strong>First Name</strong></span></td>
		<td valign="top"><span class="copy"><strong>File No</strong></span></td>
		<td valign="top"><span class="copy"><strong>Update Result</strong></span></td>
		<td valign="top"><span class="copy"><strong>Most Recent Training Date</strong></span></td>
		<td valign="top"><span class="copy"><strong>School Code</strong></span></td>
		<td valign="top"><span class="copy"><strong>City</strong></span></td>
		<td valign="top"><span class="copy"><strong>State</strong></span></td>
		<td valign="top"><span class="copy"><strong>Region</strong></span></td>
		<td valign="top"><span class="copy"><strong>District</strong></span></td>
	</tr>
    <?
while($row = mysql_fetch_array( $result ) )
    {
    ?>
	<tr>
		<td valign="top"><span class="copy"><? if( !$xls && !$csv ){ ?><a href="editresponder.php?responderid=<?=$row["responderid"]?>"><? } ?><?=$row["lastname"]?></a></span></td>
		<td valign="top"><span class="copy"><?=$row["firstname"]?> </span></td>
		<td valign="top"><span class="copy"><?=$row["lastupdateresult"]?> </span></td>
		<td valign="top"><span class="copy"><?=getIdentifier( $row )?> </span></td>
		<td valign="top"><span class="copy"><?=fixdatefordisplay( getResponderExpDate( $row[responderid] ) )?></span></td>
		<td valign="top"><span class="copy"><?=$row["schoolcode"]?></span></td>
		<td valign="top"><span class="copy"><?=$row["city"]?></span></td>
		<td valign="top"><span class="copy"><?=$row["state"]?></span></td>
		<td valign="top"><span class="copy"><?=$row["region"]?></span></td>
		<td valign="top"><span class="copy"><?=$row["schoolcode"][0]?></span></td>
	</tr>
    <?
    }
    ?>
</table>

<? } else if( $csv) { 

fwrite( $hand, "\"ESI ID\"," );
fwrite( $hand, "\"File No/SSN\"," );
fwrite( $hand, "\"Last Name\"," );
fwrite( $hand, "\"First Name\"," );
fwrite( $hand, "\"Update Result\"," );
fwrite( $hand, "\"File Number\"," );
fwrite( $hand, "\"School\"," );
fwrite( $hand, "\"SchoolCode\"," );
fwrite( $hand, "\"Most Recent Training Date\"," );
fwrite( $hand, "\"Class Type\"," );
fwrite( $hand, "\"Next Scheduled Training Date\"," );
fwrite( $hand, "\"Office ID\"," );
fwrite( $hand, "\"Office Location\"," );
fwrite( $hand, "\"City\"," );
fwrite( $hand, "\"State\"," );
fwrite( $hand, "\"Region\"," );
fwrite( $hand, "\"District\"," );
	fwrite( $hand, "\n" );
     while($row = mysql_fetch_array( $result ) ) 
         {
             $sql = ( "select class.id, startdate from class, responder_to_class where responderid = $row[responderid] and classid = class.id and startdate > now() and accepted = 1 and deleted = 0 order by startdate" );
             $classdata = db_query_first($sql);
             $class_names = $allclass_names[$row["iscorp"]];
             $sd = $classdata?getFormattedDateWTime( $classdata[startdate] ):"";
             $sql = ( "select class.id, startdate, code from class, responder_to_class where responderid = $row[responderid] and classid = class.id and startdate < now() and confirmdate > '0000-00-00' and accepted = 1 and deleted = 0 order by startdate desc" );
             $classdata = db_query_first($sql);
             $mostrecent = $classdata?getFormattedDateWTime( $classdata[startdate] ):"";
//             echo( $trainedsince . ":" . strtotime( $trainedsince ) . ":" . strtotime( $classdata[startdate] ));
             if( $trainedsince && ( !$mostrecent || ( strtotime( $trainedsince ) > strtotime( $classdata[startdate] ) ) ) )
                 continue;
//             echo( "ha" );
fwrite( $hand, "\"". $row["responderid"] ."\"," );
fwrite( $hand, "\"". getIdentifier( $row ) ."\"," );
fwrite( $hand, "\"". $row["lastname"] ."\"," );
fwrite( $hand, "\"". $row["firstname"] ."\"," );
fwrite( $hand, "\"". $row["lastupdateresult"] ."\"," );
fwrite( $hand, "\"". getIdentifier( $row ) ."\"," );
fwrite( $hand, "\"". $row["companyname"] ."\"," );
fwrite( $hand, "\"". $row["schoolcode"] ."\"," );
fwrite( $hand, "\"".  $mostrecent ."\"," );
fwrite( $hand, "\"".  $class_names[$classdata[code]] ."\"," );
fwrite( $hand, "\"". $sd ."\"," );
fwrite( $hand, "\"". $row["branchid"] ."\"," );
fwrite( $hand, "\"". $row["address"] ."\"," );
fwrite( $hand, "\"". $row["city"] ."\"," );
fwrite( $hand, "\"". $row["state"] ."\"," );
fwrite( $hand, "\"". $row["region"] ."\"," );
fwrite( $hand, "\"". $row["schoolcode"][0] ."\"," );
	fwrite( $hand, "\n" );
         }
fclose( $hand );
echo( "<a href='file.csv'>here</a>" );
    } else { 
     $rownum = 0;
     $colnum = 0;
$sheet->write( $rownum, $colnum++, "Last Name" );
$sheet->write( $rownum, $colnum++, "First Name" );
$sheet->write( $rownum, $colnum++, "Update Result" );
$sheet->write( $rownum, $colnum++, "File Number" );
$sheet->write( $rownum, $colnum++, "School" );
$sheet->write( $rownum, $colnum++, "SchoolCode" );
$sheet->write( $rownum, $colnum++, "Most Recent Training Date" );
$sheet->write( $rownum, $colnum++, "Office Location" );
$sheet->write( $rownum, $colnum++, "City" );
$sheet->write( $rownum, $colnum++, "State" );
$sheet->write( $rownum, $colnum++, "Region" );
$sheet->write( $rownum, $colnum++, "District" );
     while($row = mysql_fetch_array( $result ) ) 
         {
             $rownum++;
             $colnum = 0;
             $sd = fixdatefordisplay( getResponderExpDate( $row[responderid] ) );
             $sheet->write( $rownum, $colnum++, $row["lastname"] );
             $sheet->write( $rownum, $colnum++, $row["firstname"] );
             $sheet->write( $rownum, $colnum++, $row["lastupdateresult"] );
             $sheet->write( $rownum, $colnum++, getIdentifier( $row ) );
             $sheet->write( $rownum, $colnum++, $row["companyname"] );
             $sheet->write( $rownum, $colnum++, $row["schoolcode"] );
             $sheet->write( $rownum, $colnum++, $sd );
             $sheet->write( $rownum, $colnum++, $row["address"] );
             $sheet->write( $rownum, $colnum++, $row["city"] );
             $sheet->write( $rownum, $colnum++, $row["state"] );
             $sheet->write( $rownum, $colnum++, $row["region"] );
             $sheet->write( $rownum, $colnum++, $row["schoolcode"][0] );
         }
     $xls->close();

} ?>
