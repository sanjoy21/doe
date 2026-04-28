<?php 
$nologinrequired = 1;
include "mysql.php";

// Safely access external variables
$type_safe = $type ?? null;
$corp_safe = $corp ?? 0; // Assuming $corp is meant to be a number (0 or 1)
$q_safe = $q ?? '';
$callback_safe = $_GET['callback'] ?? 'callback'; // Safely access the JSONP callback function

// Check if the request is specifically for a "company" type; exit if not.
if( $type_safe != "company" ) return;

$iscorp = (int)$corp_safe; // Ensure iscorp is treated as an integer

// Query the database for companies matching the search query and corporate status
$sql = "SELECT id, companyname FROM company_esi 
        WHERE companyname LIKE '%" . $q_safe . "%' 
          AND deleted = 0 
          AND iscorp = '" . $iscorp . "'";

$res = db_query_rows( $sql );
$return = array();

// Format results into an array suitable for the client-side JavaScript
foreach( $res as $r ) 
{
    // Safely access quoted array keys
    $company_name_safe = $r["companyname"] ?? '';
    $company_id_safe = $r["id"] ?? 0;
    
    // Structure: {"value": "Company Name", "id": Company ID}
    $return[] = array( "value" => $company_name_safe, "id" => $company_id_safe );
}

// Output the data as JSONP
header('Content-Type: application/javascript');
echo $callback_safe . '(' . json_encode(array_values(($return))) . ');';
?>