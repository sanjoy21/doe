<?php

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}
// Initialize variables with proper validation
$locationcode = isset($_POST['locationcode']) ? trim($_POST['locationcode']) : '';
$bcodes = isset($_POST['bcodes']) && is_array($_POST['bcodes']) ? $_POST['bcodes'] : [];
$bids = isset($_POST['bids']) && is_array($_POST['bids']) ? $_POST['bids'] : [];
$bnames = isset($_POST['bnames']) && is_array($_POST['bnames']) ? $_POST['bnames'] : [];
$badds = isset($_POST['badds']) && is_array($_POST['badds']) ? $_POST['badds'] : [];
$bcitys = isset($_POST['bcitys']) && is_array($_POST['bcitys']) ? $_POST['bcitys'] : [];
$bstates = isset($_POST['bstates']) && is_array($_POST['bstates']) ? $_POST['bstates'] : [];
$bzips = isset($_POST['bzips']) && is_array($_POST['bzips']) ? $_POST['bzips'] : [];

if ($locationcode) {
    // Sanitize locationcode
    $safe_locationcode = db_escape($locationcode);
    
    // Delete existing location-to-building associations
    db_query("DELETE FROM location_to_building WHERE locationcode = '" . $safe_locationcode . "'");

    foreach ($bcodes as $i => $val) {
        if ($val) {
            // Sanitize all inputs
            $safe_val = db_escape(trim($val));
            $safe_bname = isset($bnames[$i]) ? db_escape(trim($bnames[$i])) : '';
            $safe_badd = isset($badds[$i]) ? db_escape(trim($badds[$i])) : '';
            $safe_bcity = isset($bcitys[$i]) ? db_escape(trim($bcitys[$i])) : '';
            $safe_bstate = isset($bstates[$i]) ? db_escape(trim($bstates[$i])) : '';
            $safe_bzip = isset($bzips[$i]) ? db_escape(trim($bzips[$i])) : '';
            
            // Delete specific association
            db_query("DELETE FROM location_to_building WHERE locationcode = '" . $safe_locationcode . "' AND buildingcode = '" . $safe_val . "'");
            
            // Insert new association
            db_query("INSERT INTO location_to_building (locationcode, buildingcode) VALUES ('" . $safe_locationcode . "', '" . $safe_val . "')");
            
            // Get or create building ID
            $oldi = isset($bids[$i]) ? intval($bids[$i]) : 0;
            
            if (!$oldi) {
                $oldi = db_query_first_cell("SELECT id FROM buildings WHERE buildingcode = '" . $safe_val . "'");
                if (!$oldi) {
                    $oldi = db_query_insert_id("INSERT INTO buildings (buildingcode) VALUES ('" . $safe_val . "')");
                }
            }
            
            // Update building details
            if ($oldi) {
                db_query("UPDATE buildings SET 
                    buildingname = '" . $safe_bname . "', 
                    buildingcode = '" . $safe_val . "', 
                    address = '" . $safe_badd . "', 
                    city = '" . $safe_bcity . "', 
                    state = '" . $safe_bstate . "', 
                    zip = '" . $safe_bzip . "' 
                    WHERE id = " . intval($oldi));
            }
        }
    }
}
?>