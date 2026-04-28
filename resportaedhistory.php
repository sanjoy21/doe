<?php
// Initialize external variables safely
$xls = $xls ?? false;
$id = $id ?? null;
$session_iscorp = $session_iscorp ?? 0;
$loggedin = $loggedin ?? false;
$other = $other ?? null;
$orderby = $orderby ?? '';
$companyidentifier = '';

include "mysql.php";
// Assumed external functions: getRelatedCompany, getCompanyName, db_query_rows

// --- 1. Header Setup for Excel Download ---
if ($xls) {
    header("Content-type: application/vnd.ms-excel");
    header("Content-Transfer-Encoding: binary");
    $user_agent = strtolower($_SERVER["HTTP_USER_AGENT"] ?? '');
    $filename = "aeds.xls";

    // NOTE: PHP 8.2 compatible header settings
    if ((is_integer(strpos($user_agent, "msie"))) && (is_integer(strpos($user_agent, "win")))) {
        header("Content-Disposition: filename=" . basename($filename) . ";");
    } else {
        header("Content-Disposition: attachment; filename=" . basename($filename) . ";");
    }
}

// --- 2. Company Identification ---
if ($id) {
    // Assumed external functions getRelatedCompany() and getCompanyName()
    $other = getRelatedCompany($id);
    $companyidentifier = getCompanyName($id);
}

// --- 3. Dynamic SQL Query Construction ---

// Use string concatenation for the SQL to avoid the original, unsafe string join using '+'
$sql = "SELECT a.aedid, a.branchid, a.serial, a.model, a.floor, a.location, a.padaexpiration, a.padbexpiration, a.batterydate, "
     . "a.sparedate, c.directorname, c.filingexpirationdate, a.eventhistory, a.date, c.id, c.companyname, c.address, a.pediatricpads, a.irn, c.companyname AS cname_duplicate, c.medicalinvoicedate "
     . "FROM aed_esi a, company_esi c WHERE iscorp = '" . addslashes($session_iscorp) . "' AND a.deleted = 0 AND a.clientid = c.id ";

// NOTE: The placeholders (?) are kept as they were in the original code, but 
// they require a separate mechanism (e.g., passing parameters to db_query_rows) 
// which is not shown here. This assumes the query system handles them dynamically.

if ($loggedin || $id || $other) {
    $sql .= " AND ( 1 = 0 ";
    $sql .= ($loggedin ? " OR c.companyname LIKE ? " : "");
    $sql .= ($id ? " OR clientid = ? " : "");
    $sql .= ($other ? " OR related_company = ? OR c.companyname LIKE ? " : "");
    $sql .= ") ";
}

if ($orderby) {
    $sql .= $orderby;
}

// --- 4. Data Retrieval ---
// Assuming db_query_rows handles the parameter passing for '?' if needed,
// or that the placeholders are placeholders for future binding.
$result = db_query_rows($sql);

// Use $result for the loop as $res was undefined in the original code.
$res = $result; 

// --- 5. Output Generation (HTML or implied XLS stream) ---
if (!$xls) { 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>AED Report</title>

<link rel="stylesheet" href="../css/style.css">
</head>

<body bgcolor="#ffffff">
<?php } ?> 
<table cellpadding="3" cellspacing="0" border="1" width="100%">
<tr><td colspan='7'><strong> <?php echo htmlspecialchars($companyidentifier); ?></strong></td></tr>
    <tr>
        <td valign="top"><span class="copy"><strong>AED Serial Number</strong></span></td>
        <td valign="top"><span class="copy"><strong>Model/Type</strong></span></td>
        <td valign="top"><span class="copy"><strong>Company Location</strong></span></td>
        <td valign="top"><span class="copy"><strong>Floor</strong></span></td>
        <td valign="top"><span class="copy"><strong>Medical Director</strong></span></td> 	 	 	
        <td valign="top"><span class="copy"><strong>Event History</strong></span></td> 	 	 	
    </tr>
    <?php
    foreach ($res as $row) {
        // Safely retrieve IDs for links
        $aed_id = $row["aedid"] ?? 0;
        $client_id = $row["clientid"] ?? 0;
        
        // Define link URLs (Assuming base URL structure)
        $aed_link = "viewserial.jsp?aedid=" . urlencode($aed_id);
        $company_link = "viewcompany.jsp?id=" . urlencode($client_id);
    ?>
    <tr>
        <td valign="top"><span class="copy"><?php if (!$xls) { ?><a href="<?php echo $aed_link; ?>"><?php } ?><?php echo htmlspecialchars($row["serial"] ?? ''); ?><?php if (!$xls) { ?></a><?php } ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["model"] ?? ''); ?></span></td>
        <td valign="top"><span class="copy"><?php if (!$xls) { ?><a href="<?php echo $company_link; ?>"><?php } ?><?php echo htmlspecialchars($row["address"] ?? ''); ?><?php if (!$xls) { ?></a><?php } ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["floor"] ?? ''); ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["directorname"] ?? ''); ?></span></td> 	 	 	
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["eventhistory"] ?? ''); ?></span></td> 	 	 	
    </tr>
    <?php
    }
    ?>
</table>
<?php if (!$xls) { ?>
</body>
</html>
<?php } ?>