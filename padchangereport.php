<?php

include "mysql.php";

// Use null coalescing for safer access to variables that might not be set
$csv = $csv ?? false;
$xls = $xls ?? false;
$id = $id ?? null;
$thisusersrow = $thisusersrow ?? ["healthdirector" => 0];

if( $csv )
{
    $hand = fopen( "aedfile.csv", "w+" );
}

// Set default WHERE clause
$where = " and movedate > '2022-02-01'";

// Construct the SQL query. Assuming $id might be passed to filter by company.
$sql = "select serial, o.* from oldaeddates o, aed_esi a, company_esi c where c.id = a.clientid and o.aedid = a.aedid and iscorp = 0 $where";

$result = db_query_rows( $sql );

if( $xls ) {
    // Set headers for CSV download
    $fname = "report_aeds_" . time() . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array(
        "AED ID",
        "AED Serial Number",
        "Date",
        "Type"
    );
    fputcsv($output, $headers);
    
    $tmptrainers = array();
    foreach( $result as $row )
    {
        // Get serial number with null safety
        $serial = db_query_first_cell( "select serial from aed_esi where aedid = " . (int)($row['aedid'] ?? 0) ) ?? '';
        
        // Prepare row data with null safety
        $row_data = array(
            $row['aedid'] ?? '',
            $serial,
            $row['movedate'] ?? '',
            $row['type'] ?? ''
        );
        
        fputcsv($output, $row_data);
        
        // flush() is used for progress indication in long processes
        flush();
    }
    
    fclose($output);
    exit;
}
else
{
    // HTML output remains unchanged
    $page_contents = "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<html>
<head>
    <title>AED Report</title>
</head>
<body bgcolor='#ffffff'>
";

    $page_contents .="<table cellpadding='3' cellspacing='0' border='1' width='100%'>";

    if($id) {
        // Assumes getCompanyName($id) is available
        $page_contents .=" <tr><td colspan='4'>".getCompanyName($id)." </td></tr> ";
    }

    $page_contents .="
    <tr>
        <td valign='top'><span class='copy'><strong>AED ID</strong></span></td>
        <td valign='top'><span class='copy'><strong>AED Serial Number</strong></span></td>
        <td valign='top'><span class='copy'><strong>Date</strong></span></td>     
        <td valign='top'><span class='copy'><strong>Type</strong></span></td>     
    </tr>
";

    // Check if not Excel export and not health director
    $is_not_excel_and_not_healthdirector = !$xls && !($thisusersrow["healthdirector"] ?? 0);

    foreach( $result as $row )
    { 
        // Get serial number with null safety
        $serial = db_query_first_cell( "select serial from aed_esi where aedid = " . (int)($row['aedid'] ?? 0) ) ?? '';
        
        // Construct the link part based on conditional logic
        $link_start = $is_not_excel_and_not_healthdirector ? "<a href='/viewserial.php?aedid=" . ($row['aedid'] ?? '') . "'>" : "";
        $link_end = $is_not_excel_and_not_healthdirector ? "</a>" : "";

        $page_contents .= "
    <tr>
        <td valign='top'>" . ($row['aedid'] ?? '') . "</td>
        <td valign='top'><span class='copy'>" . $link_start . $serial . $link_end . "</span></td>
        <td valign='top'><span class='copy'>" . ($row['movedate'] ?? '') . "</span></td>
        <td valign='top'><span class='copy'>" . ($row['type'] ?? '') . "</span></td>
</tr>
";
    }
    
    $page_contents .= "</table>";
    echo( $page_contents );
}
?>