<?php

include "mysql.php";

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// Initialize request variables
$doit = $_REQUEST['doit'] ?? null;
$fieldfrom = $_REQUEST['fieldfrom'] ?? null;
$fieldto = $_REQUEST['fieldto'] ?? null;
$since = $_REQUEST['since'] ?? null;
$xls = $_REQUEST['xls'] ?? null;
$concat = $_REQUEST['concat'] ?? null;
$notcompleted = $_REQUEST['notcompleted'] ?? null;

// Assuming these are defined elsewhere or passed in via session
// $thisusersrow = $_SESSION['thisusersrow'] ?? ['usertype' => '', 'userid' => 0];
// $session_iscorp = $_SESSION['session_iscorp'] ?? 0;


if ($doit) { 
    $thetable = "drill";
    $table = "drill";
    $otherfields = array("drillid", "participants", "score", "nextdate", "comments", "schoolcode", "zip", "serial", "inspector", "lastnotified");
    $extrafields = ", schoolcode, zip";

    if ($concat) {
        $extrafields .= ", group_concat(' ', serial) as serial ";
    } else {
        $extrafields .= ", serial";
    }

    $datefield = $thetable . "date";
    $extra = "";
    
    // Sanitize dates for SQL
    if ($fieldfrom) {
        $tm = fixdate($fieldfrom); 
        $extra .= " AND {$datefield} >= '" . db_escape($tm) . "' ";
    } 
    if ($fieldto) {
        $tm = fixdate($fieldto); 
        $extra .= " AND {$datefield} <= '" . db_escape($tm) . "' ";
    }

    $lj = " LEFT JOIN aed_to_drill ats ON ats.drillid = t.drillid ";
    $swhr = "";
    
    if ($since) {
        $swhr = " AND {$datefield} > '" . date("Y-m-d", strtotime(db_escape($since))) . "'";
    }

    if ($thisusersrow["usertype"] == "trainer") {
        // $visi is assumed to be defined elsewhere for trainer visibility filter
        $extra .= $visi ?? ''; 
    }

    if ($concat) {
        $extra .= " GROUP BY drillid ";
    }
    
    // Construct the main SQL query
    $sql = "
        SELECT 
            t.*, companyname, address, city, borough, principalname, 
            contactphone, contactname, contactemail, schoolcode {$extrafields} 
        FROM 
            company_esi, {$table} t {$lj} 
        WHERE 
            iscorp = '" . db_escape($session_iscorp) . "' 
            AND completed = " . ($notcompleted ? 0 : 1) . " 
            AND companyid = company_esi.id 
            AND showsondrillreports = 1 
            AND lastnotified > '0000-00-00' 
            {$swhr} {$extra} 
        ORDER BY 
            {$datefield}
    ";
    
    $res = db_query_rows($sql);

    if ($xls) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report_' . htmlspecialchars($table) . '.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Headers
    $headers = array(
        "school",
        "schoolcode",
        "address",
        "telephone number",
        "principal name",
        "aed contact name",
        "aed contact email",
        "drill date",
        "reason for failure",
        "ESI Recommendations and Remediation",
        "DOE Comments"
    );
    fputcsv($output, $headers);

    foreach ($res as $r) {
        // Build address with null safety
        $address_parts = array();
        if (!empty($r["address"])) $address_parts[] = $r["address"];
        if (!empty($r["city"])) $address_parts[] = $r["city"];
        if (!empty($r["zip"])) $address_parts[] = $r["zip"];
        if (!empty($r["borough"])) $address_parts[] = $r["borough"];
        $address = implode(", ", $address_parts);
        
        // Determine reason for failure
        $reason = "";
        if (!empty($r["refused"])) {
            $reason = "Refused Drill";
        }
        if (!empty($r["needsmoreaed"])) {
            $reason = "Building Requires Additional AED";
        }
        if (!empty($r["failedother"])) {
            $other_reason = $r['other'] ?? '';
            $reason = "Other: " . $other_reason;
        }
        
        // Fetch comments with proper escaping
        $drill_id = db_escape($r['drillid'] ?? '');
        $comm_sql = "SELECT comment FROM drillcomments WHERE drillid = '" . $drill_id . "'";
        $comm_rows = db_query_rows($comm_sql) ?? array();
        $comm = array();
        foreach ($comm_rows as $comm_row) {
            if (!empty($comm_row['comment'])) {
                $comm[] = $comm_row['comment'];
            }
        }
        $doe_comments = implode("; ", $comm);
        
        // Prepare row data with null safety
        $row_data = array(
            $r["companyname"] ?? '',
            $r["schoolcode"] ?? '',
            $address,
            $r["contactphone"] ?? '',
            $r["principalname"] ?? '',
            $r["contactname"] ?? '',
            $r["contactemail"] ?? '',
            $r[$datefield] ?? '',
            $reason,
            $r["comments"] ?? '',
            $doe_comments
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
    else {
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr><th class='copy'>school</th><th class='copy'>schoolcode</th>
<th class='copy'>address</th>
<th class='copy'>telephone number</th>
<th class='copy'>principal name</th>
<th class='copy'>aed contact name</th>
<th class='copy'>aed contact email</th>
<th class='copy'>drill date</th>
<th class='copy'>reason for failure</th>
<th class='copy'>ESI Recommendations and Remediation</th>
<th class='copy'>DOE Comments</th></tr>
<?php
foreach ($res as $r) {
    $reason = "";
    if ($r["refused"]) {
        $reason = "Refused Drill";
    }
    if ($r["needsmoreaed"]) {
        $reason = "Building Requires Additional AED";
    }
    if ($r["failedother"]) {
        $reason = "Other: " . ($r['other'] ?? '');
    }
    
    // Fetch comments
    $comm_sql = "SELECT comment FROM drillcomments WHERE drillid = '" . db_escape($r['drillid']) . "'";
    $comm_rows = db_query_rows($comm_sql);
    $comm = array_column($comm_rows, 'comment');
?>
<tr>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?= $r["companyid"] ?>'><?= htmlspecialchars($r["companyname"]) ?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?= $r["companyid"] ?>'><?= htmlspecialchars($r["schoolcode"]) ?></a></td>
<td valign='top' class='copy'><?= htmlspecialchars($r['address'] ?? '') ?>, <?= htmlspecialchars($r['city'] ?? '') ?>, <?= htmlspecialchars($r['zip'] ?? '') ?>, <?= htmlspecialchars($r['borough'] ?? '') ?></td>
<td valign='top' class='copy'><?= htmlspecialchars($r["contactphone"]) ?></td>
<td valign='top' class='copy'><?= htmlspecialchars($r["principalname"]) ?></td>
<td valign='top' class='copy'><?= htmlspecialchars($r["contactname"]) ?></td>
<td valign='top' class='copy'><?= htmlspecialchars($r["contactemail"]) ?></td>
<td valign='top' class='copy'><?= htmlspecialchars($r[$datefield]) ?></td>
<td valign='top' class='copy'><?= htmlspecialchars($reason) ?></td>
<td valign='top' class='copy'><?= htmlspecialchars($r["comments"]) ?></td>
<td valign='top' class='copy'><?= htmlspecialchars(join("; ", $comm)) ?></td>
</tr>
<?php } ?>
</table>
    <?php } ?>
        
        <br><br><br>
        <!--end center content-->
        <!--end center content-->
        
        <?php include "ssi/footer.php"; ?>
        
        <!--end footer-->
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
    </tr>
</table>
<br><br>
</div>
</body>
</html>
<?php } else { ?>
<form method='post'>
Failed Drills Since: <input type='text' name='since' value="<?= htmlspecialchars($since) ?>"><br>
XLS: <input type='checkbox' name='xls' value='1' <?= $xls ? "CHECKED" : "" ?>><br>
All Drills From One School On One Line: <input type='checkbox' name='concat' <?= $concat ? "CHECKED" : "" ?> value='1' CHECKED><br>
<input type='submit' name='doit' value='Go'>
</form>
<?php } ?>