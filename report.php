<?php 

include "mysql.php";

if ($xls) {
    header("Content-type: application/vnd.ms-excel");
    header("Content-Transfer-Encoding: binary");
    
    $user_agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    $filename = "report_".$data.".xls";
    
    if ((is_integer(strpos($user_agent, "msie"))) && (is_integer(strpos($user_agent, "win")))) {
        header("Content-Disposition: filename=" . basename($filename) . ";");
    } else {
        header("Content-Disposition: attachment; filename=" . basename($filename) . ";");
    }
}

// --- Table Mapping ---
$table_name = array(
    'company' => 'company_esi',
    'aed'     => 'aed_esi',
);

$target_table = $table_name[$data]; // Default to company_esi if $data is invalid

// --- Dynamic SQL Building: AED Specific Fields ---
if ($data == "aed") {
    $extra .= "company_esi.borough, address, city, state, zip, ";
    $extratbl = ", company_esi";
    $extrawhere .= " AND company_esi.id = clientid ";
    
    if ($expired) {
        $extrawhere .= " AND ( padaexpiration < NOW() OR padbexpiration < NOW() OR pediatricpads < NOW() OR sparedate < NOW() ) ";
    }
}

// --- Dynamic SQL Building: General Filters (Uses escMe) ---
if ($exportedonly) {
    $extrawhere .= " AND exported = 0 ";
}

// Escaping $session_iscorp before injecting into WHERE clause
$extrawhere .= " AND company_esi.iscorp = '" . escMe($session_iscorp) . "'";

// --- Order By Clause (Uses escMe) ---
if ($ob) {
    // Escaping $ob before injecting into ORDER BY clause
    $orderby = " ORDER BY " . escMe($ob); 
}

// --- Final SQL Query ---
$sql = ("SELECT {$extra} {$target_table}.* FROM {$target_table} {$extratbl} 
         WHERE {$target_table}.deleted = 0 {$extrawhere} {$orderby}");

$result = db_query_rows($sql);

// --- Column Filtering ---
$ignorecols = array(
    "refresherdate", "responderid", "isactive", "clientid", "id", 
    "isheadquarter", "canlogin", "isprimarycontact", "related_company", 
    "branchid", "deleted", "exported"
);

$mddatecols = array("cardissuedate", "cardrenewaldate");

// --- Determine Headings from the first row of data ---
$headings = array();

if (!empty($result)) {
    $first_row = reset($result);
    foreach (array_keys($first_row) as $column_name) {
        if (!in_array($column_name, $ignorecols)) {
 $headings[$column_name] = $column_name;
        }
    }
}

// Manually add company details for AED report
if ($data == "aed") {
    $headings["borough"] = "borough";
    $headings["address"] = "address";
    $headings["city"] = "city";
    $headings["state"] = "state";
    $headings["zip"] = "zip";
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Report</title>
</head>

<body bgcolor="#ffffff">

<table cellpadding="3" cellspacing="0" border="1" width="100%">
    <tr>
    <?php 
    // --- Output Header Row ---
    foreach ($headings as $h => $hdisp) {
        // hdisp is the display name
        echo "<td valign='top'><span class='copy'><strong>" . htmlspecialchars($hdisp) . "</strong></span></td>";
    }
    ?>
    </tr>

    <?php 
    // --- Output Data Rows ---
    foreach ($result as $row) { 
    ?>
    <tr>
        <?php foreach ($headings as $h => $hdisp) {
$disp = $row[$h]; // Safely retrieve value

if ($h == "rcompanyname") {
    // Assumed external function getCompanyName()
    $disp = getCompanyName($row["clientid"]);
}

// Date formatting logic
if (strpos($h, "date") !== false) {
    // Assumed external function fixdatefordisplay()
    $disp = fixdatefordisplay($disp, in_array($h, $mddatecols) ? false : true);
}

// Output cell content
?>
<td valign="top"><span class="copy"><?php echo htmlspecialchars($disp); ?></span></td>
        <?php } ?>
    </tr>
    <?php } ?>
</table>
</body>
</html>