<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Emergency Skills, Inc.</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
</head>
<body>

<table cellpadding="0" cellspacing="0" border="0" style="width: 750px; border: 1px #666666 solid;">
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top" colspan="3"><img src="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Header.jpg"></td>
</tr>
<tr>
<td valign="top"><img src="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?> /email/Emergency-Skills-Header2.jpg"></td>
<td valign="top"><a href='http://www.emergencyskills.com'><img src="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Home-Link.jpg" border=0></a></td>
<td valign="top"><a href='https://emergencyskills.com/index.php/contact/'><img src="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Contact-Link.jpg" border=0></a></td>
</tr>
<tr>
<td valign="top" colspan="3">

<!-------------begin center content---------------->

<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top" style="padding: 15px; width: 470px;">
<span style="font-family: arial; font-size: 13px; color: #333333; line-height: 17px;">
<!-----------------begin left box content------------------->
<b>Thank you for choosing Emergency Skills, Inc. for your <?= $allclass_names[$comrow["iscorp"]][$crow["code"]] ?> program.</b><br><br>
<span style="font-family: arial; font-size: 15px; color: #ff3300; line-height: 17px;"><b>ACTION REQUIRED:</b></span><br>
You <b>MUST</b> select one of the links below to <b>CONFIRM</b> or <b>CHANGE</b> your program details.<span style="text-decoration: underline;">Your program is not confirmed until you choose a link below.</span><br><br>

<table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border: 1px solid #cccccc; background-color: #edeff2; padding: 6px;">
<tr>
<td valign="top" style="width: 100%;">
<table cellpadding="0" cellspacing="6" border="0" width="100%">
<tr>
<td valign="top" colspan="2"><span style="font-family: arial; font-size: 15px; color:#15539d;"><b><?= strtoupper( getSchoolStr( "School", $comrow["iscorp"]))?> DETAILS:</b></span></td>
</tr>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b><?= getSchoolStr( "School", $comrow["iscorp"])?> Name:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $comrow["companyname"] ?></span></td>
</tr>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Contact Name:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $crow["firstname"] ?> <?= $crow["lastname"] ?></span></td>
</tr>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Phone Number:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $crow["phone"] ?></span></td>
</tr>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Address:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $comrow["address"] ?>, <?= $comrow["city"] ?>, <?= $comrow["state"] ?></span></td>
</tr>
<?php if( !$crow["remote"]) { ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b><?= $crow["remote"] ? "Participant Location" : "Training Location" ?>:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= getTrainingAddress( $crow ) ?></span></td>
</tr>
<?php } ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Email Address: </b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $crow["email"] ?></span></td>
</tr>
<tr><td colspan="2"><br></td></tr>

<tr>
<td valign="top" colspan="2"><span style="font-family: arial; font-size: 15px; color:#15539d;"><b>PROGRAM DETAILS:</b></span></td>
</tr>
<?php if( $crow["remote"] ) { ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Instructor Location:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;">REMOTE CLASS</span></td>
</tr>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Teams Link:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><a href='<?= $crow["teamslink"] ?>'><?= $crow["teamslink"] ?></a></span></td>
</tr>
<?php } ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Program:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $allclass_names[$comrow["iscorp"]][$crow["code"]] ?></span></td>
</tr>
<?php if( $crow["remote"]) { ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b><?= $crow["remote"] ? "Participant Location" : "Training Location" ?>:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= getTrainingAddress( $crow ) ?></span></td>
</tr>
<?php } ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Date:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= date( "l, F j, Y", strtotime( $crow["startdate"] )) ?></span></td>
</tr>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Time:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= date( "h:i a", strtotime( $crow["startdate"] )) ?> <?= getEndDateStr( $crow["enddate"] ) ?></span></td>
</tr>
<?php if( $comrow["iscorp"] ) { ?>
<?php if( $crow["coursefee"] ) { ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Course fee:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $crow["coursefee"] ?></span></td>
</tr>
<?php } ?>
<tr>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><b>Max # of students:</b></span></td>
<td valign="middle"><span style="font-family: arial; font-size: 12px; color: #333333;"><?= $crow["maxattendees"] ?></span></td>
</tr>
<?php } ?>

<tr><td colspan="2"><br></td></tr>
<tr><td colspan="2"><span style="font-family: arial; font-size: 12px; color: #333333;">
<span style="font-family: arial; font-size: 15px; color:#15539d;"><b>Equipment Delivery Date:</b></span>
<br>For clients in the 5 boroughs of NYC, training equipment will be delivered via courier the business day before the program, or series of programs, begins, and will be picked up the business day follow the class, or last class in the series. Delivery and pick up dates are subject to change. For clients outside the 5 boroughs, equipment will be delivered and returned via UPS. A member of our staff will contact you to confirm these dates, and other logistics.</span></td></tr>
</table>
</td>
</tr>
</table>
<br>
<span style="font-family: arial; font-size: 16px; color:#35a703;"><b>YES, my details are correct.</b></span> <b>Click this link:</b><br>
<a href="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/confirm_class.php?id=<?= $crow["id"] ?>">https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/confirm_class.php?id=<?= $crow["id"] ?></a><br><br>

<span style="font-family: arial; font-size: 16px; color: #ff0000;"><b>NO, I need to change my details.</b></span> <b>Click this link:</b>
<a href="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/needs_edit.php?id=<?= $crow["id"] ?>">https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/needs_edit.php?id=<?= $crow["id"] ?></a><br><br>

<!-----------------end left box content--------------------->
</span>
</td>
<td valign="top" style="padding: 15px; border-left: 1px solid #cccccc;">
<span style="font-family:arial; font-size: 11px; color: #333333;">

<!-----------------begin right box content--------------------->

<b>Questions? Please Contact:</b><br><br>

<?php if ( $comrow["iscorp"] == AGING ) { ?>
<strong>Sarah Gillen</strong> at <a href="mailto:sarahg@emergencyskills.com">sarahg@emergencyskills.com</a> <br>
<?php } else if ( $comrow["iscorp"] ) { ?>
<strong>Barbara Kinter</strong> at <a href="mailto:barbara@emergencyskills.com">barbara@emergencyskills.com</a> <br>
<?php } else { ?>
<strong>Rebekah Carrow</strong> at <a href="mailto:rebekah@emergencyskills.com">rebekah@emergencyskills.com</a> <br>
<?php } ?>

<?php if( !$comrow["iscorp"] ) { ?>
<font color='red'><b>If your school has a sudden closure, and you need to cancel your CPR/AED training, please call or text Sarah Gillen ASAP <a href='tel:646-465-3637'><font color='red'>646-465-3637</font></a>.</font></b><Br>
<?php } ?>

212-564-6833<br><br>

----------------------------------------------
<br><br>

<span style="font-family: arial; font-size: 15px; color:#15539d;"><b>Helpful documents</b></span><br> 
The following documents are located on ALIVE!net, our online database:<br><br>

<?php if( $crow["remote"] ) { ?>
<a href="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/pdfs/important-prepare-for-your-class.pdf">Important! Prepare for your class</a><br><br>
<?php } ?>
<a href="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/pdfs/Headstart_Handout_AED.pdf">Headstart Handout</a><br><br>

<a href="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/pdfs/Program_Checklist.pdf">Program Checklist</a><br><br>

<a href="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/Confirm_participants.DOC">Confirm Participants Document</a><br><br>

<!-----------------end right box content--------------------->

</span>
</td>
</tr>
</table>

<!-------------end center content------------------>

</td>
</tr>
<tr>
<td valign="top" colspan="3" style="background-color: #e8ebe7; border-top: 1px solid #cccccc; padding-top: 10px; padding-bottom: 10px;"><div align="center"><span style="font-family: arial; font-size: 11px; color: #666666;"><b>Emergency Skills, Inc.</b><br>
305 7th Avenue Suite 1100, New York, NY 10001<br>
Phone: (212) 564-6833 | Fax: (212) 564-6793<br>
<a href="http://www.emergencyskills.com">www.emergencyskills.com</a>
</span></div></td>
</tr>
<tr>
<td valign="top" colspan="3"><img src="https://<?php echo SUB_DOE ; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Footer.jpg"></td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>