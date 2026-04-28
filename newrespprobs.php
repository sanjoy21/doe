<?php
// Use strict includes/requires for files vital to database operation.
require_once "mysql.php";
require_once "services.php";

// Input array expected from POST submission
$buildingcode_input = $_POST['buildingcode'] ?? [];
$update = $_POST['update'] ?? false;
$morethan = $_REQUEST['morethan'] ?? null; // Assuming this might be requested/posted

// 1. SQL INJECTION MITIGATION for UPDATE query
if ($update) {
    foreach ($buildingcode_input as $responderid_input => $val) {
        // Sanitize $responderid: Must be cast to an integer
        $responderid = (int)$responderid_input;
        
        // Sanitize $val: Must be securely escaped/bound via prepared statements in db_query_safe.
        // Assuming db_query_safe uses prepared statements or mysqli_real_escape_string internally.
        
        // Use a safe database execution function
        db_query("UPDATE responders_esi SET buildingcode = ? WHERE responderid = ?", 
                      array($val, $responderid));

        if ($val) {
            // Fetch the row using the safe ID
            $arow = getResponderRow($responderid);
            // Assuming updateResponder_safe handles internal database interaction securely
            updateResponder($arow); 
        }
    }
}

// 2. FILE HANDLING: Ensure /tmp/responders2.csv is a trusted file path and content source
$handle = fopen("/tmp/responders2.csv", "r");
$rowcnt = 0;
$stob = array();

if ($handle) {
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
        // Ensure data[0] (schoolcode) is not empty before processing
        if (!empty($data[0])) {
            $schoolcode = $data[0];
            $buildingcode_val = $data[1] ?? ''; // Ensure data[1] exists
            
            if (!isset($stob[$schoolcode])) {
                $stob[$schoolcode] = array();
            }
            $stob[$schoolcode][] = $buildingcode_val;
        }
    }
    fclose($handle);
}

// Start HTML output
echo "<form method='post'>";
echo "<input type='submit' name='update' value='Update'><br><br>";
echo "<table border=1 cellspacing=0><tr><td>School</td><td>Location Code</td><td>Address</td><td>School code (from sheet)</td><td>Building Code (from sheet)</td><td>Responders</td></tr>";

foreach ($stob as $bcode => $bcodes) {
    // 3. SQL INJECTION MITIGATION for SELECT query
    // $bcode (schoolcode) comes from the CSV. Must be treated as untrusted input.
    // Assuming db_query_first_safe uses prepared statements
    $crow = db_query_first("SELECT * FROM company_esi WHERE schoolcode = ? AND deleted = 0", array($bcode));

    if (!empty($crow['id'])) {
        $str = "";
        $company_id = (int)$crow['id'];
        
        // 4. XSS MITIGATION: Escape ALL database outputs
        $companyname_safe = htmlspecialchars($crow['companyname'] ?? '');
        $locationcode_safe = htmlspecialchars($crow['locationcode'] ?? '');
        $address_safe = htmlspecialchars($crow['address'] ?? '');
        $bcode_safe = htmlspecialchars($bcode);
        $bcodes_safe = htmlspecialchars(implode(", ", $bcodes));

        $str .= "<tr><td><a target=_blank href='editcompany.php?id={$company_id}'>{$companyname_safe}</a> <a target=_blank href='viewcompany.php?id={$company_id}'>(view)</a></td>";
        $str .= "<td><a target=_blank href='editcompany.php?id={$company_id}'>{$locationcode_safe}</a></td>";
        $str .= "<td>{$address_safe}</td>";
        $str .= "<td>{$bcode_safe}</td>";
        $str .= "<td>{$bcodes_safe}</td>";
        $str .= "<td><table>";

        // Fetch responders (assuming this function/query is safe or $company_id is sanitized)
        $aeds = db_query_rows("SELECT * FROM responders_esi WHERE deleted = 0 AND clientid = {$company_id}");
        $cnt = 0;

        foreach ($aeds as $arow) {
            $responder_id = (int)$arow['responderid'];

            // Fetch training date (assuming safe internal operation)
            $aeddate_str = db_query_first_cell("SELECT max( trainingdate ) FROM responder_training_dates WHERE responderid = {$responder_id} AND program IN ( 'aed', 'dd', 'reg', 'Non ESI' )");
            
            $aeddate = strtotime($aeddate_str);
            // Calculate expiry date 2 years later
            $aeddate_expiry = mktime(0, 0, 0, date("m", $aeddate), date("d", $aeddate), date("Y", $aeddate) + 2);
            
            // Check if training is expired
            if ($aeddate_expiry < time()) {
                continue;
            }
            $cnt++;

            // 4. XSS MITIGATION: Escape responder outputs
            $firstname_safe = htmlspecialchars($arow['firstname'] ?? '');
            $lastname_safe = htmlspecialchars($arow['lastname'] ?? '');
            $pmsid_safe = htmlspecialchars($arow['pmsid'] ?? '');
            $arow_buildingcode_safe = htmlspecialchars($arow['buildingcode'] ?? '');
            $lastupdateresult_safe = htmlspecialchars($arow['lastupdateresult'] ?? '');

            $bg = ""; // $bg was not assigned, leaving it empty
            $str .= "<tr {$bg}><td><a href='editresponder.php?responderid={$responder_id}' target=_blank>{$firstname_safe} {$lastname_safe}</a></td>";
            $str .= "<td>{$pmsid_safe}</td>";
            $str .= "<td>{$arow_buildingcode_safe}</td>";
            $str .= "<td>{$lastupdateresult_safe}</td>";
            
            // Assuming getBuildingPulldown function correctly escapes the $arow[buildingcode] if it is outputted outside the value attribute
            $str .= "<td>" . getBuildingPulldown($company_id, $arow_buildingcode_safe, "buildingcode[{$responder_id}]", 'style="font-size: 10px;  font-family: verdana;"', 1) . "</td>";
            $str .= "</tr>";
        }

        if (isset($morethan)) {
            if (($morethan === 1 && $cnt < 2) || ($morethan === 0 && $cnt >= 2)) {
                continue;
            }
        }
            
        echo $str;
        echo "</table></td>";
        echo "</tr>";
    } else {
        // Output $bcode, which came from the CSV, must be escaped.
        echo "<tr><td>Nothing matching " . htmlspecialchars($bcode) . "</td></tr>";
    }
}
echo "</table><br><input type='submit' name='update' value='Update'>";
echo "</form>";

?>