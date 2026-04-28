<?php
$nologinrequired = 1;
include "mysql.php";

if( $companyid )
{   
    $ext = " and company_esi.id = $companyid";
}
else
{
    $ext = " and iscorp = $session_iscorp ";
}

// Generate CSV instead of Excel
$filename = "resmcoreport_" . time() . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// Write header row
$header = [
    "City",
    "Borough",
    "Building Code",
    "ContactPerson",
    "CPTelephone",
    "Principal Name",
    "Principal Email",
    "School Phone",
    "Floor",
    "NumberAEDs",
    "Person",
    "PersonTrained",
    "State",
    "StreetAddress",
    "Telephone",
    "Zip",
    "Serial #",
    "School Code",
    "School Name",
    "Campus?"
];

fputcsv($output, $header);

$responders = db_query_array( "select clientid, count( id ) as cnt from responders_esi r, responder_training_dates rt where r.responderid = rt.responderid and rt.trainingdate > '2017-07-01' group by clientid", "clientid", "cnt" );
$res = db_query_rows( "select aed_esi.*, company_esi.* from aed_esi, company_esi where clientid = company_esi.id and company_esi.deleted = 0 and aed_esi.deleted = 0 and aed_esi.aedstolen = 0 {$ext}" );

foreach( $res as $r )
{
    // Prepare data row - fixing unquoted array keys
    $rowData = [
        $r['city'] ?? '',
        $r['borough'] ?? '',
        $r['buildingcode'] ?? '',
        $r['contactname'] ?? '',
        $r['contactphone'] ?? '',
        $r['principalname'] ?? '',
        $r['principalemail'] ?? '',
        $r['schoolphone'] ?? '',
        $r['location'] ?? '', // Note: This is labeled "Floor" in header but uses 'location' field
        1, // NumberAEDs - hardcoded to 1
        $responders[$r['clientid']] ?? 0, // Person - number of trained responders
        "Emergency Skills", // PersonTrained - hardcoded value
        $r['state'] ?? '',
        $r['address'] ?? '',
        $r['schoolphone'] ?? '', // Telephone - same as School Phone
        $r['zip'] ?? '',
        $r['serial'] ?? '',
        $r['schoolcode'] ?? '',
        $r['companyname'] ?? '',
        $r['campusid'] ? "Y" : "N"
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