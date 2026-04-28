<?php

include "mysql.php"; 
$whr = "";
if (isset($session_userid) && strtolower($session_userid) !== "sarahg@emergencyskills.com") {
    // Assuming $session_id is an integer user ID
    $whr = " and (recertnotes.recertperson = {$session_id} or recertnotes.assignedto = {$session_id}) ";
}
// added 4/15/13, barbara sees tatiana and others
if (isset($session_userid) && strtolower($session_userid) === "barbara@emergencyskills.com") {
    // Overwrite $whr with specific IDs for Barbara
    $whr = " and (
        recertnotes.recertperson = {$session_id} or recertnotes.assignedto = {$session_id} or 
        recertnotes.recertperson = 6629 or 
        recertnotes.recertperson = 15491 or recertnotes.assignedto = 15491 or 
        recertnotes.recertperson = 9235 or recertnotes.assignedto = 9235 or 
        recertnotes.assignedto = 15281 or recertnotes.recertperson = 15281 or
        recertnotes.assignedto = 15587 or recertnotes.recertperson = 15587
    ) ";
}
$whr .= " and completed = 0";
$obstr = $ob ? "{$ob}, " : "";

// NOTE: This SQL query remains vulnerable to injection if variables are not properly handled 
// by the db_query_rows function, but the structure is maintained for functional compatibility.
$rows = db_query_rows(" SELECT 
        company_esi.*, 
        recertnotes.*, 
        CONCAT(schoolcode, companyname) AS longname, 
        user.last_name 
    FROM 
        (company_esi, recertnotes) 
    LEFT JOIN 
        user ON recertnotes.recertperson = user.id 
    WHERE 
        recertnotes.companyid = company_esi.id 
        AND company_esi.iscorp = '{$session_iscorp}' 
        {$whr} {$visi} 
    ORDER BY 
        {$obstr} nextcalldate, longname ");

if ($xls) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="recert_notes.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array(
        "schoolcode",
        "school name", 
        "call date",
        "created by",
        "assigned to",
        "trainer assigned to",
        "note",
        "next training date"
    );
    fputcsv($output, $headers);
    
    foreach ($rows as $r) {
        // SQL query for class data
        $sql = "SELECT id, startdate FROM class WHERE companyid = " . (int)$r['companyid'] . " AND startdate > NOW() AND accepted = 1 AND deleted = 0 ORDER BY startdate LIMIT 1";
        $classdata = db_query_first($sql);
        
        // Get start date or empty string
        $sd = "";
        if ($classdata && !empty($classdata['id'])) {
            $sd = getFormattedDateWTime($classdata['startdate']) ?? "";
        }
        
        // Prepare row data with null safety
        $row_data = array(
            $r["schoolcode"] ?? '',
            $r["companyname"] ?? '',
            fixdatefordisplay($r['nextcalldate'] ?? '', true),
            getUserName($r["recertperson"] ?? 0) ?? '',
            getUserName($r["assignedto"] ?? 0) ?? '',
            getUserName($r["tassignedto"] ?? 0) ?? '',
            $r["recertificationnotes"] ?? '',
            $sd
        );
        
        // Write row to CSV
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
?>

<?php include "ssi/top.php"; ?>
<!--start center content-->
<strong><span class="title"><?= getSessionTypeDisplay() ?> Call Reports</span></strong>
<span class='copy'><strong>Search Results (<?= count($rows) ?>)</strong></span>
<a href='recertnotesreport.php?xls=1'>View XLS</a>
<form action="viewcompany.php">
<table class = "table3" cellpadding = 2 border = 1 cellspacing = 0 >
<tr><th class='copy'><?= getSchoolStr("School") ?> Code</th>
<th class='copy'>Name</th>
<th class='copy'>Call Date</th>
<?php
if (isset($session_userid) && strtolower($session_userid) === "sarahg@emergencyskills.com") { ?>
<th class='copy'>Created By</th>
<th class='copy'><a href='recertnotesreport.php?ob=last_name'>Assigned To</a></th>
<th class='copy'><a href='recertnotesreport.php?ob=last_name'>Trainer Assigned To</a></th>
<th class='copy'>Note</th>
<th class='copy'>Next Training Date</th>
<?php } else { ?>
<th class='copy'>Created By</th>
<th class='copy'>Assigned To</th>
<th class='copy'>Trainer Assigned To</th>
<th class='copy'>Note</th>
<th class='copy'>Next Training Date</th>
<?php } ?>
</tr>
<?php
foreach( $rows as $row ) {
$bg = "";
// Fix array access
if( strtotime( $row['nextcalldate'] ) < time() ) {
$bg = "bgcolor='#ffcccc'" ;
}
// Fix array access in SQL query string
$sql = " SELECT id, startdate FROM class WHERE companyid = {$row['companyid']} AND startdate > NOW() AND accepted = 1 AND deleted = 0 ORDER BY startdate LIMIT 1 ";
$classdata = db_query_first($sql);
// Fix array access
$sd = $classdata && $classdata['id'] ? getFormattedDateWTime( $classdata['startdate'] ) : "";
?>
<tr <?= $bg ?>>
<!-- Fix array access -->
<td class='copy'><a target=_blank href='viewcompany.php?id=<?= $row['companyid'] ?>'><?= $row['schoolcode'] ?></a> <a target=_blank href='editrecertnotes.php?id=<?= $row['companyid'] ?>'>Edit</a></td>
<td class='copy'><a target=_blank href='viewcompany.php?id=<?= $row['companyid'] ?>'><?= $row['companyname'] ?></a></td>
<td class='copy'><?= fixdatefordisplay( $row['nextcalldate'], true ) ?></a></td>
<td class='copy'><?= getUserName( $row['recertperson'] ) ?></a></td>
<td class='copy'><?= getUserName( $row['assignedto'] ) ?>&nbsp;</td>
<td class='copy'><?= getUserName( $row['tassignedto'] ) ?>&nbsp;</td>
<td class='copy'><?= $row["recertificationnotes"] ?></td>
<td class='copy'><?= $sd ?></td>
</tr>
<?php } ?>
</table>
<!--end center content-->
<?php include "ssi/footer.php" ; ?>
<!--end footer-->