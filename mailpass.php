<?php
$nologinrequired = true;
// Assumed external file containing database connection and utility functions
include "mysql.php";

// Initialize variables safely, checking for POST data since the form uses method='post'
$userid = $_POST['userid'] ?? '';
$error = '';

if ($userid) {
    // SECURITY VULNERABILITY: Retrieving plaintext password!
    // In a real application, this should be a password reset token process.
    $user_id_safe = mysqli_real_escape_string($GLOBALS['link'] ?? '', $userid);
    $row = db_query_first_cell("SELECT password FROM user WHERE userid = '{$user_id_safe}'");

    if (!$row) {
        $error = "Sorry, a user with this email address could not be found.";
    } else {
        $password = htmlspecialchars($row); // Password is the cell result
        $body = "Your emergency skills password is: {$password}. Please note this for future reference.";
        
        // Assumed external function to send email
        sendMail($userid, "Emergency Skills Password", $body, "info@emergencyskills.com");
        
        $error = "<font color='red'>Your password has been emailed to you.</font> ";
    }
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<!-- <SCRIPT LANGUAGE="JavaScript">
 JavaScript begins...
function ChangeImage (ImageName,FileName) {
	document[ImageName].src = FileName;
}

</SCRIPT>	


<script language="JavaScript">
 hide from JavaScript-challenged browsers
function openWindow1() {  popupWin = window.open('downloads/brochure.shtml', 'figure1', 'scrollbars,resizable,width=700,height=550')
}
// done hiding 
</script>

<STYLE TYPE="text/css">

BODY {margin:0}

</STYLE> 	 -->


<STYLE TYPE="text/css">
a:link { color: #330099; text-decoration: none }
a:active { color: #330099; text-decoration: none }
a:visited { color: #330099; text-decoration: none }
a:hover { color: #330099; text-decoration: none }
</STYLE> 

<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/css/style.css">

</head>

<body bgcolor="#5a179e" marginwidth="0" marginheight="0" link="blue" visited="blue">

<br>
<div align="center">
<table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" width="700">
    <tr>
        <td colspan="4" valign="top"><img src="<?php echo WEB_ROOT; ?>/images/topbanner.jpg" width="700" alt="Top Banner"></td>
    </tr>
    <tr>
        <td colspan="4" valign="top" background="/images/topnav_background.jpg" width="700" height="24"><div align="right">
            
<?php include "ssi/newtop.php";?>
            
            </div>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <?php include "ssi/leftnav2.php" ;?>
        
        </td>
        <td valign="top" width="5"><img src="<?php echo WEB_ROOT; ?>/images/dotclear.gif" width="10" alt=""></td>
        <td valign="top" width="476"><br>

        <img src='images/alivelogo.jpg' alt="Alive Logo"><br><br>
        <span class="copy">
        <strong>PLEASE LOG IN:</strong>
        <form method='post'>
        <div align="center"> 		
        <?php 
        if($error)
        {
        ?>
            <?php echo $error; ?>
        <?php
        }
        ?>
        <table cellpadding="0" cellspacing="2" border="0"><tr><td valign='top'>
        <table cellpadding="4" cellspacing="0" border="0" valign='top'>
        
            <tr>
                <td valign="middle" align="right"><span class="copy"><strong>Email:</strong>
                </span></td>
                <td valign="middle" align="center"><span class="copy"><input type="text" size="30" VALUE="<?php echo htmlspecialchars($userid); ?>" maxlength="50" name="userid" style="font-size: 10px; font-family: verdana;"></span></td>
            </tr>
            
            <tr>
                <td valign="middle" align="center">&nbsp;</td>
                <td valign="top"><span class="copy"><input type="submit" name='go' value="Go!">
            </td>
            </tr>
        </table></td> 		
</tr></table></div>
        </form>
        <br><br><br><br><br><br><br><br>
        <br><br><br><br><br><br><br><br>
        
            <?php include "ssi/footer.php";?>
        </span>
        </td>
        <td valign="top" width="15"><img src="<?php echo WEB_ROOT; ?>/images/dotclear.gif" width="10" alt=""></td>
    </tr>
</table>
<br><br>
</div>

</body>
</html>