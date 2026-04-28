<?php
require_once('mysql.php');

// Safely initialize external variables
$session_iscorp = $session_iscorp ?? '0'; // Default to non-corporate if not set
$visi = $visi ?? ''; // $visi variable is commented out in original logic but used in SQL

// The commented-out trainer logic suggests potential future use of $thisusersrow and $zips
// global $thisusersrow;
// $zips = "";
// if( ($thisusersrow['usertype'] ?? '') == "trainer" ) {
//     $zips = ($thisusersrow['visiblezips'] ?? 0) ? " AND c.zip IN (" . getZips( $thisusersrow ) . ")" : "";
// }

// Calculate the date for the next month
$nextmonth = date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) + 1, date( "d" ), date( "Y" ) ) );

// Construct the SQL query. 
// PHP 8.2 Fix: Use proper string concatenation for variables inside the query.
$sql = "SELECT 
            company_esi.id,
            company_esi.companyname, 
            company_esi.city, 
            company_esi.zip, 
            company_esi.address, 
            CONCAT( company_esi.contactname, ' ', company_esi.contactphone, ' ', company_esi.contactemail ) AS contactinfo,
            a.serial,
            a.padaexpiration,
            a.padbexpiration, 
            a.location, 
            a.pediatricpads, 
            a.sparedate 
        FROM 
            company_esi, aed_esi a 
        WHERE 
            iscorp = '" . $session_iscorp . "' 
            AND company_esi.isactive = 1 
            AND company_esi.deleted = 0 
            AND a.deleted = 0 
            AND company_esi.id = a.clientid 
            AND newinstall = 1 " . $visi . " 
        ORDER BY 
            companyname";

// echo( $sql ); // Uncomment for debugging

$filename = "report_new";

// Include the script responsible for formatting and outputting the report data
include "traineraedslisting.php";
?>