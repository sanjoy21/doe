<?php
// We set this explicitly, but it should be handled in a framework or entry point.
$nologinrequired = true;
include "mysql.php";


// Assumed external functions (must exist in mysql.php or included files)
// function db_query_rows(string $sql): array;
// function db_query_first_cell(string $sql): string|null;
// function getZips(array $trow): string; // Assumed to return a comma-separated string of ZIP codes
// $session_iscorp - Assumed to be available globally (e.g., from session/login)

// --- Utility Functions ---

/**
 * Calculates the next required drill date (6 months after the last drill).
 * @param string $lastDrillDate 'Y-m-d' format.
 * @return int Timestamp of the next required drill date.
 */
function calculateNextDrillTimestamp(string $lastDrillDate): int
{
    $currdate = strtotime($lastDrillDate);
    // Add 6 months to the last drill date
    return mktime(0, 0, 0, date("m", $currdate) + 6, date("d", $currdate), date("Y", $currdate));
}

// --- Main Execution ---

$current_time = time();
// Two weeks from now (used for the "OVERDUE" status for Sherry)
$six_months_from_now = mktime(0, 0, 0, date("m", $current_time) + 6, date("d", $current_time), date("Y", $current_time));

$sherrybody = "";
$html_output = "";

// 1. Get all active trainers
$trainers = db_query_rows("SELECT * FROM user WHERE usertype = 'trainer' AND inactive = 0");

foreach ($trainers as $trow) {
    $trainer_body = "";
    $any_needs_drill = false;
    
    $trainer_name = htmlspecialchars($trow['first_name'] ?? '') . " " . htmlspecialchars($trow['last_name'] ?? '');
    $trainer_body .= "<h2>Drill Needs for: {$trainer_name}</h2>";
    
    $zips = array_filter(explode(",", getZips($trow))); // Get and clean ZIP codes
    
    $sherrybody .= "<h3>Status for {$trainer_name}</h3>\n";

    if (empty($zips)) {
        $trainer_body .= "<p>No ZIP codes assigned.</p>\n";
    }

    foreach ($zips as $zip) {
        $safe_zip = db_escape(trim($zip));
        
        // 2. Find relevant schools in the trainer's ZIP code
        // Note: Assumes $session_iscorp is defined globally
        $iscorp_val = (int)($session_iscorp ?? 0);
        
        $schools = db_query_rows("
            SELECT id, companyname 
            FROM company_esi 
            WHERE iscorp = {$iscorp_val} 
            AND deleted = 0 
            AND summer = 1 
            AND zip = '{$safe_zip}'
        ");
        
        foreach ($schools as $srow) {
            $school_id = (int)($srow['id'] ?? 0);
            $school_name = htmlspecialchars($srow['companyname'] ?? '');
            $view_link = "https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/viewcompany.php?id={$school_id}";
            
            // 3. Find all completed drills for this school, ordered by date
            // Uses LEFT JOIN to handle scenarios where only one table has the companyid linkage
            $drills = db_query_rows("
                SELECT drilldate 
                FROM drill 
                LEFT JOIN drill_to_companyid dtc ON drill.drillid = dtc.drillid 
                WHERE completed = 1 
                AND (drill.companyid = {$school_id} OR dtc.companyid = {$school_id}) 
                ORDER BY drilldate DESC
            ");
            
            if (empty($drills)) {
                // Case A: No previous drills found
                $trainer_body .= "<p><strong>No Previous Drills:</strong> {$school_name} (<a href='{$view_link}'>View</a>)</p>\n";
                $sherrybody .= "No Previous Drills: {$school_name} (<a href='{$view_link}'>View</a>)\n";
                $any_needs_drill = true;
            } else {
                // Case B: Check if drill is overdue (6 months rule)
                $last_drill_date = $drills[0]['drilldate'];
                
                if ($last_drill_date !== '0000-00-00' && $last_drill_date !== null) {
                    $last_drill_ts = strtotime($last_drill_date);
                    $next_required_drill_ts = calculateNextDrillTimestamp($last_drill_date);
                    
                    if ($next_required_drill_ts < $current_time) {
                        // Needs a drill now (Overdue)
                        $formatted_date = date("m/d/Y", $last_drill_ts);
                        $trainer_body .= "<p><strong>NEEDS A DRILL:</strong> {$school_name} (<a href='{$view_link}'>View</a>) - Last Drill: {$formatted_date}</p>\n";
                        $any_needs_drill = true;
                    }
                    
                    // Check for Sherry's "OVERDUE" status (needs drill in the next 6 months)
                    // The original logic checked if next drill date is before $twoweeks (which was 6 months from now)
                    // This seems to mean: if the required date is within the next 6 months, alert Sherry.
                    // Given the original logic: $twoweeks was +6 months. The check was: if ($nextdate < $twoweeks). 
                    // This means if the next required drill is BEFORE 6 months from now, it is OVERDUE for Sherry.
                    if ($next_required_drill_ts < $six_months_from_now) {
                         $formatted_date = date("m/d/Y", $last_drill_ts);
                         $sherrybody .= "OVERDUE: {$school_name} (<a href='{$view_link}'>View</a>) - Last Drill: {$formatted_date}\n";
                    }
                }
            }
        }
    }
    
    // 4. Output trainer body if any drills were needed
    if ($any_needs_drill) {
        $html_output .= $trainer_body . "<br><hr>";
    }
}

// --- Final Output ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Drill Status Report</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .sherry-report { background-color: #ffe0b2; padding: 15px; border: 1px solid #ffcc80; }
        p { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>Drill Assignment Status</h1>
    
    <!-- Output for Trainers -->
    <?php 
    if (!empty($html_output)) {
        echo $html_output;
    } else {
        echo "<p>All assigned schools are up to date on required drills or no schools are assigned.</p>";
    }
    ?>
    
    <br><br>
    
    <!-- Output for Sarah/Sherry -->
    <div class="sherry-report">
        <h2>FOR SARAH (6-Month Drill Watchlist)</h2>
        <?php 
        if (!empty($sherrybody)) {
            // Display Sherry's report, converting newlines to <br>
            echo nl2br(htmlspecialchars($sherrybody));
        } else {
            echo "<p>No schools are currently on the 6-month overdue watchlist.</p>";
        }
        ?>
    </div>
</body>
</html>