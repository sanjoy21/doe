<?php 

// --- Security Helper Function (Assumed to be globally available) ---
// This function prevents XSS by converting special characters to HTML entities.
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// -----------------------------------------------------------------
$xls = $_GET['xls'];
if( $xls )
{
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    
    // Sanitize filename to prevent header injection
    $safe_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);
    header('Content-Disposition: attachment; filename="' . $safe_filename . '.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Define headers
    $headers = array( 
        "Company", 
        "Building code", 
        "Address", 
        "City", 
        "Zip Code", 
        "Contact Info", 
        "Serial", 
        "Location", 
        "Pad A Expiration", 
        "Pad B Expiration", 
        "Pediatric Pad Expiration", 
        "Spare Date" 
    );
    
    // Write headers
    fputcsv($output, $headers);

    // Assuming $sql is securely generated upstream (no SQLi).
    $res = db_query_rows( $sql );
    foreach( $res as $r )
    {
        // Prepare row data with null safety
        $row_data = array(
            $r['companyname'] ?? '',
            $r['buildingcode'] ?? '',
            $r['address'] ?? '',
            $r['city'] ?? '',
            $r['zip'] ?? '',
            $r['contactinfo'] ?? '',
            $r['serial'] ?? '',
            $r['location'] ?? '',
            $r['padaexpiration'] ?? '',
            $r['padbexpiration'] ?? '',
            $r['pediatricpads'] ?? '',
            $r['sparedate'] ?? ''
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
else
{
    // --- SECURED HTML OUTPUT (XSS Mitigation) ---
    echo( "<table>" );
    $tmparr = array( "Company", "Building Code", "Address", "City", "Zip Code", "Contact Info", "Serial", "Location", "Pad A Expiration", "Pad B Expiration", "Pediatric Pad Expiration", "Spare Date" );
    echo( "<tr>" );
    foreach( $tmparr as $o ) { 
        // Headers don't usually contain user-controlled data, but still safe practice
        echo( "<th>". h($o)."</th>" );
    }
    echo( "</tr>" );


    // Assuming $sql is securely generated upstream (no SQLi).
    $res = db_query_rows( $sql );
    foreach( $res as $r )
    {
        echo( "<tr>" );
        
        // SECURE: Applied h() to all dynamic outputs
        echo( "<td><a target=_blank href='viewcompany.php?id=".h($r['id'])."'>". h($r['companyname']) ."</a></td>" );
        echo( "<td>". h($r['buildingcode']) ."</td>" );
        echo( "<td>". h($r['address']) ."</td>" );
        echo( "<td>". h($r['city']) ."</td>" );
        echo( "<td>". h($r['zip']) ."</td>" );
        echo( "<td>". h($r['contactinfo']) ."</td>" );
        echo( "<td>". h($r['serial']) ."</td>" );
        echo( "<td>". h($r['location']) ."</td>" );
        echo( "<td>". h($r['padaexpiration']) ."</td>" );
        echo( "<td>". h($r['padbexpiration']) ."</td>" );
        echo( "<td>". h($r['pediatricpads']) ."</td>" );
        echo( "<td>". h($r['sparedate']) ."</td>" );
        echo( "</tr>" );
    }
    echo( "</table>" );
}

?>