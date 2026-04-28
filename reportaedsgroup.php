<?php
include "mysql.php";

// Initialize variables to avoid undefined variable warnings
$where = "";
$order = "";
$id = $id ?? null;
$csv = $csv ?? null;
$xls = $xls ?? null;
$cid = $cid ?? null;

//if ($id) {$where=" and a.clientid=$id ";}
if( isset($cid) && $cid ) {
    $possids = db_query_array( "select id from company_esi where deleted =0 and campusid = '$cid' ", "id", "id" );
    $possids = join( ", ", $possids ?? [] );
} else {
    $possids = $id ?? '';
}

if (!empty($possids)) {
    $isc = db_query_first_cell( "select iscorp from company_esi where id in ( $possids )" );
    $sql= "select pediatrickey, imei, a.purchasedate, invoiced, installcomplete, c.contactphone, c.contactemail, c.contactname, c.summer,c.schoolcode,c.region,a.aedid,a.clientid,a.branchid,a.serial,a.model,a.floor,a.location,a.padaexpiration,a.padbexpiration,a.batterydate,a.sparedate,c.directorname,c.filingexpirationdate,a.eventhistory,a.date,c.id,c.companyname,c.address, c.city, c.state, c.zip ,a.pediatricpads, a.irn, c.buildingno,c.medicalinvoicedate,a.aedservicehistory from aed_esi a, company_esi c where iscorp = '$isc' and a.deleted=0 and a.clientid = c.id and c.id in ( $possids ) and c.deleted = 0 and aedmissing = 0 and aedstolen = 0 and aedinactive = 0 and outofservice = 0 $where $order";
} else {
    $sql= "select pediatrickey, imei, a.purchasedate, invoiced, installcomplete, c.contactphone, c.contactemail, c.contactname, c.summer,c.schoolcode,c.region,a.aedid,a.clientid,a.branchid,a.serial,a.model,a.floor,a.location,a.padaexpiration,a.padbexpiration,a.batterydate,a.sparedate,c.directorname,c.filingexpirationdate,a.eventhistory,a.date,c.id,c.companyname,c.address, c.city, c.state, c.zip ,a.pediatricpads, a.irn, c.buildingno,c.medicalinvoicedate,a.aedservicehistory from aed_esi a, company_esi c where iscorp = '$isc' and a.deleted=0 and a.clientid = c.id and c.deleted = 0 and aedmissing = 0 and aedstolen = 0 and aedinactive = 0 and outofservice = 0 $where $order";
}

$result = db_query_rows( $sql );
$currentlytrained = array();
$companyinfo = array();

if( isset($xls) && $xls ) {
    // CSV Download (replacing Excel)
    $filename = "report_aeds_" . time() . ".csv";
    
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
        "Model/Type",
        "Company Name",
        "Building ID",
        "Region",
        "Address",
        "City",
        "State",
        "Zip",
        "Floor",
        "AED Location",
        "Pads A Exp. Date",
        "Pads B Exp. Date",
        "Pediatric Pads",
        "Spare Battery Install Before Date",
        "Battery Installation Date",
        "Internal Reference #",
        "IMEI",
        "Medical Director",
        "Municipal Filing Exp. Date",
        "Num Service Calls",
        "Service History",
        "# Trained Responders",
        "Contact Name",
        "Contact Phone",
        "Contact Email",
        "Purchase Date"
    ];
    
    fputcsv($output, $header);
    
    foreach( $result as $row )
    {
        $numdrills = db_query_first_cell( "Select count(*) from drill where companyid = " . ($row['clientid'] ?? 0) );
        $numservicecalls = db_query_first_cell( "Select count(*) from servicecall where companyid = " . ($row['clientid'] ?? 0) );

        // Format dates (handle '0000-00-00' null dates)
        $padaexpiration = ($row['padaexpiration'] ?? '') != "0000-00-00" ? ($row['padaexpiration'] ?? '') : "";
        $padbexpiration = ($row['padbexpiration'] ?? '') != "0000-00-00" ? ($row['padbexpiration'] ?? '') : "";
        $pediatricpads = isset($row["pediatrickey"]) && $row["pediatrickey"] ? "key" : (($row['pediatricpads'] ?? '') != "0000-00-00" ? ($row['pediatricpads'] ?? '') : "");
        $sparedate = ($row['sparedate'] ?? '') != "0000-00-00" ? ($row['sparedate'] ?? '') : "";
        $batterydate = ($row['batterydate'] ?? '') != "0000-00-00" ? ($row['batterydate'] ?? '') : "";
        $filingexpirationdate = ($row['filingexpirationdate'] ?? '') != "0000-00-00" ? ($row['filingexpirationdate'] ?? '') : "";
        
        $numtrainedresponders = $companyinfo[$row['clientid']] ?? null;
        if( !isset( $numtrainedresponders ) )
        {
            $numtrainedresponders = count( getCurrentResponders( $row['clientid'] ) );
            $companyinfo[$row['clientid']] = $numtrainedresponders;
        }
        
        // Prepare data row
        $rowData = [
            $row['serial'] ?? '',
            $row['model'] ?? '',
            $row['companyname'] ?? '',
            $row['buildingno'] ?? '',
            $row['region'] ?? '',
            $row['address'] ?? '',
            $row['city'] ?? '',
            $row['state'] ?? '',
            $row['zip'] ?? '',
            $row['floor'] ?? '',
            $row['location'] ?? '',
            $padaexpiration,
            $padbexpiration,
            $pediatricpads,
            $sparedate,
            $batterydate,
            $row['irn'] ?? '',
            $row['imei'] ?? '',
            $row['directorname'] ?? '',
            $filingexpirationdate,
            $numservicecalls ?? 0,
            $row['aedservicehistory'] ?? '',
            $numtrainedresponders,
            $row['contactname'] ?? '',
            $row['contactphone'] ?? '',
            $row['contactemail'] ?? '',
            $row['purchasedate'] ?? ''
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
    
    fclose($output);
    exit();
}
else if( isset($csv) && $csv )
{
    // Static CSV file creation (existing functionality)
    $hand = fopen( "aedfile.csv", "w+" );
    
    // Write CSV headers
    fwrite( $hand,  "\"AED Serial Number\",\"Model/Type\",\"Company Name\",\"Building ID\",\"Region\",\"Address\",\"City\",\"State\",\"Zip\",\"Floor\",\"AED Location\",\"Pads A\",\"Pads B\",\"Pediatric Pads\",\"Spare Battery Intall Before Date\",\"Battery\",\"Internal Reference #\",\"IMEI\",\"Medical Director\",\"Municipal Filing\",\"Num Service Calls\",\"Service History\",\"# Trained Responders\",\"Contact Name\",\"Contact Phone\",\"Contact Email\",\"Purchase Date\"\n" );
    
    foreach( $result as $row )
    {
        $numdrills = db_query_first_cell( "Select count(*) from drill where companyid = " . ($row['clientid'] ?? 0) );
        $numservicecalls = db_query_first_cell( "Select count(*) from servicecall where companyid = " . ($row['clientid'] ?? 0) );

        $numtrainedresponders = $companyinfo[$row['clientid']] ?? null;
        if( !isset( $numtrainedresponders ) )
        {
            $numtrainedresponders = count( getCurrentResponders( $row['clientid'] ) );
            $companyinfo[$row['clientid']] = $numtrainedresponders;
        }

        // Build CSV row
        $csvRow = array(
            $row['serial'] ?? '',
            $row['model'] ?? '',
            $row['companyname'] ?? '',
            $row['buildingno'] ?? '',
            $row['region'] ?? '',
            $row['address'] ?? '',
            $row['city'] ?? '',
            $row['state'] ?? '',
            $row['zip'] ?? '',
            $row['floor'] ?? '',
            $row['location'] ?? '',
            (($row['padaexpiration'] ?? '') != "0000-00-00" ? $row['padaexpiration'] : ""),
            (($row['padbexpiration'] ?? '') != "0000-00-00" ? $row['padbexpiration'] : ""),
            (isset($row["pediatrickey"]) && $row["pediatrickey"]) ? "key" : (($row['pediatricpads'] ?? '') != "0000-00-00" ? $row['pediatricpads'] : ""),
            (($row['sparedate'] ?? '') != "0000-00-00" ? $row['sparedate'] : ""),
            (($row['batterydate'] ?? '') != "0000-00-00" ? $row['batterydate'] : ""),
            $row['irn'] ?? '',
            $row['imei'] ?? '',
            $row['directorname'] ?? '',
            (($row['filingexpirationdate'] ?? '') != "0000-00-00" ? $row['filingexpirationdate'] : ""),
            $numservicecalls ?? 0,
            $row['aedservicehistory'] ?? '',
            $numtrainedresponders,
            $row['contactname'] ?? '',
            $row['contactphone'] ?? '',
            $row['contactemail'] ?? '',
            $row['purchasedate'] ?? ''
        );

        // Escape fields for CSV
        foreach ($csvRow as $key => $value) {
            $csvRow[$key] = '"' . str_replace('"', '""', $value) . '"';
        }

        fwrite($hand, implode(',', $csvRow) . "\n");
    }
    
    fclose( $hand );
    echo( "<a href='aedfile.csv'>Download CSV Report</a>" );
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
        $page_contents .="<tr><td colspan='4'>".getCompanyName($id)."</td></tr>";
    }

    $page_contents .="
    <tr>
        <td valign='top'><span class='copy'><strong>AED Serial Number</strong></span></td>
        <td valign='top'><span class='copy'><strong>Model/Type</strong></span></td>
        <td valign='top'><span class='copy'><strong>Company Name</strong></span></td>
        <td valign='top'><span class='copy'><strong>Building ID</strong></span></td>
        <td valign='top'><span class='copy'><strong>Region</strong></span></td>
        <td valign='top'><span class='copy'><strong>Address</strong></span></td>
        <td valign='top'><span class='copy'><strong>City</strong></span></td>
        <td valign='top'><span class='copy'><strong>State</strong></span></td>
        <td valign='top'><span class='copy'><strong>Zip</strong></span></td>
        <td valign='top'><span class='copy'><strong>Floor</strong></span></td>
        <td valign='top'><span class='copy'><strong>AED Location</strong></span></td>
        <td valign='top'><span class='copy'><strong>Pads A<br>Exp. Date</strong></span></td>
        <td valign='top'><span class='copy'><strong>Pads B<br>Exp. Date</strong></span></td>
        <td valign='top'><span class='copy'><strong>Pediatric Pads<br>Exp. Date</strong></span></td>
        <td valign='top'><span class='copy'><strong>Spare Battery Intall Before Date</strong></span></td>
        <td valign='top'><span class='copy'><strong>Battery<br>Intallation Date</strong></span></td>
        <td valign='top'><span class='copy'><strong>Internal Reference #</strong></span></td>		
        <td valign='top'><span class='copy'><strong>IMEI</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Medical Director</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Municipal Filing<br> Exp. Date</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Num Service Calls</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Service History</strong></span></td>		
        <td valign='top'><span class='copy'><strong># Trained</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Contact Name</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Contact Phone</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Contact Email</strong></span></td>		
        <td valign='top'><span class='copy'><strong>Purchase Date</strong></span></td>		
    </tr>
";

    foreach( $result as $row )
    { 
        $numdrills = db_query_first_cell( "Select count(*) from drill where companyid = " . ($row['clientid'] ?? 0) );
        $numservicecalls = db_query_first_cell( "Select count(*) from servicecall where companyid = " . ($row['clientid'] ?? 0) );
        
        $numtrainedresponders = $companyinfo[$row['clientid']] ?? null;
        if( !isset( $numtrainedresponders ) )
        {
            $numtrainedresponders = count( getCurrentResponders( $row['clientid'] ) );
            $companyinfo[$row['clientid']] = $numtrainedresponders;
        }

        $page_contents .= "
    <tr>
        <td valign='top'><span class='copy'>". ((!isset($xls) || $xls == null)?"<a href='/viewserial.php?aedid=" . ($row['aedid'] ?? '') . "'>":"").($row['serial'] ?? '')."</a></span></td>
        <td valign='top'><span class='copy'>".($row['model'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['companyname'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['buildingno'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['region'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['address'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['city'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['state'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['zip'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['floor'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['location'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['padaexpiration'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['padbexpiration'] ?? '')."</span></td>
";
        if( isset($row["pediatrickey"]) && $row["pediatrickey"] )
        {
            $page_contents .= "
        <td valign='top'><span class='copy'>key</span></td>
";
        }
        else
        {
            $page_contents .= "
        <td valign='top'><span class='copy'>".($row['pediatricpads'] ?? '')."</span></td>
";
        }
        $page_contents .= "
        <td valign='top'><span class='copy'>".($row['sparedate'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['batterydate'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['irn'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['imei'] ?? '')."</span></td>
        <td valign='top'><span class='copy'>".($row['directorname'] ?? '')."</span></td>		
        <td valign='top'><span class='copy'>".($row['filingexpirationdate'] ?? '')."</span></td>		
        <td valign='top'><span class='copy'>".($numservicecalls ?? 0)."</span></td>		
        <td valign='top'><span class='copy'>".($row['aedservicehistory'] ?? '')."</span></td>
        <td valign='top'>".($numtrainedresponders ?? 0)."</td>
        <td valign='top'>".($row['contactname'] ?? '')."</td>
        <td valign='top'>".($row['contactphone'] ?? '')."</td>
        <td valign='top'>".($row['contactemail'] ?? '')."</td>
        <td valign='top'>".($row['purchasedate'] ?? '')."</td>
    </tr>" ;
    }
    
    $page_contents .= "</table>";
    echo( $page_contents );
}
?>