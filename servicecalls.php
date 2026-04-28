<?php
// 465-3637
require_once('mysql.php');
// Assumed external functions: db_query, getClassRow, getCompanyRow, getSchoolStr, getCompanyName

// Initialize external variables safely
$update = $_POST['update'] ?? null;
$invoiceno = $_POST['invoiceno'] ?? [];
$specialadmin = $specialadmin ?? false;
$lname = $_POST['lname'] ?? '';
$inspector = $_POST['inspector'] ?? '';
$iscorp = $_POST['iscorp'] ?? -1;
$actionneeded = $_POST['actionneeded'] ?? '';
$qainspection = $_POST['qainspection'] ?? '';
$year = $_POST['year'] ?? null;
$search = $_POST['search'] ?? null;

// --- Invoice Update Logic ---
if ($update) {
    foreach ($invoiceno as $id => $val) {
        $id = (int)$id;
        $val = trim($val);
        
        if (!empty($val)) {
            $val_safe = db_escape($val); // Assumed function for SQL escaping
            db_query("UPDATE servicecall SET invoiced = 1, invoiceno = '{$val_safe}' WHERE servicecallid = {$id}"); 
        } else {
            db_query("UPDATE servicecall SET invoiced = 0, invoiceno = '' WHERE servicecallid = {$id}"); 
        }
    } 	
}

// --- Access Control ---
if (!$specialadmin) {
    header("location: login.php");
    exit;
}

// --- Dynamic SQL Filtering ---
$extra = '';
if (!empty($lname)) {
    $extra .= " AND servicecallid = '" . db_escape($lname) . "'";
}
if (!empty($inspector)) {
    $extra .= " AND inspector LIKE '%" . db_escape($inspector) . "%'";
}
if ($iscorp > -1) {
    $extra .= " AND iscorp = '" . (int)$iscorp . "'";
}
if ($actionneeded) {
    $extra .= " AND actionneeded = '1'";
}
if ($qainspection) {
    $extra .= " AND qainspection = '1'";
}

// --- School Year Filtering ---
if (!$year) {
    $current_year = (int)date("Y");
    // Determine the start year of the current school year (Sept 1 - Aug 31)
    if (date("m") < 9) { 
        $year = $current_year - 1;
    } else {
        $year = $current_year;
    }
}
$year = (int)$year;
$next_year = $year + 1;

$extrajoin = " LEFT JOIN company_esi ON company_esi.id = companyid";
// Filter by servicecalldate within the school year (Sept 1st to Aug 31st of next year)
$extra .= " AND ( ( servicecalldate > '{$year}-09-01' AND servicecalldate < '{$next_year}-09-01' ) OR servicecalldate IS NULL OR servicecalldate = '0000-00-00' )";

// --- Fetch Service Calls ---
$sql = "SELECT servicecall.* FROM servicecall {$extrajoin} WHERE 1 {$extra} ORDER BY servicecallid";
$trainers = db_query_rows($sql); // $trainers is a potentially confusing name here, it holds service calls
?>
<?php include "ssi/top.php"; ?> 	 	 	 	
<p>
    	 	 	 
    	 	 	 	 	 <strong><span class="title">MANAGE SERVICE CALLS</span></strong>
    	 	 	 
    	 	 	 <p>
    	 	 	 <form method='post'>
    	 	 	 <span class='copy'>
    	 	 	     Search (school year): 
    	 	 	     <select name='year'>
<?php for ($i = 2006; $i <= (int)date("Y"); $i++) { ?>
<option value='<?php echo $i; ?>' <?php echo ($year == $i ? "SELECTED" : ""); ?>><?php echo $i; ?> - <?php echo $i + 1; ?></option>
<?php } ?>
</select> 
ID : <input type='text' name='lname' class='copy' size='6' value="<?php echo htmlspecialchars($lname); ?>"> 
<input class='copy' type='submit' name='search' value='Search'><br>
Inspector: <input type='text' name='inspector' value='<?php echo htmlspecialchars($inspector); ?>'> 
<input type='checkbox' name='actionneeded' value='1' <?php echo $actionneeded ? "checked" : ""; ?>> Action Needed Only? 
<input type='checkbox' name='qainspection' value='1' <?php echo $qainspection ? "checked" : ""; ?>> QA Inspection Only?<br>
<select name='iscorp'>
<option value='-1'>Both</option> 
<option <?php echo (isset($iscorp) && (string)$iscorp === '0' ? "SELECTED" : ""); ?> value='0'>DOE Only</option> 
<option <?php echo (isset($iscorp) && (string)$iscorp === '1' ? "SELECTED" : ""); ?> value='1'>Corp Only</option> 
</select>
<br>
<br>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" 	class="table3">
    	 	 	 	 	 <tr bgcolor="#e1e1f6">
    	 	 	 	 	     <th class='copy'>ID / Worksheet</th>
    	 	 	 	 	     <th class='copy'>Service Call Date</th>
    	 	 	 	 	     <th class='copy'><?php echo getSchoolStr("School"); ?></th>
    	 	 	 	 	     <th class='copy'>Invoice #</th>
    	 	 	 	 	 </tr>
<?php 
if (is_array($trainers)) {
    foreach ($trainers as $t) {
        $company_id = (int)($t['companyid'] ?? 0);
        $service_call_id = (int)($t['servicecallid'] ?? 0);
        $app_id = (int)($t['appid'] ?? 0);
        
        $crow = getCompanyRow($company_id);
        $is_deleted = $crow["deleted"] ?? 0;
        
        $row_color = $is_deleted ? "FFccccc" : "#FFFFFF";
        
        echo "<tr bgcolor='{$row_color}'><td class='copy' valign='top'>";
        echo "<a href='editservicecall.php?servicecallid={$service_call_id}'>S{$service_call_id}</a>";
        
        if ($app_id) {
            echo "<br><a href='appservicecall.php?id={$app_id}'>Worksheet</a>";
        }
        
        echo "</td><td class='copy'>" . htmlspecialchars($t['servicecalldate'] ?? 'N/A') . "</td> ";
        echo "<td class='copy'><a href='viewcompany.php?id={$company_id}'>" . htmlspecialchars(getCompanyName($company_id)) . "</a></td>";
        echo "<td class='copy'><input class='copy' type='text' size='7' name='invoiceno[{$service_call_id}]' value='" . htmlspecialchars($t['invoiceno'] ?? '') . "'></td>";
        echo "</tr>";
    }
}
?>
</table><p>
<input type='submit' name='update' value='Update'><br><br><br>
    	 	 	 <?php include "ssi/footer.php"; ?>