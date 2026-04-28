<?php
require_once('mysql.php');

if(!isset($specialadmin) || !$specialadmin)
{
    Header("location: login.php");
    exit;
}

$company = getCompanyRow($companyid);
if(!isset($_POST) || !$_POST)
{
$companyAddress = getCompanyAddress($companyid);

$initbody = 
"Good afternoon,

Our records show that there are ZERO trained responders at:

" . (isset($company['companyname']) ? $company['companyname'] : '') . " 
" . $companyAddress . " 

Please use our website to set up a class: https://doe.emergencyskills.com


If you're new to the site, click NEW USER to set up a profile.

Once you have a profile, you can request classes and track your responders.

Classes are capped at 12 and the minimum is 7. The class is 6 hours long and cover adult, child, and infant CPR/AED use.

If your staffing needs prevent you from meeting the 7 participant minimum to request a CPR class, please use the Individual Registration option on our website.

This link allows you to find open slots at CPR trainings in upcoming classes: https://doe.emergencyskills.com/individual_registration1.php

Thank you for keeping your school community safe.

Thank you,
Rebekah Carrow
212-564-6833
rebekah@emergencyskills.com";

    $subject = "ACTION REQUIRED! Zero Trained Responders at - " . 
               (isset($company['companyname']) ? $company['companyname'] : '') . 
               " - " . 
               (isset($company['schoolcode']) ? $company['schoolcode'] : '');

    $body = $initbody;
    $to = isset($company['contactemail']) ? $company['contactemail'] : '';
    $toname = isset($crow['contactname']) ? $crow['contactname'] : '';
}

if(isset($send) && $send)
{
    $mybody = isset($body) ? stripslashes($body) : '';

    require_once "class.phpmailer.php";

    $toarr = array();
    if(isset($company['contact2email']) && $company['contact2email'])
    {
        $toarr[$company['contact2email']] = isset($company['contact2email']) ? $company['contact2email'] : '';
    }
    if(isset($company['contactemail']) && $company['contactemail'])
    {
        $toarr[$company['contactemail']] = isset($company['contactname']) ? $company['contactname'] : '';
    }
    if(isset($company['contact3email']) && $company['contact3email'])
    {
        $toarr[$company['contact3email']] = isset($company['contact3email']) ? $company['contact3email'] : '';
    }
    if(isset($company['contact2email']) && $company['contact2email'])
    {
        $toarr[$company['contact2email']] = isset($company['contact2name']) ? $company['contact2name'] : '';
    }
    
    $fromemail = isset($fromemail) ? $fromemail : 'rebekah@emergencyskills.com';
    $fromname = isset($fromname) ? $fromname : 'Emergency Skills';
    
    foreach($toarr as $t=>$tname)
    {
        if(trim($t))
        {
            $mail = new PHPMailer();
            $mail->From = $fromemail;
            $mail->FromName = $fromname;
            $mail->AddReplyTo($fromemail);
            
            $mailSubject = isset($subject) ? stripslashes($subject) : '';
            $mail->Subject = $mailSubject;
            
            if(!$tname)
            {
                $tname = "Contact";
            }
            $tmpbody = str_replace("CONTACTNAME", $tname, $mybody);
            
            $mail->Body = $tmpbody;
            
            // Test mode condition - modify as needed
            $testMode = false; // Set to true for testing
            
            if($testMode)
            {
                $mail->AddAddress("rachelc@gmail.com");
            }
            else
            {
                $iscorp = isset($company['iscorp']) ? $company['iscorp'] : 0;
                if(!$iscorp)
                {
                    $mail->AddCC("rebekah@emergencyskills.com");
                }
                else if(isset($company['iscorp']) && $company['iscorp'] == AGING)
                {
                    // Assuming AGING is a defined constant
                    if(defined('AGING'))
                    {
                        $mail->AddCC("barbara@emergencyskills.com");
                    }
                }
                else if($iscorp)
                {
                    $mail->AddCC("barbara@emergencyskills.com");
                }
                
                $mail->AddAddress(trim($t));
            }
            
            $mail->Send();
        }
    }
    
    Header("Location: viewcompany.php?id=" . (isset($companyid) ? $companyid : '') . "&sent=1");
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<p>

<strong><span class="title">Email to Contacts from <a href='viewcompany.php?id=<?php echo isset($company['companyid']) ? $company['companyid'] : ''; ?>'><?php echo isset($company['companyname']) ? htmlspecialchars($company['companyname']) : ''; ?></a></span></strong>
<?php echo isset($err) ? htmlspecialchars($err) : ''; ?>
<p>
<!--start center content-->
<form method='post'>
<table>
<tr><td>From Name: </td><td><input type='text' size='40' name='fromname' value="<?php echo isset($fromname) && $fromname ? htmlspecialchars($fromname) : 'Emergency Skills'; ?>"></td></tr>
<tr><td>From Email: </td><td><input type='text' size='40' name='fromemail' value="<?php echo isset($fromemail) && $fromemail ? htmlspecialchars($fromemail) : 'rebekah@emergencyskills.com'; ?>"></td></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?php echo isset($subject) && $subject ? htmlspecialchars($subject) : ''; ?>"></td></tr>
<tr><td>Body: </td></tr>
<tr><td colspan='2'>
<textarea name='body' rows='30' cols='80'><?php echo isset($body) ? htmlspecialchars($body) : ''; ?></textarea></td></tr>
<tr><td></td><td><input type='submit' name='send' value='Send Email'></td></tr>
</table>
<br><br>
</div>
</body>
</html>