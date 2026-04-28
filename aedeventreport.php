<?php
include "mysql.php";

if(isset($csv) && $csv)
{
    $hand = fopen("aedfile.csv", "w+");
}

$where = " and ae.eventdate > '2010-01-01'";

$session_iscorp = isset($session_iscorp) ? $session_iscorp : 0;
$sql = "select ae.eventdate, ae.dateeventreported, eventhistory, pediatrickey, outofservice, readytoreturn, aedcomments, aedstolentext, c.accountmanager, a.isrecall,c.contactname, c.contactphone, c.contactemail,aedmissing, aedstolen,outofwarranty, datecompleted, invoiced, installcomplete, c.summer,c.schoolcode,c.region,a.aedid,a.clientid,a.branchid,a.serial,a.model,a.floor,a.location,a.padaexpiration,a.padbexpiration,a.batterydate,a.sparedate,c.directorname,c.filingexpirationdate,a.eventhistory,a.date,c.id,c.companyname,c.address, c.city, c.zip ,a.pediatricpads, a.irn, c.companyname,c.medicalinvoicedate,a.aedservicehistory, a.hasbeenupdated from aed_esi a, company_esi c, aedevents ae where iscorp = '" . $session_iscorp . "' and a.deleted=0 and a.clientid=c.id and c.deleted = 0 and c.excludereporting = 0 and ae.serial = a.serial $where order by ae.eventdate desc";

$result = db_query_rows($sql);

// echo htmlspecialchars($sql);

$companyinfo = array();

if(isset($xls) && $xls) 
{
    // Generate CSV instead of Excel
    $filename = "report_aeds_events_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = [
        "AED Serial Number",
        "Event Date",
        "Date Event Reported",
        "School/Company Name",
        "School/Company Code",
        "Address",
        "City",
        "Zip",
        "Event History"
    ];
    
    fputcsv($output, $header);
    
    if(isset($result) && is_array($result))
    {
        foreach($result as $row)
        {
            // Prepare data row
            $rowData = [
                $row['serial'] ?? '',
                $row['eventdate'] ?? '',
                $row['dateeventreported'] ?? '',
                $row['companyname'] ?? '',
                $row['schoolcode'] ?? '',
                $row['address'] ?? '',
                $row['city'] ?? '',
                $row['zip'] ?? '',
                $row['eventhistory'] ?? ''
            ];
            
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

    $page_contents .= "<table cellpadding='3' cellspacing='0' border='1' width='100%'>";

    if(isset($id) && $id) 
    {
        $page_contents .= "<tr><td colspan='9'>" . getCompanyName($id) . "</td></tr>";
    }

    $page_contents .= "
    <tr>
        <td valign='top'><span class='copy'><strong>AED Serial Number</strong></span></td>
        <td valign='top'><span class='copy'><strong>Event Date</strong></span></td>        
        <td valign='top'><span class='copy'><strong>Date Event Reported</strong></span></td>        
        <td valign='top'><span class='copy'><strong>School/Company Name</strong></span></td>
        <td valign='top'><span class='copy'><strong>School Code</strong></span></td>
        <td valign='top'><span class='copy'><strong>Address</strong></span></td>
        <td valign='top'><span class='copy'><strong>City</strong></span></td>
        <td valign='top'><span class='copy'><strong>Zip</strong></span></td>
        <td valign='top'><span class='copy'><strong>Event History</strong></span></td>
    </tr>
";

    if(isset($result) && is_array($result))
    {
        foreach($result as $row)
        { 
            $serial = isset($row['serial']) ? $row['serial'] : '';
            $eventdate = isset($row['eventdate']) ? $row['eventdate'] : '';
            $dateeventreported = isset($row['dateeventreported']) ? $row['dateeventreported'] : '';
            $address = isset($row['address']) ? htmlspecialchars($row['address']) : '';
            $companyname = isset($row['companyname']) ? htmlspecialchars($row['companyname']) : '';
            $clientid = isset($row['clientid']) ? $row['clientid'] : '';
            $schoolcode = isset($row['schoolcode']) ? htmlspecialchars($row['schoolcode']) : '';
            $city = isset($row['city']) ? htmlspecialchars($row['city']) : '';
            $zip = isset($row['zip']) ? htmlspecialchars($row['zip']) : '';
            $eventhistory = isset($row['eventhistory']) ? htmlspecialchars($row['eventhistory']) : '';
            $aedid = isset($row['aedid']) ? $row['aedid'] : '';
            
            $thisusersrow = isset($thisusersrow) ? $thisusersrow : array();
            $healthdirector = isset($thisusersrow['healthdirector']) ? $thisusersrow['healthdirector'] : 0;
            
            $serialLink = $serial;
            if(!isset($xls) && !$healthdirector)
            {
                $serialLink = "<a href='/viewserial.php?aedid=" . htmlspecialchars($aedid) . "'>" . htmlspecialchars($serial) . "</a>";
            }
            
            $companyLink = $companyname;
            if(!isset($xls))
            {
                $companyLink = "<a href='/viewcompany.php?id=" . htmlspecialchars($clientid) . "'>" . htmlspecialchars($companyname) . "</a>";
            }
            
            $page_contents .= "
    <tr>
        <td valign='top'><span class='copy'>" . $serialLink . "</span></td>
        <td valign='top'><span class='copy'>" . htmlspecialchars($eventdate) . "</span></td>
        <td valign='top'><span class='copy'>" . htmlspecialchars($dateeventreported) . "</span></td>
        <td valign='top'><span class='copy'>" . $companyLink . "<br>" . htmlspecialchars($address) . "</span></td>
        <td valign='top'><span class='copy'>" . $schoolcode . "</span></td>
        <td valign='top'><span class='copy'>" . $address . "</span></td>
        <td valign='top'><span class='copy'>" . $city . "</span></td>
        <td valign='top'><span class='copy'>" . $zip . "</span></td>
        <td valign='top'><span class='copy'>" . $eventhistory . "</span></td>
    </tr>
";
        }
    }
    
    $page_contents .= "</table></body></html>";
    echo $page_contents;
}
?>