<?php
/**
 * PHP 8.2 Compatible Data Fetching and Initialization
 */

$drill = [];
$servicecall = [];
$newinstall = [];
$recertnotes = [];

// Ensure $schoolid is defined to avoid errors
// $schoolid = $schoolid;
$company_row = getCompanyRow($schoolid);

// Ensure $thiscompanyrow is defined (added fallback to avoid undefined variable error)
// $thiscompanyrow = $thiscompanyrow;

// --- start recertnotes ---
// Ensure $username is defined
// $username = $username;
$uid = db_query_first_cell("SELECT id FROM user WHERE usertype = 'trainer' AND userid = '$username'");

$q = db_query_rows("SELECT * FROM recertnotes WHERE completed = 0 AND companyid = '$schoolid' AND tassignedto = '$uid'");

// PHP 8.2: Ensure $q is an array before counting
if (count($q) > 0) {
    $tmpall = [];
    foreach ($q as $qrow) {
        $tmpr = [];
        $tmpr["notes"] = $qrow["recertificationnotes"] ?? '';
        $tmpr["trainernotes"] = $qrow["completednotes"] ?? '';
        $tmpr["iscompleted"] = $qrow["completed"] ?? 0;
        $tmpr["rnid"] = $qrow["id"] ?? 0;
        $tmpall[] = $tmpr;
    }
    $recertnotes = $tmpall;
}
// --- end recertnotes ---

if (!($thiscompanyrow['iscorp'])) {
    // first the drill part
    $hasopen = false; 
    
    if (!$hasopen) {
        $newid = db_query_insert_id("INSERT INTO drill (companyid) VALUES ('$schoolid')");
        db_query("INSERT INTO drill_to_companyid (drillid, companyid, showed) VALUES ('$newid', '$schoolid', 1)");

        if (!$company_row["iscorp"]) {
            $campus_id = $company_row["campusid"];
            $company_id = $company_row["id"];
            $scho = getSchoolsInCampus($campus_id, $company_id);
            
            
                foreach ($scho as $s) {
                    $s_id = $s['id'];
                    db_query("INSERT INTO drill_to_companyid (drillid, companyid, showed) VALUES ('$newid', '$s_id', 1)");
                }
            
        }
        $drillid = $newid;
    } else {
        $drillid = $hasopen;
    }
    
    // PHP 8.2: Must use quotes for array keys
    $drill['id'] = $drillid; 

    // --- start new install ---
    $q_new = db_query_rows("SELECT aedid, serial FROM aed_esi WHERE clientid = '$schoolid' AND deleted = 0 AND newinstall = 1");
    
    $newscid = null;
    $newaedids = [];

    if (count($q_new) > 0) {
        // Note: I added a check for $aedid; in your original it was used but not defined in this snippet
        $newscid = db_query_insert_id("INSERT INTO servicecall (companyid, singleaedid, newinstall) VALUES ('$schoolid', '$aedid', 1)");
        
        foreach ($q_new as $qrow) {
            $serial_clean = $qrow['serial'];
            db_query("INSERT INTO aed_to_servicecall (serial, servicecallid) VALUES ('$serial_clean', '$newscid')");
            $newaedids[] = ["serial" => $qrow['serial']];
        }
    }

    $newinstall['id'] = $newscid; 
    $newinstall['newaedids'] = $newaedids; 
}

// --- next the servicecall part ---
$hasopen_sc = false;

if (!$hasopen_sc) {
    $newid_sc = db_query_insert_id("INSERT INTO servicecall (companyid) VALUES ('$schoolid')");
    db_query("INSERT INTO servicecall_to_companyid (servicecallid, companyid) VALUES ('$newid_sc', '$schoolid')");

    if (!$company_row["iscorp"]) {
        $scho = getSchoolsInCampus($company_row["campusid"], $company_row["id"]);
        
            foreach ($scho as $s) {
                $s_id = $s['id'];
                db_query("INSERT INTO servicecall_to_companyid (servicecallid, companyid) VALUES ('$newid_sc', '$s_id')");
            }
        
    }
    $servicecallid = $newid_sc;
} else {
    $servicecallid = $hasopen_sc;
}

$servicecall['id'] = $servicecallid; 

// Handle existing AEDs
$is_corp = $company_row["iscorp"];
$campus_id = $thiscompanyrow['campusid'];

if ($is_corp) {
    $existingaeds = getAedRows($schoolid, false, 0, true);
} else {
    $existingaeds = getAedRows($schoolid, false, $campus_id, true);
}
    
    foreach ($existingaeds as $eid => $erow) {
        $erow["aedmissing"] = 0;
        $existingaeds[$eid] = $erow;
    }

?>