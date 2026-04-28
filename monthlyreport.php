<?php
require_once('mysql.php');

// --- Secure Input Retrieval ---
// Check if the 'mail' flag is set in the request to trigger email sending
$mail = $_REQUEST['mail'] ?? null;

// --- Database Queries (Assumed Functions from mysql.php) ---
$totaeds = db_query_first_cell("SELECT COUNT(*) FROM aed_esi WHERE deleted = 0");
$totdrills = db_query_first_cell("SELECT COUNT(*) FROM drill WHERE completed = 1");
$totclasses = db_query_first_cell("SELECT COUNT(*) FROM class WHERE deleted = 0 AND startdate <= NOW()");

// Total currently certified responders (across all classes/methods)
$tottrained = db_query_first_cell("SELECT COUNT(DISTINCT(r.responderid)) FROM responders_esi r JOIN responder_training_dates rtc ON rtc.responderid = r.responderid WHERE r.deleted = 0");

// Total trained by ESI (where classid > 0 means associated with an ESI class)
$tottrainedesi = db_query_first_cell("SELECT COUNT(DISTINCT(r.responderid)) FROM responders_esi r JOIN responder_training_dates rtc ON rtc.responderid = r.responderid WHERE r.deleted = 0 AND rtc.classid > 0");

// Heartsaver AED, 6 hour ('reg')
$tottrained6 = db_query_first_cell("SELECT COUNT(DISTINCT(r.responderid)) FROM responders_esi r JOIN responder_training_dates rtc ON rtc.responderid = r.responderid JOIN class c ON c.id = rtc.classid WHERE r.deleted = 0 AND c.code = 'reg'");

// Heartsaver CPR, 2 hour ('dd')
$tottrained2 = db_query_first_cell("SELECT COUNT(DISTINCT(r.responderid)) FROM responders_esi r JOIN responder_training_dates rtc ON rtc.responderid = r.responderid JOIN class c ON c.id = rtc.classid WHERE r.deleted = 0 AND c.code = 'dd'");

// Other training (classid is null or 0)
$tottrainedother = db_query_first_cell("SELECT COUNT(DISTINCT(r.responderid)) FROM responders_esi r JOIN responder_training_dates rtc ON rtc.responderid = r.responderid WHERE r.deleted = 0 AND (rtc.classid IS NULL OR rtc.classid = 0)");


// --- HTML Report Body Generation ---
$body = "
<table>
<tr><Td class='copy'>Total Number of classes:</td><td class='copy'>{$totclasses}</td></tr>
<tr><Td class='copy'>Total Number of currently certified responders:</td><td class='copy'>{$tottrained}</td></tr>
<tr><td class='copy'>Heartsaver AED, 6 hour:</td><td class='copy'>{$tottrained6}</td></tr>
<tr><td class='copy'>Heartsaver CPR, 2 hour:</td><td class='copy'>{$tottrained2}</td></tr>
<tr><td class='copy'>Other:</td><td class='copy'>{$tottrainedother}</td></tr>
<tr><td class='copy'><br><br></td></tr>
<tr><td class='copy'>Total Number of people ESI has trained:</td><td class='copy'>{$tottrainedesi}</td></tr>
<tr><td class='copy'><br><br></td></tr>
<tr><td class='copy'>Total Number of AEDs deployed: </td><td class='copy'>{$totaeds}</td></tr>
<tr><td class='copy'>Total Number of drills performed: </td><td class='copy'>{$totdrills}</td></tr>
</table>
";

// --- Email Sending Logic (Conditional) ---
if ($mail) {
    // Requires PHPMailer to be installed and available
    require "class.phpmailer.php";
$mailer = new PHPMailer();

    try {
        $mailer->IsSMTP();
        $mailer->Host = "localhost";
        $mailer->SMTPAuth = false;
        $mailer->setFrom("info@emergencyskills.com", "Emergency Skills - DOE");
        $mailer->AddReplyTo("sarahg@emergencyskills.com");
        $mailer->WordWrap = 50; 
        $mailer->IsHTML(true); 

        $mailer->Subject = "Monthly Report";
        $mailer->Body    = $body;
        $mailer->AddAddress("cox@vireo.org");

        if (!$mailer->Send()) {
            // Note: In a production environment, you should log this error, not echo it publicly
            error_log("Message could not be sent. Mailer Error: " . $mailer->ErrorInfo);
        }
    } catch (\Exception $e) {
        // Handle exceptions during mail sending
        error_log("Message could not be sent. Exception: " . $e->getMessage());
    }

    // Exit immediately after attempting to send the email
    exit;
}

?>
<?php include "ssi/top.php"; ?> 	 	 	 	
<!--start center content-->
	 	 	 	
	 	 	 	 <strong><span class="title">MONTHLY REPORTS</span></strong> 	 	
	 	 	 	 <p>
<?= $body ?>
<br><br><br><br><br><br><br>

	 	 	 	 <!--end center content-->
	 	 	 	 
	 	 	 	 	 	 	 	 	 <?php include "ssi/footer.php"; ?>
	 	 	 	 
	 	 	 	 <!--end footer-->
	 	 	 	 </span>
	 	 	 	 </td>
	 	 	 	 <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
	 	 	 	 </tr>
</table>
<br><br>
</div>
</body>
</html>