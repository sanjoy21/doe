<?php 
require_once('mysql.php');

$rids = db_query_rows("SELECT * FROM user WHERE usertype = 'trainer' AND inactive = 0 ORDER BY last_name, first_name");

$drillsdontcountbefore = getsetting('drillsdontcountbefore'); 

// --- 2. Calculate Completed Drills Per Company/School ---
$drill_sql = "SELECT dtc.companyid, COUNT(DISTINCT(drill.drillid)) AS numdrills 
              FROM drill 
              LEFT JOIN drill_to_companyid dtc ON (drill.drillid = dtc.drillid) 
              WHERE (completed = 1 OR received = 1 OR isdone = 1 OR shipped = 1) 
              AND drilldate >= '{$drillsdontcountbefore}' 
              GROUP BY dtc.companyid";

$drillarr = db_query_array($drill_sql, "companyid", "numdrills");

// --- 3. CSV File Generation Setup (Replacing Excel Writer) ---
$filename = "fieldreps_" . date('Ymd') . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$output = fopen('php://output', 'w');

// CSV Header
$header = array(
    "Last Name", "First Name", "# Schools", "# Drills Remaining", 
    "% Remaining", "# Days Left", "Campuses"
);
fputcsv($output, $header);

// --- 4. Calculate Business Days Remaining in Drill Period ---
$numdays = 0;
$today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
$lastday_str = getsetting('lastdrillday');
$firstday_str = getsetting('firstdrillday');

$lastday = strtotime($lastday_str);
$firstday = strtotime($firstday_str);

$current_day = $today;

// Loop from today until the last day of the drill period, or stop if today is outside the period
while ($current_day <= $lastday && $current_day >= $firstday) {
    $day_of_week = date("w", $current_day);
    
    // Check if it's Monday (1), Tuesday (2), Wednesday (3), or Thursday (4) (i.e., not Sat (6), Sun (0), or Fri (5))
    // The original logic checked for NOT 0 (Sun), NOT 6 (Sat), and NOT 5 (Fri).
    if ($day_of_week != 0 && $day_of_week != 6 && $day_of_week != 5) {
        $date_str = date("Y-m-d", $current_day);
        
        // Check if the day is in the nodrilldates table (assumed external table)
        $dt = db_query_first_cell("SELECT dt FROM nodrilldates WHERE dt = '{$date_str}'");
        if (!$dt) {
            $numdays++;
        }
    }
    
    // Move to the next day
    $current_day = strtotime('+1 day', $current_day);
}

// --- 5. Process Trainer Data ---

foreach ($rids as $trow) {
    $numdrills = 0;
    $numschools = 0;
    $sids = "";
    $campusids = array();
    
    $zips = getZips($trow); // Assumed external function returns a CSV of zips or false

    if ($zips) {
        // Get all schools for the trainer's zips that show on drill reports
        $schools_sql = "SELECT id, campusid FROM company_esi 
                        WHERE iscorp = '{$session_iscorp}' 
                        AND deleted = 0 
                        AND zip IN ({$zips}) 
                        AND showsondrillreports = 1";
        $schools = db_query_array($schools_sql, "id", "campusid");

        foreach ($schools as $sid => $campusid) {
            $campusids[$campusid] = $campusid; // Track unique campuses
            $drills_completed = $drillarr[$sid];
            
            // If no drills completed, count it as a remaining drill
            if (!$drills_completed) {
                $numdrills++;
                $sids .= "{$sid}, ";
            }
        }
        $numschools = count($schools);
    } else {
        // Skip trainers with no assigned zips
        continue;
    }

    // --- 6. Write Data Row ---
    $last_name = $trow["last_name"];
    $first_name = $trow["first_name"];
    $percent_remaining = ($numschools > 0) ? number_format($numdrills / $numschools * 100, 2) : 0.00;
    $num_campuses = count($campusids);

    $data_row = array(
        $last_name,
        $first_name,
        $numschools,
        $numdrills,
        $percent_remaining,
        $numdays,
        $num_campuses
    );

    fputcsv($output, $data_row);
}

fclose($output);
exit;
?>