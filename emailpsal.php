<?php
require_once('mysql.php');


$origbody = $body;

?>
<?php include "ssi/top.php"; ?>
<?php 
// Set from email
if( !isset($fromemail) || !$fromemail ) {
    if( isset($thisusersrow["userid"]) ) {
        $fromemail = $thisusersrow["userid"];
    }
}

// Process email sending
if( $send ) {
    $origbody = $body;
    $body = nl2br( stripslashes( $body ) );

    require_once "class.phpmailer.php";
    
    $emails = array();

    // Get AED rows and collect emails
    if( $id > 0 ) {
        $aed_rows = db_query_rows("select * from aed_esi where clientid=$id and deleted=0 and (location = 'PSAL' and psalassignedto > 0) or (psalassigned > '') ");
        if( is_array($aed_rows) ) {
            foreach( $aed_rows as $a ) {            
                if( isset($a["psalassignedemail"]) && $a["psalassignedemail"] ) {
                    $emails[] = $a["psalassignedemail"];
                }
            }
        }
    }
    
    // Remove duplicates
    $emails = array_unique($emails);
    
    if( !empty($emails) ) {
        $already = array();
        foreach( $emails as $toemail ) {
            $mail = new PHPMailer();
            $mail->From = $fromemail;
            $mail->FromName = $fromemail;
            $mail->AddReplyTo( $fromemail );
            
            $mail->Subject = stripslashes( $subject );
            $mail->IsHTML(true); // set email format to HTML
            $mail->Body = $body;
            $mail->AddAddress($toemail);
            $mail->Send();
        }
        echo( "<font color='red'>Sent!</font>" );
    } else {
        echo( "<font color='red'>No recipients found!</font>" );
    }
}
// Restore original body for form
$body = $origbody;
?>
<p>

<strong><span class="title">EMAIL COACHES</span></strong>

<p>
<!--start center content-->
<form method='post'>
<table>
    <?php if( $session_iscorp ) { ?>
    <tr>
        <td valign='top'>Type: </td>
        <td>
            <select name='emailtypes[]' MULTIPLE>
                <?php 
                if( is_array($all_emailtypes) ) {
                    foreach( $all_emailtypes as $et ) { 
                        $selected = (is_array($emailtypes) && in_array( $et, $emailtypes )) ? "SELECTED" : "";
                        echo( "<option value='" . htmlspecialchars($et) . "' $selected>" . htmlspecialchars($et) . "</option>" );
                    }
                }
                ?>
            </select>
            <i>leave blank for all</i>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td>From Email: </td>
        <td><input readonly type='text' size='40' name='fromemail' value="<?php echo htmlspecialchars($fromemail); ?>"></td>
    </tr>
    <tr>
        <td>Subject: </td>
        <td><input type='text' size='40' name='subject' value="<?php echo htmlspecialchars(stripslashes( $subject )); ?>"></td>
    </tr>
    <tr>
        <td>Body: </td>
        <td>
            <pre><?php echo htmlentities( "<b>for bold</b> <i>for italics</i>"); ?></pre>
            <textarea name='body' rows='30' cols='60'><?php echo htmlspecialchars(stripslashes( $body )); ?></textarea>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <input onclick='return confirm( "Are you sure you want to send this email?" )' type='submit' name='send' value='Send Email'> 
            <input onclick='document.location.href="editpsals.php?id=<?php echo $id; ?>";' type='button' name='back' value='Back to PSAL AEDs'>
        </td>
    </tr>
</table>
</form>
<br><br>
</div>
</body>
</html>