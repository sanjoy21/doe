<?php 
// // Safely access external variables
// $specialadmin = $specialadmin ?? false;
$substr = $substr ?? '';
// $session_iscorp = $session_iscorp ?? 0;
// $session_id = $session_id ?? 0;
// $companyid = $companyid ?? null;

$extra = "";

// Exclusion logic for non-special admins
if( !$specialadmin )
    $extra = " and id <> 2858";

// Substring search filter
if( $substr )
    $extra .= " and ( companyname like '%" . $substr . "%' or schoolno like '%" . $substr . "%' or schoolcode like '%" . $substr . "%' ) ";

// Build the main SQL query
$sql = "SELECT id, companyname, borough FROM company_esi where iscorp = '" . (int)$session_iscorp . "' and deleted = 0 $extra order by companyname";
$schools = db_query_rows($sql);

// Check if the user has broad permissions (indicated by get_companyid returning '%')
if (get_companyid($session_id) == '%') {
    $school_select = '<select name="companyid" style="font-size: 10px; font-family: verdana;"><option value=""></option>';
    
    foreach ($schools as $school) {
        
        // Safely access quoted array keys
        $tcompanyid = $school["id"] ?? 0;
        $school_name = $school["companyname"] ?? '';
        
        // Determine if the current option should be selected
        $selected = ( $tcompanyid == $companyid ) ? "SELECTED" : "";
        
        $school_select .= '<option value="' . htmlspecialchars($tcompanyid) . '" ' . $selected . '>' . htmlspecialchars($school_name) . '</option>';
    }
    
    $school_select .= '</select>';
}
?>