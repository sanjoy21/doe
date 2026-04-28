<?php
// Assumed external functions: db_query, db_escape, getAedRow, updateAED, db_query_first, db_query_rows, getBuildingPulldown

include "mysql.php";
require_once "services.php";

// Initialize external variables safely
$update = $_POST['update'] ?? null;
$buildingcode_updates = $_POST['buildingcode'] ?? [];

// --- Update Logic ---
if ($update) {
    foreach ($buildingcode_updates as $aedid => $val) {
        $aedid = (int)$aedid;
        $val_safe = db_escape(trim($val)); // Assuming db_escape is available

        // Only update if the value is non-empty
        if (!empty($val_safe)) {
            db_query("UPDATE aed_esi SET buildingcode = '{$val_safe}' WHERE aedid = {$aedid}");
            
            $arow = getAedRow($aedid);
            // Call external/internal service to process the AED update
            updateAED($arow);
        }
    }
}

// --- CSV Processing ---
$handle = fopen("/tmp/aeds.csv", "r");
$stob = []; // School Code To Building Codes map

if ($handle === false) {
    // In a real application, you'd output an error message here.
    echo "Could not open CSV file /tmp/aeds.csv.";
    // Continue execution to at least display the form, or exit. We'll exit for clean code.
    exit;
}

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $school_code = trim($data[0] ?? '');
    $building_code = trim($data[1] ?? '');
    
    if (empty($school_code)) {
        continue;
    }
    
    // Aggregate building codes by school code
    if (!isset($stob[$school_code])) {
        $stob[$school_code] = [];
    }
    // Only store unique building codes
    if (!in_array($building_code, $stob[$school_code])) {
        $stob[$school_code][] = $building_code;
    }
}
fclose($handle);

// --- HTML Output Start ---
echo "<form method='post'>";
echo "<input type='submit' name='update' value='Update'><br><br>";
echo "<table border='1' cellspacing='0'>";
echo "<tr>
        <th>School</th>
        <th>Location Code</th>
        <th>Address</th>
        <th>School code (from sheet)</th>
        <th>Building Code (from sheet)</th>
        <th>AEDs</th>
      </tr>";

// --- Cross-Reference and Display Logic ---
$rowcnt = 0;
foreach ($stob as $bcode => $bcodes) {
    $rowcnt++;
    $bcode_safe = db_escape($bcode);
    
    // Find the company matching the school code
    $crow = db_query_first("SELECT * FROM company_esi WHERE schoolcode = '{$bcode_safe}' AND deleted = 0");
    
    if ($crow && ($crow['id'] ?? null)) {
        $company_id = (int)$crow['id'];
        
        $company_name = htmlspecialchars($crow['companyname'] ?? 'N/A');
        $location_code = htmlspecialchars($crow['locationcode'] ?? 'N/A');
        $address = htmlspecialchars($crow['address'] ?? 'N/A');
        
        echo "<tr>";
        // Company Links
        echo "<td><a target='_blank' href='editcompany.php?id={$company_id}'>{$company_name}</a> <a target='_blank' href='viewcompany.php?id={$company_id}'>(view)</a></td>";
        echo "<td><a target='_blank' href='editcompany.php?id={$company_id}'>{$location_code}</a></td>";
        echo "<td>{$address}</td>";
        echo "<td>{$bcode}</td>";
        echo "<td>" . htmlspecialchars(implode(", ", $bcodes)) . "</td>";
        
        // AEDs Inner Table
        echo "<td><table>";
        $aeds = db_query_rows("SELECT * FROM aed_esi WHERE deleted = 0 AND clientid = {$company_id}");
        
        if (is_array($aeds)) {
            foreach ($aeds as $arow) {
                $aed_id = (int)$arow['aedid'];
                $current_building_code = htmlspecialchars($arow['buildingcode'] ?? '');
                $serial = htmlspecialchars($arow['serial'] ?? 'N/A');
                
                // Highlight row if the suggested building code from the first entry matches the current one
                $bg = (!empty($bcodes[0]) && $bcodes[0] == $current_building_code) ? "bgcolor='ffffee'" : "";
                
                echo "<tr {$bg}>";
                echo "<td><a href='editaed.php?aedid={$aed_id}' target='_blank'>{$serial}</a></td>";
                echo "<td>{$current_building_code}</td>";
                echo "<td>" . htmlspecialchars($arow['lastupdateresult'] ?? '') . "</td>";
                
                // Building Code Dropdown for Update
                echo "<td>";
                echo getBuildingPulldown(
                    $company_id, 
                    $current_building_code, 
                    "buildingcode[{$aed_id}]", 
                    'style="font-size: 10px; font-family: verdana;"', 
                    1
                );
                echo "</td>";
                
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No active AEDs found.</td></tr>";
        }
        echo "</table></td>";
        echo "</tr>";
    } else {
        // Output for no company match
        echo "<tr><td colspan='6'>Nothing matching school code: " . htmlspecialchars($bcode) . "</td></tr>";
    }
}

// --- HTML Output End ---
echo "</table><br><input type='submit' name='update' value='Update'></form>";
?>