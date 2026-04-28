<?php

include "mysql.php"; 

function db_escape($string) {
    if (is_array($string)) {
return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}


$session_id_safe = db_escape($session_id);
$whr = " AND recertnotes.recertperson = '{$session_id_safe}'";

$sql = "
    SELECT *, CONCAT(schoolcode, companyname) AS longname 
    FROM company_esi, recertnotes 
    WHERE recertnotes.companyid = company_esi.id 
    AND company_esi.iscorp = '{$session_iscorp}' 
    {$whr} 
    ORDER BY nextcalldate, longname
";
$rows = db_query_rows($sql);

// --- 2. CSV Export Logic ---
if ($xls) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="recerts.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Headers
    $headers = array(
        "school code",
        "name",
        "borough",
        "region",
        "call date",
        "notes",
        "created by",
        "assigned to",
        "completed",
        "completed notes"
    );
    fputcsv($output, $headers);

    if (is_array($rows)) {
        foreach ($rows as $r) {
            $next_call_date = $r["nextcalldate"] ?? '';
            $next_call_display = "";
            
            // Check if date is valid (after 2000-01-01)
            if (!empty($next_call_date) && strtotime($next_call_date) > strtotime("2000-01-01")) {
                $next_call_display = fixdatefordisplay($next_call_date, true);
            }
            
            // Get user names with null safety
            $created_by = getUserName($r["recertperson"] ?? 0) ?? '';
            $assigned_to = getUserName($r["assignedto"] ?? 0) ?? '';
            
            // Prepare row data with null safety
            $row_data = array(
                $r["schoolcode"] ?? '',
                $r["companyname"] ?? '',
                $r["borough"] ?? '',
                $r["region"] ?? '',
                $next_call_display,
                $r["recertificationnotes"] ?? '',
                $created_by,
                $assigned_to,
                (!empty($r["completed"])) ? "Yes" : "No",
                $r["completednotes"] ?? ''
            );
            
            fputcsv($output, $row_data);
        }
    }
    
    fclose($output);
    exit;
}
?>

<?php include "ssi/top.php"; ?>
<strong><span class="title"><?php echo htmlspecialchars(getSessionTypeDisplay()); ?> Call Reports</span></strong>
<span class='copy'><strong>Search Results (<?php echo count($rows); ?>)</strong></span>
<form action="viewcompany.php">
<table class='table3' cellpadding='2' border='1' cellspacing='0'>
<tr><th class='copy'><?php echo htmlspecialchars(getSchoolStr("School")); ?> Code</th>
<th class='copy'>Name</th>
<th class='copy'>Borough</th>
<th class='copy'>Region</th>
<th class='copy'>Call Date</th>
<?php $is_admin = ($session_userid == "sarahg@emergencyskills.com");
if ($is_admin) { ?>
<th class='copy'>Notes</th>
<th class='copy'>Created By</th>
<th class='copy'>Assigned To</th>
<th class='copy'>Completed</th>
<th class='copy'>Completed Notes</th>

<?php } ?>
</tr>

<?php foreach ($rows as $row) {
    $next_call_date = $row['nextcalldate'];
    $company_id = (int)($row['companyid']);
    $school_code = htmlspecialchars($row['schoolcode']);
    $company_name = htmlspecialchars($row['companyname']);
    $borough = htmlspecialchars($row['borough']);
    $region = htmlspecialchars($row['region']);
    $notes = htmlspecialchars($row['recertificationnotes']);
    $completed_notes = htmlspecialchars($row['completednotes']);
    $recert_person_id = $row['recertperson'];
    $assigned_to_id = $row['assignedto'];
    $completed_status = (int)($row['completed']);
    
    $bg = "";
    if (strtotime($next_call_date) < time()) {
        $bg = "bgcolor='#ffcccc'"; // Highlight overdue calls
    }
    
    $date_display = (strtotime($next_call_date) > strtotime("2000-01-01")) 
         ? fixdatefordisplay($next_call_date, true) 
         : "&nbsp;";
    ?>
<tr <?php echo $bg; ?>>
    <td class='copy'>
        <a href='viewcompany.php?id=<?php echo $company_id; ?>'><?php echo $school_code; ?></a>
        <a href='editrecertnotes.php?id=<?php echo $company_id; ?>'>Edit</a>
    </td>
    <td class='copy'>
<a href='editcompany.php?id=<?php echo $company_id; ?>'><?php echo $company_name; ?></a>
    </td>
    <td class='copy'><?php echo $borough; ?></td>
    <td class='copy'><?php echo $region; ?></td>
    <td class='copy'><?php echo $date_display; ?></td>
<?php if ($is_admin) { ?>
    <td class='copy'><?php echo $notes; ?></td>
    <td class='copy'><?php echo htmlspecialchars(getUserName($recert_person_id)); ?></td>
    <td class='copy'><?php echo htmlspecialchars(getUserName($assigned_to_id)); ?>&nbsp;</td>
    <td class='copy'><?php echo $completed_status ? "Yes" : "No"; ?>&nbsp;</td>
    <td class='copy'><?php echo $completed_notes; ?>&nbsp;</td>
<?php } ?>
</tr>
<?php } // End row loop ?>
</table>
<?php include "ssi/footer.php"; ?>
       
