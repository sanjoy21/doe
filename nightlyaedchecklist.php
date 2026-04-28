<?php
// Set flag to indicate login is not required
$nologinrequired = 1;
// Assumes 'mysql.php' handles database connection and defines db_query_rows and getCompanyName
include "mysql.php";

// Get today's date in 'Y-m-d' format for the database query
$dt = date("Y-m-d");
$dt_safe = $dt; // Already safe as it's generated internally

// Fetch all AED inspections completed today that have a corresponding AED record
$sql = "SELECT * FROM aedinspections ai, aed_esi a 
        WHERE a.aedid = ai.aedid 
        AND thedate = '{$dt_safe}' 
        ORDER BY clientid, serial";

$todays = db_query_rows($sql);

$body = "";
$lastcompany = "";
$has_issues = false;

// Loop through today's inspections to find issues
foreach ($todays as $t) {
    
    // Issue 1: Chirping (Original logic: chirping > 0)
    if (($t['chirping'] ?? 0) > 0) {
        $has_issues = true;
        
        // If the company ID changes, add a new header for the company
        if (($t["clientid"] ?? null) != $lastcompany) {
            // Assumes getCompanyName is defined and returns the client name
            $company_name = getCompanyName($t['clientid'] ?? 0);
            $body .= "\n" . $company_name . ": \n";
            $lastcompany = $t['clientid'] ?? null;
        }
        
        $body .= "Serial number {$t['serial']} has been reported **chirping** during monthly inspection.\n";
    }
    
    // Issue 2: No Status/Blinking (Original logic: blinking < 0, often used for 'no status')
    if (($t['blinking'] ?? 0) < 0) {
        $has_issues = true;
        
        // If the company ID changes, add a new header for the company
        if (($t["clientid"] ?? null) != $lastcompany) {
            // Assumes getCompanyName is defined and returns the client name
            $company_name = getCompanyName($t['clientid'] ?? 0);
            $body .= "\n" . $company_name . ": \n";
            $lastcompany = $t['clientid'] ?? null;
        }
        
        $body .= "Serial number {$t['serial']} has been reported **no status/blinking** during monthly inspection.\n";
    }
}

$subject = "Monthly Inspection - Nightly report";

// Only send email if there were issues found
if ($has_issues) {
    // Assumes sendMail is a defined function in the included file
    sendMail("sarahg@emergencyskills.com", $subject, $body, "info@emergencyskills.com");
}
?>