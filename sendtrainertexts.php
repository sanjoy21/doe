<?php
// Initialize external variables safely
$nologinrequired = true;

include "mysql.php";
// Assumed external functions: db_query_rows, db_query_first, getCompanyName, getFormattedDateWTime, sendText, sendMail

// --- Calculate Tomorrow's Date ---
// mktime(hour, minute, second, month, day, year)
$cldate_ts = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
$tomorrow_date = date("Y-m-d", $cldate_ts);

// --- Build SQL Query for Tomorrow's Confirmed Classes ---
$sql = "
    SELECT * FROM class 
    WHERE confirmdate > '0000-00-00' 
    AND confirmdate IS NOT NULL 
    AND deleted = 0 
    AND DATE(startdate) = '{$tomorrow_date}'
";

// Fetch classes
$classes = db_query_rows($sql, "id");
$trainers_all = [];

// --- Process Each Class ---
foreach ($classes as $cid => $c) {
    $cid = (int)$cid; // Ensure class ID is an integer

    // 1. Fetch Assigned Trainers
    $trows = db_query_rows("
        SELECT user.*, trainerconfirmeddate 
        FROM trainer_to_class, user 
        WHERE classid = {$cid} 
        AND user.id = trainerid
    ", "id");

    // 2. Fetch Assigned TCF (if any)
    $tcfac = db_query_first("
        SELECT user.*, tcfacultyconfirmeddate 
        FROM class, user 
        WHERE class.id = {$cid} 
        AND user.id = tcfacultyid 
        AND tcfacultyid > 0
    ");

    // --- Determine Class Location/Name ---
    $comp_name = $c["training_location"] 
                 ? $c["training_location"] 
                 : getCompanyName($c["companyid"] ?? 0);
    
    if ($c["remote"] ?? false) {
        $comp_name = "REMOTE CLASS";
    }

    // --- Prepare Text Message Bodies ---
    $formatted_time = getFormattedDateWTime($c["startdate"] ?? 'now');
    
    $body = "You are scheduled to teach tomorrow: {$formatted_time} at {$comp_name}. DO NOT REPLY TO THIS TEXT. Call office directly.";
    $tcbody = "You are scheduled as TCF to teach tomorrow: {$formatted_time} at {$comp_name}. DO NOT REPLY TO THIS TEXT. Call office directly.";

    // --- Combine Trainers and TCF ---
    $all_instructors = $trows;
    if ($tcfac && !empty($tcfac['id'])) {
        // Add TCF to the list using their ID as key
        $all_instructors[$tcfac['id']] = $tcfac;
    }

    // --- Send Reminders and Check Confirmation Status ---
    foreach ($all_instructors as $t) {
        $is_tcf = ($t['id'] ?? 0) == ($c['tcfacultyid'] ?? 0);
        $message_body = $is_tcf ? $tcbody : $body;
        
        // Send the text message reminder
        sendText("ALIVE!net Reminder!", $message_body, $t);

        // Check for confirmation and send email warning if missing
        $is_confirmed = ($t["trainerconfirmeddate"] ?? null) || ($t["tcfacultyconfirmeddate"] ?? null);
        
        if (!$is_confirmed) {
            $tbody = htmlspecialchars($t['userid'] ?? 'Unknown User') . " has not yet confirmed for tomorrow's class {$cid}.";
            sendMail(
                "barbara@emergencyskills.com", 
                "WARNING: trainer has not confirmed for class {$cid}", 
                $tbody, 
                "info@emergencyskills.com"
            );
        }
    }
}
?>