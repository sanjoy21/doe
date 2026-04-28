<?php 
include "mysql.php";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="responderexport.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Write Header Row
$headers = array(
    "Responder Name",
    "Responder Recert Date (Max Training Date + 2 Years)",
    "School",
    "Code",
    "Address"
);
fputcsv($output, $headers);

// Query Target Companies
$cs = db_query_rows(" SELECT * FROM company_esi c WHERE companyname LIKE '%Success%' AND c.deleted = 0 " );

// Process Companies and Responders
foreach( $cs as $crow )
{
    $company_id = $crow['id'] ?? null;
    $resps = getNonExpiredResponders( $company_id );
    
    if( count( $resps ) )
    {
        foreach( $resps as $arow )
        {
            $responder_id = $arow['responderid'] ?? 0;

            $lt = db_query_first_cell( "SELECT max( trainingdate ) FROM responder_training_dates WHERE responderid = " . (int)$responder_id );
            
            // Data Preparation
            $responder_name = trim(($arow['firstname'] ?? '') . " " . ($arow['lastname'] ?? ''));
            $recert_date = '';
            if ($lt) {
                $recert_date = date( "Y-m-d", strtotime( "$lt + 2 years" ) );
            }

            // Prepare row data for responder
            $row_data = array(
                $responder_name,
                $recert_date,
                $crow['companyname'] ?? '',
                $crow['schoolcode'] ?? '',
                $crow['address'] ?? ''
            );
            
            fputcsv($output, $row_data);
        }
    }
    else
    {
        // Write a row with company details but empty responder fields if no responders are found
        $row_data = array(
            '', // Responder Name
            '', // Recert Date
            $crow['companyname'] ?? '',
            $crow['schoolcode'] ?? '',
            $crow['address'] ?? ''
        );
        
        fputcsv($output, $row_data);
    }
}

fclose($output);
exit;
?>