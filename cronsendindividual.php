<?php
// Assumed external functions: db_query_rows, db_query_first, sendHTMLMail, db_escape
$nologinrequired = true;
include "mysql.php";

// Define the expiration window: 24 months ago (start and end of that month)
// Current Date is Thursday, November 13, 2025.
$date_now = new DateTimeImmutable();

// Find the month 24 months ago. This is the month of certification expiration.
$two_years_ago_month = $date_now->modify('-2 years');

// First day of that month (inclusive start date for old class)
$twomonthsago = $two_years_ago_month->format('Y-m-01');

// Last second of that month (exclusive end date for old class)
$twomonthsagoend_obj = $two_years_ago_month->modify('last day of this month')->setTime(23, 59, 59);
$twomonthsagoend = $twomonthsagoend_obj->format('Y-m-d H:i:s');

// Month of Expiration for the email body
// The original code used strtotime( $twomonthsagoend + 400 ) which is unreliable.
// The correct expiration month is the month represented by $twomonthsagoend_obj.
$expiration_month = $twomonthsagoend_obj->format('F');

// URL_WITHOUT_SUBDOMAIN must be defined/available globally
$url_without_subdomain = defined('URL_WITHOUT_SUBDOMAIN') ? URL_WITHOUT_SUBDOMAIN : 'example.com'; 
$sub_doe = $GLOBALS['SUB_DOE'] ?? SUB_DOE ?? 'example.com';

// --- 1. Identify Responders with Expiring Certifications ---
$sql = "
    SELECT 
        r.*, c.startdate, c.id AS classid
    FROM 
        responder_to_class rtc
    INNER JOIN 
        class c ON rtc.classid = c.id
    INNER JOIN 
        responders_esi r ON r.responderid = rtc.responderid
    INNER JOIN 
        company_esi co ON co.id = r.clientid
    WHERE 
        c.startdate >= '" . db_escape($twomonthsago) . "' 
        AND c.startdate < '" . db_escape($twomonthsagoend) . "' 
        AND r.individual = 1 
        AND c.deleted = 0 
        AND r.deleted = 0 
        AND rtc.attended = 1 
        AND co.iscorp = 0 
"; 
$res = db_query_rows($sql);

$already_emailed = [];

if (is_array($res)) {
    foreach ($res as $row) {
        $responder_email = $row['email'] ?? '';
        $responder_id = (int)($row['responderid'] ?? 0);
        $class_id = (int)($row['classid'] ?? 0);

        if (empty($responder_email) || $responder_id === 0 || $class_id === 0) {
            continue;
        }

        // Deduplication: Only process one record per unique email address
        if (isset($already_emailed[$responder_email])) {
            continue;
        }
        $already_emailed[$responder_email] = 1;

        echo "checking on <font color=green>{$responder_email}</font><br>";

        // --- 2. Check for Upcoming Class ---
        $sql_upcoming = "
            SELECT 
                rtc.classid, c.startdate 
            FROM 
                responder_to_class rtc
            INNER JOIN 
                class c ON rtc.classid = c.id
            WHERE 
                c.startdate > '" . db_escape($twomonthsagoend) . "' 
                AND rtc.responderid = {$responder_id} 
                AND c.deleted = 0
        ";
        $upcoming = db_query_first($sql_upcoming);
        
        if ($upcoming) {
            echo "already upcoming in {$upcoming['classid']}, {$upcoming['startdate']}<br>";
            continue;
        }

        // --- 3. Send Reminder Email ---
        $subject = "Your AED/CPR certification is expiring";

        $body = "This is a courtesy email to inform you that your AED/CPR certification will expire in {$expiration_month}.<br>
To schedule a training program for your school staff, please visit our website: <A href='https://{$sub_doe}.{$url_without_subdomain}/login.php'>https://{$sub_doe}.{$url_without_subdomain}/login.php</a><br>
To schedule individual training click here: <a href='http://{$sub_doe}.{$url_without_subdomain}/individual_registration1.php'>http://{$sub_doe}.{$url_without_subdomain}/individual_registration1.php</a><br>
If you have any questions or concerns please feel free to contact me.<br>
<br>
Sarah<br>
<br>
Sarah Gillen - Emergency Skills, Inc<br>
Senior Project Manager<br>
NYC Dept. Of Ed. AED Program<br>
ESI: 212-564-6833<br>
DOE: 718-391-8382<br>
<a href='http://{$sub_doe}.{$url_without_subdomain}'>http://{$sub_doe}.{$url_without_subdomain}</a><br>
";
        $tracking_key = "individremin" . $responder_id . "-" . $class_id;
        
        sendHTMLMail(
            $responder_email, 
            $subject, 
            $body, 
            "sarahg@emergencyskills.com", 
            "Sarah Gillen", 
            $tracking_key
        );
        
        echo "<br>Sending, key is: {$tracking_key}<br>";
    }
}
?>