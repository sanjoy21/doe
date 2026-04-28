<?php
include "mysql.php";
// we're no longer doing this this way so be sure before you uncomment
// if( $xls ) {
//     Header( "Content-type: application/vnd.ms-excel" );
//     header("Content-Transfer-Encoding: binary");
//     $user_agent = strtolower ($_SERVER["HTTP_USER_AGENT"]);
//     $filename = "aeds.xls";
//     if ((is_integer (strpos($user_agent, "msie"))) && (is_integer (strpos($user_agent, "win")))) {
//        header( "Content-Disposition: filename=".basename($filename).";" );
//     } else {
//     header( "Content-Disposition: attachment; filename=".basename($filename).";" );
//     }
// }

if (isset($id) && $id) {
    $row = getCompanyRow( $id );
    if( isset($row["iscorp"]) && !$row["iscorp"] && isset($row["campusid"]) && $row["campusid"] )
    {
        
        $where=" and ( a.clientid=$id or c.campusid = $row[campusid] )";
    }
    else
    {
         $where=" and a.clientid=$id ";
    }
}

if( isset($csv) && $csv )
{
    $hand = fopen( "aedfile.csv", "w+" );
}

if( isset($expired) && $expired )
{
    $set = getsetting( "expredbefore" );
    $where .= " and ( ( a.padaexpiration > '0000-00-00' and a.padaexpiration < '$set' ) or ( a.padbexpiration > '0000-00-00' and a.padbexpiration <  '$set'  ) or ( a.model <> 'FRX' and a.pediatricpads > '0000-00-00' and a.pediatricpads <  '$set' ) ) and aedmissing = 0 and aedstolen = 0 ";
//    $where .= " and ( ( a.padaexpiration > '0000-00-00' and a.padaexpiration < now() ) or ( a.padbexpiration > '0000-00-00' and a.padbexpiration <  now()  ) or ( a.model <> 'FRX' and a.pediatricpads > '0000-00-00' and a.pediatricpads <  now() ) ) and aedmissing = 0 and aedstolen = 0 ";
}

if( isset($missing) && $missing )
{
    $where .= " and aedmissing = 1 ";
}

if( isset($terrance) && $terrance )
{
    $where .= " and buildingcode in ( 'M048', 'M808', 'M844', 'M860', 'M401', 'M633', 'M882', 'X807', 'X815', 'X833', 'X953', 'K801', 'K802', 'K818', 'K831', 'K986', 'K986', 'K986', 'K989', 'K997', 'Q732', 'Q733', 'Q800', 'Q801', 'Q801', 'Q823', 'Q859', 'Q980', 'R080' ) ";
}

if( isset($stolen) && $stolen )
{
    $where .= " and aedstolen = 1 ";
}

if( !isset($stolen) || !$stolen && !isset($missing) || !$missing && !isset($psal) || !$psal && !isset($expired) || !$expired)
{
    $where .= " and aedstolen = 0 ";
}

if( isset($newinstall) && $newinstall )
{
    $where .= " and ( newinstall = 1 )" ;
}

if( isset($installcomplete) && $installcomplete )
{
    $where .= " and ( installcomplete = 1 and invoiced = 0 )" ;
}

if( isset($notexpired) && $notexpired )
{
    $set = getsetting( "expredbefore" );
    $where .= " and ( ( a.padaexpiration is null or a.padaexpiration = '0000-00-00' or a.padaexpiration >  '$set'  ) and ( a.padbexpiration is null or a.padbexpiration = '0000-00-00' or a.padbexpiration > '$set' ) and ( a.pediatricpads is null or a.pediatricpads = '0000-00-00' or a.pediatricpads > '$set' ) ) ";
}

if( isset($notwithinyear) && $notwithinyear )
{
    $dt = date( "Y-m-d", strtotime( "1 year ago" ) );
    $where .= " and ( a.datecompleted < '$dt' or a.datecompleted is null ) ";
    $where .= " and ( a.serial not in ( select serial from aed_to_drill ad, drill d where ad.drillid = d.drillid and completed = 1 and drilldate >= '$dt' ) )  ";
    $where .= " and ( a.serial not in ( select serial from aed_to_servicecall ad, servicecall d where ad.servicecallid = d.servicecallid and completed = 1 and servicecalldate >= '$dt' ) )  ";
}

if( isset($psal) && $psal )
    $where .= " and location like '%PSAL%'";

if( isset($charter) && $charter )
{
    $where .= " and companyname like '%charter%' ";
}

// if( !$id )
//     $sodr = $session_iscorp?"":" and c.showsondrillreports = 1";

$where .= " and c.id <> 12424 and c.id <> 12068 and c.companyname <> 'Warranty Returns' and c.companyname <> 'Triple Chirp Returns'";

$sql= ("select c.campusid, a.buildingcode, pediatrickey, outofservice, readytoreturn, aedcomments, aedstolentext, c.accountmanager, a.isrma, a.isrecall,c.contactname, c.contactphone, c.contactemail,aedmissing, aedstolen,outofwarranty, datecompleted, invoiced, installcomplete, c.summer,c.schoolcode,c.region,c.iscoolingcenter,a.aedid,a.clientid,a.branchid,a.serial,a.model,a.floor,a.location,a.padaexpiration,a.padbexpiration,a.batterydate,a.sparedate,c.directorname,c.filingexpirationdate,a.eventhistory,a.date,c.id,c.companyname,c.address, c.city, c.zip ,a.pediatricpads, a.irn, c.companyname,c.medicalinvoicedate,a.aedservicehistory, a.hasbeenupdated from aed_esi a, company_esi c where iscorp = '$session_iscorp' and a.deleted=0 and a.clientid=c.id and c.deleted = 0 and c.excludereporting = 0 $sodr $where $order");
//echo( $sql );
//exit;
$result = db_query_rows( $sql );
//echo("a" );
//exit;
$companyinfo = array();

if( isset($xls) && $xls ) {
    $filename = "report_aeds_".time().".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write BOM for UTF-8 to handle special characters in Excel
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = array(
        "AED Serial Number",
        "Missing?",
        "Warranty?",
        "Model/Type",
        "School Location",
        "School Name",
        "School Code",
    );
    
    if( isset($session_iscorp) && $session_iscorp == (defined('AGING') ? AGING : 4) ) {
        $header[] = "Group";
    } else {
        $header[] = "Building Code";
    }
    
    $header = array_merge($header, array(
        "Region",
        "Is Cooling Center?",
        "City",
        "Zip",
        "Floor",
        "Summer School?",
        "Complete?",
        "Date Completed",
        "Invoiced?",
        "Recalled?",
        "RMA?",
        "AED Location"
    ));
    
    if( isset($psal) && $psal ) {
        $header[] = "Stolen?";
        $header[] = "Missing?";
    }
    
    $header = array_merge($header, array(
        "Pads A",
        "Pads B",
        "Pediatric Pads",
        "G 2005",
        "Spare Battery Install Before Date",
        "Battery Installation Date",
        "Medical Director",
        "Municipal Filing",
        "Num Drills",
        "Num Service Calls",
        "Service History",
        "Last Service Date",
        "Trainer Name",
        "# Trained Responders",
        "Contact Name",
        "Contact Phone",
        "Contact Email",
        "Next Scheduled Training Date"
    ));
    
    if( isset($session_iscorp) && $session_iscorp ) {
        $header[] = "Account Manager";
    }
    
    if( (isset($missing) && $missing) || (isset($stolen) && $stolen) ) { 
        $header[] = "Previous Locations";
        if( isset($stolen) && $stolen ) {
            $header[] = "Police Report";
        }
    } 
    
    $header = array_merge($header, array(
        "AED ID",
        "School ID",
        "Out of Service?",
        "Ready To Return?",
        "AED Comments"
    ));
    
    fputcsv($output, $header);
    
    $tmptrainers = array();
    if( isset($result) && is_array($result) ) {
        foreach( $result as $row ) {
            $numdrills = isset($row["clientid"]) ? db_query_first_cell( "Select count(*) from drill where companyid = $row[clientid]" ) : 0;
            $numservicecalls = isset($row["clientid"]) ? db_query_first_cell( "Select count(*) from servicecall where companyid = $row[clientid]" ) : 0;

            $rowData = array(
                isset($row["serial"]) ? $row["serial"] : '',
                isset($row["aedmissing"]) && $row["aedmissing"] ? "Yes" : "No",
                isset($row["outofwarranty"]) && $row["outofwarranty"] ? "W" : "",
                isset($row["model"]) ? $row["model"] : '',
                isset($row["address"]) ? $row["address"] : '',
                isset($row["companyname"]) ? $row["companyname"] : '',
                isset($row["schoolcode"]) ? $row["schoolcode"] : ''
            );
            
            if( isset($session_iscorp) && $session_iscorp == (defined('AGING') ? AGING : 4) ) {
                $rowData[] = isset($row["campusid"]) ? getCampusName( $row["campusid"] ) : '';
            } else {
                $rowData[] = isset($row["buildingcode"]) ? $row["buildingcode"] : '';
            }
            
            $rowData = array_merge($rowData, array(
                isset($row["region"]) ? $row["region"] : '',
                isset($row["iscoolingcenter"]) && $row["iscoolingcenter"] ? "Yes" : "No",
                isset($row["city"]) ? $row["city"] : '',
                isset($row["zip"]) ? $row["zip"] : '',
                isset($row["floor"]) ? $row["floor"] : '',
                isset($row["summer"]) && $row["summer"] ? "Yes" : "No",
                isset($row["installcomplete"]) && $row["installcomplete"] ? "Yes" : "No",
                isset($row["installcomplete"]) && $row["installcomplete"] && isset($row["datecompleted"]) ? $row["datecompleted"] : "",
                isset($row["invoiced"]) && $row["invoiced"] ? "Yes" : "No",
                isset($row["isrecall"]) && $row["isrecall"] ? "Yes" : "No",
                isset($row["isrma"]) && $row["isrma"] ? "Yes" : "No",
                isset($row["location"]) ? $row["location"] : ''
            ));
            
            if( isset($psal) && $psal ) {
                $rowData[] = isset($row["aedstolen"]) && $row["aedstolen"] ? "Yes" : "No";
                $rowData[] = isset($row["aedmissing"]) && $row["aedmissing"] ? "Yes" : "No";
            }
            
            $rowData = array_merge($rowData, array(
                isset($row["padaexpiration"]) && $row["padaexpiration"]!="0000-00-00"?$row["padaexpiration"]:"",
                isset($row["padbexpiration"]) && $row["padbexpiration"]!="0000-00-00"?$row["padbexpiration"]:"",
                (isset($row["pediatrickey"]) && $row["pediatrickey"]) ? "key" : (isset($row["pediatricpads"]) && $row["pediatricpads"]!="0000-00-00"?$row["pediatricpads"]:""),
                isset($row["hasbeenupdated"]) && $row["hasbeenupdated"] ? "Y" : "N",
                isset($row["sparedate"]) && $row["sparedate"]!="0000-00-00"?$row["sparedate"]:"",
                isset($row["batterydate"]) && $row["batterydate"]!="0000-00-00"?$row["batterydate"]:"",
                isset($row["directorname"]) ? $row["directorname"] : '',
                isset($row["filingexpirationdate"]) && $row["filingexpirationdate"]!="0000-00-00"?$row["filingexpirationdate"]:"",
                $numdrills,
                $numservicecalls,
                isset($row["aedservicehistory"]) ? $row["aedservicehistory"] : ''
            ));
            
            $maxsc = isset($row["serial"]) ? db_query_first_cell( "select max( servicecalldate ) from servicecall s, aed_to_servicecall ats where ats.servicecallid = s.servicecallid and serial = '".mysql_escape_string( $row["serial"] )."'" ) : '';
            $maxdr = isset($row["serial"]) ? db_query_first_cell( "select max( drilldate ) from drill s, aed_to_drill ats where ats.drillid = s.drillid and serial = '".mysql_escape_string( $row["serial"] )."'" ) : '';
            $row["lastservicedate"] = $maxsc>$maxdr?$maxsc:$maxdr;
            
            $rowData[] = isset($row["lastservicedate"]) ? $row["lastservicedate"] : '';
            
            if( isset($tmptrainers[$row["zip"]]) ) {
                $tr = $tmptrainers[$row["zip"]];
            } else {
                $tr = isset($row["zip"]) ? getTrainersForZip( $row["zip"] ) : array();
                $tmptrainers[$row["zip"]] = $tr; 
            }
            
            $trainername = "";
            if( isset($tr) && is_array($tr) ) {
                foreach( $tr as $tw ) {
                    if( $trainername ) {
                        $trainername .= ", ";
                    }
                    $trainername .= isset($tw["name"]) ? $tw["name"] : '';
                }
            }
            
            $rowData[] = $trainername;
            
            $numtrainedresponders = isset($companyinfo[$row["clientid"]]) ? $companyinfo[$row["clientid"]] : null;
            if( !isset( $numtrainedresponders ) ) {
                $numtrainedresponders = isset($row["clientid"]) ? count( getCurrentResponders( $row["clientid"] ) ) : 0;
                $companyinfo[$row["clientid"]] = $numtrainedresponders;
            }
            
            $rowData[] = $numtrainedresponders;
            $rowData[] = isset($row["contactname"]) ? $row["contactname"] : '';
            $rowData[] = isset($row["contactphone"]) ? $row["contactphone"] : '';
            $rowData[] = isset($row["contactemail"]) ? $row["contactemail"] : '';
            
            $upds = isset($row["clientid"]) ? db_query_first_cell( "select startdate from class where companyid = $row[clientid] and startdate > now()  and canceldate is null order by startdate" ) : '';
            $rowData[] = $upds;
            
            if( isset($session_iscorp) && $session_iscorp ) {
                $rowData[] = isset($row["accountmanager"]) ? getUserName( $row["accountmanager"] ) : '';
            }
            
            if( (isset($missing) && $missing) || (isset($stolen) && $stolen) ) { 
                $rowData[] = isset($row["aedid"]) ? getOldSchoolsString( $row["aedid"], ";" ) : '';
                if( isset($stolen) && $stolen ) {
                    $rowData[] = isset($row["aedstolentext"]) ? $row["aedstolentext"] : '';
                }
            } 
            
            $rowData = array_merge($rowData, array(
                isset($row["aedid"]) ? $row["aedid"] : '',
                isset($row["clientid"]) ? $row["clientid"] : '',
                isset($row["outofservice"]) && $row["outofservice"] ? "Yes" : "No",
                isset($row["readytoreturn"]) && $row["readytoreturn"] ? "Yes" : "No",
                isset($row["aedcomments"]) ? $row["aedcomments"] : ''
            ));
            
            // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
            foreach($rowData as &$value) {
                if($value !== null && $value !== '') {
                    $firstChar = substr($value, 0, 1);
                    if(in_array($firstChar, array('=', '+', '-', '@'))) {
                        $value = "'" . $value;
                    }
                }
            }
            
            fputcsv($output, $rowData);
        }
    }
    
    fclose($output);
    exit();
}
else if( isset($csv) && $csv )
{
    // This section creates a static file - consider updating it too for consistency
    $header = array(
        "AED Serial Number",
        "Model/Type",
        "School Location",
        "School Name",
        "School Code",
        "Building Code",
        "Region",
        "Is Cooling Center?",
        "City",
        "Zip",
        "Floor",
        "Summer School?",
        "Complete?",
        "Date Completed",
        "Invoiced?",
        "Recalled?",
        "RMA?",
        "AED Location",
        "Pads A",
        "Pads B",
        "Pediatric Pads",
        "G 2005",
        "Spare Battery Install Before Date",
        "Battery",
        "Medical Director",
        "Municipal Filing",
        "Num Drills",
        "Num Service Calls",
        "Service History",
        "Last Service Date",
        "Trainer Name",
        "# Trained",
        "Contact Name",
        "Contact Phone",
        "Contact Email"
    );
    
    if( isset($session_iscorp) && $session_iscorp ) {
        $header[] = "Account Manager";
    }
    if( isset($stolen) && $stolen ) {
        $header[] = "Previous Schools";
        $header[] = "Police Report";
    }
    
    fputcsv($hand, $header);
    
    $ziptrainers = array();
    if( isset($result) && is_array($result) ) {
        foreach( $result as $row ) {
            $numdrills = isset($row["clientid"]) ? db_query_first_cell( "Select count(*) from drill where companyid = $row[clientid]" ) : 0;
            $numservicecalls = isset($row["clientid"]) ? db_query_first_cell( "Select count(*) from servicecall where companyid = $row[clientid]" ) : 0;
            
            $rowData = array(
                isset($row["serial"]) ? $row["serial"] : '',
                isset($row["model"]) ? $row["model"] : '',
                isset($row["address"]) ? $row["address"] : '',
                isset($row["companyname"]) ? $row["companyname"] : '',
                isset($row["schoolcode"]) ? $row["schoolcode"] : '',
                isset($row["buildingcode"]) ? $row["buildingcode"] : '',
                isset($row["region"]) ? $row["region"] : '',
                isset($row["iscoolingcenter"]) && $row["iscoolingcenter"] ? "Y" : "N",
                isset($row["city"]) ? $row["city"] : '',
                isset($row["zip"]) ? $row["zip"] : '',
                isset($row["floor"]) ? $row["floor"] : '',
                isset($row["summer"]) && $row["summer"] ? "Yes" : "No",
                isset($row["installcomplete"]) && $row["installcomplete"] ? "Yes" : "No",
                isset($row["installcomplete"]) && $row["installcomplete"] && isset($row["datecompleted"]) ? $row["datecompleted"] : "",
                isset($row["invoiced"]) && $row["invoiced"] ? "Yes" : "No",
                isset($row["isrecall"]) && $row["isrecall"] ? "Yes" : "No",
                isset($row["isrma"]) && $row["isrma"] ? "Yes" : "No",
                isset($row["location"]) ? $row["location"] : '',
                isset($row["padaexpiration"]) && $row["padaexpiration"]!="0000-00-00"?$row["padaexpiration"]:"",
                isset($row["padbexpiration"]) && $row["padbexpiration"]!="0000-00-00"?$row["padbexpiration"]:"",
                (isset($row["pediatrickey"]) && $row["pediatrickey"]) ? "key" : (isset($row["pediatricpads"]) && $row["pediatricpads"]!="0000-00-00"?$row["pediatricpads"]:""),
                isset($row["hasbeenupdated"]) && $row["hasbeenupdated"] ? "Y" : "N",
                isset($row["sparedate"]) && $row["sparedate"]!="0000-00-00"?$row["sparedate"]:"",
                isset($row["batterydate"]) && $row["batterydate"]!="0000-00-00"?$row["batterydate"]:"",
                isset($row["directorname"]) ? $row["directorname"] : '',
                isset($row["filingexpirationdate"]) && $row["filingexpirationdate"]!="0000-00-00"?$row["filingexpirationdate"]:"",
                $numdrills,
                $numservicecalls,
                isset($row["aedservicehistory"]) ? $row["aedservicehistory"] : ''
            );
            
            $maxsc = isset($row["serial"]) ? db_query_first_cell( "select max( servicecalldate ) from servicecall s, aed_to_servicecall ats where ats.servicecallid = s.servicecallid and serial = '".mysql_escape_string( $row["serial"] )."'" ) : '';
            $maxdr = isset($row["serial"]) ? db_query_first_cell( "select max( drilldate ) from drill s, aed_to_drill ats where ats.drillid = s.drillid and serial = '".mysql_escape_string( $row["serial"] )."'" ) : '';
            $row["lastservicedate"] = $maxsc>$maxdr?$maxsc:$maxdr;
            
            $rowData[] = isset($row["lastservicedate"]) ? $row["lastservicedate"] : '';
            
            $tr = isset($ziptrainers[$row["zip"]]) ? $ziptrainers[$row["zip"]] : null;
            if( !isset( $tr ) ) {
                $tr = isset($row["zip"]) ? getTrainersForZip( $row["zip"] ) : array();
                $ziptrainers[$row["zip"]] = $tr;
            }
            
            $trainername = "";
            if( isset($tr) && is_array($tr) ) {
                foreach( $tr as $tw ) {
                    if( $trainername ) {
                        $trainername .= ", ";
                    }
                    $trainername .= isset($tw["name"]) ? $tw["name"] : '';
                }
            }
            
            $rowData[] = $trainername;
            
            $numtrainedresponders = isset($companyinfo[$row["clientid"]]) ? $companyinfo[$row["clientid"]] : null;
            if( !isset( $numtrainedresponders ) ) {
                $numtrainedresponders = isset($row["clientid"]) ? count( getCurrentResponders( $row["clientid"] ) ) : 0;
                $companyinfo[$row["clientid"]] = $numtrainedresponders;
            }
            
            $rowData[] = $numtrainedresponders;
            $rowData[] = isset($row["contactname"]) ? $row["contactname"] : '';
            $rowData[] = isset($row["contactphone"]) ? $row["contactphone"] : '';
            $rowData[] = isset($row["contactemail"]) ? $row["contactemail"] : '';
            
            if( isset($session_iscorp) && $session_iscorp ) {
                $rowData[] = isset($row["accountmanager"]) ? getUserName( $row["accountmanager"]) : '';
            }
            
            if( isset($stolen) && $stolen ) {
                $rowData[] = isset($row["aedid"]) ? getOldSchoolsString( $row["aedid"], "; " ) : '';
                $rowData[] = isset($row["aedstolentext"]) ? $row["aedstolentext"] : '';
            }
            
            // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
            foreach($rowData as &$value) {
                if($value !== null && $value !== '') {
                    $firstChar = substr($value, 0, 1);
                    if(in_array($firstChar, array('=', '+', '-', '@'))) {
                        $value = "'" . $value;
                    }
                }
            }
            
            fputcsv($hand, $rowData);
        }
    }
    
    fclose( $hand );
    echo( "<a href='aedfile.csv'>here</a>" );
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
    if(isset($id) && $id) {
         $page_contents .="<tr><td colspan='4'>".getCompanyName($id)."     </td></tr> ";
     }
    $page_contents .="
    <tr>
    <td valign='top'><span class='copy'><strong>AED Serial Number</strong></span></td>
    <td valign='top'><span class='copy'><strong>Missing</strong></span></td>
    <td valign='top'><span class='copy'><strong>Warranty</strong></span></td>
    <td valign='top'><span class='copy'><strong>Model/Type</strong></span></td>
    <td valign='top'><span class='copy'><strong>School Location</strong></span></td>
    <td valign='top'><span class='copy'><strong>School Code</strong></span></td>
    <td valign='top'><span class='copy'><strong>Building Code</strong></span></td>
    <td valign='top'><span class='copy'><strong>Region</strong></span></td>
    <td valign='top'><span class='copy'><strong>Is Cooling Center</strong></span></td>
    <td valign='top'><span class='copy'><strong>City</strong></span></td>
    <td valign='top'><span class='copy'><strong>Zip</strong></span></td>
    <td valign='top'><span class='copy'><strong>Floor</strong></span></td>
    <td valign='top'><span class='copy'><strong>Summer School?</strong></span></td>
    <td valign='top'><span class='copy'><strong>Complete?</strong></span></td>
    <td valign='top'><span class='copy'><strong>Date Completed</strong></span></td>
    <td valign='top'><span class='copy'><strong>Invoiced?</strong></span></td>
    <td valign='top'><span class='copy'><strong>Recalled?</strong></span></td>
    <td valign='top'><span class='copy'><strong>RMA?</strong></span></td>
    <td valign='top'><span class='copy'><strong>AED Location</strong></span></td>
    ". (isset($psal) && $psal ? "<td valign='top'><span class='copy'><strong>Stolen?</strong></span></td>":"" )."
    ". (isset($psal) && $psal ? "<td valign='top'><span class='copy'><strong>Missing?</strong></span></td>":"" )."
    <td valign='top'><span class='copy'><strong>Pads A<br>Exp. Date</strong></span></td>
    <td valign='top'><span class='copy'><strong>Pads B<br>Exp. Date</strong></span></td>
    <td valign='top'><span class='copy'><strong>Pediatric Pads<br>Exp. Date</strong></span></td>
    <td valign='top'><span class='copy'><strong>G2005</strong></span></td>
    <td valign='top'><span class='copy'><strong>Spare Battery Install Before Date</strong></span></td>
    <td valign='top'><span class='copy'><strong>Battery<br>Installation Date</strong></span></td>
    <td valign='top'><span class='copy'><strong>Medical Director</strong></span></td>
    <td valign='top'><span class='copy'><strong>Municipal Filing<br> Exp. Date</strong></span></td>
    <td valign='top'><span class='copy'><strong>Num Drills</strong></span></td>
    <td valign='top'><span class='copy'><strong>Num Service Calls</strong></span></td>
    <td valign='top'><span class='copy'><strong>Service History</strong></span></td>
    <td valign='top'><span class='copy'><strong>Last Service Date</strong></span></td>
    <td valign='top'><span class='copy'><strong>Trainer Name</strong></span></td>
    <td valign='top'><span class='copy'><strong># Trained</strong></span></td>
    <td valign='top'><span class='copy'><strong>Contact Name</strong></span></td>
    <td valign='top'><span class='copy'><strong>Contact Phone</strong></span></td>
    <td valign='top'><span class='copy'><strong>Contact Email</strong></span></td>
    ". (isset($session_iscorp) && $session_iscorp ? "<td valign='top'><span class='copy'><strong>Account Manager</strong></span></td>":"" )."
    ". ((isset($missing) && $missing) || (isset($stolen) && $stolen) ? "<td valign='top'><span class='copy'><strong>Missing?</strong></span></td>":"" )."
    ". (isset($stolen) && $stolen ? "<td valign='top'><span class='copy'><strong>Police Report</strong></span></td>":"" )."
    </tr>
    ";
    $ziptrainers = array();
    if( isset($result) && is_array($result) ) {
        foreach( $result as $row )
        { 
            $numdrills = isset($row["clientid"]) ? db_query_first_cell( "Select count(*) from drill where companyid = $row[clientid]" ) : 0;
            $numservicecalls = isset($row["clientid"]) ? db_query_first_cell( "Select count(*) from servicecall where companyid = $row[clientid]" ) : 0;
            $tr = isset($ziptrainers[$row["zip"]]) ? $ziptrainers[$row["zip"]] : null;
            if( !isset( $tr ) )
            {
                $tr = isset($row["zip"]) ? getTrainersForZip( $row["zip"] ) : array();
                $ziptrainers[$row["zip"]] = $tr;
            }
            $trainername = "";
            if( isset($tr) && is_array($tr) ) {
                foreach( $tr as $tw )
                {
                    if( $trainername )
                        $trainername .= ", ";
                    $trainername .= isset($tw["name"]) ? $tw["name"] : '';
                }
            }
            $maxsc = isset($row["serial"]) ? db_query_first_cell( "select max( servicecalldate ) from servicecall s, aed_to_servicecall ats where ats.servicecallid = s.servicecallid and serial = '".mysql_escape_string( $row["serial"] )."'" ) : '';
            $maxdr = isset($row["serial"]) ? db_query_first_cell( "select max( drilldate ) from drill s, aed_to_drill ats where ats.drillid = s.drillid and serial = '".mysql_escape_string( $row["serial"] )."'" ) : '';
            $row["lastservicedate"] = $maxsc>$maxdr?$maxsc:$maxdr;
            $page_contents .= "
    <tr>
     <td valign='top'><span class='copy'>". ((!isset($xls) || $xls == null) && isset($thisusersrow["healthdirector"]) && !$thisusersrow["healthdirector"] ?"<a href='/viewserial.php?aedid=$row[aedid]'>":"").(isset($row["serial"]) ? $row["serial"] : '')."</a></span></td>
     <td valign='top'><span class='copy'>".(isset($row["aedmissing"]) && $row["aedmissing"] ? "Yes":"No")."</span></td>
     <td valign='top'><span class='copy'>".(isset($row["outofwarranty"]) && $row["outofwarranty"] ? "W":"")."</span></td>
     <td valign='top'><span class='copy'>".(isset($row["model"]) ? $row["model"] : '')."</span></td>
    <td valign='top'><span class='copy'>". ((!isset($xls) || $xls == null) ?"<a href='/viewcompany.php?id=$row[clientid]'>":"").(isset($row["address"]) ? $row["address"] : '')."</a><br>".(isset($row["companyname"]) ? $row["companyname"] : '')."</span></td>
     <td valign='top'><span class='copy'>".(isset($row["schoolcode"]) ? $row["schoolcode"] : '')."</span></td>
     <td valign='top'><span class='copy'>".(isset($row["buildingcode"]) ? $row["buildingcode"] : '')."</span></td>
     <td valign='top'><span class='copy'>".(isset($row["region"]) ? $row["region"] : '')."</span></td>
     <td valign='top'><span class='copy'>".(isset($row["iscoolingcenter"]) && $row["iscoolingcenter"] ? "Yes":"No")."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["city"]) ? $row["city"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["zip"]) ? $row["zip"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["floor"]) ? $row["floor"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["summer"]) && $row["summer"] ? "Yes":"No" )."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["installcomplete"]) && $row["installcomplete"] ? "Yes":"No" )."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["installcomplete"]) && $row["installcomplete"] && isset($row["datecompleted"]) ? $row["datecompleted"] : "" )."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["invoiced"]) && $row["invoiced"] ? "Yes":"No" )."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["isrecall"]) && $row["isrecall"] ? "Yes":"No" )."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["isrma"]) && $row["isrma"] ? "Yes":"No" )."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["location"]) ? $row["location"] : '')."</span></td>
    ".(isset($psal) && $psal ? "<td valign='top'><span class='copy'>".(isset($row["aedstolen"]) && $row["aedstolen"] ? "Yes":"No" )."</span></td>":"" )."
    ".(isset($psal) && $psal ? "<td valign='top'><span class='copy'>".(isset($row["aedmissing"]) && $row["aedmissing"] ? "Yes":"No" )."</span></td>":"" )."
    <td valign='top'><span class='copy'>".(isset($row["padaexpiration"]) ? $row["padaexpiration"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["padbexpiration"]) ? $row["padbexpiration"] : '')."</span></td>
    " ;
    if( isset($row["pediatrickey"]) && $row["pediatrickey"] )
    {
        $page_contents .= "
    <td valign='top'><span class='copy'>key</span></td>
    ";
    }
    else
    {
        $page_contents .= "
    <td valign='top'><span class='copy'>".(isset($row["pediatricpads"]) ? $row["pediatricpads"] : '')."</span></td>
    ";
    }
    $page_contents .= "
    <td valign='top'><span class='copy'>".(isset($row["hasbeenupdated"]) && $row["hasbeenupdated"] ? "Y":"N")."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["sparedate"]) ? $row["sparedate"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["batterydate"]) ? $row["batterydate"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["directorname"]) ? $row["directorname"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["filingexpirationdate"]) ? $row["filingexpirationdate"] : '')."</span></td>
    <td valign='top'><span class='copy'>$numdrills</span></td>
    <td valign='top'><span class='copy'>$numservicecalls</span></td>
    <td valign='top'><span class='copy'>".(isset($row["aedservicehistory"]) ? $row["aedservicehistory"] : '')."</span></td>
    <td valign='top'><span class='copy'>".(isset($row["lastservicedate"]) ? $row["lastservicedate"] : '')."</span></td>
    <td valign='top'><span class='copy'>$trainername</span></td>";
            $numtrainedresponders = isset($companyinfo[$row["clientid"]]) ? $companyinfo[$row["clientid"]] : null;
            if( !isset( $numtrainedresponders ) )
            {
                $numtrainedresponders = isset($row["clientid"]) ? count( getCurrentResponders( $row["clientid"] ) ) : 0;
                $companyinfo[$row["clientid"]] = $numtrainedresponders;
            }
            $page_contents .= ( "<td valign='top'>$numtrainedresponders</td>" );
            $page_contents .= ( "<td valign='top'>".(isset($row["contactname"]) ? $row["contactname"] : '')."</td>" );
            $page_contents .= ( "<td valign='top'>".(isset($row["contactphone"]) ? $row["contactphone"] : '')."</td>" );
            $page_contents .= ( "<td valign='top'>".(isset($row["contactemail"]) ? $row["contactemail"] : '')."</td>" );
            if( isset($session_iscorp) && $session_iscorp )
                $page_contents .= ( "<td valign='top'>".(isset($row["accountmanager"]) ? getUserName( $row["accountmanager"] ) : '')."</td>" );
            
            if( (isset($missing) && $missing) || (isset($stolen) && $stolen) ) { 
             $page_contents.="<td valign='top'><span class='copy'>".(isset($row["aedid"]) ? getOldSchoolsString( $row["aedid"] ) : '')."</td>";
         }
            if( isset($stolen) && $stolen ) { 
             $page_contents.="<td valign='top'><span class='copy'>".(isset($row["aedstolentext"]) ? $row["aedstolentext"] : '')."</td>";
         }
    $page_contents .= "
    </tr>
    ";
        }
    }
        $page_contents .= "</table>";
    echo( $page_contents );
}
?>