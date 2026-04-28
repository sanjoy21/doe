<?php 
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
include "mysql.php";

// Safely retrieve assumed global/external variables
$xls = $_REQUEST['xls'] ?? null; 
// $session_iscorp = $session_iscorp ?? 0; 
// $minnum = $minnum ?? 0;

// Sanitize $session_iscorp for the query
$session_iscorp_safe = $session_iscorp;

// --- 1. Main Query: Find Building Numbers with 0 AEDs ---
$sql = "SELECT 
            buildingno, 
            SUM(CASE WHEN aedid IS NOT NULL THEN 1 ELSE 0 END ) AS cnt 
        FROM 
            company_esi 
        LEFT JOIN 
            aed_esi ON clientid = company_esi.id AND aed_esi.deleted = 0 
        WHERE 
            buildingno > '' 
            AND buildingno <> '0' 
            AND iscorp = '{$session_iscorp_safe}' 
            AND company_esi.deleted = 0 
        GROUP BY 
            buildingno 
        HAVING 
            cnt = 0";

// Fetch the result set (Building Number is both key and value)
$schools = db_query_array( $sql, "buildingno", "buildingno" );

// --- 2. CSV Report Generation Logic ---
if( $xls )
{
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Header Row
    $headers = array(
        "Building No",
        "Zip Code",
        "Schools"
    );
    fputcsv($output, $headers);
    
    foreach( $schools as $buildingno )
    {
        // Clean building number for SQL (assuming it's a string)
        $buildingno_clean = mysqli_real_escape_string( $link, $buildingno );
        
        // Query to get address details (only need one row)
        $mysrow = db_query_first( "SELECT zipcode FROM company_esi WHERE buildingno = '{$buildingno_clean}' LIMIT 1" );
        
        // Query to get all schools associated with this building number
        $s = db_query_rows( "SELECT companyname, id FROM company_esi WHERE buildingno='" . $buildingno_clean . "' AND deleted = 0" );
        
        // Build school list
        $school_names = array();
        foreach( $s as $sr )
        {
            if (!empty($sr['companyname'])) {
                $school_names[] = $sr['companyname'];
            }
        }
        $school_list = implode(", ", $school_names);
        
        // Prepare row data
        $row_data = array(
            $buildingno,  // Original value for display (fputcsv will escape it)
            $mysrow['zipcode'] ?? '',
            $school_list
        );
        
        fputcsv($output, $row_data);
    } 
    
    fclose($output);
    exit;
}
// --- 3. HTML Table Output Logic ---
else
{
    $i = 1;
    echo( "<table border='1'>" );
    
    // Header Row
    echo( "<tr><td>Building No</td><td>Zipcode</td><td>Schools</td></tr>" );
    
    foreach( $schools as $buildingno )
    {
        $buildingno_safe = htmlspecialchars($buildingno); // Used for display only
        
        // Query to get address details (only need one row)
        $mysrow = db_query_first( "SELECT zipcode FROM company_esi WHERE buildingno = '{$buildingno}' LIMIT 1" );
        
        echo( "<tr><td>{$buildingno_safe}</td>" );
        
        // PHP 8.2 Fix: Use quoted array keys
        $zipcode_safe = htmlspecialchars($mysrow['zipcode'] ?? '');
        echo( "<td>{$zipcode_safe}</td>" );
        
        // Query to get all schools associated with this building number
        $s = db_query_rows( "SELECT companyname, id FROM company_esi WHERE buildingno='" . mysqli_real_escape_string( $link, $buildingno ) . "' AND deleted = 0" );
        
        echo( "<td>" );
        
        foreach( $s as $sr )
        {
            // PHP 8.2 Fix: Use quoted array keys and htmlspecialchars()
            $id_safe = htmlspecialchars($sr['id'] ?? '');
            $companyname_safe = htmlspecialchars($sr['companyname'] ?? '');
            echo( "<a href='viewcompany.php?id={$id_safe}'>{$companyname_safe}</a><br>" );
        }
        
        echo( "</td></tr>" );
        $i++;
    } 
    ?>
 </table>
 <?php 
} 
?>