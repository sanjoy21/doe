<?php 
include "mysql.php";

// Set headers for CSV download
header("Content-type: application/csv");
header("Content-Disposition: attachment; filename=all-buildings.csv");

// Output CSV header row
echo( "School,Location Code,Building Code,Building Name,Building Address,City,State,Zip\n" );

// SQL query to fetch school, location, and building data
$sql = "Select schoolcode, c.locationcode, b.buildingcode, b.buildingname, b.address, b.city, b.state, b.zip 
        from company_esi c 
        left join location_to_building lb on lb.locationcode = c.locationcode 
        left join buildings b on b.buildingcode = lb.buildingcode 
        where iscorp = 0 and deleted = 0 and showsondrillreports = 1";

$res = db_query_rows( $sql );

// Output CSV data rows
foreach( $res as $row )
{
    // Safely access quoted array keys and format for CSV, quoting fields to handle commas/special characters
    $schoolcode_safe = $row["schoolcode"] ?? '';
    $locationcode_safe = $row["locationcode"] ?? '';
    $buildingcode_safe = $row["buildingcode"] ?? '';
    $buildingname_safe = $row["buildingname"] ?? '';
    $address_safe = $row["address"] ?? '';
    $city_safe = $row["city"] ?? '';
    $state_safe = $row["state"] ?? '';
    $zip_safe = $row["zip"] ?? '';
    
    // Using double quotes around fields as per the original code's intent for CSV formatting
    echo( "\"{$schoolcode_safe}\",\"{$locationcode_safe}\",\"{$buildingcode_safe}\",\"{$buildingname_safe}\",\"{$address_safe}\",\"{$city_safe}\",\"{$state_safe}\",\"{$zip_safe}\"\n" );
}
?>