<?php 
$nologinrequired = true;
require_once('mysql.php');

// Initialize variables
$arr = [];
$tmparr = [];

// --- 1. Identify Clients with Multiple Responders Trained Since 2011-12-01 ---
// This subquery creates an array of client IDs to EXCLUDE from the main report.
$exclude_clients_sql = "SELECT COUNT(*) AS c, clientid 
                        FROM responders_esi r, responder_training_dates rtd 
                        WHERE rtd.responderid = r.responderid 
                        AND rtd.trainingdate > '2011-12-01' 
                        AND r.deleted = 0 
                        GROUP BY clientid 
                        HAVING c > 1";

// Assumed external function to fetch associative array
$arr = db_query_array($exclude_clients_sql, "clientid", "clientid");

// Prepare the list of IDs to exclude for the SQL query
$exclude_ids = !empty($arr) ? join(", ", $arr) : '0';

// --- 2. Main Query: Select target companies (Schools) ---
$sql = "SELECT c.* FROM company_esi c 
        WHERE iscorp = '0' 
        AND schoolcode NOT LIKE '84%' 
        AND c.isactive = 1 
        AND c.deleted = 0 
        AND c.id NOT IN ({$exclude_ids}) 
        ORDER BY companyname";

// echo $sql . "<br>";

$res = db_query_rows($sql);

// --- 3. CSV File Generation ---
$csv_path = "/tmp/rep.csv";
$fl = fopen($csv_path, "w+");

// Define Header Row
$tmparr = array("School Code", "School Name", "Address", "Building Code", "Location Code", "Last Expiration Date", "Upcoming Classes");
fputcsv($fl, $tmparr);

// Loop through company results and gather detailed data
foreach ($res as $r) {
    $company_id = $r['id'] ?? 0;
    $location_code = $r['locationcode'] ?? '';
    
    // Get Building Code
    $buildingcode = db_query_first_cell("SELECT buildingcode FROM location_to_building WHERE locationcode = '{$location_code}'");
    
    // Get Last Responder Training/Expiration Date
    $lastexp = db_query_first_cell("SELECT MAX(rtd.trainingdate) FROM responder_training_dates rtd, responders_esi r WHERE r.responderid = rtd.responderid AND r.clientid = {$company_id}");
    
    // Get Upcoming Classes
    $upcoming = db_query_first_cell("SELECT GROUP_CONCAT(id) FROM class WHERE companyid = '{$company_id}' AND deleted = 0 AND startdate > NOW()");
    
    // Format Address
    $full_address = ($r['address'] ?? '') . " , " . ($r['city'] ?? '') . ", NY " . ($r['zip'] ?? '');
    
    // Prepare Data Row
    $tmparr = array();
    $tmparr[] = $r['schoolcode'] ?? '';
    $tmparr[] = $r['companyname'] ?? '';
    $tmparr[] = $full_address;
    $tmparr[] = $r['locationcode'] ?? '';
    $tmparr[] = $buildingcode;
    $tmparr[] = $lastexp;
    $tmparr[] = $upcoming;
    
    fputcsv($fl, $tmparr);
}

// Write the final empty $arr row (original logic preserved)
$arr = []; 
fputcsv($fl, $arr); 
fclose($fl);

// --- 4. Email the CSV Report ---
$subject = "Terrance Export: School Responder Report";

require "class.phpmailer.php";
$mail = new PHPMailer();

$mail->From = "info@emergencyskills.com";
$mail->FromName = "Emergencyskills.com";
$mail->WordWrap = 50; // set word wrap to 50 characters

$mail->Subject = $subject;
$mail->IsHTML(false); // set email format to plain text
$mail->Body = "Report attached. This automated report lists schools that are active, non-corporate, and have not had more than one responder trained since 2011-12-01.";

$mail->AddAttachment($csv_path, "School_Responder_Report.csv");

$mail->AddAddress("sarahg@emergencyskills.com");

if (!$mail->Send()) {
    echo "Message could not be sent. <p>";
    echo "Mailer Error: " . $mail->ErrorInfo;
}
?>