<?php
require_once('mysql.php');

if (!$specialadmin) {
    Header("location: login.php");
    exit;
}

$crow = getClassRow($classid);
$classemail = getClassEmail($crow);
$classcontact = getClassContact($crow);
$company = getCompanyRow($crow["companyid"]);

if (!$_POST) {
    $tmpattendees = get_attendees($crow["id"], false, true);
    $attendees = array();
    foreach ($tmpattendees as $arow) {
        $attendee = get_attendee($arow["responderid"]);
        if (isset($attendee["lastname"]) && isset($attendee["firstname"]) && isset($arow["responderid"])) {
            $attendees[$attendee["lastname"] . "," . $attendee["firstname"] . "," . $arow["responderid"]] = $attendee;
        }
    }
    ksort($attendees);

    $initbody = "
Dear $classcontact,

Thank you very much for your recent training program. The American Heart Association requires that we have a unique email address for each participant to which to issue the certification.  Please review the list of participants below for whom we either do not have an email address, or for whom the email address was not legible on the registration paperwork.  Once you have replied to this message and provided the email addresses, cards will be issued.
 

Class: " . (isset($allclass_names[$company["iscorp"]][$crow["code"]]) ? $allclass_names[$company["iscorp"]][$crow["code"]] : "") . "
Date: " . getFormattedDateWTime($crow["startdate"]) . " " . getEndDateStr($crow["enddate"]) . "
Class #: $classid

";

    $i = 1;
    foreach ($attendees as $attendee) {
        $firstName = isset($attendee["firstname"]) ? $attendee["firstname"] : "";
        $lastName = isset($attendee["lastname"]) ? $attendee["lastname"] : "";
        $email = isset($attendee["email"]) ? $attendee["email"] : "";
        $initbody .= "$i. $firstName $lastName $email\n";
        $i++;
    }

    $initbody .= "

Thank you,
Amy

Amy Spagnuolo
Emergency Skills, Inc.
212-564-6833";

    $body = $initbody;
    $to = $classemail;
}

if (isset($send) && $send) {
    $body = stripslashes($body);
    require_once "class.phpmailer.php";
    $mail = new PHPMailer();
    $mail->From = $fromemail;
    $mail->FromName = $fromname;
    $mail->AddReplyTo($fromemail);

    $ext = "\n\n ESI Use Only: <a href='http://" .SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/nameshandler.php?classid=$classid'>http://doe." . URL_WITHOUT_SUBDOMAIN . "/handler.php?classid=$classid</a>\n\n";
        
    $mail->Subject = stripslashes($subject);
    $mail->IsHTML(true);                                  // set email format to HTML
    $mail->Body = nl2br($body . $ext);

    $toarr = explode(",", $to);
    foreach ($toarr as $t) {
        if (trim($t)) {
            $mail->AddAddress(trim($t));
        }
    }
    $ccarr = explode(",", $cc);
    foreach ($ccarr as $c) {
        if (trim($c)) {
            $mail->AddCC(trim($c));
        }
    }
    $mail->Send();
    db_query("update class set lastsentconfirmnames = now() where id = $classid");
    $err = "<font color='red'>Sent!</font>";
}
?>
<?php include "ssi/top.php"; ?>  
  <p>
  
   <strong><span class="title">Confirm Names for <A href='class_detail.php?id=<?php echo $classid; ?>'>Class #<?php echo $classid; ?></a> - <a href='viewcompany.php?id=<?php echo $crow["companyid"]; ?>'><?php echo htmlspecialchars($company["companyname"]); ?></a></span></strong>
<?php 
if (isset($err)) {
    echo $err;
}
?>  
  <p>
<!--start center content-->
  <form method='post'>
<table>
<tr><td>From Name: </td><td><input type='text' size='40' name='fromname' value="<?php 
    if (isset($fromname) && $fromname) {
        echo htmlspecialchars($fromname);
    } else {
        echo "Emergency Skills";
    }
?>"></td></tr>
<tr><td>From Email: </td><td><input type='text' size='40' name='fromemail' value="<?php 
    if (isset($fromemail) && $fromemail) {
        echo htmlspecialchars($fromemail);
    } else {
        echo "cards@emergencyskills.com";
    }
?>"></td></tr>
<tr><td>To: </td><td><input type='text' size='40' name='to' value="<?php 
    if (isset($to)) {
        echo htmlspecialchars($to);
    }
?>"></td></tr>
<tr><td>CC: </td><td><input type='text' size='40' name='cc' value="<?php 
    if (isset($cc)) {
        echo htmlspecialchars($cc);
    }
?>"></td></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?php 
    if (isset($subject) && $subject) {
        echo htmlspecialchars($subject);
    } else {
        echo "Emergency Skills - Please Provide Missing Email Address - " . htmlspecialchars($company["companyname"]);
    }
?>"></td></tr>
<tr><td>Body: </td></tr>
    <tr><td colspan='2'>
<pre> <?php echo htmlentities("<b>for bold</b> <i>for italics</i>"); ?></pre>
<textarea name='body' rows='30' cols='80'><?php 
    if (isset($body)) {
        echo htmlspecialchars($body);
    }
?></textarea></td></tr>
 <tr><td></td><td><input type='submit' name='send' value='Send Email'></td></tr>
</table>
<br><br>
</div>
</body>
</html>