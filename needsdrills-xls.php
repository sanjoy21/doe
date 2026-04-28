<?php

$nologinrequired = true;
include "mysql.php";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="needsdrills.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

$arr = array(); // Stores schools needing immediate drills
$sherryarr = array(); // Stores schools needing drills within two weeks (or overdue)

// Get all active trainers
$res = db_query_rows("SELECT * FROM user WHERE usertype = 'trainer' AND inactive = 0");

$currdate = time();
// Time six months (approx 24 weeks) in the future for checking due date
$six_months_ago_timestamp = mktime(0, 0, 0, date("m", $currdate) - 6, date("d", $currdate), date("Y", $currdate));
// Time two weeks in the future, used for 'sherryarr' tracking
$twoweeks_timestamp = mktime(0, 0, 0, date("m", $currdate), date("d", $currdate) + 14, date("Y", $currdate));

foreach ($res as $trow) {
    $zips = explode(",", getZips($trow));
    
    // Iterate through all zips covered by this trainer
    foreach ($zips as $zip) {
        $zip = trim($zip);
        if (empty($zip)) continue; // Skip empty zips
        
        // Find schools in the trainer's ZIP area that are active (summer=1 seems to indicate activity)
        // Note: $session_iscorp should be defined elsewhere
        $schools = db_query_rows("
            SELECT * FROM company_esi 
            WHERE iscorp = '{$session_iscorp}' 
            AND deleted = 0 
            AND summer = 1 
            AND zip = '{$zip}'
        ");

        foreach ($schools as $srow) {
            // Find completed drills for the school (joining drill and drill_to_companyid)
            $drills = db_query_rows("
                SELECT 
                    drill.drilldate
                FROM 
                    drill 
                LEFT JOIN 
                    drill_to_companyid dtc ON drill.drillid = dtc.drillid 
                WHERE 
                    completed = 1 
                    AND (drill.companyid = {$srow['id']} OR dtc.companyid = {$srow['id']}) 
                ORDER BY 
                    drilldate DESC
            ");
            
            // Case 1: No previous drills found
            if (!$drills) {
                $trainer_name = ($trow['first_name'] ?? '') . " " . ($trow['last_name'] ?? '');
                $arr[] = array(
                    $srow['companyname'] ?? '', 
                    $srow['schoolcode'] ?? '', 
                    $trainer_name, 
                    "N/A - No Previous Drill"
                );
                $sherryarr[] = array(
                    $srow['companyname'] ?? '', 
                    $srow['schoolcode'] ?? '', 
                    $trainer_name, 
                    "N/A - No Previous Drill"
                );
            }
            // Case 2: Check the most recent drill date against the 6-month threshold
            else {
                // Get the most recent drill date
                $drow = array_shift($drills);
                $drill_date_str = $drow['drilldate'] ?? '';

                if ($drill_date_str !== '0000-00-00' && $drill_date_str !== '') {
                    $currdate_drill = strtotime($drill_date_str);
                    $next_due_date = mktime(0, 0, 0, date("m", $currdate_drill) + 6, date("d", $currdate_drill), date("Y", $currdate_drill));
                    $formatted_drill_date = date("m/d/Y", $currdate_drill);

                    // A) Needs a drill (overdue - due date is in the past)
                    if ($next_due_date < time()) {
                        $trainer_name = ($trow['first_name'] ?? '') . " " . ($trow['last_name'] ?? '');
                        $arr[] = array(
                            $srow['companyname'] ?? '', 
                            $srow['schoolcode'] ?? '', 
                            $trainer_name, 
                            $formatted_drill_date
                        );
                    }

                    // B) Needs a drill within the next two weeks (due date is within the next 14 days)
                    if ($next_due_date < $twoweeks_timestamp) {
                        $trainer_name = ($trow['first_name'] ?? '') . " " . ($trow['last_name'] ?? '');
                        $sherryarr[] = array(
                            $srow['companyname'] ?? '', 
                            $srow['schoolcode'] ?? '', 
                            $trainer_name, 
                            $formatted_drill_date
                        );
                    }
                }
            }
        }
    }
}

// Write CSV headers
$headers = array("School", "SchoolCode", "Trainer", "Previous Drill Date");
fputcsv($output, $headers);

// Write the main data (schools needing immediate drills)
foreach ($arr as $row) {
    fputcsv($output, $row);
}

// Optionally, you could add a separator and the sherryarr data
// fputcsv($output, array('')); // Empty row separator
// fputcsv($output, array('--- Schools Needing Drills Within Two Weeks ---', '', '', ''));
// foreach ($sherryarr as $row) {
//     fputcsv($output, $row);
// }

fclose($output);
exit;
?>