<?php 
require "mysql.php"; 

// Safely retrieve external variables
$xls = $xls ?? null;
$session_iscorp = $session_iscorp ?? 0;
$minnum = $minnum ?? 0;

// Get the database connection link for escaping strings
$db_link = $GLOBALS['link'] ?? $link; 

// --- Function to safely escape database strings (Replacing escMe()) ---
function safe_esc($str, $db_link) {
    return mysqli_real_escape_string($db_link, $str ?? '');
}

// --- 1. Main Query: Find unique addresses with more than one company ---
$safe_iscorp = (int)$session_iscorp;

$sql = "SELECT 
            c.address, c.city, c.borough, c.region, 
            COUNT(c.id) AS numaeds 
        FROM 
            company_esi c 
        WHERE 
            c.iscorp = '{$safe_iscorp}' 
            AND c.deleted = 0 
        GROUP BY 
            c.address, c.city, c.borough, c.region 
        HAVING 
            numaeds > 1 
        ORDER BY 
            c.borough, c.address";

$schools = db_query_rows( $sql );

// --------------------------------------------------------------------------
// --- 2. Excel (CSV) Export Logic (Modern Replacement for PEAR Writer) ---
// --------------------------------------------------------------------------
if( $xls )
{
    // WARNING: File extension (.xls) is misleading as the output is CSV.
    // Preserving the original filename as requested.
    $filename = "expired.xls"; 
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Write Header Row
    fputcsv($output, ["Address", "City", "Borough", "Region", "Schools"]);
    
    foreach( $schools as $crow )
    {
        // PHP 8.2 Fix: Quote array keys
        $address_val = $crow['address'] ?? '';
        $city_val = $crow['city'] ?? '';
        $borough_val = $crow['borough'] ?? '';
        $region_val = $crow['region'] ?? '';
        
        // --- Sub-Query to get all company names at this exact location ---
        $rstr = $region_val ? "AND region = '".safe_esc($region_val, $db_link)."'" : "AND (region IS NULL OR region = '')";
        
        $sql_companies = "SELECT 
                            companyname 
                          FROM 
                            company_esi 
                          WHERE 
                            iscorp = '{$safe_iscorp}' 
                            AND deleted = 0 
                            AND address = '".safe_esc($address_val, $db_link)."' 
                            AND city = '".safe_esc($city_val, $db_link)."' 
                            AND borough = '".safe_esc($borough_val, $db_link)."' 
                            {$rstr}";
                            
        $rows = db_query_rows( $sql_companies );
        
        $str = "";
        foreach( $rows as $r ) {
            $str .= ($r['companyname'] ?? 'N/A') . "; ";
        }
        $str = rtrim($str, "; "); // Prepare semicolon-separated list for CSV column
        
        // Write Data Row
        fputcsv($output, [
            $address_val, 
            $city_val, 
            $borough_val, 
            $region_val, 
            $str
        ]);
    } 
    
    fclose($output);
    exit; // Stop execution after sending file
}
else
{
    // --------------------------------------------------------------------------
    // --- 3. HTML Table Display Logic ---
    // --------------------------------------------------------------------------
    $i = 1;
    echo( "<table border=1>" );
    echo( "<tr><th>Address</th><th>City</th><th>Borough</th><th>Region</th><th>Schools</th></tr>" );
    
    foreach( $schools as $crow )
    {
        // PHP 8.2 Fix: Quote array keys
        $address_val = $crow['address'] ?? '';
        $city_val = $crow['city'] ?? '';
        $borough_val = $crow['borough'] ?? '';
        $region_val = $crow['region'] ?? '';

        // --- Sub-Query to get all company names at this exact location ---
        $rstr = $region_val ? "AND region = '".safe_esc($region_val, $db_link)."'" : "AND (region IS NULL OR region = '')";
        
        $sql_companies = "SELECT 
                            id, companyname 
                          FROM 
                            company_esi 
                          WHERE 
                            iscorp = '{$safe_iscorp}' 
                            AND deleted = 0 
                            AND address = '".safe_esc($address_val, $db_link)."' 
                            AND city = '".safe_esc($city_val, $db_link)."' 
                            AND borough = '".safe_esc($borough_val, $db_link)."' 
                            {$rstr}";
                            
        $rows = db_query_rows( $sql_companies );
        
        $str = "";
        foreach( $rows as $r ) {
            // PHP 8.2 Fix/Security: Use htmlspecialchars() for output
            $company_id_safe = htmlspecialchars($r['id'] ?? '');
            $company_name_safe = htmlspecialchars($r['companyname'] ?? 'N/A');
            $str .= "<a href='viewcompany.php?companyid={$company_id_safe}'>{$company_name_safe}</a><br>";
        }

        // --- Display Row ---
        echo( "<tr>" );
        echo( "<td>" . htmlspecialchars($address_val) . "</td>" );
        echo( "<td>" . htmlspecialchars($city_val) . "</td>" );
        echo( "<td>" . htmlspecialchars($borough_val) . "</td>" );
        echo( "<td>" . htmlspecialchars($region_val) . "</td>" );
        echo( "<td>{$str}</td>" ); 
        echo( "</tr>" );
        
        $i++;
    } 
    
    echo( "</table>" );
} 
?>