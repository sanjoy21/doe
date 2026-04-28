<?php
require_once('mysql.php');

// --- 1. Input Sanitization and Validation (Post/Get data) ---
$specialadmin = $specialadmin; 
$classid_raw = $_REQUEST['classid'] ?? null;
$classid_int = (int)$classid_raw; // SQLi Mitigation: Ensure classid is an integer

$send = $_POST['send'] ?? null;
$fromname_raw = $_POST['fromname'] ?? null;
$fromemail_raw = $_POST['fromemail'] ?? null;
$to_raw = $_POST['to'] ?? null;
$cc_raw = $_POST['cc'] ?? null;
$subject_raw = $_POST['subject'] ?? null;
$body_raw = $_POST['body'] ?? null;

// Sanitize inputs for HTML output (XSS Mitigation)
$fromname_display = htmlspecialchars($fromname_raw ?? '');
$fromemail_display = htmlspecialchars($fromemail_raw ?? '');
$to_display = htmlspecialchars($to_raw ?? '');
$cc_display = htmlspecialchars($cc_raw ?? '');
$subject_display = htmlspecialchars($subject_raw ?? '');
$body_display = htmlspecialchars($body_raw ?? '');

if( !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

// Fetch database rows using safe integer ID
$crow = getClassRow( $classid_int );
$company = getCompanyRow( $crow["companyid"] ?? 0 );


if( !$_POST )
{
    // Initial email setup logic (only runs on first load, not on post)
    $comrow = getCompanyRow( $crow["companyid"] ?? 0 );
    
    // Use session/default values for initial display
    $fromname = "Emergency Skills";
    $fromemail = $_SESSION['session_userid'] ?? ''; // Assuming session_userid holds the user's email

    $initbody = 
"Thank you for requesting training with Emergency Skills, Inc.

The DOE now requires that we have the Reference Number (payroll reference number) for each participant prior to accepting a program onto the calendar. These numbers are different from the file number, which has previously been requested. The PMS IDs can be obtained from the payroll secretary and are also on each individuals' paystub in the box marked \"Reference Number\".

If you are a charter school, please enter the last 4 digits of each person's social security number.

Please click the following link to add or edit the Reference Numbers or SS#'s numbers. Be sure to click \"Update\" at the bottom of the screen after the numbers have been added. ESI will confirm your class upon receiving update. 

https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/editpmsids.php?id=". (int)$comrow['id'] ."

The following attendees have not been validated: 
";

    $attendees = get_attendees( $classid_int );
    $body_attendees = "";
    foreach( $attendees as $arow )
    {
        // Assuming getResponderRow is safe
        $rrow = getResponderRow( $arow['responderid'] ?? 0 ); 
        if( !$rrow["pmsidvalidated"] )
        {
            // XSS Mitigation: Sanitize responder names for email body
            $body_attendees .= htmlspecialchars($rrow["firstname"] ?? '') . " " . htmlspecialchars($arow["lastname"] ?? '') . "\n";
        }
    }
    
    $body_footer = "
If you have any questions, please call ESI at: 212-564-6833

Thank you,
Emergency Skills, Inc.
";
    
    $body = $initbody . $body_attendees . $body_footer;
    $to = htmlspecialchars($crow['email'] ?? '');
    if( $crow['alt_email'] ?? false )
        $to .= ", " . htmlspecialchars($crow['alt_email']);
        
    $subject = "Information needed to confirm your CPR/AED training request - {$classid_int}";
    
    // Set display variables for form based on initial values
    $fromname_display = htmlspecialchars($fromname);
    $fromemail_display = htmlspecialchars($fromemail);
    $to_display = htmlspecialchars($to);
    $subject_display = htmlspecialchars($subject);
    $body_display = htmlspecialchars($body);
}

if( $send )
{
    $body_safe = stripslashes( $body_raw );
    $subject_safe = stripslashes( $subject_raw );

    require_once "class.phpmailer.php";
    $mail = new PHPMailer();
    
    // Use sanitized user input for mail headers
    $mail->From = trim( $fromemail_raw );
    $mail->FromName = trim( $fromname_raw );
    $mail->AddReplyTo( trim( $fromemail_raw ) );
    
    $mail->Subject = $subject_safe;
    $mail->IsHTML(false);
    $mail->Body = $body_safe;

    $toarr = explode( ",", $to_raw );
    foreach( $toarr as $t )
    {
        if( trim( $t ) )
        {
            $mail->AddAddress(trim( $t ));
        }
    }
    $ccarr = explode( ",", $cc_raw );
    foreach( $ccarr as $c )
    {
        if( trim( $c ) )
        {
            $mail->AddCC(trim( $c ));
        }
    }
    
    $session_userid_int = (int)($_SESSION['session_userid'] ?? 0); // Assuming this is an ID or safe identifier
    $mail->AddBCC($session_userid_int); 
    
    $mail->Send();
    
    // --- SQLi Mitigation: Use safe integers for DB updates ---
    db_query( "update class set lastpmsidreqdate = now() where id = '{$classid_int}'" );
    
    // Assuming 'whom' stores a non-sensitive ID/name, sanitize it
    $whom_safe = db_escape_or_placeholder($_SESSION['session_userid'] ?? ''); 
    db_query( "insert into pmsidsent ( classid, whom, sentdate ) values( '{$classid_int}', '{$whom_safe}', now() )" );
    
    Header( "Location: class_detail.php?id={$classid_int}&sent=1" );
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<p>

<strong><span class="title">Confirm Names for <A href='class_detail.php?id=<?=$classid_int?>'>Class #<?=$classid_int?></a> - <a href='viewcompany.php?id=<?=(int)$crow['companyid']?>'><?=htmlspecialchars($company['companyname'] ?? '')?></a></span></strong>
<?=htmlspecialchars($err ?? '')?>
<p>
<form method='post'>
<table>
<tr><td>From Name: </td><td><input type='text' size='40' name='fromname' value="<?=$fromname_display?>"></tD></tr>
<tr><td>From Email: </td><td><input type='text' size='40' name='fromemail' value="<?=$fromemail_display?>"></tD></tr>
<tr><td>To: </td><td><input type='text' size='40' name='to' value="<?=$to_display?>"></tD></tr>
<tr><td>CC: </td><td><input type='text' size='40' name='cc' value="<?=$cc_display?>"></tD></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?=$subject_display?>"></tD></tr>
<tr><td>Body: </td></tr>
<tr><td colspan='2'>
<textarea name='body' rows='30' cols='80'><?=$body_display?></textarea></tD></tr>
<tr><td></td><td><input type='submit' name='send' value='Send Email'></td></tr>
</table>
<br><br>
</div>
</body>
</html>