<?php
require_once('mysql.php');

// --- Security Check ---
if (!$specialadmin) {
    header("Location: login.php");
    exit;
}

// --- Email/Text Sending Logic ---
if ($send) {
    $ext = "";
    $natext = "";
    
    // --- 1. Apply Filters ---
    
    // Drill/Inspectors only
    if ($viewschoolsonly) {
        $ext .= " AND viewschools = 1 ";
    }
    
    // Include National trainers
    if (!$andnational) { 
        $natext .= " AND national = 0";
    }

    // Exclude Paused trainers
    if ($excludepaused) {
        $natext .= " AND paused = 0";
    }

    // Include Training Sites trainers
    if (!$andtrainingsites) {
        $natext .= " AND trainingsites = 0";
    }

    // First Aid Only
    if ($aidonly) {
        $ext .= " AND firstaid = 1";
    }
    
    // Has Car Only
    if ($hascar) {
        $ext .= " AND hascar = 1";
    }
    
    // ASHI Only
    if ($ashi) {
        $ext .= " AND ashi = 1";
    }
    
    // Remote Only (This overrides/replaces $ext for location)
    if ($remoteonly) {
        // NOTE: The original SQL assumes a table trainer_to_borough exists
        $ext = " AND user.id IN (SELECT trainerid FROM trainer_to_borough WHERE borough = 'Remote') ";
    }

    // Text sending (Original code included a commented out test line here)
    // if ($sendtext) {
    // $ext .= " and userid = 'SFennessy@schools.nyc.gov'";
    // } 

    // --- 2. Fetch Trainers ---
    $sql = "SELECT user.* FROM user {$extrajoin} 
            WHERE usertype = 'trainer' {$ext} {$natext} 
            AND inactive = 0"; 

    $trainers = db_query_rows($sql); 
    
    $subject_safe = mysqli_real_escape_string($GLOBALS['link'], stripslashes($subject));
    $body_safe = mysqli_real_escape_string($GLOBALS['link'], stripslashes($body));
    
    // --- 3. Acknowledge Tracking Setup (Email only) ---
    $sentid = 0;
    if ($doack && !$sendtext) {
        // Record the group email details
        $sentid = db_query_insert_id("INSERT INTO emailsentgroup (subject, datesent, fromemail, body) VALUES ('{$subject_safe}', NOW(), '{$fromemail}', '{$body_safe}')");
    }
    
    // Assumed PHPMailer class is available
    require_once "class.phpmailer.php";
    
    // --- 4. Loop Through Trainers and Send ---
    foreach ($trainers as $trow) {
        $trainer_userid = $trow['userid'] ?? '';
        $trainer_id = $trow['id'] ?? 0;

        if ($sendtext) {
            // Send Text
            echo "sending text to " . htmlspecialchars($trainer_userid) . "<br>";
            // Assumed external function
            sendText(stripslashes($subject), stripslashes($body), $trow); 
        } else {
            // Send Email
            $mail = new PHPMailer();
            $mail->IsSMTP(); // Added later to fix html format by Sanjoy Dey
            $mail->Host = 'localhost'; // Added later to fix html format by Sanjoy Dey
            $mail->SMTPAuth = false; // Added later to fix html format by Sanjoy Dey
            $mail->Port = 25; // Added later to fix html format by Sanjoy Dey
            $mail->From = $fromemail;
            $mail->FromName = $fromemail;
            $mail->AddReplyTo($fromemail);
            
            $ack = "";
            $sent = 0;

            // Insert individual receipt record if acknowledgement is requested
            if ($doack) {
                $sent = db_query_insert_id("INSERT INTO emailsent (emailsentid, sentto) VALUES ('{$sentid}', '{$trainer_id}')");
                // Construct the acknowledgement link
                $ack = "\n <br><br>Please acknowledge receipt of this email by clicking <a href='https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN ."/emailack.php?lackid={$sent}'>here.</a>\n";
            }
            
            $mail->Subject = stripslashes($subject);
            $mail->IsHTML(true); 
            
            $mail->Body = nl2br(stripslashes($body) . $ack);
            
            $mail->AddAddress($trainer_userid);
            $mail->Send();
        }
    }

    // --- 5. Send Confirmation/Test to Admin (sarahg@emergencyskills.com + smushogillen@gmail.com for text) ---
    
    if ($sendtext) {
        // Send text to specific test user
        // Assumed external function
        $rrow = getUserRow("smushogillen@gmail.com"); 
        sendText(stripslashes($subject), stripslashes($body), $rrow);
    } else {
        // Send email confirmation
        $mail = new PHPMailer();
        $mail->IsSMTP(); // Added later to fix html format by Sanjoy Dey
        $mail->Host = 'localhost'; // Added later to fix html format by Sanjoy Dey
        $mail->SMTPAuth = false; // Added later to fix html format by Sanjoy Dey
        $mail->Port = 25; // Added later to fix html format by Sanjoy Dey
        $mail->From = $fromemail;
        $mail->FromName = $fromemail;
        $mail->AddReplyTo($fromemail);

        $mail->Subject = stripslashes($subject);
        $mail->IsHTML(true); 
        
        // No acknowledgement link for this confirmation email
        $mail->Body = nl2br(stripslashes($body)); 
        
        $mail->AddAddress("sarahg@emergencyskills.com");
        $mail->Send();
    }

    $send_type = $sendtext ? "text" : "email";
    $err = "<br><font color='red'>Sent {$send_type} to all matching trainers</font><br><br>";
}
?>
<?php include "ssi/top.php"; ?>
 <p>
 <strong><span class="title">EMAIL/TEXT TRAINERS</span></strong>
<a href='traineracks.php'>Check on email receipt</a>
 <?php echo $err; ?>
</p>
 <form method='post'>
<table class="table3" >
<tr><td colspan='2'>
 <b>Send Text</b>? <input type='checkbox' name='sendtext' value="1" onClick="$('#sendme').val( this.checked ? 'Send Text to All Active Trainers':'Send Email to All Active Trainers' );" <?php echo $sendtext ? "CHECKED" : ""; ?>> <i>Note: Remember to add your cell number to the text for replies.</i>
<br>

Drill/Inspectors only? <input type='checkbox' name='viewschoolsonly' value="1" <?php echo $viewschoolsonly ? "CHECKED" : ""; ?>>
<br>
Exclude Paused? <input type='checkbox' name='excludepaused' value="1" <?php echo $excludepaused ? "CHECKED" : ""; ?>>
<br>
Send to National as well ? <input type='checkbox' name='andnational' value="1" <?php echo $andnational ? "CHECKED" : ""; ?>>
<br>
Send to Training Sites as well ? <input type='checkbox' name='andtrainingsites' value="1" <?php echo $andtrainingsites ? "CHECKED" : ""; ?>>
<br>
Remote Only ? <input type='checkbox' name='remoteonly' value="1" <?php echo $remoteonly ? "CHECKED" : ""; ?>>
<br>
First Aid Only ? <input type='checkbox' name='aidonly' value="1" <?php echo $aidonly ? "CHECKED" : ""; ?>>
<br>
Has Car Only ? <input type='checkbox' name='hascar' value="1" <?php echo $hascar ? "CHECKED" : ""; ?>>
<br>
ASHI Only ? <input type='checkbox' name='ashi' value="1" <?php echo $ashi ? "CHECKED" : ""; ?>>
<br>
Require Read Receipt ? (not valid for text)<input type='checkbox' name='doack' value="1" <?php echo $doack ? "CHECKED" : ""; ?>>
</td></tr>
<tr><td>From Email: (not valid for text) </td><td><input type='text' size='40' name='fromemail' value="<?php echo htmlspecialchars($fromemail); ?>"></td></tr>
<tr><td>Subject: </td><td><input type='text' size='40' name='subject' value="<?php echo htmlspecialchars($subject); ?>"></td></tr>
<tr><td>Body: </td><td>
<pre> <?php echo htmlentities("<b>for bold</b> <i>for italics</i> (<--not valid for text)"); ?></pre>
<textarea name='body' rows='10' cols='60'><?php echo htmlspecialchars($body); ?></textarea></td></tr>
 <tr><td></td><td><input id="sendme" type='submit' name='send' value='Send Email To All Active Trainers'></td></tr>
</table>
<br><br>
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