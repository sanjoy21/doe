<?php

require_once('mysql.php');

$rids = db_query_rows( " select * from company_esi where deleted = 0 and iscorp = 1" );

// Generate CSV instead of Excel
$filename = "programsummaryreport_" . time() . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// Write Headers
$header = [
    "Company",
    "Company Link",
    "Contact Name",
    "Street Address",
    "Floor/Room",
    "City",
    "State",
    "Zip",
    "Contact Phone",
    "Contact Email",
    "Last Training Date",
    "Last Training Type",
    "Next Scheduled Date",
    "Next Scheduled Type",
    "Class History Link",
    "AHA Exp Date",
    "Muni Filing Exp Date", // Fixed typo from original: "Muni Filing Exp Dat"
    "Medical Director Name",
    "Medical Director Invoice Date"
];

fputcsv($output, $header);

foreach( $rids as $trow )
{
    // Core data fields
    $company_id = $trow['id'];
    $is_corp = $trow['iscorp'];
    
    // Fetch Last Training (Order by DESC for latest)
    $prevrow = db_query_first( "select * from class where companyid = {$company_id} and accepted = 1 and startdate < now() order by startdate desc limit 1" );
    // Fetch Next Training (Order by ASC for earliest upcoming date)
    $nextrow = db_query_first( "select * from class where companyid = {$company_id} and accepted = 1 and startdate > now() order by startdate asc limit 1" );
    
    $prevdate = $prevrow["startdate"] ?? '';
    $prevtype = $prevrow["code"] ?? '';
    
    $nextdate = $nextrow["startdate"] ?? '';
    $nexttype = $nextrow["code"] ?? '';
    
    // Format dates
    $lastTrainingDate = $prevdate ? getFormattedDateWTime( $prevdate ) : "";
    $nextScheduledDate = $nextdate ? getFormattedDateWTime( $nextdate ) : "";
    
    // Get training type names
    $lastTrainingType = $prevtype ? ($allclass_names[$is_corp][$prevtype] ?? '') : "";
    $nextScheduledType = $nexttype ? ($allclass_names[$is_corp][$nexttype] ?? '') : "";
    
    // AHA Expiration Date (2 years after last training)
    $twoyears = $prevdate ? date( "Y-m-d", strtotime( $prevdate . " + 2 years" ) ) : "";
    
    // Prepare data row
    $rowData = [
        $trow["companyname"] ?? '',
        "http://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/viewcompany.php?id={$company_id}",
        $trow['contactname'] ?? '',
        $trow['address'] ?? '',
        $trow['floor'] ?? '',
        $trow['city'] ?? '',
        $trow['state'] ?? '',
        $trow['zip'] ?? '',
        $trow['contactphone'] ?? '',
        $trow['contactemail'] ?? '',
        $lastTrainingDate,
        $lastTrainingType,
        $nextScheduledDate,
        $nextScheduledType,
        "http://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/classhistory.php?id={$company_id}",
        $twoyears,
        getFormattedDate($trow['filingexpirationdate'] ?? ''),
        $trow['directorname'] ?? '',
        getFormattedDate($trow['medicalinvoicedate'] ?? '')
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

?>