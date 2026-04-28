<?php
include "mysql.php";

// Assume $whr and $xls are defined externally. Defaulting to safe values if not.
$whr = $whr ?? ""; 
$xls = $xls ?? null; 

// Retrieve AED data for corporate clients
$sql = "SELECT aedid, imei, serial FROM aed_esi a, company_esi c 
        WHERE iscorp = '1' AND a.deleted = 0 AND a.clientid = c.id AND c.deleted = 0 $whr";

$result = db_query_rows( $sql );
$companyinfo = array();

if( $xls ) {
    // --- CSV Output Section with File Save ---
    
    $filename = "report_imei_" . time() . ".csv";
    $filepath = "reports/" . $filename;
    
    // Create reports directory if it doesn't exist
    if (!file_exists('reports')) {
        mkdir('reports', 0777, true);
    }
    
    // Open file for writing
    $output = fopen($filepath, 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array(
        "AED Serial Number",
        "IMEI"
    );
    fputcsv($output, $headers);

    foreach( $result as $row )
    {
        $row_data = array(
            $row['serial'] ?? '',
            $row['imei'] ?? ''
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    
    // Provide download link
    echo "<a href='$filepath'>Download CSV Report</a>";
}
else {
    // --- HTML Output Section ---
    
    $page_contents = "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<html>
<head>
    <title>AED Report</title>
    <style>
        .copy { font-family: sans-serif; font-size: 12px; }
        table { border-collapse: collapse; }
    </style>
</head>
<body bgcolor='#ffffff'>
";

    $page_contents .="<table cellpadding='3' cellspacing='0' border='1' width='100%'>";

    $page_contents .="
    <tr>
        <td valign='top'><span class='copy'><strong>AED Serial Number</strong></span></td>
        <td valign='top'><span class='copy'><strong>IMEI</strong></span></td>
    </tr>
    ";

    foreach( $result as $row )
    { 
        // Safely access and quote array keys
        $aedid = $row['aedid'] ?? null;
        $serial = htmlspecialchars($row['serial'] ?? '');
        $imei = htmlspecialchars($row['imei'] ?? '');
        
        // Conditional linking for the serial number
        $link_start = ($xls === null || $xls === false) ? "<a href='/editaed.php?aedid=" . $aedid . "'>" : "";
        $link_end = ($xls === null || $xls === false) ? "</a>" : "";

        $page_contents .= "
        <tr>
            <td valign='top'><span class='copy'>" . $link_start . $serial . $link_end . "</span></td>
            <td valign='top'><span class='copy'>$imei</span></td>
        </tr>
        ";
    }
    
    $page_contents .= "</table>";
    
    // Close HTML tags
    $page_contents .= "
</body>
</html>
";
    
    echo( $page_contents );
}
?>