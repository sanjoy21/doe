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

    $initbody = "
Dear CONTACTNAME,

The digital certifications for your DATE class have been sent to each participant's email. 

The email with their card will not be from Emergency Skills, but will come from the Health & Safety Institute. The sender will be listed as info@hsi.com and the subject line will be \"Your Class Digital Certificate\". There is a link in the email that will open their card and information on how to access the digital book. Sometimes the email does get diverted into the spam folder, so be sure to check there as well as the inbox.

A copy of the digital certifications has been saved on Alive!net and can be sent to you as a pdf upon request.

If you have any questions, please feel free to reach out at any time.

Thank you,

Barbara Kinter
Emergency Skills
212-564-6833
barbara@emergencyskills.com
"; 

    $body = $initbody;

    $body = str_replace("DATE", getFormattedDate($crow["startdate"]), $body);
    $body = str_replace("LOCATION", $crow["training_location"], $body);

    //$to = $crow[email];
    //$toname = $crow[firstname] . " " . $crow[lastname];
}

$toarr = array();
if (isset($crow["email"])) {
    $toarr[$crow["email"]] = $crow["firstname"] . " " . $crow["lastname"];
}
if (isset($crow["alt_email"]) && $crow["alt_email"]) {
    $toarr[$crow["alt_email"]] = $crow["alt_firstname"] . " " . $crow["alt_lastname"];
}

if (isset($send) && $send) {
    $mybody = stripslashes($body);
    //    echo($mybody);
    require_once "class.phpmailer.php";

    foreach ($toarr as $t => $tname) {
        $mail = new PHPMailer();
        $mail->From = $fromemail;
        $mail->FromName = $fromname;
        $mail->AddReplyTo($fromemail);
        
        $mail->Subject = stripslashes($subject);
        $mail->IsHTML(false);                                  // set email format to HTML
        
        if (trim($t)) {
            $tmpbody = str_replace("CONTACTNAME", $tname, $mybody);
            
            $mail->Body = $tmpbody;
            $mail->AddAddress(trim($t));
            // $mail->AddBCC("rachelc@gmail.com");
            $mail->Send();
            // echo($tmpbody);exit;
        }
    }
    Header("Location: class_detail.php?id=$classid&sent=1");
    exit;
}
?>
<?php include "ssi/top.php"; ?>  
  <p>
  
   <strong><span class="title">ASHI Email for <A href='class_detail.php?id=<?php echo $classid; ?>'>Class #<?php echo $classid; ?></a> - <a href='viewcompany.php?id=<?php echo $crow["companyid"]; ?>'><?php echo $company["companyname"]; ?></a></span></strong>
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
        echo "barbara@emergencyskills.com";
    }
?>"></td></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?php 
    if (isset($subject) && $subject) {
        echo htmlspecialchars($subject);
    } else {
        echo "Your Digital Certifications have been sent";
    }
?>"></td></tr>
<tr><td>Body: </td></tr>
    <tr><td colspan='2'>
<textarea name='body' rows='30' cols='80'><?php 
    if (isset($body)) {
        echo htmlspecialchars($body);
    }
?></textarea></td></tr>
        <!-- <tr><td>Sending To:</td><td> <?php 
            // if (isset($toarr) && $toarr) {
            //     echo nl2br(htmlspecialchars(print_r($toarr, true)));
            // }
        ?></td></tr> -->
        <tr><td></td></tr>
 <tr><td></td><td><input type='submit' name='send' value='Send Email'></td></tr>
</table>
<br><br>
</div>
</body>
</html>