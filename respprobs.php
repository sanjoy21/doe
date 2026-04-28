<?php
// Initialize external variables safely
$update = $_POST['update'] ?? null;
$buildingcode = $_POST['buildingcode'] ?? [];
// Note: $morethan is used as a flag but never initialized/set in the provided code.
$morethan = $morethan ?? false; 
$stob = []; // School Code To Building Code array
$rowcnt = 0;

include "mysql.php";
require_once "services.php";
// Assumed external functions: db_query, db_query_first, db_query_rows, 
// getResponderRow, updateResponder, getBuildingPulldown

// --- 1. Update Logic (Form Submission) ---
if ($update) {
    foreach ($buildingcode as $responderid => $val) {
        // Safely prepare values for SQL
        $responder_id_safe = is_numeric($responderid) ? (int)$responderid : 0;
        $val_safe = addslashes($val);

        if ($responder_id_safe > 0) {
            db_query("UPDATE responders_esi SET buildingcode = '{$val_safe}' WHERE responderid = {$responder_id_safe}");
            
            if (!empty($val)) {
                $arow = getResponderRow($responder_id_safe);
                // Assumed external function updateResponder()
                updateResponder($arow);
            }
        }
    }
}

// --- 2. CSV Processing ---
$handle = fopen("/tmp/resps.csv", "r");
if ($handle) {
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
        // Expecting $data[0] = School Code, $data[1] = Building Code
        $school_code = $data[0] ?? null;
        $building_code = $data[1] ?? null;

        if ($school_code) {
            if (!isset($stob[$school_code])) {
                $stob[$school_code] = [];
            }
            if ($building_code) {
                $stob[$school_code][] = $building_code;
            }
        }
    }
    fclose($handle);
}

// --- 3. HTML Output & Form Generation ---
echo "<form method='post'>";
echo "<input type='submit' name='update' value='Update'><br><br><table border=1 cellspacing=0><tr><td>School</td><td>Location Code</td><td>Address</td><td>School code (from sheet)</td><td>Building Code (from sheet)</td><td>Responders</td></tr>";

foreach ($stob as $bcode => $bcodes) {
    $rowcnt++;
    
    // Safely prepare school code for SQL
    $bcode_safe = addslashes($bcode);
    
    $crow = db_query_first("SELECT * FROM company_esi WHERE schoolcode = '{$bcode_safe}' AND deleted = 0 ");
    
    $company_id = $crow['id'] ?? null;
    
    if ($company_id) {
        $str = '';
        $company_name = htmlspecialchars($crow['companyname'] ?? 'N/A');
        $location_code = htmlspecialchars($crow['locationcode'] ?? 'N/A');
        $address = htmlspecialchars($crow['address'] ?? 'N/A');
        $bcode_html = htmlspecialchars($bcode);
        $bcodes_html = htmlspecialchars(join(", ", $bcodes));

        $str .= "<tr><td><a target='_blank' href='editcompany.php?id={$company_id}'>{$company_name}</a> <a target='_blank' href='viewcompany.php?id={$company_id}'>(view)</a></td>";
        $str .= "<td><a target='_blank' href='editcompany.php?id={$company_id}'>{$location_code}</a></td>";
        $str .= "<td>{$address}</td>";
        $str .= "<td>{$bcode_html}</td>";
        $str .= "<td>{$bcodes_html}</td>";
        $str .= "<td><table>";
        
        // Fetch responders for this company
        $aeds = db_query_rows("SELECT * FROM responders_esi WHERE deleted = 0 AND clientid = {$company_id}");
        
        $cnt = 0;
        foreach ($aeds as $arow) {
            $responder_id = $arow['responderid'] ?? 0;

            // Find expiration date (2 years after max training date)
            $aeddate_str = db_query_first_cell("SELECT MAX(trainingdate) FROM responder_training_dates WHERE responderid = {$responder_id} AND program IN ( 'aed', 'dd', 'reg', 'Non ESI' )");
            
            $aeddate = strtotime($aeddate_str);
            if ($aeddate === false) {
                continue; // Skip if no valid training date found
            }

            // Calculate 2 years expiration date
            $aed_exp_time = mktime(0, 0, 0, date("m", $aeddate), date("d", $aeddate), date("Y", $aeddate) + 2);
            
            // Filter: skip if expiration is past current time
            if ($aed_exp_time < time()) {
                continue;
            }
            
            $cnt++;
            
            // Check if building code matches the code from the CSV (for background color)
            $bg = (in_array($arow['buildingcode'] ?? '', $bcodes)) ? "bgcolor='ffffee'" : "";

            $responder_link = "editresponder.php?responderid=" . urlencode($responder_id);
            $full_name = htmlspecialchars(($arow['firstname'] ?? '') . ' ' . ($arow['lastname'] ?? ''));
            $pms_id = htmlspecialchars($arow['pmsid'] ?? 'N/A');
            $current_bcode = htmlspecialchars($arow['buildingcode'] ?? 'N/A');
            $last_update_result = htmlspecialchars($arow['lastupdateresult'] ?? 'N/A');

            $str .= "<tr {$bg}><td><a href='{$responder_link}' target='_blank'>{$full_name}</a></td>";
            $str .= "<td>{$pms_id}</td>";
            $str .= "<td>{$current_bcode}</td>";
            $str .= "<td>{$last_update_result}</td>";
            
            // Dropdown name needs to be correct for submission processing
            $dropdown_name = "buildingcode[{$responder_id}]";
            // Assumed external function getBuildingPulldown()
            $str .= "<td>" . getBuildingPulldown($company_id, $arow['buildingcode'], $dropdown_name, 'style="font-size: 10px; font-family: verdana;"', 1) . "</td>";
            $str .= "</tr>";
        }
        
        $str .= "</table></td>";
        $str .= "</tr>";

        // Final Filter: check if the number of valid responders matches criteria
        if (($morethan && $cnt < 2) || (!$morethan && $cnt >= 2)) {
            continue; // Skip outputting this row
        }
        
        echo $str;

    } else {
        echo "<tr><td>Nothing matching " . htmlspecialchars($bcode) . "</td></tr>";
    }
}

echo "</table><br><input type='submit' name='update' value='Update'>";
echo "</form>";
?>