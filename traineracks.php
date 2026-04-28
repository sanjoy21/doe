<?php
require_once('mysql.php');

if( !$specialadmin )
{
    header( "Location: login.php" );
    exit;
}


if( $delid )
{
    $safe_delid = $delid;
    db_query( "DELETE FROM emailsentgroup WHERE id = {$safe_delid}" );
    db_query( "DELETE FROM emailsent WHERE emailsentid = {$safe_delid}" );
}

if( $id && $resend )
{
    $safe_id = $id;
    $r = db_query_first( "SELECT * FROM emailsentgroup WHERE id = {$safe_id}" );

    $subject = $r['subject'];
    $body = $r['body'];
    $fromemail = $r['fromemail'];
    
    $trows = db_query_rows( "SELECT user.*, emailsent.id AS esid 
                             FROM emailsent, user 
                             WHERE user.id = sentto 
                             AND emailsentid = {$safe_id} 
                             AND ack = 0" );
                             
    require_once "class.phpmailer.php";
    
    foreach( $trows as $trow )
    {
        $mail = new PHPMailer();
        $mail->From = $fromemail;
        $mail->FromName = $fromemail;
        $mail->AddReplyTo( $fromemail );

        $sent = $trow['esid'];
        $recipient_email = $trow['userid'];
        
        if (!$recipient_email || !$sent) continue; // Skip if crucial data is missing
        
        // Acknowledgment link setup
        $ack = "\n <br><br>Please acknowledge receipt of this email by clicking <a href='https://". SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/emailack.php?lackid={$sent}'>here.</a>\n";
        
        $mail->Subject = stripslashes( $subject );
        $mail->IsHTML(true);
        $mail->Body = nl2br( stripslashes( $body ) . $ack );
        
        $mail->AddAddress($recipient_email);
        
        // Error suppression on Send() is generally not recommended but retained for original behavior context
        @$mail->Send(); 
    }
    
    // Redirect after resend
    header( "Location: traineracks.php?id={$safe_id}&sent=1");
    exit;
}

// --- HTML Output Starts ---
?>
<?php include "ssi/top.php"; ?>
<p>
<strong><span class="title">EMAILS SENT</span></strong>
<?php if( $sent ) { ?> <br><font color='red'>Emails have been resent.</font><br><?php } ?> 
</p>
<?php if( !$id ) { 
// Display list of email groups
$res = db_query_rows( "SELECT * FROM emailsentgroup ORDER BY datesent DESC" );
echo( "<table border='1' cellpadding='2' cellspacing='0' width='500'>" );

foreach( $res as $r )
{ 
    $r_id = htmlspecialchars($r['id']);
    $r_subject = htmlspecialchars($r['subject']);
    $r_datesent = htmlspecialchars($r['datesent']);
    
    echo( "<tr>
              <td><a href='traineracks.php?id={$r_id}'>{$r_subject}</a></td>
              <td><a href='traineracks.php?id={$r_id}'>{$r_datesent}</a></td>
              <td><a href='traineracks.php?delid={$r_id}'>delete</a></td>
          </tr>" );
}
echo( "</table>" );
?>
<?php } else { 
// Display recipients and acknowledgment status for a specific group
$safe_id = $id;

echo( "<a href='traineracks.php?id={$safe_id}&resend=1'>Resend unacknowledged</a><Br><br>" );

$res = db_query_rows( "SELECT emailsent.*, user.* FROM emailsent, user 
                       WHERE sentto = user.id AND emailsentid = {$safe_id} 
                       ORDER BY last_name" );
                       
echo( "<table border='1' cellpadding='2' cellspacing='0' width='500'>" );

foreach( $res as $r )
{
    // PHP 8.2 Fix: Quote array keys and use htmlspecialchars()
    $first_name = htmlspecialchars($r['first_name']);
    $last_name = htmlspecialchars($r['last_name']);
    $ack_status = ($r['ack']) ? "Yes" : "No";

    echo( "<tr>
              <td>{$first_name} {$last_name}</td>
              <td>{$ack_status}</td>
          </tr>" );
}
?>
</table>

<?php }

// Display email details
if( $id ) { 
    $safe_id = $id;
    $r = db_query_first( "SELECT * FROM emailsentgroup WHERE id = {$safe_id}" );
    
    // PHP 8.2 Fix: Quote array keys and use htmlspecialchars()
    $subject = htmlspecialchars($r['subject']);
    $body = htmlspecialchars($r['body']);
    $fromemail = htmlspecialchars($r['fromemail']);
?>
<br><br>
<table cellpadding='0' border='1' cellspacing='0'>
<?php
echo( "<tr><td>From:</td><td>{$fromemail}</td></tr>" );
echo( "<tr><td>Subject:</td><td>{$subject}</td></tr>" );
echo( "<tr><td valign='top'>Body:</td><td>".nl2br( $body )."</td></tr>" );
?>
</table>
<?php } ?>

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