<?php

include "mysql.php";

// Set flag for no new school creation
$nonewschool = 1;

// Safely retrieve request variables
$id = $_REQUEST['id'] ?? null;
$return = isset($_REQUEST['return']);
$save = isset($_REQUEST['save']);
$saveandsend = isset($_REQUEST['saveandsend']);

$firstname = $_REQUEST['firstname'] ?? '';
$lastname = $_REQUEST['lastname'] ?? '';
$email = $_REQUEST['email'] ?? '';
$phone = $_REQUEST['phone'] ?? '';
$club = $_REQUEST['club'] ?? '';
$requestedurl = $_REQUEST['requestedurl'] ?? '';
$gm = $_REQUEST['gm'] ?? '';
$filenumber = $_REQUEST['filenumber'] ?? '';
$companyid = $_REQUEST['companyid'] ?? null;

// Handle Return
if ($return)
{
    header("Location: tsicouponcodes.php");
    exit;
}

// Handle Save or Save and Send
if (($save || $saveandsend) && $id)
{
    // Sanitize and update registrant info
    $firstname_safe = htmlspecialchars($firstname);
    $lastname_safe = htmlspecialchars($lastname);
    $email_safe = htmlspecialchars($email);
    $phone_safe = htmlspecialchars($phone);
    $club_safe = htmlspecialchars($club);
    $requestedurl_safe = htmlspecialchars($requestedurl);
    $gm_safe = htmlspecialchars($gm);
    $filenumber_safe = htmlspecialchars($filenumber);

    db_query("UPDATE tsi_registrants SET 
        firstname = '$firstname_safe', 
        lastname = '$lastname_safe', 
        email = '$email_safe', 
        phone = '$phone_safe', 
        club = '$club_safe', 
        requestedurl = '$requestedurl_safe', 
        gm = '$gm_safe', 
        filenumber = '$filenumber_safe' 
        WHERE id = $id"
    );

    // Update school ID if provided (this part is currently non-functional due to commented-out form elements)
    if ($companyid) {
        db_query("UPDATE tsi_registrants SET schoolid = '$companyid' WHERE id = $id");
    }
    
    if ($saveandsend)
    {
        // Email body content
        $body = "Thank you for your inquiry on how to complete a CPR/AED training program at no charge. If approved, an email will be sent to the address indicated with instructions on how to register. You must follow those instructions to complete the registration process. <br>";
        
        // Assuming sendFormattedHTMLMail is defined and works
        sendFormattedHTMLMail($email_safe, "CERTIFICATION REQUEST APPROVAL PROCESS", $body, "info@emergencyskills.com", "", false);
        
        // Update database status
        db_query("UPDATE tsi_registrants SET sentemail = '1', dateemailsent = NOW() WHERE id = $id");
    }

    // After save/send, prevent form resubmission
    header("Location: tsicouponcodes_edit.php?id=" . $id);
    exit;
}

// Fetch registrant data for the form display
$row = [];
if ($id) {
    $row = db_query_first("SELECT * FROM tsi_registrants WHERE id = $id") ?? [];
}

// Helper to safely extract regtype from requestedurl
$position_chosen = '';
$requested_url_val = $row['requestedurl'] ?? '';
if (!empty($requested_url_val)) {
    $url_parts = explode("regtype=", $requested_url_val);
    if (count($url_parts) > 1) {
        $position_chosen = htmlspecialchars($url_parts[1]);
    }
}
?>
<?php
// Assuming 'ssi/top.php' and 'ssi/footer.php' are available
include "ssi/top.php"; 
include "getschooldropdown_ajax.php";
?>
<!--start center content-->

<form method="post">
<input type="hidden" name="id" value="<?=htmlspecialchars($id)?>">
<table class="table3" cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="tsicouponcodes.php">&laquo; Back to List</a></strong></span></td> 
</tr>
</table>
<table class="table3" cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>Registrant Information</strong></span></td>  
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>First Name*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row['firstname'] ?? '')?>" name="firstname" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Last Name*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row['lastname'] ?? '')?>" name="lastname" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Email*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row['email'] ?? '')?>" name="email" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Phone*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row['phone'] ?? '')?>" name="phone" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Club*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row['club'] ?? '')?>" name="club" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>GM Name*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row['gm'] ?? '')?>" name="gm" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>File Number*:</strong><br><input type="text" size="40" VALUE="<?=htmlspecialchars($row['filenumber'] ?? '')?>" name="filenumber" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Requested URL*:</strong><br><input type="text" size="100" VALUE="<?=htmlspecialchars($row['requestedurl'] ?? '')?>" name="requestedurl" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Position Chosen:</strong><br><font color='red'><?=$position_chosen?></font></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Email Sent:</strong> <?=($row["sentemail"] ?? 0) ? "Yes (Last Sent: " . getFormattedDateWTime($row['dateemailsent'] ?? '') . ")" : "No"?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Coupon Code:</strong><br><?=htmlspecialchars($row['acceptcode'] ?? '')?></span></td>
</tr>
<tr><td>
<div align="center">
<input type="submit" name='save' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name='saveandsend' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save & Send Initial Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name='return' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Return&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">

</div>
</td>
</tr>
</table>
<br><br>
<br><br>
<?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>