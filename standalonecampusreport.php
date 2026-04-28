<?php

include "standalonecampusreportwithlinks.php";
exit;

include "mysql.php";

// --- Security Helper Functions ---
// Helper for HTML escaping (XSS mitigation)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// ---------------------------------

// The query for $buildings is safe as it is hardcoded and returns numeric IDs.
$buildings = db_query_array( "select campusid from company_esi c where companyname not like '%charter%' and schoolcode not like '84-%' and campusid > 0 and iscorp = 0 and deleted = 0", "campusid", "campusid" );

// Sanitize $xls input
$xls = $_GET['xls'] ?? false;


if( $xls )
{
    // Set headers for CSV download
    $filename = "expired" . time() . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array(
        "Campus ID",
        "Campus Name"
    );
    fputcsv($output, $headers);
    
    foreach( $buildings as $b_unsafe )
    {
        // SQLi Mitigation: Explicitly cast $b to an integer before use in queries
        $b = (int)$b_unsafe;
        
        // SQLi Mitigation: Use cast integer in the query
        $num = db_query_first_cell( "select count(*) from responders_esi r, responder_training_dates rtd, company_esi c where r.responderid = rtd.responderid and clientid = c.id and rtd.trainingdate > '2018-12-01' and campusid = {$b}" );
        
        // SQLi Mitigation: Use cast integer in the query
        $name = db_query_first_cell( "select name from campus where id = {$b}" );
        
        if( !$name ) continue;
        if( $num ) continue; 
        
        // Get campus name again for the output (or use the already fetched $name)
        $campus_name = $name;
        
        // Prepare row data
        $row_data = array(
            (string)$b,
            $campus_name ?? ''
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
else // HTML output
{
    $i = 1;
    echo( "<table border=1>" );
    
    // Sanitize $minnum input just in case it were used
    $minnum = (int)($_GET['minnum'] ?? 0);
    
    echo( "<tr><td></td><td>Campus ID</td><td>Campus Name</td>" );
    echo( "</tr>" );

    foreach( $buildings as $b_unsafe )
    {
        // SQLi Mitigation: Explicitly cast $b to an integer before use in queries
        $b = (int)$b_unsafe;
        
        // SQLi Mitigation: Use cast integer in the query
        $num = db_query_first_cell( "select count(*) from responders_esi r, responder_training_dates rtd, company_esi c where r.responderid = rtd.responderid and clientid = c.id and rtd.trainingdate > '2018-12-01' and campusid = {$b}" );
        
        // SQLi Mitigation: Use cast integer in the query
        $name = db_query_first_cell( "select name from campus where id = {$b}" );
        
        if( !$name ) continue;
        if( $num ) continue; 
        
        $rownum++;
        $colnum = 0;
        
        echo( "<tr>" );
        // XSS Mitigation: Escaping all output
        echo( "<td>". ($i++) . "</td>" );
        echo( "<td>". h($b) . "</td>" );
        echo( "<td>" . h($name) . "</td>" );
        echo( "</tr>" );
        
    }

    echo( "</table>" );
} 
?>