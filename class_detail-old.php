<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once('mysql.php');
$crow = getClassRow($id);
$comrow = getCompanyRow($crow["companyid"]);
$class_names = $allclass_names[$comrow["iscorp"]];

// $requesttrainers = $_post['requesttrainers'] ?? null;
// $viewpossibletrainers = $_post['viewpossibletrainers'] ?? null;
//print_r( $class_names );

if ($resendall) {
 $sql = ("select classid, responderid from responder_to_class,class, company_esi where class.id = $id and classid = class.id and class.companyid = company_esi.id and iscorp = 0");
 //echo( $sql . "<br>");
 $expiring = db_query_rows($sql);
 require_once('services.php');

 foreach ($expiring as $e) {
  echo ($e['classid'] . ", " . $e['responderid'] . " : ");
  $arow = getResponderRow($e['responderid']);
  echo ($arow['pmsid'] . ", ");
  $res = updateResponder($arow);
  echo ($res . "<br>");
 }
}

//echo( "select class.id from class, company_esi where class.id = '$id' and canceldate is null and code not in ( 'MHFA', 'AEDI', 'Inspections', 'MHFA', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc'  ) and iscorp <> 3 and companyid = company_esi.id and isnational = 0 and companyname not like 'Sample%' and companyname not like 'Open Registration' and numtrainers > 0 " );

if ($sos) {
 $curr = mysqli_real_escape_string($link, db_query_first_cell("select group_concat( concat( first_name, ' ', last_name ) ) from user, trainer_to_class where classid = '$id' and trainerid = user.id"));
 db_query("insert into sostrainers ( classid, trainerid, dateadded, type, currenttrainers ) values ( '$id', '$session_id', now(), $sos, '$curr' )");
 db_query("delete from trainer_to_class where classid = $id and trainerid = $session_id");
 if ($sos == 36) {
  db_query("update requesttotrain set done = 0 where done in ( -1, -2 ) and trainerid  <> '$session_id' and classid = $id and trainerid not in ( select trainerid from trainer_to_class ) ");
 }
 requestTrainers($id, 1);
}

if ($readddenials) {
 db_query("update requesttotrain set previouslydenied = 1 where done in ( -1 ) and classid = $id");
 db_query("update requesttotrain set done = 0, previouslydenied = 1 where done in ( -1, -2 ) and classid = $id");
 header("Location: trainerrequests.php");
 exit;
}

if ($canceltrainer) {
 db_query("delete from trainer_to_class where classid = $id and trainerid = $canceltrainer");
 db_query("insert into cancelled_trainers values( $id, $canceltrainer, now(), '$cancelnotes',  '$session_userid' )");
 header("Location: class_detail.php?id=$id&canceldeleted=1");
}

if ($sendmaskemail) {
 sendMaskEmail($id);
 header("Location: class_detail.php?id=$id&masklettersent=1");
}

if ($sendmissingprnemail) {
 sendMissingPRNEmail($id);
 header("Location: class_detail.php?id=$id&missingprnsent=1");
}

if ($canceltcf) {
 db_query("update class set tcfacultyid = null, tcfacultyconfirmeddate = null  where id = $id");
}

if ($assignyourself) {
 db_query("update class set tcfacultyid = $session_id, tcfacultyconfirmeddate = now()  where id = $id");
}

if ($removeyourself) {
 db_query("update class set tcfacultyid = 0, tcfacultyconfirmeddate = null  where id = $id");
}

if ($resendtrainerconfirms) {
 $trainers = getTrainers($id, $unconfirmedonly);
 $crow = getClassRow($id);
 foreach ($trainers as $tmprow) {
  sendTrainerConfirmEmail($tmprow['id'], $crow);
 }


 header("Location: class_detail.php?id=$id&sent=1");
 exit;
}

if ($senddoecloseout) {
 $crow = getClassRow($id);
 $pemail = $crow["principalemail"];
 sendDoeCloseoutEmail($pemail, $crow);

 $pemail = $crow["email"];
 sendDoeCloseoutEmail($pemail, $crow);

 $urow = getUserRow($crow["addedby"]);
 sendDoeCloseoutEmail($urow["userid"], $crow);

 sendDoeCloseoutEmail("cards@emergencyskills.com", $crow);


 header("Location: class_detail.php?id=$id&sent=1");
 exit;
}

if ($sendashicardemail) {
 $crow = getClassRow($id);
 $classid = $id;
 $em = getClassEmail($crow);
 $cont = getClassContact($crow);
 $comrow = getCompanyRow($crow["companyid"]);
 $fromemail = "info@emergencyskills.com";
 $fromname = "Emergency Skills";
 $subject = "Your CPR AED Certification Card Has Been Sent";
 $initbody =
  "Dear CONTACTNAME,

Thank you for completing CPR/AED Training class number $classid DATE at LOCATION!

Your eCard and eBook have been issued.  Please look for an email from Health & Safety Institute (info@hsi.com) with your card and book included.  Be sure to check your spam, junk, or clutter folder, as it sometimes winds up there.

Additionally, please click the link below and complete the course evaluation.  We value your feedback and use it to ensure we are providing the best training programs possible!

CPR AED Course Evaluation survey:
https://www.surveymonkey.com/r/ZLBFWH7

If you have any questions, or need assistance in location your card, please call 212-564-6833 or email cards@emergencyskills.com.

Thank you,
Barbara Kinter
212-564-6833
305 7th Avenue 
Suite 1100
New York, NY 10001
";

 $body = $initbody;

 $body = str_replace("DATE", getFormattedDate($crow["startdate"]), $body);
 $body = str_replace("LOCATION", $crow["training_location"], $body);

 $to = $crow["email"];
 $toname = $crow["firstname"] . " " . $crow["lastname"];

 $toarr = db_query_array("select concat( firstname, ' ', lastname ) as name, email from responders_esi, responder_training_dates where classid = $classid and responders_esi.responderid = responder_training_dates.responderid", "email", "name");

 $mybody = stripslashes($body);
 // echo( $mybody );
 require_once "class.phpmailer.php";


 foreach ($toarr as $t => $tname) {
  $mail = new PHPMailer();
  $mail->From = $fromemail;
  $mail->FromName = $fromname;
  $mail->AddReplyTo($fromemail);

  $mail->Subject = stripslashes($subject);
  $mail->IsHTML(true); // set email format to HTML

  if (trim($t)) {
$tmpbody = str_replace("CONTACTNAME", $tname, $mybody);
//  echo( $tmpbody );exit;

$mail->Body = $tmpbody;
$mail->AddAddress(trim($t));
$mail->Send();
  }
 }

 header("Location: class_detail.php?id=$id&sent=1");
 exit;
}

if ($sendecardemail) {
 $crow = getClassRow($id);
 sendEcardEmail($crow);

 header("Location: class_detail.php?id=$id&sent=1");
 exit;
}

if ($sendblendedecardemail) {
 $crow = getClassRow($id);
 sendBlendedEcardEmail($crow);

 header("Location: class_detail.php?id=$id&sent=1");
 exit;
}


if ($resendtrainernames) {
 sendTrainerNamesEmail($id);
 header("Location: class_detail.php?id=$id&sent=1");
 exit;
}


if ($dupedate) {
 $addedid = db_query_insert_id("insert into class( requestdate, addedby ) values ( now(), '$session_id' )");
 foreach ($crow as $cid => $cval) {
  if (
$cid == "invoiceno"
|| $cid == "ponumber"
|| $cid == "invoicepaid"
|| $cid == "cancelreason"
|| $cid == "addedby"
|| $cid == "coursefee"
|| $cid == "rosterreceived"
|| $cid == "cardsmaileddate"
|| $cid == "cardsmailed"
|| $cid == "booksmaileddate"
|| $cid == "booksmailed"
|| $cid == "invoicenotes"
|| $cid == "ponumber"
|| $cid == "available_computer"
|| $cid == "available_smartboard"
|| $cid == "available_powerpoint"
|| $cid == "available_tvdvd"
|| $cid == "available_dvdremote"
|| $cid == "available_streaming"
|| $cid == "noavavailable"
|| $cid == "equipnotes"
|| $cid == "available_tvvcr"
|| $cid == "avequip"
|| $cid == "duplicatedby"
|| $cid == "lasttrainerreqdate"
|| $cid == "requestdate"
|| $cid == "accepted"
|| $cid == "iscallconfirmed"
|| $cid == "canceldate"
|| $cid == "birdieid"
|| $cid == "returnbirdieid"
|| $cid == "xpoid"
|| $cid == "returnxpoid"
|| $cid == "ecardssent"
|| $cid == "xpodatesent"
|| $cid == "returnxpodatesent"
|| $cid == "returnxpoerror"
|| $cid == "markedreturnedby"
|| $cid == "confirmdate"
|| $cid == "hostconfirmdate"
|| $cid == "confirmationtext"
|| $cid == "lastsentconfirmnames"
|| $cid == "rosterreceived"
|| $cid == "lastpmsidreqdate"
|| $cid == "tcfacultyconfirmeddate"
|| $cid == "lastspecialrequestdate"
|| $cid == "invoicename"
|| $cid == "invoiceemail"
|| $cid == "invoicephone"
|| $cid == "invoiceinstr"
|| $cid == "xpodatesent"
|| $cid == "returnbirdieerror"
|| $cid == "markedreturnedby"
|| $cid == "birdieerror"
|| $cid == "xpoerror"
|| $cid == "admiralerror"
|| $cid == "admiralresponse"
|| $cid == "bagset"
|| $cid == "shipmentstatus"
|| $cid == "returnxpodatesent"
|| $cid == "xporesponse"
|| $cid == "returnxporesponse"
|| $cid == "returnbirdiedatesent"
|| $cid == "birdieresponse"
|| $cid == "returnbirdieresponse"
|| $cid == "equip_roundtrip"
|| $cid == "equip_tokeep"
|| $cid == "equip_hirt"
|| $cid == "equip_hik"
|| $cid == "enotesadded"
|| $cid == "teamslink"
|| $cid == "enotesby"
|| $cid == "notesconfirmed"
|| $cid == "equipreturned"
|| $cid == "confirmationnotes"
|| $cid == "newcoi"
|| $cid == "acceptpaymentpolicy"
|| $cid == "hasanydvd"
|| $cid == "available_streaming"
|| $cid == "available"
|| $cid == "available_computer"
|| $cid == "instructornotes"
|| $cid == "trainerid"
|| $cid == "tcfacultyid"
  )
continue;

  if ($cid != "id") {
if (strpos($cid, "date") !== false) {
 $cval = $cval == "0000-00-00 00:00:00" ? "" : $cval;
 db_query("update class set $cid = " . (!$cval ? "NULL" : "'$cval'") . " where id = $addedid");
} else {
 db_query("update class set $cid = '" . mysqli_real_escape_string($link, $cval) . "' where id = $addedid");
}
  }
 }
 $sd = date("Y-m-d H:i:s", strtotime($dupedate));
 db_query("update class set startdate = '$sd' where id = $addedid");
 db_query("insert into reschedules ( classid, newdate, newtime, thedate, who ) values ( '$addedid', '$sd', '', now(), '$session_userid' )");


 db_query("update class set duplicatedby = '" . $dupedate . ", " . $session_userid . "' where id = $addedid");


 $body = "Thank you for requesting an Emergency Skills, Inc. Training Program. This
e-mail confirms that you have a pending program registration. A staff member
will review this registration request and will respond back to you on your
class status, as soon as possible. If you have any changes, please return to
ALIVE!net (doe.emergencyskills.com) to update your request.

Please note - this message is generated automatically. If you have any
additional questions about this enrollment, please do not hesitate to send
an email to rebekah@emergencyskills.com.

";

 $crow = getClassRow($addedid);
 $em = getClassEmail($crow);
 foreach ($crow as $n => $v) {
  if (!$v)
continue;
  if (
$n == "invoiceno"
|| $n == "invoicepaid"
|| $n == "ponumber"
|| $n == "accepted"
|| $n == "cancelreason"
|| $n == "coursefee"
|| $n == "duplicatedby"
|| $n == "extranotes"
|| $n == "lasttrainerreqdate"
|| $n == "requestdate"
|| $n == "canceldate"
|| $n == "confirmdate"
|| $n == "cardsmailed"
|| $n == "cardsmaileddate"
|| $n == "booksmailed"
|| $n == "booksmaileddate"
|| $n == "hostconfirmdate"
|| $n == "confirmationtext"
|| $n == "notesconfirmed"
|| $n == "trainerid"
|| $n == "tcfacultyid"
  )
continue;
  else if (strpos($n, "attendee") !== false) {
//nothing here  
  } else if (strpos($n, "addedby") !== false) {
$body .= "$n : " . getFullname($v) . " \n";
  } else if ($n == "code") {
$body .= "$n : " . $class_names[$v] . " \n";
  } else if (strpos($n, "companyid") !== false) {
$body .= "$n : " . getCompanyName($v) . " \n";
  } else {
$body .= "$n : $v \n";
  }
 }

 $comrow = getCompanyRow($crow["companyid"]);

 if (!$comrow["iscorp"]) // only for DOE
 {
  $bic = $comrow["bic"] ? "- BIC" : "";
  sendMail($em, "New class scheduled$bic", $body, "info@emergencyskills.com");
  if ($crow["alt_email"])
sendMail($crow["alt_email"], "New class scheduled$bic", $body, "info@emergencyskills.com");
  if ($crow["principalemail"])
sendMail($crow["principalemail"], "New class scheduled$bic", $body, "info@emergencyskills.com");
 }

 header("Location: class_detail.php?id=$id&addedid=$addedid");
 exit;
}


if ($requesttrainer) {
 requestTrainers($id);
}

if ($viewpossibletrainer) {
 $vals = requestTrainers($id, false, false);
 $tstr = "";
 foreach ($vals as $v) {
  $tmpa = db_query_first("select * from user where id = $v");
  $tstr .= "<a href='trainer_profile.php?id=$v'>" . $tmpa["first_name"] . " " .  $tmpa["last_name"] . " ($tmpa[userid])</a><br>";
 }
}

if ($undelete) {
 db_query("update class set deleted =0, canceldate = null where id = $id");
}
if ($delete) {
 db_query("update class set cancelledby = '$session_userid', deleted =1, canceldate = Now() where id = $id");
 $crow = getClassRow($id);
 $companyname = getCompanyName($crow["companyid"]);
 $em = getClassEmail($crow);

 $body = stripslashes($rejectbody);

 if (trim($body) && !$dontemail) {
  sendMail($em, "Class Request Declined", $body, "info@emergencyskills.com");
  $ab = getUserEmail($crow["addedby"]);
  if ($ab != $em)
sendMail($ab, "Class Request Declined", $body, "info@emergencyskills.com");

  if ($crow["alt_email"])
sendMail($crow["alt_email"], "Class Request Declined", $body, "info@emergencyskills.com");
  if ($crow["principalemail"])
sendMail($crow["principalemail"], "Class Request Declined", $body, "info@emergencyskills.com");
  sendMail("sarahg@emergencyskills.com, barbara@emergencyskills.com", "Class Request Declined", $body, "info@emergencyskills.com");
  // echo( "would mail: $body" );
 }

 header("Location: calendar.php");
 exit;
}

if ($resendscheduled) {
 $crow = getClassRow($id);
 $companyname = getCompanyName($crow["companyid"]);
 $em = getClassEmail($crow);
 $cont = getClassContact($crow);
 $comrow = getCompanyRow($crow["companyid"]);
 resendScheduledEmail($crow, $comrow, $em, $companyname, $cont);
 header("Location: class_detail.php?id=$id&sentresend=1");
 exit;
}


if ($accept) {
 $isaccepted = db_query_first_cell("select accepted from class where id = $id");
 if (!$isaccepted) {
  db_query("update class set accepted =1, confirmdate = now() where id = $id");
  $crow = getClassRow($id);
  $companyname = getCompanyName($crow["companyid"]);
  $em = getClassEmail($crow);
  $cont = getClassContact($crow);
  $comrow = getCompanyRow($crow["companyid"]);

  $sd = date("Y-m-d", strtotime($crow["startdate"]));
  $numclasses = db_query_first_cell("select count(*) from class, company_esi where companyid = company_esi.id and startdate like '$sd%' and class.deleted = 0 and iscorp = 0 and accepted = 1");
  if ($numclasses == 10) {
$subject = "ALERT! 10 classes scheduled for $sd";

$body = "ALERT!
On this date, $sd, you have just accepted 10 programs.

Click here to block this date:

https://" . SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN . "/blockeddates.php
";
sendMail("barbara@emergencyskills.com", $subject, $body, "alert@emergencyskills.com");
  }

  $oneweek = mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"));
  if (strtotime($crow["startdate"]) < $oneweek && strtotime($crow["startdate"]) > time()) {
$subject = "ALERT: CLASS ADDED";
$body = "Class #$id has been accepted to the calendar. (< 7 days away)
https://" . SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN . "/class_detail.php?id=$id";
sendMail("dfunnye@emergencyskills.com", $subject, $body, "info@emergencyskills.com");
sendMail("barbara@emergencyskills.com", $subject, $body, "info@emergencyskills.com");
  }


  $threeweeks = mktime(0, 0, 0, date("m"), date("d") + 28, date("Y"));
  $numtrainers = $crow["numtrainers"];
  $numalready = db_query_first_cell("select count(*) from trainer_to_class where classid = $id");
  // echo( "req?" . strtotime( $crow[startdate] ). "<br>" );
  // echo( "req?" . $threeweeks. "<br>" );
  // echo( "req?" . $numalready. "<br>" );
  // echo( "req?" . $numtrainers. "<br>" );
  if (strtotime($crow["startdate"]) < $threeweeks && strtotime($crow["startdate"]) > time()  && $numalready < $numtrainers) {
//echo( "would send" );
//requestTrainers( $crow[id] );
  }
  //  exit;
  if ($comrow["iscorp"] && !isParksCompany($companyname))
sendMaskEmail($id);
  sendToAttendees($id, true);
  resendScheduledEmail($crow, $comrow, $em, $companyname, $cont);
 }
}

if ($sendtoattendees) {
 sendToAttendees($id);
}


if ($updatependingnotes) {
 db_query("update class set pendingnotes = '$pendingnotes', pendingby = '$session_userid', pendingbydate = now() where id = '$id'");
}
if ($unaccept) {
 db_query("update class set accepted =0, confirmdate = now() where id = $id");
}


$sql = "
SELECT c.*,
s.companyname,
s.address,
s.city,
s.zip,
s.borough,
s.contactphone,
s.id as companyid,
schoolcode,
date_format(c.startdate, '%W, %M %e, %Y') as date_str,
date_format(c.startdate, '%l:%i %p') as time_str
FROM `class` as c,
company_esi as s
where c.id = '$id'
and c.companyid = s.id
";
//echo $sql;
$class = db_query_first($sql);
//print_r($class);
foreach ($class as $key => $val) {
 ${$key} = $val;
}
$twoweeks = mktime(0, 0, 0, date("m"), date("d") + 14, date("Y"));
$within2weeks = (strtotime($startdate) < $twoweeks);

if ($scheduler_is_contact) {
 $sql = "select * from user where id = '$addedby'";
 //echo $sql;exit;
 $user = db_query_first($sql);
 //  print_r($user);exit;

 $firstname = $user["first_name"];
 $mi = $user["mi"];
 $lastname = $user["last_name"];
 $phone = $user["phone"];
 $phone_ext = $user["phone_ext"];
 $fax = $user["fax"];
 $email = $user["userid"];
}

if ($phone_ext) {
 $phone = "$phone Ext. $phone_ext";
}

if ($alt_phone_ext) {
 $alt_phone = "$alt_phone Ext. $alt_phone_ext";
}

$name = $class_names[$code];

$crow = getClassRow($id);
$comrow = getCompanyRow($crow["companyid"]);
//$trainerid = $crow["trainerid"];
$trainers = getTrainers($id);
$ahaid = "";
foreach ($trainers as $trow) {
 $ahaid = db_query_first_cell("Select ahaid from user where id = '$trow[trainerid]'");
 break;
}

if ($comrow['iscorp']) {
 /// corp can edit up to 3 days
 $twoweeks = mktime(0, 0, 0, date("m"), date("d") + 3, date("Y"));
 $within2weeks = (strtotime($startdate) < $twoweeks);
}
if (!$forschedule) {


 $okaytoedit = (getcurrentusercompany() == $companyid) || ($comrow["region"] && $comrow["region"] == $thisusersrow["visibleregion"]) || strtolower($session_userid) == "stregistrar@ahrcnyc.org";

 if ($thisusersrow["districts"]) {
  $okaytoedit = false;
  $expl = explode(",", $thisusersrow["districts"]);
  foreach ($expl as $e) {
if (strpos($comrow["schoolcode"], "{$e}-") !== false) $okaytoedit = true;
  }
 }

 if ($thisusersrow["iscorp"] && !$okaytoedit) {
  $thisusercompanycampus = db_query_first_cell("select campusid from company_esi where id = " . getcurrentusercompany());
  if ($thisusercompanycampus == $comrow["campusid"])
$okaytoedit = 1;
 }

 //echo( "okay: " . $okaytoedit );
?>
 <?php if (!$forprint) { ?>
  <?php include "ssi/top.php"; ?>
 <?php } ?>
 <!--start center content-->
 <script language='javascript'>
  function cancelTrainer(tid) {
var notes = prompt("Why are you cancelling this trainer?");
document.location.href = 'class_detail.php?canceltrainer=' + tid + '&id=<?php echo $id; ?>&cancelnotes=' + escape(notes);
  }

  function cancelTcf(tid) {
document.location.href = 'class_detail.php?canceltcf=' + tid + '&id=<?php echo $id; ?>';
  }

  function duplicateClass() {
val = prompt("What date/time would you like the new class to be on? (MM/DD/YYYY HH:MM format) ");
if (val) {
 document.location.href = "class_detail.php?id=<?php echo $id; ?>&dupedate=" + escape(val);
}
  }
 </script>
<?php } ?>

<?php if (!$forprint && !$forschedule) {  ?>
 <table width='100%'>
  <tr>
<td valign='top'>
 <?php if (isOverallAdmin() || ($currentusertype != "trainer" && ((!$within2weeks && $okaytoedit)))) {  ?>
  <a href='class_edit.php?id=<?php echo $id; ?>'><img border=0 src="images/button_editclass.gif"></a><br>
  <?php
  if ($specialadmin) {
  ?>
<?php if ($class["accepted"] && !$class["deleted"]) { ?>
 <a href='class_detail.php?id=<?php echo $id; ?>&unaccept=1' onclick='return confirm( "Are you sure you want to unaccept this class?" )'>Unaccept Class</a><br>
<?php } ?>
<a href='#' onClick='javascript:duplicateClass(); return false'>Duplicate Class</a>
<?php if ($class["accepted"]) { ?>
 <br><a onMouseover="popup('Subject: Class Accepted<br>Sender: info@<br>Receiver: Contact, Alternate Contact, Principal, Sarah, Barbara', 'white')" onMouseout="kill()" onClick='return confirm( "Are you sure you want to resend the scheduled email?" )' href='class_detail.php?id=<?php echo $id; ?>&resendscheduled=1'>Resend Scheduled Email</a>
 <br><a onMouseover="popup('Subject: Emergency Skills, Inc Training Program<br>Sender: info@<Br>Receiver: All Attendees', 'white')" onMouseout="kill()" onClick='return confirm( "Are you sure you want to send the reminder email?" )' href='class_detail.php?id=<?php echo $id; ?>&sendtoattendees=1'>Send Reminder Email</a>
 <br><a onMouseover="popup('Subject: Class Scheduled <Date><br>Sender: info@<Br>Receiver: All Trainers', 'white')" onMouseout="kill()" onClick='return confirm( "Are you sure you want to resend the confirm training emails?" )' href='class_detail.php?id=<?php echo $id; ?>&resendtrainerconfirms=1'>Resend Confirm Training Emails</a> <a onMouseover="popup('Subject: Class Scheduled <Date><br>Sender: info@<Br>Receiver: All Unconfirmed Trainers', 'white')" onMouseout="kill()" onClick='return confirm( "Are you sure you want to resend the confirm training emails?" )' href='class_detail.php?id=<?php echo $id; ?>&resendtrainerconfirms=1&unconfirmedonly=1'>(unconfirmed only)</a>
 <br><a onMouseover="popup('Subject: Emergency Skills, Inc<br>Course Instructor Name(s)<br>Sender: info@<Br>Receiver: Contact, Alt Contact, Principal, Barbara', 'white')" onMouseout="kill()" onClick='return confirm( "Are you sure you want to resend the trainer names?" )' href='class_detail.php?id=<?php echo $id; ?>&resendtrainernames=1'>Resend Trainer Names Email</a>
 <br><a href='ashi.php?id=<?php echo $id; ?>' target='_blank'>ASHI Card export</a>
 <br><a href='ecard.php?id=<?php echo $id; ?>' target='_blank'>eCards export</a>
 <br><a href='ebook.php?id=<?php echo $id; ?>' target='_blank'>eBooks export</a>
 <?php if ($sentresend) { ?><br>
  <font color='red'>Sent!</font><?php } ?>
 <br><a onClick='return confirm( "Are you sure you want to re-add denied trainers?" )' href='class_detail.php?id=<?php echo $id; ?>&readddenials=1'>Re-add Denied Trainers</a>
 <?php if (strtotime($class["startdate"]) < time()) { ?>
  <br><a href='sendnameconfirmemail.php?classid=<?php echo $id; ?>'>Request Email Addresses</a> <?php if ($class["lastsentconfirmnames"]) { ?>
Last Sent: <?php echo date("m/d/Y h:ia", strtotime($class["lastsentconfirmnames"])); ?>
  <?php } ?>
  <br>
  <a onClick='return confirm( "Are you sure you want to send the missing PRN email?" )' href='class_detail.php?id=<?php echo $id; ?>&sendmissingprnemail=1'>Send Missing PRN Email</a><br>
  <?php if (!$comrow["iscorp"]) {  ?>
<a onClick='return confirm( "Are you sure you want to resend all exp dates?" )' href='class_detail.php?id=<?php echo $id; ?>&resendall=1'>RESEND ALL RESPONDER EXP DATES TO DOE</a><br>
  <?php } ?>
  <?php if ($missingprnsent) { ?><br>
<font color='red'>Sent!</font><?php } ?>

 <?php } ?>
<?php } else { ?>
 <?php if (!$comrow["iscorp"]) { ?>
  <?php
  $pval = "";
  $r = db_query_rows("select sentdate, whom from pmsidsent where classid = '$id' order by sentdate desc");
  $pval = "Sent:<br>";
  foreach ($r as $trow) {
$pval .= $trow["sentdate"] . ": " . $trow["whom"] . "<br>";
  }

  ?>
  <br><a <?php if ($class["lastpmsidreqdate"]) { ?>onMouseover="popup('<?php echo $pval; ?>', 'white')" onMouseout="kill()" <?php } ?> href='sendpmsidemail.php?classid=<?php echo $id; ?>'>Send PMS ID Email</a>
  <?php if ($class["lastpmsidreqdate"]) { ?>
Last Sent: <?php echo date("m/d/Y h:i a", strtotime($class["lastpmsidreqdate"])); ?>
  <?php } ?>
  <br><br>
 <?php } ?>
<?php } ?>
<?php if ($addedid) { ?><font color='red'> <br>New Class: <a href='class_detail.php?id=<?php echo $addedid; ?>'><?php echo $addedid; ?></font><?php } ?>
<?php
if (!$class["accepted"] && !$class["deleted"]) { ?>
 <span class='copy'><strong>
<a href='class_detail.php?id=<?php echo $id; ?>&accept=1'>Accept Class</a>&nbsp;&nbsp;&nbsp;
<a href='#' onClick='javascript:document.getElementById( "rejectdiv").style.display="block"; return false;'>Reject Class</a><a name='rejectclass'></a>
<span id='rejectdiv' style='display:none'>
 <form method='post'>
  <textarea rows='10' cols='40' name='rejectbody'>
<?php $body = "The " . strip_tags($class_names[$crow["code"]]) . " Program requested for " . $companyname . " on " . fixdatefordisplay($crow["startdate"], true) . " has been declined by Emergency Skills, Inc.

This school currently has the DOE recommended number of trained responders.

This message is generated automatically. If you have any additional questions about this enrollment, please do not hesitate to send an email to rebekah@emergencyskills.com.

";
 echo ($body);
?>
  </textarea><br>
  <input type='checkbox' name='dontemail' value='1'> Don&apos;t Send Email<br>
  <input type='submit' name='delete' value='Reject'>
 </form>
</span>
<form method='post'><br> <input type='text' name='pendingnotes' value="<?php echo $class["pendingnotes"]; ?>"> <input type='submit' name='updatependingnotes' value='Update Pending Notes'><br></form>
  <?php }
 if ($class["deleted"]) {
  ?>
<span class='copy'><strong>
  &nbsp;&nbsp;&nbsp;<a href='class_detail.php?id=<?php echo $id; ?>&undelete=1'>Undelete Class</a>
<?php }
 }
}

if ($thisusersrow["canduplicate"]) {
?>
<a href='#' onClick='javascript:duplicateClass(); return false'>Duplicate Class</a><Br>
  <?php
 }

 if (getcurrentusertype() == 'trainer' || isOverallAdmin()) {  //  BAD TRAINERS removed 10/13

  $tm = strtotime($class["startdate"]);
  $day = 24 * 60 * 60;
  if ((time() + 36 * 60 * 60) > $tm && time() < $tm) {
// less than 36 hours
echo ("<br><a onClick='return confirm( \"Are you sure you want to remove yourself from this class?\" )' href='class_detail.php?id=$id&sos=36'>Request To Cancel</a>");
  }
  if ((time() + (8 * $day)) > $tm && (time() + (36 * 60 * 60)) < $tm) {
// If the alert is sent when the class is 8 days – two weeks away, it goes to anyone who replied and was denied (if there are NONE, then it goes to everyone) and the instructor is removed from the class 
echo ("<br><a onClick='return confirm( \"Are you sure you want to remove yourself from this class?\" )' href='class_detail.php?id=$id&sos=1'>Request To Cancel</a>");
  }
  if ((time() + (14 * $day)) > $tm && (time() + $day * 8) < $tm) {
// If the alert is sent more than 36 hours – 1 week, it automatically goes to everyone available and the instructor is removed from the class  
echo ("<br><a onClick='return confirm( \"Are you sure you want to remove yourself from this class?\" )'  href='class_detail.php?id=$id&sos=2'>Request To Cancel</a>");
  }
 }
  ?>

</td>
<td valign='top' align='right' class='copy'><a href='class_detail.php?forprint=1&id=<?php echo $id; ?>'><img border=0 src="images/button_print.gif"></a><br>
 <?php if (isOverallAdmin()) { ?>
  <a href='doh_roster_print.php?id=<?php echo $id; ?>'>Print DOH Roster Worksheet</a><br>
  <a href='roster_print.php?id=<?php echo $id; ?>'>Print Roster Worksheet</a><br>
  <a href='roster_print.php?names=1&id=<?php echo $id; ?>'>Print Roster Worksheet w/names</a><br>
 <?php } ?>
 <?php if ($currentusertype == "trainer") { ?>
  <a href='doh_roster_print.php?id=<?php echo $id; ?>'>Print DOH Roster Worksheet</a><br>
 <?php } ?>
 <a href='roster_emails.php?id=<?php echo $id; ?>'>Print Email Roster</a><br>
 <?php if (isOverallAdmin() && $comrow["iscorp"]) { ?>
  <a onMouseover="popup('Subject: Personal Masks Available<br>Sender: info@<Br>Receiver: class contact ', 'white')" onMouseout="kill()" href='class_detail.php?id=<?php echo $id; ?>&sendmaskemail=1'>Send Mask Letter</a><br>
  <a onMouseover="popup('Subject: Your CPR/AED certification card has been sent.<br>Sender: info@<Br>Receiver: participants', 'white')" onMouseout="kill()" onClick='return confirm( "Are you sure you want to send the closeout letter?" )' href='class_detail.php?id=<?php echo $id; ?>&sendashicardemail=1'>ASHI card email</a><br>
  <a onMouseover="popup('Subject: Your ecards have been sent.', 'white')" onMouseout="kill()" href='ecardcodeemail.php?classid=<?php echo $id; ?>'>eCard Code Email</a><br>
  <a href='ashicardemail.php?classid=<?php echo $id; ?>'>ASHI closeout email</a><br>
 <?php } ?>
 <?php if (isOverallAdmin() && !$comrow["iscorp"]) { ?>
  <a onMouseover="popup('Subject: Congratulation! CPR/AED Training Complete<Date><br>Sender: info@<Br>Receiver: Principal', 'white')" onMouseout="kill()" onClick='return confirm( "Are you sure you want to send the closeout letter?" )' href='class_detail.php?id=<?php echo $id; ?>&senddoecloseout=1'>eCards closeout email</a><br>
 <?php } ?>
 <?php if (isOverallAdmin()) { ?>
  <?php if (time() < strtotime($crow["startdate"])) { ?>
<?php if ($crow["confirmemaildate"]) { ?>
 Last Sent <?php echo fixdatefordisplay($crow["confirmemaildate"], true); ?>
<?php } ?>
<a href='sendconfirmationemail.php?id=<?php echo $class["id"]; ?>' target=blank>Send Confirmation Email</a><br>
  <?php } ?>
  <a onMouseover="popup('Subject: Claim your CPR/AED certification card<br>Sender: info@<Br>Receiver: participants who are highlighted in green ', 'white')" onMouseout="kill()" href='class_detail.php?id=<?php echo $id; ?>&sendecardemail=1'>AHA eCard Email</a><br>
  <a href='class_detail.php?id=<?php echo $id; ?>&sendblendedecardemail=1'>AHA Blended Learning eCard Email</a><br>
  <a href='envelopeexport.php?id=<?php echo $id; ?>'>Envelope Export</a><br>
  <a href='editrecertnotes.php?addnote=1&id=<?php echo $class["companyid"]; ?>&sd=<?php echo $class["startdate"]; ?>'>Add Annual Note</a><br>
  <a href='editrecertnotes.php?addnamesnote=1&id=<?php echo $class["companyid"]; ?>'>Add Names/Emails Requested</a><br>
 <?php } ?>
 <?php if (isOverallAdmin() && $comrow["iscorp"]) { ?>
  <a href='custom_packing_sheet.php?id=<?php echo $id; ?>'>Custom Packing Sheet</a><br>
 <?php } ?>
 <a href='attendeeexport.php?xls=1&id=<?php echo $id; ?>'>Attendee Export (xls)</a><br>
</td>
  </tr>
 </table>
 <BR CLEAR="ALL">
<?php } ?>
<?php if ($sent) { ?> <font color='red'>Sent.</font><br> <?php } ?>
<?php if ($masklettersent) { ?> <font color='red'>Masks Email Sent.</font><br> <?php } ?>
<?php if ($islocked) { ?> <font color='red'>CLASS LOCKED <?php echo $class["lockreason"] ? " - " . $class["lockreason"] : ""; ?></font><br> <?php } ?>
<strong><span class="title">CLASS DETAIL <?php echo $getsebooks ? "<font color='red'>eBooks</font>" : ""; ?></span></strong>
<hr>
<table cellpadding="0" cellspacing="0" border="0" width="576">
 <tr>
  <td valign="top">
<table cellpadding="0" cellspacing="4" border="0">
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td>
  <td valign="top" colspan='4'><span class="copy"><?php echo $name; ?> <?php echo $blendedlearning ? "<font color='red'><nobr>BLENDED LEARNING</nobr></font>" : ""; ?></span></td>
 </tr>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $date_str; ?> <?php echo isPeakDate($crow["startdate"]) ? "<font color='red'>PEAK DAY</font>" : ""; ?></span></td>
 </tr>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Time:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $time_str; ?> <?php echo getEndDateStr($enddate); ?></span></td>
 </tr>
 <?php if ($thisusersrow["tcfaculty"] && $tcfacultyid) { ?>
  <td valign="top" align="right"><span class="copy"><strong>TC Faculty:</strong></span></td>
  <td valign="top"><span class="copy"> <?php echo getFullname($tcfacultyid); ?> <?php echo $tcfacultyconfirmeddate ? "(confirmed on $tcfacultyconfirmeddate)" : ""; ?>
  </td>
 </tr>
<?php } ?>

<?php if ($session_userid == "sarahg@emergencyskills.com") { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Add To Calendar (beta):</strong></span></td>
  <td valign="top"><span class="copy"><a href='addtocal.php?id=<?php echo $id; ?>'>Add</a></span></td>
 </tr>
<?php } ?>
<?php if (getcurrentusertype() == 'trainer') { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Instructor Notes:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $instructornotes; ?><br>
 <?php echo $notesconfirmed; ?></span></td>
 </tr>
 <?php if ($teamslink) { ?>
  <tr>
<td valign="top" align="right"><span class="copy"><strong>Teams Link:</strong></span></td>
<td valign="top"><span class="copy"><a target=_blank href='<?php echo $teamslink; ?>'><?php echo $teamslink; ?></a><br>
 </span></td>
  </tr>
 <?php } ?>
<?php } ?>
<?php if (isOverallAdmin()) { ?>
 <tr>
  <td colspan='2'>
<table class="classdetailtable">
 <tr>
  <td valign="top" align="right" colspan='2'><span class="copy"><strong>ESI Confirmed:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $iscallconfirmed ? "Yes" : "No"; ?></span></td>
 </tr>
 <tr>
  <td valign="top" align="right" colspan='2'><span class="copy"><strong>Host Confirmation Date:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo getFormattedDate($hostconfirmdate); ?></span></td>
 </tr>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Is UPS:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $isups ? "<font color='red'>Yes</font>" : "No"; ?></td>
  <td valign="top" align="right"><span class="copy"><strong>Conference Room:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $isconferenceroom ? "<font color='red'>Yes</font>" : "No"; ?></td>
 </tr>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Roster Received:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $rosterreceived ? "<font color='red'>Yes</font>" : "No"; ?></span></td>
  <td valign="top" align="right"><span class="copy"><strong>Cards Mailed:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo fixdatefordisplay($cardsmaileddate, true); ?><?php echo $ecardssent ? "(e)" : ""; ?>

 <?php if (file_exists("classcards/$id.pdf")) {
  echo (" <A href='classcards/$id.pdf'>View Card</a><Br>");
 }
 if (file_exists("classcards/$id.xls")) {
  echo (" <A href='classcards/$id.xls'>View Card</a><Br>");
 }
 ?>

  </td>
  <td valign="top" align="right"><span class="copy"><strong>Books Mailed:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo fixdatefordisplay($booksmaileddate, true); ?><?php echo $ebookssent ? "(e)" : ""; ?></td>
 </tr>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Is Remote:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $remote ? "<font color='red'>Yes</font>" : "No"; ?></td>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Blended Learning:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo $blendedlearning ? "<font color='red'>Yes</font>" : "No"; ?></td>
  <?php if ($comrow["iscorp"]) { ?>
<td valign="top" align="right"><span class="copy"><strong>Is National:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $isnational ? "<font color='red'>Yes</font>" : "No"; ?></td>
  <?php } ?>
 </tr>
 <?php

 $num = db_query_first_cell("select count(*) from emergencyskills_wordpress.wp_wpforms_entry_fields where field_id = 1 and value = '$id'");
 if ($num && isOverallAdmin()) { ?>
<tr>
<td colspan='2'><span class='copy'><a href='https://emergencyskills.com/wp-admin/admin.php?page=wpforms-entries&view=list&form_id=13687&search%5Bfield%5D=1&search%5Bcomparison%5D=contains&search%5Bterm%5D=<?php echo $id; ?>&action=-1&date&paged=1&action2=-1'>View Questionnaire responses (<?php echo $num; ?>)</a></td>
  </tr>
 <?php } ?>
 <?php if ($teamslink) { ?>
  <tr>
<td valign="top" align="right"><span class="copy"><strong>Teams Link:</strong></span></td>
<td valign="top" colspan='8'><span class="copy"><A href='<?php echo $teamslink; ?>' target=_blank><?php echo $teamslink; ?></a></td>
  </tr>
 <?php } ?>
 <?php if ($tcfacultyid && isOverallAdmin()) { ?>
  <tr>
<td valign="top" align="right"><span class="copy"><strong>TC Faculty:</strong></span></td>
<td valign="top" colspan='3'><span class="copy">
  <A href='mailto:<?php echo getUserEmail($tcfacultyid); ?>'><?php echo getFullname($tcfacultyid); ?></a> <?php echo $tcfacultyconfirmeddate ? "(confirmed on $tcfacultyconfirmeddate)" : ""; ?>
  <?php if (isOverallAdmin()) { ?>
<a onClick='cancelTcf(<?php echo $trow["id"]; ?>); return false' href='#'>cancel</a>
  <?php } ?>
 </span></td>
  </tr>
 <?php } ?>
 <table class="classdetailtable">
  <tr>
<td valign="top" width='10%' align="right"><span class="copy"><strong>Equipment Notes:</strong></span></td>
<td valign="top"><span class="copy">
  <font color='#00ba2e'><b><?php echo $equipnotes; ?></b>
<?php if ($enotesby && $session_userid == "sarahg@emergencyskills.com") { ?>
 <br><?php echo $enotesby; ?> - <?php echo getFormattedDate($enotesadded); ?>
<?php } ?>
  </font>
 </span></td>
  </tr>
  <?php if (isOverallAdmin()) { ?>
<?php
$sd = date("Y-m-d", strtotime($crow["startdate"]));
$sd2 = date("Y-m-d", strtotime($crow["startdate"]) + 24 * 60 * 60 * 7);
$update = db_query_rows("select class.* from class, company_esi where company_esi.id = companyid and class.deleted = 0 and startdate >= '$sd' and startdate <= '$sd2 23:59:59' and training_location = '" . mysqli_real_escape_string($link, $crow["training_location"]) . "' and training_location > '' and class.id <> $id order by startdate");
//echo( "select class.* from class, company_esi where company_esi.id = companyid and class.deleted = 0 and startdate >= '$sd' and startdate <= '$sd2 23:59:59' and training_location = '$comrow[training_location]' and training_location > '' and class.id <> $id order by startdate" );
$any = false;
if (count($update))
 echo ("<br><font color='red'>Other Classes at this address within the week:</font><br> ");
foreach ($update as $urow) {
 $any = true;
 echo ("<a target=_blank href='class_detail.php?id=$urow[id]'>$urow[id] - " . getFormattedDateWTime($urow["startdate"]) . "</a><br>");
}
?>
  <?php } ?>

 </table>
 <?php
 $col = "";
 if ($specialadmin) { ?>
  <table class="classdetailtable">
<tr>
 <td valign="top" width='10%' align="right"><span class="copy"><strong>Instructor Notes:</strong></span></td>
 <td valign="top" colspan='3'><span class="copy"><?php echo $instructornotes; ?><br>
<?php echo $notesconfirmed; ?></span></td>
</tr>
<?php if ($specialadmin && $comrow["clientrequests"]) { ?>
  </table>
  <table class="classdetailtable">
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Client Requests:</strong></span></td>
 <td valign="top"><span class="copy">
<font color='red'><?php echo $comrow["clientrequests"]; ?></font>
  </span></td>
</tr>
  <?php } ?>


  </table>
 <?php } ?>
 <?php if ($comrow["iscorp"]) { ?>
  <table class="classdetailtable">
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Course Fee:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo $coursefee; ?></span></td>
</tr>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Invoice No:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo $invoiceno; ?> PO#: <?php echo $ponumber; ?></span></td>
</tr>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Invoice Notes:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo $invoicenotes; ?></span></td>
</tr>
<?php if ($nummasks) { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Masks Ordered:</strong></span></td>
  <td valign="top"><span class="copy">
 <font color='red'><b><?php echo $nummasks; ?> - <?php echo getFormattedDateWTime($masksordered); ?><br>Ordered By: <?php echo $masksorderedby; ?></b></font>
</span></td>
 </tr>
<?php } ?>
<?php if ($invoiceinstr || $invoiceemail || $invoicephone || $invoicename) ?>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Invoice Name:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo $invoicename; ?></span></td>
 <td valign="top" align="right"><span class="copy"><strong>Invoice Phone:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo formatPhone($invoicephone); ?></span></td>
</tr>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Invoice Email:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo $invoiceemail; ?></span></td>
 <td valign="top" align="right"><span class="copy"><strong>Invoice Instructions:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo $invoiceinstr; ?></span></td>
</tr>

  </table>


 <?php } ?>
<?php } ?>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Requested Instructor:</strong></span></td>
 <td valign="top"><span class="copy">
<?php if ($trainerreq && isOverallAdmin()) { ?>
 <A href='mailto:<?php echo getUserEmail($trainerreq); ?>'><?php echo getFullname($trainerreq); ?></a> <a href='sendspecialrequestemail.php?classid=<?php echo $id; ?>&trainerid=<?php echo $trainerreq; ?>' onClick='return confirm( "Are you sure you want to send this email?" )'>Send Special Request Email</a> <?php if ($lastspecialrequestdate) { ?>(last sent <?php echo getFormattedDateWTime($lastspecialrequestdate); ?>)<?php } ?>
 <br>
<?php } ?>
<?php if (isOverallAdmin()) { ?>
 <a href='sendspecialtfrequestemail.php?classid=<?php echo $id; ?>' onClick='return confirm( "Are you sure you want to send this email?" )'>Send Special Training Faculty Request Email</a> <?php if ($lastspecialtfrequestdate) { ?>(last sent <?php echo getFormattedDateWTime($lastspecialtfrequestdate); ?>)<?php } ?>
<?php } ?>
  </span></td>
</tr>
<tr>
 <td colspan='2'>
  <table class="classdetailtable">
<tr>
 <td valign="top" align="right" width="20%"><span class="copy"><strong>Trainer(s):</strong></span></td>
 <td valign="top" width="80%"><span class="copy">&nbsp;
<?php foreach ($trainers as $trainerid => $trow) {
?>
 <?php if (isOverallAdmin()) { ?>
  <a <?php echo $col; ?> href='mailto:<?php echo $trow['userid']; ?>'><?php echo getFullname($trainerid); ?></a> <a <?php echo $col; ?> href='trainer_profile.php?tid=<?php echo $trow["id"]; ?>'>(info)</a> <?php echo $trow["trainerconfirmeddate"] ? "(confirmed on $trow[trainerconfirmeddate])" : ""; ?>
  <a onClick='cancelTrainer(<?php echo $trow["id"]; ?>); return false' href='#'>cancel</a>

  <Br>
 <?php } else { ?>
  <?php echo getFullname($trainerid); ?> <?php echo $trow["trainerconfirmeddate"] ? "(confirmed on $trow[trainerconfirmeddate])" : ""; ?><Br>
 <?php } ?>
 <br>
<?php } ?>
  </span></td>
</tr>
  </table>
  <?php if ($thisusersrow["tcfaculty"] && !$tcfacultyid) { ?>
<br><A href='class_detail.php?id=<?php echo $id; ?>&assignyourself=1' onClick='return confirm( "Are you sure you want to assign yourself as a TC Faculty for this class?" )'>Assign yourself to be TC Faculty for this class</a> <br>
  <?php } else if ($session_id == $tcfacultyid && $thisusersrow["tcfaculty"]) { ?>
<br><A href='class_detail.php?id=<?php echo $id; ?>&removeyourself=1' onClick='return confirm( "Are you sure you want to REMOVE yourself as a TC Faculty for this class?" )'>Remove yourself to be TC Faculty for this class</a> <br>
  <?php } else if ($tcfacultyid) { ?>
TC Faculty: <?php echo getFullname($tcfacultyid); ?><Br>
  <?php
  }


  $ctrainers = getCancelledTrainers($id);
  if (count($ctrainers) && isOverallAdmin()) {
echo ("<table class=\"classdetailtable\">
<tr><td><b>Cancelled Trainers:</b></td><td class='copy' colspan='4'>");
foreach ($ctrainers as $canrow) {
 echo ("<a href='#' style='text-decoration: none; color: #000;' title=\"Cancelled By: $canrow[cancelledby]\nReason: $canrow[note]\">" . getFullname($canrow['trainerid']) . " - " . getFormattedDateWTime($canrow["canceldate"]) . "</a><br>");
}
echo ("</tr></tr></table>");
  }
  ?>

  <?php if ($crow['canceldate']) { ?>
<table class="classdetailtable">
 <tr>
  <td valign="top" align="right"><span class="copy">
 <font color='red'><strong>Cancelled:</strong></font>
</span></td>
  <td valign="top"><span class="copy">
 <?php echo $crow["canceldate"]; ?> (<?php echo $crow["cancelreason"]; ?>)
 <br>Cancelled By: <?php echo $crow["cancelledby"]; ?>
</span></td>
 </tr>
</table>
  <?php } ?>
 </td>
</tr>
<?php
$rescheds = getReschedules($crow);
if (count($rescheds) > 1 && isOverallAdmin()) {
?>
 <script language='javascript'>
  function toggleDiv(element) {
var href = document.getElementById(element + "href");
if (document.getElementById(element).style.display == 'none') {
 document.getElementById(element).style.display = 'block';
 href.innerHTML = "v";
} else if (document.getElementById(element).style.display == 'block') {
 document.getElementById(element).style.display = 'none';
 href.innerHTML = ">";
}
  }
 </script>
 <tr>
  <td valign="top" align="right"><span class="copy">
 <font color='red'><strong>Reschedules:</strong></font>
</span></td>
  <td valign="top">
<?php echo ("<a href='#' onClick=\"javascript:toggleDiv( 'histdiv' );return false\" class='copy'> View (" . count($rescheds) . ")</a><span id='histdivhref'>></span></nobr> <div id='histdiv' style='display:none'>");
?>

<table>
 <tr>
  <th class="copy">New Date</th>
  <th class="copy">Who requested?</th>
  <th class="copy">When requested?</th>
 </tr>
 <?php foreach ($rescheds as $r) { ?>
  <tr>
<td class="copy"><?php echo $r["newdate"] . " " . $r["newtime"]; ?></td>
<td class="copy"><?php echo $r["who"]; ?></td>
<td class="copy"><?php echo $r["thedate"]; ?></td>
  </tr>
 <?php } ?>
</table>
</div>
</span>
  </td>
 </tr>
<?php } ?>
<tr>
 <td>
  <?php if (isOverallAdmin() && count($trainers) < $crow["numtrainers"]  && !$crow["isnational"]) { ?>
<tr>
 <form method='post'>
  <td valign="top" colspan='2'><span class="copy"><input type='submit' name='requesttrainer' value='Send Trainer Request Email'> (Last Sent: <?php echo $lasttrainerreqdate && $lasttrainerreqdate <> '0000-00-00 00:00:00' ? getFormattedDateWTime($lasttrainerreqdate) : "Never"; ?>)</span></td>
 </form>
 <form method='post'>
  <td valign="top" colspan='2'><span class="copy"><input type='submit' name='viewpossibletrainer' value='View Possible Trainers (TESTING ONLY)'></span>
<br>
<font color='red'><?php echo $tstr; ?></font>
  </td>
 </form>
<?php } ?>
<tr>
 <td colspan="2"><br></td>
</tr>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>
 <?php echo getSchoolStr("School", $comrow['iscorp']); ?>:</strong></span></td>
 <?php if (getcurrentusertype() != "trainer") { ?>
  <td valign="top"><span class="copy"><a href='viewcompany.php?id=<?php echo $companyid; ?>'><?php echo $companyname; ?></a></span>

<?php if (isOverallAdmin()) { ?>
 <?php
 $update = db_query_rows("select * from class where deleted = 0 and startdate > now() and companyid = $companyid and id <> $id order by startdate");
 $any = false;
 if (count($update))
  echo ("<br>Other Classes:<br> ");
 foreach ($update as $urow) {
  $any = true;
  echo ("<a target=_blank href='class_detail.php?id=$urow[id]'>$urow[id] - " . getFormattedDateWTime($urow["startdate"]) . "</a><br>");
 }
 ?>
<?php } ?>

  </td>
 <?php } else { ?>
  <td valign="top"><span class="copy"><?php echo $companyname; ?></span></td>
 <?php } ?>
</tr>
<?php if (!$comrow["iscorp"]) { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>
  <?php echo getSchoolStr("School", $comrow["iscorp"]); ?> Code:</strong></span></td>
  <?php if (getcurrentusertype() != "trainer") { ?>
<td valign="top"><span class="copy"><a href='viewcompany.php?id=<?php echo $companyid; ?>'><?php echo $schoolcode; ?></a></span></td>
  <?php } else { ?>
<td valign="top"><span class="copy"><?php echo $schoolcode; ?></span></td>
  <?php } ?>
 </tr>
<?php } ?>
<?php if ($session_ut != "trainer" && !$training_location) { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong><?php echo getSchoolStr("School Address", $comrow["iscorp"]); ?>:</strong></span></td>
  <td valign="top"><span class="copy">
 <?php if (strpos($address, "http") !== false) { ?>
  <A target=_blank href='<?php echo $address; ?>'>
<font color='red'><?php echo $address; ?></font>
  </a>
 <?php } else { ?>
  <a target=_blank href="http://maps.google.com?q=<?php echo $address; ?>, <?php echo $city; ?> <?php echo $zip; ?>"><?php echo $address; ?>, <?php echo $city; ?> <?php echo $zip; ?></a>

 <?php } ?>


</span></td>
 </tr>
<?php } ?>
<?php if ($training_location) { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong><?php echo $remote ? "Participant Location" : getSchoolStr("Training Location", $comrow["iscorp"]); ?>:</strong></span></td>
  <td valign="top"><span class="copy">
 <font color='red'>
  <?php if (strpos($training_location, "http") === false) { ?>
<A target=_blank href='http://maps.google.com/?q=<?php echo $training_location; ?>, <?php echo $training_city; ?>, <?php echo $training_state; ?> <?php echo $training_zip; ?>'>
 <font color='red'><?php echo $training_location; ?> <?php echo $training_city; ?> <?php echo $training_state; ?> <?php echo $training_zip; ?></font>
</a>
<br><?php echo $training_room_number; ?>
  <?php } else { ?>
<A target=_blank href='<?php echo $training_location; ?>'>
 <font color='red'><?php echo $training_location; ?></font>
</a> <br><?php echo $training_room_number; ?>

  <?php } ?>
  </td>
 </tr>
<?php } ?>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Borough:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo $borough; ?></span></td>
</tr>
<tr>
 <td valign="top" align="right"><span class="copy"><strong>Phone:</strong></span></td>
 <td valign="top"><span class="copy"><?php echo formatPhone($contactphone); ?></span></td>
</tr>
<?php if (isOverallAdmin()) { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Cell Phone:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo formatPhone($cellphone); ?></span></td>
 </tr>
<?php } ?>
<?php
if (getcurrentusertype() == "trainer" || getcurrentusertype() == "principal") { ?>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Cards Mailed:</strong></span></td>
  <td valign="top"><span class="copy"><?php echo fixdatefordisplay($cardsmaileddate, true); ?><?php echo $ecardssent ? "(e)" : ""; ?>

 <?php if (file_exists("classcards/$id.pdf")) {
  echo (" <A href='classcards/$id.pdf'>View Card</a><Br>");
 }
 if (file_exists("classcards/$id.xls")) {
  echo (" <A href='classcards/$id.xls'>View Card</a><Br>");
 }
 ?>

  </td>
 </tr>
<?php } ?>
</table>
<?php
if ($training_location) {
 $gmap = getGoogleGeocode("$training_location, $training_city, $training_state $training_zip");
 if ($gmap) {
?>
  <iframe src="<?php echo $gmap; ?>" width="200" height="150" frameborder="0" style="border:0; position: relative; left: 500px; top: -100px" allowfullscreen></iframe>
<?php }
}
?>
</td>
</tr>

</table>

<p><br>
 <?php
 $attendees = get_attendees($class["id"]);
 ?>
 <?php if (!$comrow["iscorp"]) { ?>
  <font color='red'><b>
 ***Please Note: A Code Blue Drill will be performed at your school as part
 of the training program. Please accommodate the instructor appropriately.
</b></font><br><br>
  <font color='blue'><b>*If fewer than 12 attendees are listed, the remaining slots will be
 posted online for additional staff registration.***</b></font>
 <?php } ?>
 <br><br>
 <strong><span class="copy">ATTENDEES</span></strong> <?php if (isOverallAdmin()) { ?><a <?php echo !$ahaid ? "onClick='alert( \"There is no AHA ID for this export.\" )'" : ""; ?> href='exportresponders.php?classid=<?php echo $class["id"]; ?>&xls=true&ob=lastname'>view export</a>&nbsp; <a href='writepdf.php?specificclass=<?php echo $class["id"]; ?>'>export cards</a>&nbsp; <a href='#' onClick='javascript:showStuff("answer1"); selectText( "inneranswer1" ); return false'>Show Attendees for copy</a> <a href='#' onClick='showHiddenEmails(); return false;'>Show Emails</a> <a href='#' onClick='showHiddenPhones(); return false;'>Show Phone #s</a> <a href='emailattendees.php?id=<?php echo $class["id"]; ?>' target=blank>Email Attendees</a>
  <span id="answer1">

<span id="inneranswer1">

 <?php
 foreach ($attendees as $a) {

  if ($a["attended"]) {
$attendee = get_attendee($a["responderid"]); ?>
<?php echo $attendee["firstname"]; ?> <?php echo $attendee["lastname"]; ?><br>
 <?php
  }
 }
 ?> <br><br>
</span>
<a href='#' onClick='javascript: hideStuff("answer1"); return false;'>Close</a>

  </span>
</p>

<?php } ?>
<hr>
<table cellpadding="0" cellspacing="0" border="0" width="600">
 <tr>
  <td valign="top">
<table cellpadding="6" cellspacing="0" border="0">
 <?php

 $lasti = 0;
 $counter = 1;
 foreach (array(true, false) as $showcompleted) {

  foreach ($attendees as $i => $arow) {
if (isCompleted($arow["responderid"], $id) != $showcompleted) {
 $lasti = $i;
 continue;
}

$bg = $showcompleted ? "bgcolor='#A1D490'" : "";

// while( $lasti + 1 < $i )
// {
// $lasti++;
// echo( "<tr><td>$lasti.</td></tr>" );
// }
$lasti = $i;
//$arow = $attendees[$i];
$attendee = array();
if ($arow["responderid"])
 $attendee = get_attendee($arow["responderid"]);
//print_r( $attendee );
$dele = "";
$rrow = array();
if ($arow["responderid"]) {
 $rrow = getResponderRow($arow["responderid"]);
 if (isOverallAdmin()) {
  //print_r( $rrow );
  if ($rrow["deleted"])
$dele = "<font color='red'>";
  if (!$rrow["clientid"])
$dele = "<font color='red'>";
 }
}
 ?>
<tr <?php echo $bg; ?>>
 <td class='padding4' style="padding-left: 10px" valign="top"><span class="copy"><?php echo $counter++; ?>.</span></td>
 <?php if ($arow) {
  if ($specialadmin) {

 ?>
<td class='padding4' valign="top"><span class="copy"><A href='viewresponder.php?responderid=<?php echo $arow["responderid"]; ?>'><?php echo $dele; ?><?php echo $attendee["firstname"]; ?> <?php echo $attendee["lastname"]; ?></a>
  <?php if (isOverallAdmin()) { ?> <A target=_blank href='editresponder.php?responderid=<?php echo $arow["responderid"]; ?>'>(e)</a>
<a href="#" onClick="if( 1==1 ) { MyWindow=window.open('moveattendee.php?classid=<?php echo $id; ?>&responderid=<?php echo $arow["responderid"]; ?>','moveattendee','toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=400,height=300'); MyWindow.focus(); } return false;">(m)</a>
  <?php } ?>
  <?php if ($attendee["clientid"] != $comrow["id"]) {
echo (" (O) ");
  } ?>
  <?php if ($arow["individual"]) {
echo ("(I) ");
  } ?>
  <?php if ($arow["ispaid"]) {
echo ("(P) ");
  } ?>
  <?php if ($arow["attended"]) {
echo ("(A) ");
  } ?>
  <?php if (isCompleted($arow["responderid"], $id)) {
echo (" (C) ");
  } ?>
  <?php if ($counter - 1 > $class["maxattendees"]) { ?> <font color='red'>(E)<?php } ?>
 </strong></span> </td>
  <?php } else { ?>
<td class='padding4' valign="top"><span class="copy"><strong><?php echo $attendee["firstname"]; ?> <?php echo $attendee["lastname"]; ?>
<?php if ($attendee["clientid"] != $comrow["id"]) {
 echo (" (O) ");
} ?>
<?php if ($arow["individual"]) {
 echo ("(I) ");
} ?>
<?php if ($counter - 1 > $class["maxattendees"]) { ?> <font color='red'>(E) </font><?php } ?>
  </strong></span></td>
 <?php }
 } else {
  echo ("<td class='padding4' ></td>");
 }
 ?>
 <?php if ($comrow["iscorp"] || isOverallAdmin() || strtolower($session_userid) == "tpeele@schools.nyc.gov"  || strtolower($session_userid) == "laustin@schools.nyc.gov" || strtolower($session_userid) == "sgumbs4@schools.nyc.gov" || strtolower($session_userid) == "dtorres37@schools.nyc.gov" || strtolower($session_userid) == "hthomps@schools.nyc.gov") { ?>
  <td class='padding4' valign="top"><span class="copy">#<?php echo getIdentifier($attendee, $comrow["iscorp"], !$rrow["pmsidvalidated"], $rrow["emptype"] == "Charter School Employee" || $rrow["emptype"] == "Custodial Staff" || $rrow["emptype"] == "SSA"); ?></span></td>
 <?php } ?>
 <td class='padding4' valign="top"><span class="copy"><?php echo $attendee["title"]; ?> <?php echo $rrow["emptype"] ? "($rrow[emptype])" : ""; ?></span></td>
 <?php $exp =  getResponderExpDatePlus($arow["responderid"]);
 //if( $exp ) $exp .= " + 2 years";
 ?>
 <td class='padding4' style="padding-right: 10px"><?php if (isOverallAdmin()) { ?><?php echo getFormattedDate($exp); ?><?php } ?></td>
 <?php if (isOverallAdmin() || $session_ut == "trainer" || $comrow["iscorp"] == AGING) { ?> <td class='padding4' style="padding-right: 10px"><?php echo $arow["timeslot"]; ?></td><?php } ?>
 <td class='padding4' style="padding-right: 10px"><?php echo $rrow["buildingcode"] ? $rrow["buildingcode"] : "<font color='red'>None</font>"; ?></td>
</tr>
<?php if ($session_userid == "sarahg@emergencyskills.com" || strtolower($session_userid) == "noah@emergencyskills.com" || strtolower($session_userid) == "rebekah@emergencyskills.com" || strtolower($session_userid) == "barbara@emergencyskills.com") { ?>
 <Tr>
  <td></td>
  <td class='padding4' colspan='10'>Added by <?php echo $arow["sessionid"]; ?> on <?php echo getFormattedDate($arow["dateadded"]); ?></td>
 </tr>
<?php } ?>
<tr class='emailaddresshidden' style="display:none">
 <td colspan='10'><?php echo $rrow["email"]; ?></td>
</tr>
<tr class='phonehidden' style="display:none">
 <td colspan='10'>&nbsp;<?php echo formatPhone($rrow["dayphone"]); ?></td>
</tr>
 <?php }
 }
 ?>
 <?php
 $i = $counter - 1;
 if ($i < $class["maxattendees"]) {
  for (++$i; $i <= $class["maxattendees"]; $i++) {
 ?>
<tr>
 <td style="padding-left: 10px" valign="top"><span class="copy"><?php echo $i; ?>.</span></td>
</tr>

 <?php }
 } ?>
</table>
  </td>
 </tr>
</table>
<?php if ((isOverallAdmin() || $thisusersrow["onlyoneclasstype"] || $okaytoedit) && count($attendees) < $class["maxattendees"]) {  ?>
 <a href="#" onClick="if( 1==1 ) { MyWindow=window.open('add_attendee.php?fromview=<?php echo $id; ?>&overrideiscorp=<?php echo $company["iscorp"]; ?>&companyid=<?php echo $companyid; ?>&c=<?php echo $c; ?>&s=<?php echo $s; ?>&m=<?php echo $m; ?>&d=<?php echo $d; ?>&yr=<?php echo $y; ?>','attendee','toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=400,height=300'); MyWindow.focus(); } return false;"><img src="images/button_addattendee.gif" border="0"></A>
 <br><br>
<?php } else { ?>
 <?php if (count($attendees) >= $class["maxattendees"]) { ?>
  <font color='red'>Class full.</font>

 <?php } ?>
<?php } ?>
<?php if ($session_userid == "sarahg@emergencyskills.com") { ?>
 <a href='updatecompleteds.php?id=<?php echo $id; ?>'>Update Certifications</a>
<?php } ?>

<p><br>

 <strong><span class="copy">CONTACTS:</span></strong>
 <hr>

<table cellpadding="0" cellspacing="4" border="0">
 <tr>
  <td valign="top"><span class="copy"><strong>On-Site Contact:</strong></span></td>
  <td valign="top"><span class="copy">
 <?php echo $firstname; ?> <?php echo $mi; ?> <?php echo $lastname; ?><br>
 Title: <?php echo $contacttitle; ?><br>
 Phone: <?php echo formatPhone($phone); ?><br>
 Fax: <?php echo $fax; ?><br>
 Email: <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a>
 <?php if (!$comrow["iscorp"]) { ?>
  Cell: <?php echo formatPhone($cellphone); ?><br>
 <?php } ?>
</span><br>
  </td>
 </tr>
 <tr>
  <td valign="top"><span class="copy"><strong>Alternate Contact:</strong></span></td>
  <td valign="top"><span class="copy">
 <?php echo $alt_firstname; ?> <?php echo $alt_mi; ?> <?php echo $alt_lastname; ?><br>
 Title: <?php echo $altcontacttitle; ?><br>
 Phone: <?php echo formatPhone($alt_phone); ?><br>
 Fax: <?php echo $alt_fax; ?><br>
 Email: <a href="mailto:<?php echo $alt_email; ?>"><?php echo $alt_email; ?></a>
 <?php if (!$comrow["iscorp"]) { ?>
  Cell: <?php echo formatPhone($altcellphone); ?><br>
 <?php } ?>
</span><br>
  </td>
 </tr>
 <?php if (!$comrow["iscorp"]) { ?>
  <tr>
<td valign="top"><span class="copy"><strong>Principal:</strong></span></td>
<td valign="top"><span class="copy">
  <?php echo $principalname; ?><br>
  Email: <?php echo $principalemail; ?><br>
  Phone: <?php echo formatPhone($principalphone); ?><br>
 </span><br>
</td>
  </tr>
  <tr>
<td valign="top"><span class="copy"><strong>Emergency Contact:</strong></span></td>
<td valign="top"><span class="copy">
  <?php echo $emergency_name; ?><br>
  Cell: <?php echo formatPhone($emergency_cell); ?><br>
 </span><br>
</td>
  </tr>
 <?php } ?>
 <tr>
  <td valign="top"><span class="copy"><strong>Added By:</strong></span></td>
  <td valign="top"><span class="copy">
<?php echo getFullname($addedby)."<br>" ."Phone: ".getPhone($addedby)."<br>"."Ext: ".getPhoneExt($addedby)."<br>". "Email: ".getEmail($addedby)."<br>". "Fax: ".getFax($addedby)."<br>"; ?>
  <?php echo "Added on: " .getFormattedDateWTime($requestdate); ?><br>
  </td>
 </tr>
</table>

<p><br>


 <strong><span class="copy">TRANSPORTATION INFO:</span></strong>
 <hr>

<table cellpadding="0" cellspacing="4" border="0">

 <?php if ($parking_reserved > 0)
  $parking = "Yes";
 else //  if( $parking_reserved < 0 )
  $parking = "No";
 // else
 //  $parking = "Not Specified";

 ?>
 <tr>
  <td valign="top"><span class="copy"><strong>Parking space reserved for the educator:</strong> <?php echo $parking; ?></span></td>
 </tr>
 <?php if (!$comrow["iscorp"]) { ?>
  <tr>
<td valign="top"><span class="copy"><strong>Is there parking security?:</strong><br>
  <?php echo $parking_security; ?>
 </span><br><br>
</td>
  </tr>
 <?php } ?>
 <tr>
  <td valign="top"><span class="copy"><strong>Nearest Subway Line / Station:</strong><br>
 <?php echo $nearest_subway; ?>
</span>
  </td>
 </tr>
</table>

<p><br>

 <strong><span class="copy">ADDITIONAL ITEMS:</span></strong>
 <hr>

 <?php
 $available_equipment = array();
 if ($available_tvvcr)
  $available_equipment[] = "TV ONLY";
 if ($available_tvdvd)
  $available_equipment[] = "TV with DVD Player";
 if ($available_powerpoint)
  $available_equipment[] = "Power Point";
 if ($noavavailable)
  $available_equipment[] = "None Available";
 if ($available_computer)
  $available_equipment[] = "Computer (or DVD player) with Projector";
 if ($available_smartboard)
  $available_equipment[] = "Smartboard";

 $available = implode(", ", $available_equipment);

 $reserved = ($reserved_class_adequate) ? "Yes" : "No";
 $room = ($room_permit) ? "Yes (" . $room_permit_no . ")" : "No";
 ?>

<table cellpadding="0" cellspacing="4" border="0">
 <?php if (!$comrow["iscorp"]) { ?>
  <tr>
<td valign="top"><span class="copy"><strong><?php echo getSchoolStr("School Entrance", $comrow["iscorp"]); ?>:</strong> <?php echo $school_entrance; ?></span></td>
  </tr>
 <?php } ?>
 <tr>
  <td valign="top"><span class="copy"><strong>Has DVD:</strong> <?php echo $hasanydvd ? "Yes" : "No"; ?></span></td>
 </tr>
 <tr>
  <td valign="top"><span class="copy"><strong>Computer with Streaming Available?:</strong> <?php echo $available_streaming ? "Yes" : "No"; ?></span></td>
 </tr>
 <tr>
  <td valign="top"><span class="copy"><strong>Computer with DVD Available?</strong> <?php echo $available_computer ? "Yes" : "No"; ?></span></td>
 </tr>
 <tr>
  <td valign="top"><span class="copy"><strong>Available Equipment:</strong> <?php echo $available; ?></span></td>
 </tr>
 <tr>
  <td valign="top"><span class="copy"><strong>Available Equipment (New):</strong> <?php echo $avail_technologies; ?></span></td>
 </tr>
 <?php if ($comrow["iscorp"]) { ?>
  <tr>
<td valign="top"><span class="copy"><strong>COI?</strong> <?php echo $newcoi; ?></td>
  </tr>
  <tr>
<td valign="top"><span class="copy"><strong>Accepted Payment Policy?</strong> <?php
  if ($acceptpaymentpolicy == 1)
echo ("Yes");
  else if ($acceptpaymentpolicy == -1)
echo ("Declined");
  ?></span></td>
  </tr>

 <?php } ?>
 <?php if (!$comrow["iscorp"]) { ?>
  <tr>
<td valign="top"><span class="copy"><strong>Reserved classroom of adequate size:</strong> <?php echo $reserved; ?></span></td>
  </tr>
  <tr>
<td valign="top"><span class="copy"><strong>Building permit complete:</strong> <?php echo $room_permit ? "Yes: " : "No"; ?> <?php echo $room_permit_no; ?></span></td>
  </tr>
 <?php } ?>
</table>

<p><br>

 <?php if ($notes) { ?>
  <strong><span class="COPY">NOTES:</span></strong>
  <hr>
<table cellpadding="0" cellspacing="0" border="0" width="476">
 <tr>
  <td valign="top">
<table cellpadding="0" cellspacing="4" border="0">
 <tr>
  <td valign="top"><span class="copy">
 <?php echo nl2br($notes); ?>
 </tr>
</table>
  </td>
 </tr>
</table>
<?php } ?>
<?php if (isOverallAdmin()) { ?>
 <?php if ($confirmationnotes) { ?>
  <strong><span class="COPY">Confirmation Notes:</span></strong> <a target=_blank href='editrecertnotes.php?id=<?php echo $companyid; ?>'>View Recert Notes</a>
  <hr>
  <table cellpadding="0" cellspacing="0" border="0" width="476">
<tr>
 <td valign="top">
  <table cellpadding="0" cellspacing="4" border="0">
<tr>
 <td valign="top"><span class="copy">
<?php echo nl2br($confirmationnotes); ?>
<br><?php echo $cnotesadded ? "Edited: $cnotesadded" : ""; ?>
</tr>
  </table>
 </td>
</tr>
  </table>
 <?php } ?>
<?php } ?>

<P>
 <?php if (!$forschedule) { ?>
  <?php if (!$forprint) {  ?>
<?php if (isOverallAdmin() || ($currentusertype != "trainer" && ((!$within2weeks && $okaytoedit)))) {  ?>
 <a href='class_edit.php?id=<?php echo $id; ?>'><img border=0 src="images/button_editclass.gif"></a>
 <?php
 if ($specialadmin) {
  if (!$class["accepted"]) { ?>
&nbsp;&nbsp;&nbsp;<a href='class_detail.php?id=<?php echo $id; ?>&accept=1'>Accept Class</a>&nbsp;&nbsp;&nbsp;
<a href='#rejectclass' onClick='javascript:document.getElementById( "rejectdiv").style.display="block"; return true;'>Reject Class</a>

  <?php } ?>
<?php }
} ?>
<br><br><br><br>

<!--end center content-->
<?php include "ssi/footer.php"; ?>
<!--end footer-->
  <?php } ?>
  </span>
  </td>
  <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
  </tr>
  </table>
  <br><br>
  </div>
  <?php include "popupjs.php"; ?>
  </body>

  </html>
 <?php } ?>