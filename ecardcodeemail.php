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
    $em = getClassEmail($crow);
    $cont = getClassContact($crow);
    $comrow = getCompanyRow($crow["companyid"]);

    $initbody = 
"Dear CONTACTNAME,
The ecards for your DATE class have been sent to each participant's email. Below is a link to the spreadsheet with everyone's individual ecard code for you to keep for your records. If you have any issue viewing the codes, please let me know and I'll resend the list for you.

The email with their card will not be from Emergency Skills, but will come from AHA. The sender will be listed as \"ecards@heart.org\"and the subject line will be \"Your AHA eCard\". There is a link in the email that will take them to the AHA site, where they will be prompted to set up their account and will then be able to view their card. Sometimes the email does get diverted into the spam folder, so be sure to check there as well as the inbox.

Once each person has claimed his/her card, they will be able to access their card via the AHA site, or print a wallet size card if they prefer. The site they should use to view their card once claimed is: http://heart.org/cpr/mycards. You will also be able to view each participant's card via this website by entering their ecard code. Please be advised that you will not be able to view the card until it has been claimed by the recipient.

If you have any questions, please feel free to reach out at any time.

Thank you,

FROMNAME
Emergency Skills
212-564-6833
FROMEMAIL
";

    $body = $initbody;
    $to = $crow["email"];
    $toname = $crow["firstname"] . " " . $crow["lastname"];
}

if (isset($send) && $send) {
    $mybody = stripslashes($body);
    $mybody = str_replace("FROMNAME", $fromname, $mybody);
    $mybody = str_replace("FROMEMAIL", $fromemail, $mybody);
    //    echo($mybody);
    require_once "class.phpmailer.php";

    $tonamesarr = explode(",", $toname);
    $toarr = explode(",", $to);
    foreach ($toarr as $tmpid => $t) {
        if (isset($tonamesarr[$tmpid])) {
            $tmpname = $tonamesarr[$tmpid];
        } else {
            $tmpname = "";
        }
        
        $mail = new PHPMailer();
        $mail->From = $fromemail;
        $mail->FromName = $fromname;
        $mail->AddReplyTo($fromemail);
        
        $mail->Subject = stripslashes($subject);
        $mail->IsHTML(false);                                  // set email format to HTML
        
        if (trim($t)) {
            $tmpbody = str_replace("CONTACTNAME", $tmpname, $mybody);
            $tmpbody = str_replace("DATE", getFormattedDate($crow["startdate"]), $tmpbody);
            
            $mail->Body = $tmpbody;
            $mail->AddAddress(trim($t));
            $mail->Send();
            //    echo($tmpbody);exit;
        }
    }
    Header("Location: class_detail.php?id=$classid&sent=1");
    exit;
}
?>
<?php include "ssi/top.php"; ?>  
  <p>
  
   <strong><span class="title">Ecard Code Email for <A href='class_detail.php?id=<?php echo $classid; ?>'>Class #<?php echo $classid; ?></a> - <a href='viewcompany.php?id=<?php echo $crow["companyid"]; ?>'><?php echo $company["companyname"]; ?></a></span></strong>
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
        if (isset($session_userid)) {
            echo htmlspecialchars($session_userid);
        }
    }
?>"></td></tr>
<tr><td>To: </td><td><input type='text' size='40' name='to' value="<?php 
    if (isset($to)) {
        echo htmlspecialchars($to);
    }
?>"></td></tr>
<tr><td>To Name: </td><td><input type='text' size='40' name='toname' value="<?php 
    if (isset($toname)) {
        echo htmlspecialchars($toname);
    }
?>"></td></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?php 
    if (isset($subject) && $subject) {
        echo htmlspecialchars($subject);
    } else {
        echo "Your eCards Have Been Sent!";
    }
?>"></td></tr>
<tr><td>Body: </td></tr>
    <tr><td colspan='2'>
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