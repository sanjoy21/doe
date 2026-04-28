<?php 
include "mysql.php"; 

// Safely initialize the date variable
// PHP 8.2 Fix: Use null coalescing and set a default date if $day is not set
$day = $day ?? date('Y-m-d'); 
$dt = $day;

// --- 1. Set Excel/Download Headers ---
Header( "Content-type: application/vnd.ms-excel" ); 
header("Content-Transfer-Encoding: binary"); 
// PHP 8.2 Fix: Access $_SERVER superglobal with quoted key
$user_agent = strtolower ( $_SERVER["HTTP_USER_AGENT"] ?? '' ); 
$filename = "fun.xls"; 

// Original header logic preserved
if ((is_integer (strpos($user_agent, "msie"))) && (is_integer (strpos($user_agent, "win")))) { 
    header( "Content-Disposition: filename=".basename($filename).";" ); 
} else { 
    header( "Content-Disposition: attachment; filename=".basename($filename).";" ); 
} 

// --- 2. Start HTML Table Output ---
echo( "<table><tr><td>Site Contact</td><td>School Name</td><td>Address</td><td>Address 2</td><td>City</td><td>State</td><td>Zip</td></tr>" );

// --- 3. Process Date and Query Classes ---
// Sanitize the date input for the query
$dt_sql = date("Y-m-d", strtotime( $dt ) );

$sql_classes = "SELECT 
                    class.* FROM 
                    class 
                WHERE 
                    startdate LIKE '{$dt_sql}%' 
                    AND deleted = 0 
                    AND accepted = 1";
                    
$cl = db_query_rows( $sql_classes );

// --- 4. Iterate and Output Rows ---
foreach ( $cl as $class )
{
    // PHP 8.2 Fix: Quote array key 'companyid'
    $company_id = $class['companyid'] ?? null;
    $crow = getCompanyRow( $company_id );

    // PHP 8.2 Fix: Quote array keys for site contact name
    $sitecontact = ($class['firstname'] ?? '') . " " . ($class['lastname'] ?? '');
    
    // PHP 8.2 Fix/Security: Quote array keys and use htmlspecialchars() for safe output
    $sitecontact_safe = htmlspecialchars($sitecontact);
    $company_name_safe = htmlspecialchars($crow['companyname'] ?? '');
    $address_safe = htmlspecialchars($crow['address'] ?? '');
    $address2_safe = ''; // Original code had an empty column
    $city_safe = htmlspecialchars($crow['city'] ?? '');
    $state_safe = htmlspecialchars($crow['state'] ?? '');
    $zip_safe = htmlspecialchars($crow['zip'] ?? '');

    echo( "<tr>
              <td>{$sitecontact_safe}</td>
              <td>{$company_name_safe}</td>
              <td>{$address_safe}</td>
              <td>{$address2_safe}</td>
              <td>{$city_safe}</td>
              <td>{$state_safe}</td>
              <td>{$zip_safe}</td>
          </tr>" );
}

// --- 5. Close Table ---
echo( "</table>" );
?>