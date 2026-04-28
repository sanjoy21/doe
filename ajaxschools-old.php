<?php
$nologinrequired = true;
require "mysql.php";

if( !$fieldname )
    $fieldname = "companyid";

// Filter by Borough
if( $borough && $borough !== 'other') {
    $safe_borough = mysqli_real_escape_string($link, $borough);
    $whr .= " AND borough = '{$safe_borough}'";
}

// Filter by Name/School Code
if( $name ) {
    $safe_name = mysqli_real_escape_string($link, $name);
    
    // Check if name matches DBN format (e.g., 02M408)
    if( preg_match("/^[0-9]{2}[a-zA-Z]{1}[0-9]{3}$/", $name) ) {
        // Original logic to search schoolcode without hyphens
        $extor = " OR REPLACE(schoolcode, '-', '') = '{$safe_name}'";  
    }

    $whr .= " AND ( companyname LIKE '%{$safe_name}%' OR schoolcode LIKE '%{$safe_name}%' {$extor} ) ";
}

// Ensure $extra is stripped and escaped if needed
$extra = stripslashes( $extra );
$safe_extra = mysqli_real_escape_string($link, $extra);

// Define the concatenation order for the 'longname' sort field
$concat = $session_iscorp ? "CONCAT( companyname, schoolcode )" : "CONCAT( schoolcode, companyname )";

// Exclude specific companies (Preserve original exclusion logic)
$whr .= " AND companyname NOT LIKE 'Responder Holding%' ";

// --- 2. Determine Final Query ---
$query = "";

if( $companyid )
{
    // If a specific company ID is provided, query only that one
    $safe_companyid = $companyid;
    $query="SELECT id, schoolcode, companyname, {$concat} AS longname 
            FROM company_esi 
            WHERE id = {$safe_companyid} 
            ORDER BY longname";
    $selected = $companyid;
}
else
{
    // Standard filtered query
    $safe_iscorp = $session_iscorp;
    
    // Note: $safe_extra is inserted raw here, assuming it contains clean, existing SQL clauses.
    $query="SELECT id, schoolcode, companyname, {$concat} AS longname 
            FROM company_esi 
            WHERE deleted = 0 
            AND iscorp = '{$safe_iscorp}' 
            {$safe_extra} {$whr} 
            ORDER BY longname";
}

// echo( $query ); // Original debugging line

$result = mysqli_query($link, $query);

// --- 3. Output HTML Dropdown ---
?>
<span class="copy">
<?php if( strpos( $extra, "VIR" ) === false ) { ?>
<Font color='black'><b>Next, choose your <?= getSchoolStr("school") ?>:</b></font><br>
<?php } else { ?>
<Font color='black'><b>Please select Emergency Skills below.</b></font>
<?php } ?>
    <select onChange='updateBuildings( this )' name="<?= htmlspecialchars($fieldname) ?>"  style='font-size: 10px;  font-family: verdana;' id='companyid'>
    <option value=''>Click Down Arrow</option>
    <?php 
$count = 0;
// PHP 8.2 Fix: Use mysqli_fetch_assoc/array and quote keys
while($tmpschool = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
        $count++;
        
        $schoolcode_safe = htmlspecialchars($tmpschool["schoolcode"]);
        $companyname_safe = htmlspecialchars($tmpschool["companyname"]);
        $id_safe = htmlspecialchars($tmpschool["id"]);

        if( $session_iscorp ) {
            $school_name = "{$companyname_safe} ({$schoolcode_safe})";
        } else {
            $school_name = "{$schoolcode_safe} ({$companyname_safe})";
        }

        // Check for selection
        $selected_attr = ($selected == $tmpschool["id"]) ? " SELECTED" : "";
        
        // PHP 8.2 Fix: Use proper closing tag for the option value
        ?>
        <option <?= $selected_attr ?> value='<?= $id_safe ?>'><?= $school_name ?></option>
    <?php } ?>
    </select>