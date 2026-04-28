<?php 
require_once('mysql.php');

if (!isOverallAdmin()) {
    header("Location: /index.php");
    exit;
}

$isdashboard = 1; // Used for ssi/top.php inclusion

// --- 3. Database Query Extensions based on filter type ---
$ext = '';
$type_safe = $type; // Sanitize input once

if (strlen($type_safe) > 1) {
    if ($type_safe === "ADAPT") {
        // Assuming ADAPT maps to campusid '2437'
        $ext = " AND companyid IN (SELECT id FROM company_esi WHERE campusid = '2437')";
    }
} elseif (strlen($type_safe) === 1 && is_numeric($type_safe)) {
    // Assuming type is a numeric value for iscorp (0, 1, 2, 3)
    $type_int = (int)$type_safe;
    $ext = " AND companyid IN (SELECT id FROM company_esi WHERE iscorp = {$type_int})";
}

// --- 4. HTML Output Start ---
include "ssi/top.php";
?> 	 	 	

    <div class="box-header">
        <div class="box-title">Classes</div>
    </div><!--end box-header--><br>

<form>
<select name="type" onChange='document.location.href="outstandingclasses.php?type=" + this.options[this.selectedIndex].value'>
    <option value=''>All</option>
    <?php
    // Options for numeric iscorp types (0, 1, 2, 3)
    for ($i = 0; $i < 4; $i++) {
        $sel = (strlen($type) && (string)$type === (string)$i) ? " selected" : "";
        echo "<option{$sel} value='{$i}'>" . htmlspecialchars(getSessionTypeDisplay($i)) . "</option>";
    }
    
    // Option for ADAPT type
    $i_adapt = "ADAPT";
    $sel_adapt = (strlen($type) && $type === $i_adapt) ? " selected" : "";
    echo "<option{$sel_adapt} value='{$i_adapt}'>" . htmlspecialchars($i_adapt) . "</option>";
    ?>
</select>
</form>

<br>
<!-- --- Classes Not Yet Accepted / Pending --- -->
<b>Classes Not Yet Accepted / Pending:</b><br><br>
<table>
<?php
$cl_pending = db_query_rows("
    SELECT class.id, startdate, companyid, pendingnotes 
    FROM class 
    WHERE deleted = 0 
    AND accepted = 0 
    AND startdate > NOW() 
    {$ext} 
    ORDER BY startdate
");

foreach ($cl_pending as $c) {
    $class_id = $c['id'];
    $company_id = $c['companyid'];
    
    // Check for reschedules count
    $num_reschedules = db_query_first_cell("SELECT COUNT(*) FROM reschedules WHERE classid = {$class_id}");
    
    $is_rescheduled = $num_reschedules > 1;
    $font_color_start = $is_rescheduled ? "<font color='red'>" : "";
    $font_color_end = $is_rescheduled ? "</font>" : "";
    
    $start_date_formatted = date("m/d/Y", strtotime($c['startdate'] ?? ''));
    $company_name = htmlspecialchars(getCompanyName($company_id));
    $pending_notes = htmlspecialchars($c['pendingnotes']);
    
    echo "<tr>";
    echo "<td>{$font_color_start}{$start_date_formatted} <a href=\"class_detail.php?id={$class_id}\">{$font_color_start}{$company_name}{$font_color_end}</a>{$font_color_end}</td>";
    echo "<td>{$font_color_start}{$pending_notes}{$font_color_end}</td>";
    echo "</tr>";
}
?>
</table>

<br> 	 	 	 	
<!-- --- Quick Schedule Classes --- -->
<b>Quick Schedule Classes:</b><br><br>
<table>
<?php 
$cl_quick = db_query_rows("
    SELECT class.id, startdate, companyid, pendingnotes 
    FROM class 
    WHERE deleted = 0 
    AND confirmationnotes LIKE '%Quick Schedule%' 
    AND startdate > NOW() 
    {$ext} 
    ORDER BY startdate
");

foreach ($cl_quick as $c) {
    $class_id = $c['id'];
    $company_id = $c['companyid'];
    
    $start_date_formatted = date("m/d/Y", strtotime($c['startdate']));
    $company_name = htmlspecialchars(getCompanyName($company_id));
    $pending_notes = htmlspecialchars($c['pendingnotes']);
    
    echo "<tr>";
    echo "<td>{$start_date_formatted} <a href=\"class_detail.php?id={$class_id}\">{$company_name}</a></td>";
    echo "<td>{$pending_notes}</td>";
    echo "</tr>";
}
?>
</table>

<br> 	 	 	 	
<!-- --- Classes Complete, But No Roster --- -->
<b>Classes Complete, But No Roster:</b> <br><br>
<?php 
// Calculate date two weeks ago
$two_weeks_ago = date("Y-m-d", strtotime("2 weeks ago"));

// Note: The original query used a hardcoded start date '2020-12-05'. 
// It is preserved here for historical consistency, though usually 'startdate < NOW()' or similar dynamic logic is preferred.
$cl_roster = db_query_rows("
    SELECT class.id, startdate, companyid 
    FROM class 
    WHERE startdate > '2020-12-05' 
    AND startdate < '{$two_weeks_ago}' 
    AND deleted = 0 
    AND accepted = 1 
    AND rosterreceived = 0 
    {$ext} 
    ORDER BY startdate
");

foreach ($cl_roster as $c) {
    $class_id = $c['id'];
    $company_id = $c['companyid'];
    
    $start_date_formatted = date("m/d/Y", strtotime($c['startdate']));
    $company_name = htmlspecialchars(getCompanyName($company_id));
    
    echo "{$start_date_formatted} <a href=\"class_detail.php?id={$class_id}\">{$company_name}</a><br>";
}
?>

<?php include "ssi/footer.php"; ?>