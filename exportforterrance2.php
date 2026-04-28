<?php 
$nologinrequired = true;
require_once('mysql.php');

// Initialize variables
$arr = [];
$tmparr = [];
$subject = "terrance export results attached";
$csv_path = "/tmp/rep.csv";

// --- 1. First Unused Query (Original logic preserved but result is overwritten) ---
// NOTE: The result of this query is loaded into $arr, but $arr is immediately reset below.
// $arr = db_query_array("SELECT COUNT(*) AS c, clientid 
//                         FROM responders_esi r, responder_training_dates rtd 
//                         WHERE rtd.responderid = r.responderid 
//                         AND rtd.trainingdate > '2011-12-01' 
//                         AND r.deleted = 0 
//                         GROUP BY clientid HAVING c > 1", "clientid", "clientid");


// --- 2. CSV File Generation Setup ---

$fl = fopen($csv_path, "w+");

// Reset $arr to an empty array (overwrites the result of the first query)
$arr = array();
$tmparr = array("TR ID", "PMS ID", "First Name", "Last Name");
fputcsv($fl, $tmparr);

// --- 3. Main Query: Select Responders ---
$sql = "SELECT * FROM responders_esi 
        WHERE responderid IN ( SELECT responderid FROM responder_training_dates WHERE trainingdate > '2011-08-01' ) 
        AND clientid IN ( SELECT id FROM company_esi WHERE iscorp = 0 AND deleted = 0 ) 
        AND deleted = 0 
        AND pmsidvalidated = 1 
        AND lastpmsvalidated > '2001-01-01'";

$res = db_query_rows($sql);

// Loop through responders and write to CSV
foreach ($res as $r) {
    $tmparr = array();
    $tmparr[] = $r['responderid'] ?? '';
    $tmparr[] = $r['pmsid'] ?? '';
    $tmparr[] = $r['firstname'] ?? '';
    $tmparr[] = $r['lastname'] ?? '';
    fputcsv($fl, $tmparr);
}

// Write the final empty $arr row (original logic preserved)
fputcsv($fl, $arr); 
fclose($fl);

// --- 4. Email the CSV Report ---

require "class.phpmailer.php";
$mail = new PHPMailer();

$mail->From = "info@emergencyskills.com";
$mail->FromName = "Emergencyskills.com";
$mail->WordWrap = 50; 

$mail->Subject = $subject;
$mail->IsHTML(false); 
$mail->Body = "results attached";
$mail->AddAttachment($csv_path, "responder_export_results.csv");

$mail->AddAddress("TPeele@schools.nyc.gov");
$mail->AddAddress("sarahg@emergencyskills.com");

if (!$mail->Send()) {
    echo "Message could not be sent. <p>";
    echo "Mailer Error: " . $mail->ErrorInfo;
}
?>