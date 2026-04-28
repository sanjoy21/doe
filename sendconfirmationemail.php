<?php
// Initialize external variables safely
// $specialadmin = $specialadmin ?? false;
// $id = $id ?? 0;
$send = $_POST['send'] ?? null;
$fromname = $_POST['fromname'] ?? null;
$fromemail = $_POST['fromemail'] ?? null;
$cc = $_POST['cc'] ?? null;
$subject = $_POST['subject'] ?? null;
$body = $_POST['body'] ?? null;
$err = '';

require_once('mysql.php');
// Assumed external functions: getClassRow, getClassEmail, getCompanyRow, date, getFormattedTime, 
// getTrainingAddress, getEmail, getUserName, getFormattedDate

// --- Access Control ---
if (!$specialadmin) {
    header("location: login.php");
    exit;
}

// --- Fetch Class and Company Details ---
$crow = getClassRow($id);
$classemail = getClassEmail($crow);
$company = getCompanyRow($crow["companyid"] ?? 0);
$comrow = $company; // Alias for consistency with original code
// $iscorp = $comrow['iscorp'] ?? 0;

// --- Initialization Block (First Load) ---
if (!$send) {

    $start_date_display = date("l, F j, Y", strtotime($crow['startdate'] ?? 'now'));
    $start_time_display = getFormattedTime($crow['startdate'] ?? 'now');
    $end_time_display = $crow['enddate'] ?? '';
    $training_address = getTrainingAddress($crow);

    // --- Non-Corporate Template (e.g., School/DOE) ---
    if (!$iscorp) {
$initbody = "
Dear CONTACTNAME,
We are preparing for your upcoming CPR/AED training! The following logistical information is needed so
that we can adequately prepare. Please read the ENTIRE email and answer ALL questions.

Is the date/time and location information listed below correct?

- Date: {$start_date_display}
- Time: {$start_time_display} - {$end_time_display}
- Training Address: {$training_address}

Please answer the following questions:

- To what floor and room in the building is the equipment being delivered?
- Is there an operational elevator in your building?
- What is the number of the room will you be using for class?
- How many participants do you expect to attend?  

This class is video driven; we can STREAM, use a USB flash drive, or use a DVD to play the course.
Choose ONE option below:
- We will provide a computer/monitor/speakers (or smartboard) connected to the internet.
- We will provide a computer/monitor/speaker on which the instructor can play a USB.
- We will provide a DVD player connected to a monitor with speakers so instructor can play a DVD.
- We have a monitor and speakers but do not have a computer or DVD player to attach.
- We have no audio/visual capability at all.

NOTE: ESI instructors DO NOT travel with personal computers or other equipment.

EQUIPMENT DELIVERY AND PICKUP
Equipment will arrive via courier the school day before class and be picked up the school day after the
class.

INSTRUCTOR ARRIVAL
The instructor will need access to the training room 30 minutes prior to the course for set up and will
need access to the training room 30 minutes post-course to re-pack equipment. Please ensure the
training equipment and A/V that you are providing is in the room at least 30 minutes before the start of
the class.

REMINDER
The certification class WILL take 6 hours. All participants MUST be present for the duration to be
certified, and to be adequately prepared to respond in a cardiac emergency.

We look forward to working with you on this lifesaving project and to preparing your staff to TAKE
ACTION in a medical emergency.

Thank you,
Emergency Skills
212-564-6833
savealife@emergencyskills.com
";

    // --- Corporate Template ---
    } else {
$initbody = "
Dear CONTACTNAME
We are preparing for your upcoming CPR/AED training! The following logistical information is needed so
that we can adequately prepare. Please read the ENTIRE email and answer all questions.

Is the date/time and location information listed below correct?

- Date: {$start_date_display}
- Time: {$start_time_display} - {$end_time_display}
- Training Address: {$training_address}

Do you need a COI (Certificate of Insurance) from ESI and/or our courier? If yes, please send a sample
ASAP to COIrequest@emergencyskills.com and dfunnye@emergencyskills.com.

Please answer the following questions:
- To what floor and room in the building is the equipment being delivered?
- Is there an operational elevator in your building?
- What is the number of the room will you be using for class?
- How many participants do you expect to attend?  

This class is video driven; we can STREAM, use a USB flash drive, or use a DVD to play the course.
Choose ONE option below:

- We will provide a computer/monitor/speakers (or smartboard) connected to the internet.
- We will provide a computer/monitor/speaker on which the instructor can play a USB.
- We will provide a DVD player connected to a monitor with speakers so instructor can play a DVD.
- We have a monitor and speakers but do not have a computer or DVD player to attach.
- We have no audio/visual capability at all.

NOTE: ESI instructors DO NOT travel with personal computers or other equipment.

EQUIPMENT DELIVERY AND PICKUP
Equipment will arrive via courier the business day before class and be picked up the business day after
the class (or series of classes). IF you are outside the 5 boroughs of NYC, your equipment will arrive via
UPS.

INSTRUCTOR ARRIVAL
The instructor will need access to the training room 20 minutes prior to the course for set up and will
need access to the training room 20 minutes post-course to re-pack equipment. Please ensure the
training equipment and A/V that you are providing is in the room prior to their arrival.

We look forward to working with you on this lifesaving project, and to preparing your staff to TAKE
ACTION in a medical emergency.

Thank you,
Emergency Skills
212-564-6833
savealife@emergencyskills.com

";
}

    // --- Set Initial Form Values ---
    $school_code = htmlspecialchars($company['schoolcode'] ?? '');
    $company_id = htmlspecialchars($id);

    $subject = "ACTION REQUIRED! CPR/AED Training confirmation - {$school_code} - #{$company_id}";
    $body = $initbody;
    $to = $crow['email'] ?? '';
    $fromname = "Emergency Skills";
    $fromemail = "savealife@emergencyskills.com";
}

// --- Sending Logic ---
if ($send) {
    $mybody = stripslashes($body);

    require_once "class.phpmailer.php";

    // Compile ALL potential recipients
    $toarr = [];
    $toarr[$crow['principalemail'] ?? ''] = $crow['principalname'] ?? '';
    $toarr[$crow['contactemail'] ?? ''] = $crow['contactname'] ?? '';
    $toarr[$crow['email'] ?? ''] = ($crow['firstname'] ?? '') . " " . ($crow['lastname'] ?? '');
    $toarr[$crow['contact2email'] ?? ''] = $crow['contact2name'] ?? '';
    $toarr[$crow['alt_email'] ?? ''] = ($crow['alt_firstname'] ?? '') . " " . ($crow['alt_lastname'] ?? '');
    $toarr[getEmail($crow['addedby'] ?? 0)] = getUserName($crow['addedby'] ?? 0);

    // Remove blank/duplicate keys (emails)
    // $toarr = array_filter($toarr, function($k) { return !empty(trim($k)); }, ARRAY_FILTER_USE_KEY);

    // Loop and send individual email to each recipient
    foreach ($toarr as $t => $tname) {
$mail = new PHPMailer();
$mail->From = $fromemail;
$mail->FromName = $fromname;
$mail->AddReplyTo($fromemail);

$mail->Subject = stripslashes($subject);
$mail->IsHTML(false); // Send as plain text (since nl2br wasn't used on body, only $mybody)

$mail->IsSMTP(); // Added later to send CC by Sanjoy Dey
    $mail->Host = 'localhost'; // Added later to send CC by Sanjoy Dey
    $mail->SMTPAuth = false; // Added later to send CC by Sanjoy Dey
    $mail->Port = 25; // Added later to send CC by Sanjoy Dey

if (trim($t)) {
    if (!$tname) {
$tname = "Contact";
    }

    // Personalize the body content
    $tmpbody = str_replace("CONTACTNAME", $tname, $mybody);
    $tmpbody = str_replace("DATE", getFormattedDate($crow['startdate'] ?? ''), $tmpbody);

    $mail->Body = $tmpbody;

    // --- Internal CC/BCC Logic ---
    if (!$iscorp) {
// Non-corporate (e.g., DOE)
$mail->AddCC("rebekah@emergencyskills.com");
    } elseif ($iscorp == AGING) { // Assumes AGING is a defined constant
// Specific Corporate type (e.g., Aging)
$mail->AddCC("barbara@emergencyskills.com");
    } elseif ($iscorp) {
// General Corporate
$mail->AddCC("barbara@emergencyskills.com");
    } elseif ($iscorp == 1) {
// Corporate
$mail->AddCC("barbara@emergencyskills.com");
    }

    if ($cc) {
$mail->AddCC($cc);
    }

    // Add mandatory BCC
    $mail->AddBCC("savealife@emergencyskills.com");

	// Add mandatory CC
    $mail->AddCC("savealife@emergencyskills.com");

    // Add the main recipient
    $mail->AddAddress(trim($t));

    $mail->Send();
}
    }

    // Log successful send and redirect
    db_query("UPDATE class SET confirmemaildate = NOW() WHERE id = {$id}");
    header("Location: class_detail.php?id={$id}&sent=1");
    exit;
}

// Retrieve values for form display after potential submission/slashes
$company_name_display = htmlspecialchars($company['companyname'] ?? 'N/A');
$class_id_display = htmlspecialchars($id);
$crow_company_id = htmlspecialchars($crow['companyid'] ?? 0);
$fromname_display = htmlspecialchars($fromname ?? "Emergency Skills");
$fromemail_display = htmlspecialchars($fromemail ?? "savealife@emergencyskills.com");
$cc_display = htmlspecialchars($cc ?? '');
$subject_display = htmlspecialchars($subject ?? '');
$body_display = htmlspecialchars($body ?? '');

?>
<?php include "ssi/top.php"; ?>
<p>

<strong><span class="title">Email to Contacts from <A href='class_detail.php?id=<?php echo $class_id_display; ?>'>Class #<?php echo $class_id_display; ?></a> - <a href='viewcompany.php?id=<?php echo $crow_company_id; ?>'><?php echo $company_name_display; ?></a></span></strong>
<?php echo $err; ?>
<p>
<form method='post'>
<table>
<tr><td>From Name: </td><td><input type='text' size='40' name='fromname' value="<?php echo $fromname_display; ?>"></tD></tr>
<tr><td>From Email: </td><td><input type='text' size='40' name='fromemail' value="<?php echo $fromemail_display; ?>"></tD></tr>
<tr><td>Cc: </td><td><input type='text' size='50' name='cc' value="<?php echo $cc_display; ?>"></tD></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?php echo $subject_display; ?>"></tD></tr>
<tr><td>Body: </td></tr>
<tr><td colspan='2'>
<textarea name='body' rows='30' cols='80'><?php echo $body_display; ?></textarea></tD></tr>
<tr><td></td><td><input type='submit' name='send' value='Send Email'></td></tr>
</table>
<input type='hidden' name='id' value='<?php echo $class_id_display; ?>'>
</form>
<br><br>
</div>
</body>
</html>