<?php
// Include the database connection file
include "mysql.php";

// --- Date Calculation using DateTime objects (Modern PHP) ---
$now = new DateTime();
$dt_obj = (clone $now)->sub(new DateInterval('P2Y3M')); // 2 years and 3 months ago
$today_obj = (clone $now)->sub(new DateInterval('P2Y')); // 2 years ago

$dt = $dt_obj->format("Y-m-d");
$today = $today_obj->format("Y-m-d");

// --- Permission and Filtering Logic ---
$rstr = "";

// Assumed external function getRegionDisp()
$region = getRegionDisp($thisusersrow["visibleregion"]);
if ($region && $region != "''") {
    // Note: The original SQL assumes $region contains a quoted, comma-separated list like "'NY','NJ'"
    $rstr = " AND region IN ({$region})";
}

// Assumed external function getDistrictString()
if ($thisusersrow["districts"]) {
    $rstr .= getDistrictString($thisusersrow["districts"]);
}

// --- Main SQL Query ---
// Filters companies where no responder has training more recent than 2 years and 3 months ($dt)
$sql = "SELECT 
            c.id, 
            COUNT(r.responderid) AS numresponders, 
            SUM(CASE WHEN rt.trainingdate > '{$dt}' THEN 1 ELSE 0 END) AS numdates, 
            SUM(CASE WHEN rt.trainingdate > '{$today}' THEN 1 ELSE 0 END) AS numcurrent, 
            c.* FROM company_esi c 
        LEFT JOIN responders_esi r ON r.clientid = c.id AND r.deleted = 0 
        LEFT JOIN responder_training_dates rt ON r.responderid = rt.responderid 
        WHERE 
            iscorp = '" . escMe((string)$session_iscorp) . "' AND 
            c.deleted = 0 AND 
            c.retired = 0 AND 
            c.donotinclude = 0 
            {$rstr} 
        GROUP BY c.id 
        HAVING numdates = 0 
        ORDER BY schoolcode, companyname";

// $schools is an array of rows indexed by 'id'
$schools = db_query_rows($sql, "id");

// --- Excel/CSV Output Mode ---
if ($xls) {
    // Instead of using deprecated Spreadsheet_Excel_Writer, we use standard CSV output.
    $filename = "expired_" . $now->getTimestamp() . ".csv";

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // CSV Header Row
    $header = [
        "School Code", "School Name", "CFN", "Num Current Responders", 
        "Latest Training Date", "Next Training Date", "Principal Name", "Principal Email"
    ];
    fputcsv($output, $header);

    foreach ($schools as $sid => $srow) {
        // Assumed external function getCompanyRow()
        $crow = getCompanyRow($sid); 

        // Get Latest Training Date
        $maxdate = db_query_first_cell("SELECT MAX(rtd.trainingdate) FROM responder_training_dates rtd, responders_esi r WHERE rtd.responderid = r.responderid AND clientid = " . intval($crow['id']));
        $maxdate_formatted = $maxdate ? date("m/d/Y", strtotime($maxdate)) : "";

        // Get Next Training Date
        $sql_next_class = "SELECT id, startdate FROM class WHERE companyid = " . intval($sid) . " AND startdate > NOW() AND accepted = 1 AND deleted = 0 ORDER BY startdate LIMIT 1";
        $classdata = db_query_first($sql_next_class);
        
        // Assumed external function getFormattedDateWTime()
        $sd = $classdata ? getFormattedDateWTime($classdata['startdate']) : "";
        
        // CSV Data Row
        $data_row = [
            $crow['schoolcode'],
            $crow['companyname'],
            $crow['cfn'],
            $srow['numcurrent'],
            $maxdate_formatted,
            $sd,
            $crow['principalname'],
            $crow['principalemail']
        ];
        
        fputcsv($output, $data_row);
    } 
    
    fclose($output);
    exit;

} else {
    // --- HTML Output Mode ---
    $i = 1;
    echo ("<table border=1>");
    echo ("<tr><td>ID</td><td>School Code</td><td>School Name</td><td>CFN</td>");
    echo ("<td>Num Current Responders</td>");
    echo ("<td>Latest Training Date</td>");
    echo ("<td>Next Training Date</td>");
    echo ("<td>Principal Name</td>");
    echo ("<td>Principal Email</td></tr>");

    foreach ($schools as $sid => $srow) {
        $crow = getCompanyRow($sid);

        // Get Latest Training Date
        $maxdate = db_query_first_cell("SELECT MAX(rtd.trainingdate) FROM responder_training_dates rtd, responders_esi r WHERE rtd.responderid = r.responderid AND clientid = " . intval($crow['id']));
        $maxdate_formatted = $maxdate ? date("m/d/Y", strtotime($maxdate)) : "";

        // Get Next Training Date
        $sql_next_class = "SELECT id, startdate FROM class WHERE companyid = " . intval($sid) . " AND startdate > NOW() AND accepted = 1 AND deleted = 0 ORDER BY startdate LIMIT 1";
        $classdata = db_query_first($sql_next_class);
        $sd = $classdata ? getFormattedDateWTime($classdata['startdate']) : "";

        // HTML Output
        echo ("<tr>");
        echo ("<td><a href='viewcompany.php?id=" . htmlspecialchars($sid) . "'>" . htmlspecialchars($sid) . "</a></td>");
        echo ("<td><a href='viewcompany.php?id=" . htmlspecialchars($sid) . "'>" . htmlspecialchars($crow['schoolcode']) . "&nbsp;</a></td>");
        echo ("<td>" . htmlspecialchars($crow['companyname']) . "</td>");
        echo ("<td>" . htmlspecialchars($crow['cfn']) . "</td>");
        echo ("<td>" . htmlspecialchars($srow['numcurrent']) . "</td>");
        echo ("<td>" . htmlspecialchars($maxdate_formatted) . "</td>");
        echo ("<td>" . htmlspecialchars($sd) . "</td>");
        echo ("<td>" . htmlspecialchars($crow['principalname']) . "</td>");
        echo ("<td>" . htmlspecialchars($crow['principalemail']) . "</td>");
        echo ("</tr>");
        $i++;
    } 
    echo ("</table>");
}
?>