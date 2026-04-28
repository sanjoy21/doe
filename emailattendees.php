<?php
require_once('mysql.php');

if( !$specialadmin )
{
    header( "location: login.php" );
    exit;
}

$crow = getClassRow( $id );
$classemail = getClassEmail( $crow );
$company = getCompanyRow( $crow["companyid"] );


if( !isset($_POST['send']) )
{

    $em = getClassEmail( $crow );
    $comrow = getCompanyRow( $crow["companyid"] );

    $initbody = 
    "Dear CONTACTNAME,


Thank you,

Barbara Kinter
Emergency Skills
212-564-6833
barbara@emergencyskills.com";


    $body = $initbody;
    $to = $crow['email'];
    $toname = $crow['firstname'] . " " . $crow['lastname'];
}

if( isset($_POST['send']) )
{
    $body = $_POST['body'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $fromname = $_POST['fromname'] ?? '';
    $fromemail = $_POST['fromemail'] ?? '';
    
    $mybody = stripslashes( $body );

    require_once "class.phpmailer.php";

    $toarr = db_query_array( "SELECT email, CONCAT(firstname, ' ', lastname) FROM responder_to_class rtc, responders_esi r WHERE r.responderid = rtc.responderid AND classid = " . intval($id), "email", "name" );
    
    foreach( $toarr as $t => $tname )
    {
        $mail = new PHPMailer();
        $mail->From = $fromemail;
        $mail->FromName = $fromname;
        $mail->AddReplyTo( $fromemail );
        
        $mail->Subject = stripslashes( $subject );
        $mail->IsHTML(false);                                  // set email format to HTML

        if( trim( $t ) )
        {
            if( !$tname )
            {
                $tname = "ESI attendee";
            }
            $tmpbody = str_replace( "CONTACTNAME", $tname, $mybody );
            $tmpbody = str_replace( "DATE", getFormattedDate( $crow['startdate'] ), $tmpbody );
            
            $mail->Body = $tmpbody;
            $mail->AddAddress(trim( $t ));
            $mail->Send();
            // echo( $tmpbody );exit;
        }
    }
    header( "Location: class_detail.php?id=" . intval($id) . "&sent=1" );
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<p>
<strong><span class="title">Email to Attendees from <a href='class_detail.php?id=<?php echo intval($id); ?>'>Class #<?php echo htmlspecialchars($id); ?></a> - <a href='viewcompany.php?id=<?php echo intval($crow['companyid']); ?>'><?php echo htmlspecialchars($company['companyname']); ?></a></span></strong>
<?php echo isset($err) ? htmlspecialchars($err) : ''; ?>
<p>
<!--start center content-->
<form method='post'>
<table>
<tr><td>From Name: </td><td><input type='text' size='40' name='fromname' value="<?php echo isset($fromname) && $fromname ? htmlspecialchars($fromname) : 'Emergency Skills'; ?>"></td></tr>
<tr><td>From Email: </td><td><input type='text' size='40' name='fromemail' value="<?php echo isset($fromemail) && $fromemail ? htmlspecialchars($fromemail) : (isset($session_userid) ? htmlspecialchars($session_userid) : ''); ?>"></td></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>"></td></tr>
<tr><td>Body: </td></tr>
<tr><td colspan='2'>
<textarea name='body' rows='30' cols='80'><?php echo isset($body) ? htmlspecialchars($body) : ''; ?></textarea></td></tr>
<tr><td></td><td><input type='submit' name='send' value='Send Email'></td></tr>
</table>
<br><br>
</div>
</body>
</html>