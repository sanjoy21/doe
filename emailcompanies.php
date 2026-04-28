<?php
require_once('mysql.php');

// Initialize variables from request/session
$specialadmin = $specialadmin ?? null;
$send = $_POST['send'] ?? null;
$sendtest = $_POST['sendtest'] ?? null;
$body = $_POST['body'] ?? null;
$origbody = $origbody ?? null; // Restore from previous execution if available
$subject = $_POST['subject'] ?? null;
$fromemail = $_POST['fromemail'] ?? null;
$emailtypes = $_POST['emailtypes'] ?? [];
$session_iscorp = $_SESSION['session_iscorp'] ?? 0; // Assuming this is set in session
$all_emailtypes = $all_emailtypes ?? []; // Assumed external array

// --- Security Check ---
if (!$specialadmin) {
    header("Location: login.php");
    exit;
}
?>

<?php include "ssi/top.php"; ?>

<?php 
// --- Email Sending Logic ---
if ($send || $sendtest) {
    
    // Store original body before formatting for email display back in the form
    $origbody = $body; 
    
    // Format body for HTML email (nl2br converts newlines to <br>)
    $body_html = nl2br(stripslashes($body));
    $body_text = stripslashes($body); // Use unformatted version for personalization logic

    // Assumed PHPMailer class is available
    require_once "class.phpmailer.php";
    
    $ext = "";
    
    if ($send) {
        // --- 1. LIVE SEND TO COMPANY CONTACTS ---
        if (count($emailtypes) > 0) {
            // Join selected email types for SQL IN clause
            $ets = "'" . join("', '", $emailtypes) . "'";
            $ext = " emailtype IN ({$ets}) AND ";
        }
        
        // Fetch contacts based on filters
        $sql_companies = "SELECT * FROM company_esi 
                          WHERE {$ext} deleted = 0 
                          AND contactemail > '' 
                          AND iscorp = {$session_iscorp}";
                          
        $companies = db_query_rows($sql_companies); 
        
        $already = array();
        foreach ($companies as $trow) {
            $contact_email = $trow["contactemail"] ?? '';
            $contact_name = $trow["contactname"] ?? '';
            
            // Skip if this email address has already been processed
            if (isset($already[$contact_email])) {
                continue;
            }
            
            $mail = new PHPMailer();
            $mail->From = $fromemail;
            $mail->FromName = $fromemail;
            $mail->AddReplyTo($fromemail);
            
            $mail->Subject = stripslashes($subject);
            $mail->IsHTML(true); // set email format to HTML
            
            // Personalize body content
            // NOTE: Using $body_text here to ensure personalization is done once per contact
            $tmpbody = str_replace("%NAME%", $contact_name, $body_html); 
            
            $mail->Body = $tmpbody;
            $mail->AddAddress($contact_email);
            echo "emailing: " . htmlspecialchars($contact_email) . "<br>";
            $already[$contact_email] = 1;
            $mail->Send();
        }
    }
    
    // --- 2. SEND TO ADMIN/SENDER (Test or Confirmation) ---
    
    // Use the original body formatting for the final email to the sender
    $mail = new PHPMailer();
    $mail->From = $fromemail;
    $mail->FromName = $fromemail;
    $mail->AddReplyTo($fromemail);
    
    $mail->Subject = stripslashes($subject);
    $mail->IsHTML(true); // set email format to HTML
    
    // NOTE: Personalization isn't typically needed for the sender test, so use $body_html directly
    $mail->Body = $body_html;
    
    $sender_email = $_SESSION['session_userid'] ?? ''; // Assumed admin email from session
    
    echo "emailing: " . htmlspecialchars($sender_email) . "<br>";
    $mail->AddAddress($sender_email);
    $mail->Send();

    // --- Confirmation Message ---
    if ($send) {
        echo "<font color='red'>Sent to contacts</font>";
    } else {
        echo "<font color='red'>Sent to TEST contacts</font>";
    }
}

// Restore body content for display back in the form
$body = $origbody;
?>
 <p>
<strong><span class="title">EMAIL <?php echo strtoupper(getSchoolStr("school") ?? 'CONTACTS'); ?> CONTACTS</span></strong>
 
</p>
 <form method='post'>
<table>
 <tr>
        <td colspan='2'>
            <font color='red'>Note: YOU ARE SET TO <?php echo $session_iscorp ? "CORP" : "DOE"; ?></font>
        </td>
    </tr>
 <?php if ($session_iscorp) { ?>
 <tr>
        <td valign='top'>Type: </td>
        <td>
            <select name='emailtypes[]' MULTIPLE>
                <?php foreach ($all_emailtypes as $et) { 
                    $selected = in_array($et, $emailtypes) ? "SELECTED" : "";
                    $et_safe = htmlspecialchars($et);
                    echo "<option value='{$et_safe}' {$selected}>{$et_safe}</option>";
                } ?>
            </select>
            <i>leave blank for all</i>
        </td>
    </tr>
<?php } ?>
<tr>
    <td>From Email: </td>
    <td><input type='text' size='40' name='fromemail' value="<?php echo htmlspecialchars($fromemail ?? ''); ?>"></td>
</tr>
<tr>
    <td>Subject: </td>
    <td><input type='text' size='40' name='subject' value="<?php echo htmlspecialchars(stripslashes($subject ?? '')); ?>"></td>
</tr>
<tr>
    <td>Body: </td>
    <td>
        <pre> <?php echo htmlentities("<b>for bold</b> <i>for italics</i>"); ?></pre>
        <textarea name='body' rows='30' cols='60'><?php echo htmlspecialchars(stripslashes($body ?? '')); ?></textarea>
    </td>
</tr>
 <tr>
        <td></td>
        <td>
            <input onclick='return confirm( "Are you sure you want to send this email to EVERYONE?" )' type='submit' name='send' value='Send Email'>
        </td>
    </tr>
 <tr>
        <td></td>
        <td>
            <input type='submit' name='sendtest' value='Send TEST Email'>
        </td>
    </tr>
</table>
<br><br>
</div>
</body>
</html>