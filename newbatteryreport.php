<?php 
include "mysql.php"; 

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="replacementdates.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// --- Write Header Row ---
$headers = array(
    "school name",
    "serial",
    "spare battery date",
    "date changed"
);
fputcsv($output, $headers);

// --- Fetch Data ---
$sql = "SELECT companyname, movedate, aed_esi.*, oldaeddates.aedid, oldaeddates.type 
        FROM oldaeddates, aed_esi, company_esi 
        WHERE oldaeddates.type = 'sparedate' 
        AND aed_esi.aedid = oldaeddates.aedid 
        AND clientid = company_esi.id 
        AND iscorp = 0 
        ORDER BY movedate DESC";

$res = db_query_rows( $sql );

$already = array();

// --- Process and Write Data Rows ---
foreach( $res as $r )
{
    // Use null safety for array accesses
    $serial = $r['serial'] ?? '';
    $aedid = $r['aedid'] ?? null;
    $companyname = $r['companyname'] ?? '';
    $sparedate = $r['sparedate'] ?? '';
    $movedate = $r['movedate'] ?? '';
    
    // Skip if serial is empty
    if( !trim( $serial) ) continue;
    
    // Skip if this AED ID has already been processed (deduplication logic)
    if( isset($already[$aedid]) ) continue;
    
    $already[$aedid] = 1;
    
    // Prepare row data
    $row_data = array(
        $companyname,
        $serial,
        $sparedate,
        $movedate
    );
    
    fputcsv($output, $row_data);
}

// --- Close and Send File ---
fclose($output);
exit;
?>