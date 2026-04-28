<?php 
echo date("H:i:s");
?>

<?php 
phpinfo();
?>

<?php

exit;

// The rest of the script is unreachable code.
// It is converted here for compliance completeness, but it will not run.

// include "mysql.php";

// // header("Content-type:application/csv");
// // header("Content-Disposition:filename=philips.xls");

// $handle = fopen("/tmp/d79.csv", "r");
// $rowcnt = 0;
// $stob = array();
// echo "<table border=1 cellspacing=0><tr><td>Row #</td><td>Building Code</td><td>Schools in that building</td><td># responders in that building</td><td>Latest Training Date</td></tr>";

// while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
//     // print_r($data); // Original debug line

//     $rowcnt++;
    
//     if ($rowcnt == 1) continue; // Skip header row
    
//     // Reconstruct school code from CSV column 0 (data[0])
//     $schoolcode_raw = $data[0] ?? '';
//     $schoolcode = substr($schoolcode_raw, 0, 2);
//     $schoolcode .= "-" . substr($schoolcode_raw, 2, 1);
//     $schoolcode .= "-" . substr($schoolcode_raw, 3, 3);
    
//     // Retrieve schools associated with the building code from CSV column 4 (data[4])
//     $buildingcode_safe = escMe($data[4] ?? '');
//     $ids = db_query_array("SELECT id, companyname FROM location_to_building, company_esi c WHERE buildingcode = '{$buildingcode_safe}' AND c.locationcode = location_to_building.locationcode AND deleted = 0", "id", "companyname");
    
//     $str = implode(",", array_keys($ids)); // Comma-separated list of school IDs
//     $str2 = implode(",", $ids); // Comma-separated list of school names

//     if (!$str) {
//         $str = "-1";
//     }
    
//     $arows = [];
//     if ($str != -1) {
//         // Find active responders in these schools who were trained after 2012-06-01
//         $arows = db_query_array("SELECT responderid FROM responders_esi WHERE clientid IN ({$str}) AND deleted = 0 AND responderid IN (SELECT responderid FROM responder_training_dates WHERE trainingdate > '2012-06-01')", "responderid", "responderid");
//     } 
    
//     if (!count($arows)) {
//         $arows = [];
//     }

//     $count2 = "";
//     if (count($arows)) {
//         // Find the latest training date among the selected responders
//         $responder_list = implode(", ", $arows);
//         $count2 = db_query_first_cell("SELECT MAX(trainingdate) FROM responder_training_dates WHERE responderid IN ({$responder_list})");
//     }
    
//     // Output Table Row
//     echo "<tr>";
//     echo "<td>{$rowcnt}</td>";
//     echo "<td>" . htmlspecialchars($data[4] ?? '') . "</td>";
//     echo "<td>" . htmlspecialchars($str2) . "</td>";
//     echo "<td>" . count($arows) . "</td>";
//     echo "<td>";
//     if ($count2) {
//         echo date("Y-m-d", strtotime($count2));
//     }
//     echo "</td>";
//     echo "</tr>";
// }
// echo "</table>";
?>