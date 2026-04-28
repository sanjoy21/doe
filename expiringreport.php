<?php
$nologinrequired = true;
require_once('mysql.php');

// Safely retrieve assumed global/session variables
$thisusersrow = $thisusersrow ?? [];
$session_iscorp = $session_iscorp ?? 0;
$zips = ""; // Initialize ZIP filter string

// Get the database connection link for safe queries
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Apply Trainer ZIP Code Filtering ---
if( ($thisusersrow["usertype"] ?? null) == "trainer" ) {
    // Assuming getVisibleZipsString() exists and returns a WHERE clause fragment
    $zips_clause_raw = getVisibleZipsString( "c" );
    // Wrap the clause in "AND" if present
    $zips = !empty($zips_clause_raw) ? " AND ({$zips_clause_raw}) " : "";
}

// --- 2. Calculate Date Cutoff (Next Month) ---
$today_ts = time();
$nextmonth_ts = mktime( 0, 0, 0, date( "m", $today_ts ) + 1, date( "d", $today_ts ), date( "Y", $today_ts ) );
$nextmonth_sql = date( "Y-m-d", $nextmonth_ts );

// --- 3. Construct the SQL Query ---
$sql = "SELECT 
            c.id, c.companyname, c.city, c.zip, c.address, 
            CONCAT( c.contactname, ' ', c.contactphone, ' ', c.contactemail) AS contactinfo,
            a.serial, a.padaexpiration, a.padbexpiration, a.location, 
            a.pediatricpads, a.sparedate 
        FROM 
            company_esi c
        JOIN
            aed_esi a ON c.id = a.clientid 
        WHERE 
            c.iscorp = '{$session_iscorp}' 
            AND c.isactive = 1 
            AND c.deleted = 0 
            AND a.deleted = 0 
            AND (
                -- Pad A expiring by next month
                ( '{$nextmonth_sql}' >= a.padaexpiration AND a.padaexpiration <> '' )
                -- OR Pad B expiring by next month
                OR ( '{$nextmonth_sql}' >= a.padbexpiration AND a.padbexpiration <> '' ) 
                -- OR Pediatric pads expiring by next month
                OR ( a.pediatricpads <> '' AND '{$nextmonth_sql}' >= a.pediatricpads )
            ) 
            {$zips} 
        ORDER BY 
            c.companyname";

$res = db_query_rows( $sql );

// --- 4. Generate CSV File ---
$csv_path = "/tmp/rep.csv";
$fl = fopen( $csv_path, "w+");

$arr = array();
$header_row = array( 
    "Company", "Address", "City", "Zip Code", "Contact Info", 
    "Serial", "Location", "Pad A Expiration", "Pad B Expiration", 
    "Pediatric Pad Expiration", "Spare Date" 
);
fputcsv( $fl, $header_row ); // Write header row

foreach( $res as $r )
{
    // PHP 8.2 Fixes: Quoted array keys and checking for nulls
    $tmparr = array();
    $tmparr[] = $r['companyname'] ?? '';
    $tmparr[] = $r['address'] ?? '';
    $tmparr[] = $r['city'] ?? '';
    $tmparr[] = $r['zip'] ?? '';
    $tmparr[] = $r['contactinfo'] ?? '';
    $tmparr[] = $r['serial'] ?? '';
    $tmparr[] = $r['location'] ?? '';
    $tmparr[] = $r['padaexpiration'] ?? '';
    $tmparr[] = $r['padbexpiration'] ?? '';
    $tmparr[] = $r['pediatricpads'] ?? '';
    $tmparr[] = $r['sparedate'] ?? '';
    
    fputcsv( $fl, $tmparr ); // Write data row
}

fclose( $fl );

// --- 5. Configure and Send Email ---
$plainemailbody = "Report attached.";
$subject_date = date( "M", $today_ts );
$subject = "Pads Expiring for {$subject_date}";

// Assuming class.phpmailer.php is available and correctly implemented for PHP 8.2
require "class.phpmailer.php"; 
$mail = new PHPMailer(true); // Using 'true' enables exceptions for error handling

try {
    $mail->From = "info@emergencyskills.com";
    $mail->FromName = "Emergencyskills.com";
    $mail->AddReplyTo( "info@emergencyskills.com", "Emergencyskills.com" );
    $mail->WordWrap = 50; 
    
    $mail->Subject = $subject;
    $mail->IsHTML(false); 
    $mail->Body = $plainemailbody;
    $mail->addAttachment( $csv_path, "expiring_aeds_{$subject_date}.csv" ); // Add a meaningful filename
    
    $mail->AddAddress("sarahg@emergencyskills.com");

    if(!$mail->Send()) {
        // This block is only reached if exceptions are off or if PHP < 5.0
        echo "Message could not be sent. <p>";
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

// Optionally, remove the temporary file after sending
// unlink($csv_path);
?>