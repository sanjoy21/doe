<?php
include "mysql.php";

// Assume $link is the mysqli connection object from 'mysql.php'
global $link; 

// --- Security Helper Functions ---

// 1. HTML Escape for XSS prevention
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// 2. SQL Escape for SQL Injection prevention
if (!function_exists('s')) {
    function s($str) {
        global $link;
        return $link->real_escape_string((string)($str ?? ''));
    }
}
// ---------------------------------

// Capture user input flags
$xls = isset($_REQUEST['xls']);
$minnum = isset($_REQUEST['minnum']) ? (int)$_REQUEST['minnum'] : 0; // Cast to integer for safety

// Initial query for buildings (no user input used here)
$buildings = db_query_array( "select buildingcode, count(*) as cn from location_to_building, company_esi c where c.locationcode = location_to_building.locationcode group by buildingcode having cn = 1", "buildingcode", "buildingcode" );

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
    
    // Write Header Row
    $headers = array(
        "Building Code",
        "School Code",
        "Serial",
        "Num Stolen",
        "Num Missing"
    );
    fputcsv($output, $headers);
    
    foreach( $buildings as $b )
    {
        // SQLi REMEDIATION: Escape $b before use
        $safe_b = s($b);
        $schools = db_query_rows( "select c.* from company_esi c, location_to_building l where l.locationcode = c.locationcode and buildingcode = '$safe_b' and deleted = 0 and iscorp = 0" );

        foreach( $schools as $sid=>$crow )
        {
            // SQLi REMEDIATION: Escape $crow['id'] before use (cast to int for safety)
            $safe_client_id = (int)($crow['id'] ?? 0);

            $resps = db_query_rows( "select * from aed_esi where clientid = '$safe_client_id' and deleted = 0 and aedmissing = 0 and aedstolen = 0 " );
            if( count( $resps ) > 1 ) continue; 
            
            foreach( $resps as $rrow )
            {
                // Get counts with proper escaping
                $numstolen = db_query_first_cell( "select count(*) from aed_esi where clientid = '$safe_client_id' and deleted = 0 and aedstolen = 1 " );
                $nummissing = db_query_first_cell( "select count(*) from aed_esi where clientid = '$safe_client_id' and deleted = 0 and aedmissing = 1 " );
                
                // Prepare row data with null safety
                $row_data = array(
                    $b,
                    $crow['schoolcode'] ?? '',
                    $rrow['serial'] ?? '',
                    $numstolen ?? 0,
                    $nummissing ?? 0
                );
                
                fputcsv($output, $row_data);
            }
        }
    }
    
    fclose($output);
    exit;
}
else
{
    // --- HTML Table Generation ---
    $i = 1;
    echo( "<table border=1>" );
    
    echo( "<tr><td>Building Code</td><td>School Code</td><td>Serial</td>" );
    echo( "<td>Num Stolen</td>" );
    echo( "<td>Num Missing</td>" );
    echo( "</tr>" );

    foreach( $buildings as $b )
    {
        // SQLi REMEDIATION: Escape $b before use
        $safe_b = s($b);
        $schools = db_query_rows( "select c.* from company_esi c, location_to_building l where l.locationcode = c.locationcode and buildingcode = '$safe_b' and deleted = 0 and iscorp = 0" );

        foreach( $schools as $sid=>$crow )
        {
            // SQLi REMEDIATION: Escape $crow['id'] before use
            $safe_client_id = s($crow['id']);

            $resps = db_query_rows( "select * from aed_esi where clientid = '$safe_client_id' and deleted = 0 and aedmissing = 0 and aedstolen = 0 " );
            if( count( $resps ) > 1 ) continue; 
            
            foreach( $resps as $rrow )
            {
                $rownum++;
                $colnum = 0;
                
                // SQLi REMEDIATION: Escape $crow['id'] before use (for final counts)
                $numstolen = db_query_first_cell( "select count(*) from aed_esi where clientid = '$safe_client_id' and deleted = 0 and aedstolen = 1 " );
                $nummissing = db_query_first_cell( "select count(*) from aed_esi where clientid = '$safe_client_id' and deleted = 0 and aedmissing = 1 " );

                echo( "<tr>" );
                // XSS REMEDIATION: Escape all database output for HTML display
                echo( "<td>". h($b) . "</td>" );
                echo( "<td>". h($crow['schoolcode']) . "</td>" );
                echo( "<td>". h($rrow['serial']) . "</td>" );
                echo( "<td>". h($numstolen) . "</td>" );
                echo( "<td>". h($nummissing) . "</td>" );
                echo( "</tr>" );
            }
        }
    }
    echo( "</table>" );
}
?>