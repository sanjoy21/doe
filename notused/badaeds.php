<?
include "mysql.php";

if( $nbc )
    $whr = "and ( buildingcode = '' or buildingcode is null ) ";
else
    $whr = "and buildingcode > ''  ";
 
$sql= ("select c.contactname, c.contactphone,aedmissing, lastupdateresult, a.buildingcode, outofwarranty, datecompleted, invoiced, installcomplete, c.summer,c.schoolcode,c.region,a.aedid,a.clientid,a.branchid,a.serial,a.model,a.floor,a.location,a.padaexpiration,a.padbexpiration,a.batterydate,a.sparedate,c.directorname,c.filingexpirationdate,a.eventhistory,a.date,c.id,c.companyname,c.address, c.city, c.zip ,a.pediatricpads, a.irn, c.companyname,c.medicalinvoicedate,a.aedservicehistory from aed_esi a, company_esi c where iscorp = '0' and a.deleted=0 and a.clientid=c.id and c.deleted = 0 and (lastupdateresult is not null and lastupdateresult <> 'Success') $whr ");

$result = mysqli_query( $link, $sql );
$companyinfo = array();
if( $xls ) {
require_once "Spreadsheet/Excel/Writer.php";
$fname = "reports/report_badaeds_".time().".xls" ;
$xls = new Spreadsheet_Excel_Writer( $fname );
$sheet =& $xls->addWorksheet("Report");
//     $xls->send( $filename );
     $rownum = 0;
     $colnum = 0;
     $sheet->write( $rownum, $colnum++, "AED Serial Number" );
     $sheet->write( $rownum, $colnum++, "Update Result" );
     $sheet->write( $rownum, $colnum++, "Building Code" );
     $sheet->write( $rownum, $colnum++, "Missing?" );
     $sheet->write( $rownum, $colnum++, "Warranty?" );
     $sheet->write( $rownum, $colnum++, "Model/Type" );
     $sheet->write( $rownum, $colnum++, "School Location" );
     $sheet->write( $rownum, $colnum++, "School Name" );
     $sheet->write( $rownum, $colnum++, "School Code" );
     $sheet->write( $rownum, $colnum++, "Region" );
     $sheet->write( $rownum, $colnum++, "City" );
     $sheet->write( $rownum, $colnum++, "Zip" );
     $sheet->write( $rownum, $colnum++, "Floor" );
     $sheet->write( $rownum, $colnum++, "Summer School?" );
     $sheet->write( $rownum, $colnum++, "Complete?" );
     $sheet->write( $rownum, $colnum++, "Date Completed" );
     $sheet->write( $rownum, $colnum++, "Invoiced?" );
     $sheet->write( $rownum, $colnum++, "AED Location" );
     $sheet->write( $rownum, $colnum++, "Pads A" );
     $sheet->write( $rownum, $colnum++, "Pads B" );
     $sheet->write( $rownum, $colnum++, "Pediatric Pads" );
     $sheet->write( $rownum, $colnum++, "Spare Battery Install Before Date" );
     $sheet->write( $rownum, $colnum++, "Battery" );
     $sheet->write( $rownum, $colnum++, "Medical Director" );
     $sheet->write( $rownum, $colnum++, "Municipal Filing" );
     $sheet->write( $rownum, $colnum++, "Num Drills" );
     $sheet->write( $rownum, $colnum++, "Num Service Calls" );
     $sheet->write( $rownum, $colnum++, "Service History" );
     $sheet->write( $rownum, $colnum++, "Last Service Date" );
     $sheet->write( $rownum, $colnum++, "Trainer Name" );
     $sheet->write( $rownum, $colnum++, "Contact Name" );
     $sheet->write( $rownum, $colnum++, "Contact Phone" );


	$tmptrainers = array();
     while($row = mysql_fetch_array( $result ) ) 
         {
             $rownum++;
             $colnum = 0;
		$numdrills = db_query_first_cell( "Select count(*) from drill where companyid = $row[clientid]" );
		$numservicecalls = db_query_first_cell( "Select count(*) from servicecall where companyid = $row[clientid]" );

             $sheet->write( $rownum, $colnum++, $row[serial] );
             $sheet->write( $rownum, $colnum++, $row[lastupdateresult] );
             $sheet->write( $rownum, $colnum++, $row[buildingcode] );
             $sheet->write( $rownum, $colnum++, $row[aedmissing]?"Yes":"No" );
             $sheet->write( $rownum, $colnum++, $row[outofwarranty]?"W":"" );
             $sheet->write( $rownum, $colnum++, $row[model] );
             $sheet->write( $rownum, $colnum++, $row[address] );
             $sheet->write( $rownum, $colnum++, $row[companyname] );
             $sheet->write( $rownum, $colnum++, $row[schoolcode] );
             $sheet->write( $rownum, $colnum++, $row[region] );
             $sheet->write( $rownum, $colnum++, $row[city] );
             $sheet->write( $rownum, $colnum++, $row[zip] );
             $sheet->write( $rownum, $colnum++, $row[floor] );
             $sheet->write( $rownum, $colnum++, $row[summer]?"Yes":"No");
             $sheet->write( $rownum, $colnum++, $row[installcomplete]?"Yes":"No");
             $sheet->write( $rownum, $colnum++, $row[installcomplete]?$row["datecompleted"]:"");
             $sheet->write( $rownum, $colnum++, $row[invoiced]?"Yes":"No");
             $sheet->write( $rownum, $colnum++, $row[location] );
             $sheet->write( $rownum, $colnum++, $row[padaexpiration]!="0000-00-00"?$row[padaexpiration]:"" );
             $sheet->write( $rownum, $colnum++, $row[padbexpiration]!="0000-00-00"?$row[padbexpiration]:"" );
             $sheet->write( $rownum, $colnum++, $row[pediatricpads]!="0000-00-00"?$row[pediatricpads]:"" );
             $sheet->write( $rownum, $colnum++, $row[sparedate]!="0000-00-00"?$row[sparedate]:"" );
             $sheet->write( $rownum, $colnum++, $row[batterydate]!="0000-00-00"?$row[batterydate]:"" );
             $sheet->write( $rownum, $colnum++, $row[directorname] );
             $sheet->write( $rownum, $colnum++, $row[filingexpirationdate]!="0000-00-00"?$row[filingexpirationdate]:"" );
             $sheet->write( $rownum, $colnum++, $numdrills );
             $sheet->write( $rownum, $colnum++, $numservicecalls );
             $sheet->write( $rownum, $colnum++, $row[aedservicehistory] );
             $maxsc = db_query_first_cell( "select max( servicecalldate ) from servicecall s, aed_to_servicecall ats where ats.servicecallid = s.servicecallid and serial = '".mysql_escape_string( $row[serial] )."'" );
             $maxdr = db_query_first_cell( "select max( drilldate ) from drill s, aed_to_drill ats where ats.drillid = s.drillid and serial = '".mysql_escape_string( $row[serial] )."'" );
             $row[lastservicedate] = $maxsc>$maxdr?$maxsc:$maxdr;
             $sheet->write( $rownum, $colnum++, $row[lastservicedate] );
             if( $tmptrainers[$row[zip]] )
                 $tr = $tmptrainers[$row[zip]];
             else
             {
                 $tr = getTrainersForZip( $row[zip] );
                 $tmptrainers[$row[zip]] = $tr ; 
             }
             $trainername = "";
             foreach( $tr as $tw )
                 {
                     if( $trainername )
                         $trainername .= ", ";
                     $trainername .= $tw[name];
                 }
             $sheet->write( $rownum, $colnum++, $trainername );

             $sheet->write( $rownum, $colnum++, $row[contactname] );
             $sheet->write( $rownum, $colnum++, $row[contactphone] );

             echo( "." );
             flush();
         }
     $xls->close();
	echo( "<a href='$fname'> View it here</a>" );

}
else
{

$page_contents = "

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>

<html>
<head>
	<title>AED Report</title>

</head>

<body bgcolor='#ffffff'>
";


     $page_contents .="<table cellpadding='3' cellspacing='0' border='1' width='100%'>";

if($id) {
     $page_contents .="	<tr><td colspan='4'>".getCompanyName($id)."     </td></tr> ";
 }

$page_contents .="
	<tr>
		<td valign='top'><span class='copy'><strong>AED Serial Number</strong></span></td>
		<td valign='top'><span class='copy'><strong>Update Result</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Building Code</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Missing</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Warranty</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Model/Type</strong></span></td>
		<td valign='top'><span class='copy'><strong>School Location</strong></span></td>
		<td valign='top'><span class='copy'><strong>School Code</strong></span></td>
		<td valign='top'><span class='copy'><strong>Region</strong></span></td>
		<td valign='top'><span class='copy'><strong>City</strong></span></td>
		<td valign='top'><span class='copy'><strong>Zip</strong></span></td>
		<td valign='top'><span class='copy'><strong>Floor</strong></span></td>
		<td valign='top'><span class='copy'><strong>Summer School?</strong></span></td>
		<td valign='top'><span class='copy'><strong>Complete?</strong></span></td>
		<td valign='top'><span class='copy'><strong>Date Completed</strong></span></td>
		<td valign='top'><span class='copy'><strong>Invoiced?</strong></span></td>
		<td valign='top'><span class='copy'><strong>AED Location</strong></span></td>
		<td valign='top'><span class='copy'><strong>Pads A<br>Exp. Date</strong></span></td>
		<td valign='top'><span class='copy'><strong>Pads B<br>Exp. Date</strong></span></td>
		<td valign='top'><span class='copy'><strong>Pediatric Pads<br>Exp. Date</strong></span></td>
		<td valign='top'><span class='copy'><strong>Spare Battery Intall Before Date</strong></span></td>
		<td valign='top'><span class='copy'><strong>Battery<br>Intallation Date</strong></span></td>
		<td valign='top'><span class='copy'><strong>Medical Director</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Municipal Filing<br> Exp. Date</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Num Drills</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Num Service Calls</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Service History</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Last Service Date</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Trainer Name</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Contact Name</strong></span></td>		
		<td valign='top'><span class='copy'><strong>Contact Phone</strong></span></td>		
	</tr>
";

$ziptrainers = array();
while($row = mysql_fetch_array( $result ) ) 
	{ 
	$numdrills = db_query_first_cell( "Select count(*) from drill where companyid = $row[clientid]" );
	$numservicecalls = db_query_first_cell( "Select count(*) from servicecall where companyid = $row[clientid]" );
    $tr = $ziptrainers[$row[zip]];
    if( !isset( $tr ) )
    {
        $tr = getTrainersForZip( $row[zip] );
        $ziptrainers[$row[zip]] = $tr;
    }
	$trainername = "";
	foreach( $tr as $tw )
        {
            if( $trainername )
                $trainername .= ", ";
            $trainername .= $tw[name];
        }
    $maxsc = db_query_first_cell( "select max( servicecalldate ) from servicecall s, aed_to_servicecall ats where ats.servicecallid = s.servicecallid and serial = '".mysql_escape_string( $row[serial] )."'" );
    $maxdr = db_query_first_cell( "select max( drilldate ) from drill s, aed_to_drill ats where ats.drillid = s.drillid and serial = '".mysql_escape_string( $row[serial] )."'" );
    $row[lastservicedate] = $maxsc>$maxdr?$maxsc:$maxdr;
    $page_contents .= "
	<tr>
 		<td valign='top'><span class='copy'>". ($xls == null?"<a href='/editaed.php?aedid=$row[aedid]'>":"")."$row[serial]</a></span></td>
 		<td valign='top'><span class='copy'>$row[lastupdateresult]</span></td>
 		<td valign='top'><span class='copy'>$row[buildingcode]</span></td>
 		<td valign='top'><span class='copy'>".($row[aedmissing]?"Yes":"No")."</span></td>
 		<td valign='top'><span class='copy'>".($row[outofwarranty]?"W":"")."</span></td>
 		<td valign='top'><span class='copy'>$row[model]</span></td>
		<td valign='top'><span class='copy'>". ($xls == null?"<a href='/viewcompany.php?id=$row[clientid]'>":"")."$row[address]</a><br>$row[companyname]</span></td>
 		<td valign='top'><span class='copy'>$row[schoolcode]</span></td>
 		<td valign='top'><span class='copy'>$row[region]</span></td>
		<td valign='top'><span class='copy'>$row[city]</span></td>
		<td valign='top'><span class='copy'>$row[zip]</span></td>
		<td valign='top'><span class='copy'>$row[floor]</span></td>
		<td valign='top'><span class='copy'>".($row[summer]?"Yes":"No" )."</span></td>
		<td valign='top'><span class='copy'>".($row[installcomplete]?"Yes":"No" )."</span></td>
		<td valign='top'><span class='copy'>".($row[installcomplete]?$row["datecompleted"]:"" )."</span></td>
		<td valign='top'><span class='copy'>".($row[invoiced]?"Yes":"No" )."</span></td>
		<td valign='top'><span class='copy'>$row[location]</span></td>
		<td valign='top'><span class='copy'>$row[padaexpiration]</span></td>
		<td valign='top'><span class='copy'>$row[padbexpiration]</span></td>
		<td valign='top'><span class='copy'>$row[pediatricpads]</span></td>
		<td valign='top'><span class='copy'>$row[sparedate]</span></td>
		<td valign='top'><span class='copy'>$row[batterydate]</span></td>
		<td valign='top'><span class='copy'>$row[directorname]</span></td>		
		<td valign='top'><span class='copy'>$row[filingexpirationdate]</span></td>		
		<td valign='top'><span class='copy'>$numdrills</span></td>		
		<td valign='top'><span class='copy'>$numservicecalls</span></td>		
		<td valign='top'><span class='copy'>$row[aedservicehistory]</span></td>
		<td valign='top'><span class='copy'>$row[lastservicedate]</span></td>
		<td valign='top'><span class='copy'>$trainername</span></td>";
    $page_contents .= ( "<td valign='top'>$row[contactname]</td>" );
    $page_contents .= ( "<td valign='top'>$row[contactphone]</td>" );
    
$page_contents .= "
</tr>
";
    }
        $page_contents .= "</table>";
    echo( $page_contents );
}
?>
