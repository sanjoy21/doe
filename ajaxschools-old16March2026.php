<?php
$nologinrequired = true;
require "mysql.php";

// Initialize variables
$whr = "";
$extor = "";
$selected = 0;
$borough = isset($_GET['borough']) ? $_GET['borough'] : '';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$fieldname = isset($_GET['fieldname']) ? $_GET['fieldname'] : 'companyid';
$extra = isset($_GET['extra']) ? $_GET['extra'] : '';
$companyid = isset($_GET['companyid']) ? intval($_GET['companyid']) : 0;
$iscorp = isset($_GET['iscorp']) ? intval($_GET['iscorp']) : 0;

// Set session_iscorp based on the iscorp parameter
$session_iscorp = $iscorp;

// Filter by Borough
if($borough && $borough !== '' && $borough !== 'other') {
    $safe_borough = mysqli_real_escape_string($link, $borough);
    $whr .= " AND borough = '{$safe_borough}'";
}

// Filter by Name/School Code
if($name) {
    $safe_name = mysqli_real_escape_string($link, $name);
    
    // Check if name matches DBN format (e.g., 02M408)
    if(preg_match("/^[0-9]{2}[a-zA-Z]{1}[0-9]{3}$/", $name)) {
        $extor = " OR REPLACE(schoolcode, '-', '') = '{$safe_name}'";  
    }

    $whr .= " AND ( companyname LIKE '%{$safe_name}%' OR schoolcode LIKE '%{$safe_name}%' {$extor} ) ";
}

// Ensure $extra is stripped and escaped if needed
$extra = stripslashes($extra);
$safe_extra = $extra; // Don't re-escape as it contains SQL clauses

// Define the concatenation order for the 'longname' sort field
$concat = $session_iscorp ? "CONCAT(companyname, schoolcode)" : "CONCAT(schoolcode, companyname)";

// Exclude specific companies
$whr .= " AND companyname NOT LIKE 'Responder Holding%' ";

// --- 2. Determine Final Query ---
$query = "";

if($companyid > 0) {
    // If a specific company ID is provided, query only that one
    $query = "SELECT id, schoolcode, companyname, {$concat} AS longname 
              FROM company_esi 
              WHERE id = {$companyid} 
              ORDER BY longname";
    $selected = $companyid;
} else {
    // Standard filtered query
    $safe_iscorp = $session_iscorp;
    
    $query = "SELECT id, schoolcode, companyname, {$concat} AS longname 
              FROM company_esi 
              WHERE deleted = 0 
              AND iscorp = '{$safe_iscorp}' 
              {$safe_extra} {$whr} 
              ORDER BY longname";
}

// For debugging - uncomment to see the query
// error_log("AJAX Schools Query: " . $query);

$result = mysqli_query($link, $query);

if(!$result) {
    error_log("MySQL Error: " . mysqli_error($link));
    echo "<span class='copy' style='color:red'>Database error. Please try again.</span>";
    exit;
}

// --- 3. Output HTML Dropdown ---
?>
<span class="copy">
<?php if(strpos($extra, "VIR") === false) { ?>
    <font color='black'><b>Next, choose your <?= ($session_iscorp ? "company" : "school") ?>:</b></font><br>
<?php } else { ?>
    <font color='black'><b>Please select Emergency Skills below.</b></font>
<?php } ?>

<select onChange='updateBuildings(this)' name='<?= htmlspecialchars($fieldname) ?>' style='font-size: 10px; font-family: verdana;' id='companyid'>
    <option value=''>-- Select your <?= ($session_iscorp ? "company" : "school") ?> --</option>
    <?php 
    $count = 0;
    while($tmpschool = mysqli_fetch_assoc($result)) {
        $count++;
        
        $schoolcode_safe = htmlspecialchars($tmpschool["schoolcode"] ?? '');
        $companyname_safe = htmlspecialchars($tmpschool["companyname"] ?? '');
        $id_safe = htmlspecialchars($tmpschool["id"]);
        
        if($session_iscorp) {
            $school_name = "{$companyname_safe}" . ($schoolcode_safe ? " ({$schoolcode_safe})" : "");
        } else {
            $school_name = $schoolcode_safe ? "{$schoolcode_safe} - {$companyname_safe}" : $companyname_safe;
        }
        
        // Truncate long names
        if(strlen($school_name) > 60) {
            $school_name = substr($school_name, 0, 57) . "...";
        }
        
        $selected_attr = ($selected == $tmpschool["id"]) ? " selected" : "";
        ?>
        <option value='<?= $id_safe ?>'<?= $selected_attr ?>><?= $school_name ?></option>
    <?php } ?>
    
    <?php if($count == 0): ?>
        <option value='' disabled>No <?= ($session_iscorp ? "companies" : "schools") ?> found</option>
    <?php endif; ?>
</select>

<?php if($count == 0): ?>
    <br><span class='copy' style='color:red'>No <?= ($session_iscorp ? "companies" : "schools") ?> found matching your criteria. Please try a different search.</span>
<?php endif; ?>

</span>