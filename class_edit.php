<?php
require_once('mysql.php');


if($sendupdated )
{
$crow = getClassRow( $id );
$company = getCompanyRow( $crow["companyid"] );
$code = $crow["code"];
$mynames = $allclass_names[$company["iscorp"]];
$body = "This email is to inform you that the details of your CPR/AED Training Program have been updated:

Date: ".getFormattedDateWTime( $crow["startdate"] )." " .getEndDateStr( $crow['enddate'] )."
Class: " . $mynames[$code]."
Location: ".getCompanyName( $crow['companyid'] )." {$company['address']}, {$company['city']} {$company['zip']}
".getSchoolStr( "Training Location", $crow['iscorp']).":  {$crow['training_location']} {$crow['training_room_number']} {$crow['training_city']} {$crow['training_state']} {$crow['training_zip']}
".getSchoolStr( "School Entrance", $crow['iscorp']).": {$crow['school_entrance']}
Subway: {$crow['nearest_subway']}

Click here to view the class information:
https://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/class_detail.php?id={$crow['id']}
";

if( $company["iscorp"] )
{
$body .= "
Please note - this message is generated automatically. If you have any additional questions please call ESI at: 212-564-6833."
;

}
else
{
$body .= "
Please note - this message is generated automatically. If you have any
additional questions about this enrollment, please do not hesitate to send
an email to rebekah@emergencyskills.com.

{$termsstr}
";
}

$em = getClassEmail( $crow );
sendMail( $em, "CPR/AED Training Program Updated", $body, "info@emergencyskills.com" ); 
sendMail( "sarahg@emergencyskills.com", "CPR/AED Training Program Updated", $body, "info@emergencyskills.com" ); 
sendMail( "barbara@emergencyskills.com", "CPR/AED Training Program Updated", $body, "info@emergencyskills.com" ); 

$attendees = get_attendees( $id );
foreach( $attendees as $arow )
{
$rrow = getResponderRow( $arow["responderid"] );
sendMail( $rrow["email"] , "CPR/AED Training Program Updated", $body, "info@emergencyskills.com" ); 
}

sendMail( $crow["alt_email"] , "CPR/AED Training Program Updated", $body, "info@emergencyskills.com" );
if( $crow["principalemail"] )
sendMail( $crow["principalemail"], "CPR/AED Training Program Updated", $body, "info@emergencyskills.com" ); 
$err = "Message(s) sent.<br>";
}

if( $rt )
{
 requestTrainers( $id );exit;
}

$sql = "
SELECT c.*,
s.companyname,
s.address,
s.floor,
s.city,
s.zip,
s.borough,
schoolcode,
s.id as companyid,
date_format(c.startdate, '%W, %M %e, %Y') as date_str,
date_format(c.startdate, '%h:%i %p') as time_str
FROM `class` as c,
company_esi as s
where c.id = '$id'
and c.companyid = s.id
";
//echo $sql;
$class = db_query_first($sql);

$attendees = get_attendees( $id );
//print_r( $attendees );
//print_r($class);
foreach ($class as $key => $val) {
  $$key = $val;
}

// The user is rescheduling the day/time for this class
if ($_GET['s']) {
  $starttime = $_GET['s'];
  $hour = substr($starttime, 0, 2);
  $min = substr($starttime, 2, 4);
  //echo $hour, $min;exit;
  $date_str = date("l, F j, Y", mktime($hour, $min, 0, $_GET['m'], $_GET['d'], $_GET['yr']));
  $time_str = date("g:i A", mktime($hour, $min, 0, $_GET['m'], $_GET['d'], $_GET['yr']));
  $startdate = date("Y-m-d H:i", mktime($hour, $min, 0, $_GET['m'], $_GET['d'], $_GET['yr']));
  db_query( "update class set hostconfirmdate = null where id = '$id' " );
//  db_query( "insert into reschedules ( classid, newdate, newtime, thedate, who ) values ( '$id', '$date_str', '$time_str', now(), '$session_userid' )" );
}

if ($c) $code = $c;
  // Name of the class based on class code.
$crow = getClassRow( $id );
$company = getCompanyRow( $crow["companyid"] );
$class_names = $allclass_names[$company["iscorp"]];
$name = $class_names[$code] ;

//$checked_scheduler = ($scheduler_is_contact) ? "CHECKED" : "";
$checked_tvvcr = ($available_tvvcr) ? "CHECKED" : "";
$checked_noav = ($noavavailable) ? "CHECKED" : "";
$checked_tvdvd = ( $available_tvdvd) ? "CHECKED" : "";
$checked_hasanydvd = ($hasanydvd) ? "CHECKED" : "";
$checked_powerpoint = ( $available_powerpoint) ? "CHECKED" : "";
$checked_dvdremote = ($available_dvdremote) ? "CHECKED" : "";
$checked_computer = ( $available_computer) ? "CHECKED" : "";
$checked_smartboard = ($available_smartboard) ? "CHECKED" : "";

$parking_selected = ($parking_reserved>0) ? "SELECTED" : "";
//$parking_selected_no = ($parking_reserved<0) ? "SELECTED" : "";
$reserved_selected = ($reserved_class_adequate) ? "SELECTED" : "";
$room_selected = ($room_permit) ? "SELECTED" : "";

$currenttrainers = getTrainers( $id );

$overridecname = "newcompanyid";
if( $specialadmin || strtolower( $session_userid ) == "stregistrar@ahrcnyc.org" )
{

 $trainerstr = "<select class=copy multiple size=4 name='trainerids[]'>";
$trainerstr .= "<option value='-1'></option>";
$nonremotetrainer = false;
$didalready = array();
foreach( $currenttrainers as $tid=>$tval )
{
 $trainerstr .= "<option SELECTED value='$tid'>".getUserName( $tid ). getStageDisplayByTrainerid( $tid ). "</option>";
 $didalready[$tid] = $tid;
 $isremote = db_query_first_cell( "select borough from trainer_to_borough where borough = 'Remote' and trainerid = $tid" );
 if( !$isremote )
  $nonremotetrainer = true;
}
if( $company["iscorp"] == TRAININGSITES )
{
$trainers = getAllTrainers("", 1 );
}
 else if( $company["iscorp"] )
{
$trainers = getAllTrainers( "", 0, 1);
}
else
{
$trainers = getTrainersForBorough( $borough, 1, $crow["remote"]);
}

if($session_id == 14088 )
{
 $trainers = getAllTrainers( " and trainingsite = 'AHRC' ", 1 );
}

//echo( $borough );
$code = $crow["code"];
$classname = $class_names[$code] ;
//print_r( $trainers );
foreach( $trainers as $trow )
{
if($didalready[$trow["id"]] )
continue;
 //  if( !$trow[rollout2010] ) continue;
$avon = availableOn( $startdate, $trow["id"], $id );
 if( strpos( $classname, "ALIVE!" ) !== false && !$trow["firstaid"] )
  continue;
 
if( $avon || $session_iscorp == 3 || $session_id == 14088 )
  {
  $ext = $avon==2 || $avon==3?" (AB)":"";
  if( $trow['paused']) $ext .= " (P)";
  $confirmdate = db_query_first_cell( "select trainerconfirmeddate from trainer_to_class where trainerid = {$trow['id']} and classid = {$crow['id']}" );
  $ext .= getStageDisplayByTrainerid( $trow["id"] );
  $trainerstr .= "<option value='".$trow["id"]."'>".$trow["first_name"] . " " . $trow["last_name"] . ($confirmdate?" (C)":"")." $ext</option>";
  }
}
$trainerstr .= "</select> <br><i>(AB): already booked on this date.</i>";


}
else
{
foreach( $currenttrainers as $trainerid )
{
if( $trainerstr )
$trainerstr .= ", ";
$trainerstr .= getFullname( $trainerid ) . "<input type='hidden' name='trainerids[]' value='$trainerid'>" ;
}
}


if($scheduler_is_contact )
{
 $urow = getUserRow( $addedby  );
 if(!$firstname )
  $firstname = $urow["first_name"] ;
 if(!$lastname )
  $lastname = $urow["last_name"] ;
 if(!$mi )
  $mi = $urow["mi"] ;
 if( !$phone )
  $phone = $urow["phone"] ;
 if(!$phone_ext )
  $phone_ext = $urow["phone_ext"] ;
 if( !$email )
  $email = $urow["userid"] ;
 if(!$fax )
  $fax = $urow["fax"] ;
}

$classinfo = getClassInfo( $id );

?>

<?php include "ssi/top.php"; ?>  
<?php include "getschooldropdown_ajax.php";?>
<!--start center content-->

  
<script language='javascript'>

function checkSubmit( frm )
{

 if( $("#islocked").is( ":checked" ) )
 {
if( !confirm( "Are you sure you want this class to become/remain locked? " ) )
return false;
 }


 if( $("#finishedloading").length  == 0 )
 {
 alert( "Please wait for the page to finish loading." );
 return false;
 }
//  if( 1 == 1 )
//   return false;
<?php if( $company["iscorp"] ) {?>
 return true;
 <?php } else { ?>
 if( frm.firstname.value == "" || frm.lastname.value == "" || frm.alt_firstname.value == "" || frm.alt_lastname.value == "" || frm.emergency_name.value == "" || frm.principalname.value == "")
{
alert( "Primary, alternate, principal and emergency contact names are required." );
return false;
}
 if( frm.phone.value == "" || frm.alt_phone.value == "" || frm.emergency_cell.value == "" || frm.principalphone.value == "")
{
alert( "Primary, alternate, principal and emergency contact phone numbers are required." );
return false;
}
if( frm.alt_phone.value == frm.emergency_cell.value  || frm.phone.value == frm.emergency_cell.value  || frm.principalphone.value == frm.emergency_cell.value )
{
alert( "Valid Emergency Cell Phone Number Required." );
return false;
}

<?php
if(!$specialadmin )
{
?>
if( frm.parking_reserved.selectedIndex == 0 )
{
alert( "Please mark if parking is available for the instructor." );
return false;
}
if( frm.training_room_number.value == "" )
{
alert( "Please enter the training room number, or N/A." );
return false;
}
<?php } ?>

var num = 0;
<?php
if(!$specialadmin )
{
for( $i = 1; $i <= ($class["maxattendees"]); $i++ ) { ?>
if( frm.attendee<?=$i?>.selectedIndex > 0 )
num++;
<?php } ?>
if( num < 7 )
{
alert( "At least 7 participants are required. You have only chosen " + num + "." );
return false;
}
   <?php } ?>
return true;
<?php } ?>
}
function addMyOption( name, val, timeslot )
{
var frm = document.forms["clform"].elements;
for( i = 0; i < frm.length; i ++ )
{
if( frm[i].name.indexOf( "attendee" ) != -1 && frm[i].selectedIndex == 0 && frm[i].options[0].text == "Please select" )
{
var o = new Option( name, val );
frm[i].options[ frm[i].options.length ] = o;
frm[i].selectedIndex = frm[i].options.length-1;
 var ts = frm[i].name.replace( "attendee", "" );
 document.getElementById( "timeslot" + ts ).value = timeslot;

 
break;
}
}
}
function classfull()
{
return classfullinner( true );
}
function classfullinner( dopopup )
{
var frm = document.forms["clform"].elements;
for( i = 0; i < frm.length; i ++ )
{
if( frm[i].name.indexOf( "attendee" ) != -1 && frm[i].selectedIndex == 0 && frm[i].options[0].text == "Please select" )
{
 return false;
}
}
if( dopopup )
 alert( "Sorry, this class is now full." ); 
return true;
}
</script>  
<?php if($uploaded ) { ?>
<font color='red'> Card uploaded.</font>
<?php } ?>
<?php if($nameserror ) { ?><br>
<font color='red'><b><?=$nameserror?></b></font><br><br>
<?php } ?>
<strong><span class="title">SCHEDULE A CLASS</span></strong> &nbsp;&nbsp;<span class="copy"><em>(Step 2 of 2)</em></span>
<form name="clform" action='class_edit_thanks.php' method='post' onSubmit="return checkSubmit( this )" enctype='multipart/form-data'>
<br><hr><br>
<font color='red'><?=$err ?></font>
  <input type="image" src="images/button_submitrequest.gif"><br><br>
<?php if( count( $classinfo ) || $entershipping ) { 
?>
<a href='#shippinginfo'>Go To Shipping Details</a><Br>
<?php } ?>
The class you are requesting is:<p>
   <table border="0" cellpadding="0" cellspacing="0"><tr><td valign="top">
  <table cellpadding="0" cellspacing="0" border="0" >
  <tr>
 <td valign="top">
   <table cellpadding="0" cellspacing="4" border="0">
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td> 
 <td valign="top"><span class="copy">
<?php if( isOverallAdmin() ) { ?>
<select name='code' class='copy' style="width:350px">
  <option  SELECTED value="<?=$code?>"><?=$class_names[$code] ?></option>
<?php 
foreach ($class_names as $tmpcode => $name) {
if( isCodeRetired( $tmpcode ) )
continue;
?>
  <option value="<?=$tmpcode?>"><?=$name?></option>
<?php } ?>
</select>
<?php } else { ?><?=$name?>
<input type='hidden' name='code' value='<?=$code?>'>
<?php } ?>
</span></td>
 </tr> 
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td> 
 <td valign="top"><span class="copy"><?=$date_str ?></span></td>
 </tr>
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Request Date:</strong></span></td> 
 <td valign="top"><span class="copy"><?=$requestdate ?></span></td>
 </tr>
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Start Time:</strong></span></td> 
 <td valign="top"><span class="copy">
<?php if( isOverallAdmin() && strpos($confirmationnotes , "Quick Schedule" )!==false ) { ?>
<input type='text' name='starttime' size='8' value='<?=$time_str ?>'>
<?php } else { ?>
<?=$time_str ?>
<?php } ?>
</span></td>
 </tr>   
<tr>
  <td valign="top" align="right"><span class="copy"><strong>End Time:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='text' name='enddate' size='8' value='<?=!$_GET["s"] && $enddate && $enddate!="0000-00-00"?$enddate:""?>'></span></td>
 </tr>   
<?php if( isOverallAdmin() ) { ?>
   <tr>
  <td valign="top" align="right"><span class="copy"><strong><font color='red'>LOCK CLASS</font>:</strong></span></td> 
   <td valign="top"><input id="islocked" type='checkbox' name='islocked' value='1' <?=( !$islocked )?"onClick='if( this.checked ) alert( \"Once saved, this class will not show up on individual registration.\" );'":"" ?><?= $islocked?"CHECKED":""?>> Reason: <input type='text' name='lockreason' size='10' value="<?=$lockreason ?>"></td>
 </tr>
<?php } ?>
<?php if( isOverallAdmin() ) { ?>
   <tr>
  <td valign="top" align="right"><span class="copy"><strong><font color='red'>Remote?</font>:</strong></span></td> 
   <td valign="top"><input type='checkbox' name='remote' value='1' <?=( !$remote && $nonremotetrainer )?"onClick='if( this.checked ) alert( \"This instructor is not a REMOTE instructor. Please notify Barbara.\" );'":"" ?><?=$remote?"CHECKED":""?>></td>
 </tr>
  <?php if($remote ) { ?>
   <tr>
  <td valign="top" align="right"><span class="copy"><strong><font color='red'>Teams Link:</font></strong></span></td> 
 <td valign="top"><textarea name='teamslink' cols='70' rows='4' ><?=$teamslink ?></textarea></td>
 </tr>
  <?php } ?>
   <tr>
  <td valign="top" align="right"><span class="copy"><strong>Blended Learning?:</strong></span></td> 
   <td valign="top"><input type='checkbox' name='blendedlearning' value='1' <?= $blendedlearning?"CHECKED":""?>> <!--&nbsp;&nbsp;&nbsp;&nbsp;Keep Equipment?  <input type='checkbox' name='blendedlearningkeep' value='1' <?=$blendedlearningkeep?"CHECKED":""?>>
   <input name="was_bl_keep" type="hidden" value="<?=$blendedlearningkeep?"1":"0"?>">-->
   </td>
 </tr>
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Annual Recert Note?</strong></span></td> 
 <td valign="top"><span class="copy"><input type='checkbox' name='addannual' value='1' > </td>
 </tr>
 <input type='hidden' name='getsecards' value='1' >
 <input type='hidden' name='getsebooks' value='1' >
<!--
   <tr>
   <td valign="top" align="right"><span class="copy"><strong>eBooks?:</strong></span></td> 
 <td valign="top"><input type='checkbox' name='getsebooks' value='1' <?=$getsebooks?"CHECKED":""?>></td>
 </tr>
  -->
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Books Mailed Date:</strong></span></td>
   <td valign="top"><span class="copy"> <?=printdates2( "booksmaileddate", ($booksmaileddate>'0000-00-00')?$booksmaileddate:"picktoday" )?> &nbsp;&nbsp;&nbsp;<input type='hidden' name='ebookssent' value='<?= $ebookssent?"1":"0"?>'> </td>
 </tr>   
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Cards Mailed Date:</strong></span></td> 
 <td valign="top"><span class="copy"><?=printdates2( "cardsmaileddate", ($cardsmaileddate>'0000-00-00')?$cardsmaileddate:"picktoday" )?> <input type='hidden' name='ecardssent' value='<?= $ecardssent?"1":"0"?>'></td>
 </tr>   
   <tr>
  <td valign="top" align="right"><span class="copy"><strong>Spot Check?:</strong></span></td> 
 <td valign="top"><input type='checkbox' name='spotcheck' value='1' <?=$spotcheck?"CHECKED":""?>></td>
 </tr>   
<tr>
   <tr>
  <td valign="top" align="right"><span class="copy"><strong>Practice Code Blue Paperwork Received:</strong></span></td> 
 <td valign="top"><input type='checkbox' name='pcbpreceived' value='1' <?= $pcbpreceived?"CHECKED":""?>></td>
 </tr>   
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Roster Received:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='checkbox' name='rosterreceived' value='1' <?=$rosterreceived?"CHECKED":""?>> </td>
 </tr>   
  <td valign="top" align="right"><span class="copy"><strong>Cards/Books Not Needed:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='checkbox' name='cardsnotneeded' value='1' <?= $cardsnotneeded?"CHECKED":""?>> </td>
 </tr>   
<?php } else { ?>
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Books Mailed:</strong></span></td> 
 <td valign="top"><span class="copy"><?=fixdatefordisplay( $booksmaileddate , true ) ?></td>
 </tr>
<tr>
<td valign="top" align="right"><span class="copy"><strong>Cards Mailed:</strong></span></td> 
 <td valign="top"><span class="copy"><?=fixdatefordisplay( $cardsmaileddate , true ) ?></td>
<input type='hidden' name='cardsmaileddate' value="<?=$cardsmaileddate ?>">
<input type='hidden' name='booksmaileddate' value="<?=$booksmaileddate ?>">
<input type='hidden' name='cardsnotneeded' value="<?=$cardsnotneeded ?>">
<input type='hidden' name='rosterreceived' value="<?=$rosterreceived ?>">
<input type='hidden' name='getsebooks' value="<?=$getsebooks ?>">
<input type='hidden' name='getsecards' value="<?=$getsecards ?>">
<input type='hidden' name='spotcheck' value="<?=$spotcheck ?>">
<input type='hidden' name='pcbpreceived' value="<?=$pcbpreceived ?>">
<input type='hidden' name='remote' value="<?=$remote ?>">
<input type='hidden' id="islocked" name='islocked' value="<?=$islocked ?>">
<input type='hidden' name='blendedlearning' value="<?=$blendedlearning ?>">
<input type='hidden' name='blendedlearningkeep' value="<?=$blendedlearningkeep ?>">
<input type='hidden' name='ecardssent' value="<?=$ecardssent ?>">
<input type='hidden' name='ebookssent' value="<?=$ebookssent ?>">
 </tr>   
<?php } ?>
<tr>
<td valign="top" align="right"><span class="copy"><strong>Description:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='text' name='cardsmailed' size='20' value='<?=$cardsmailed ?>'></span></td>
 </tr>   
<?php if( isOverallAdmin() ) { ?>
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Is Conference Room:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='checkbox' name='isconferenceroom' value="1" <?= $isconferenceroom?"CHECKED":""?>> <?php if( $company["iscorp"] ) { ?>
<b>Is National:</b> <input type='checkbox' name='isnational' value="1" <?= $isnational?"CHECKED":""?>>
<?php } ?>

<b>UPS?</b> <input type='checkbox' name='isups' value="1" <?= $isups?"CHECKED":""?>></td></tr>
   <tR><td align='right'><span class=copy><b>Masks Ordered:</span></b></td><td><span class=copy> <input size='3' type='text' name='nummasks' value="<?=$nummasks ?>"></span>
   </td> 
 </tr>
<?php } else { ?>
 <input type='hidden' name='isnational' value="<?=$isnational ?>" >
 <input type='hidden' name='nummasks' value="<?=$nummasks ?>">
 <input type='hidden' name='isups' value="<?=$isups ?>" >
 <input type='hidden' name='isconferenceroom' value="<?=$isconferenceroom ?>" >
<?php }?>
<?php if( $company["iscorp"] && isOverallAdmin() ){ ?>
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Course Fee:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='text' name='coursefee' size='20' value="<?=$coursefee ?>"></span></td> 
 </tr>
<tr>
  <td valign="top" align="right"><span class="copy"><strong>Invoice No:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='text' name='invoiceno' size='15' value="<?=$invoiceno ?>"> PO #: <input type='text' name='ponumber' value="<?=$ponumber ?>"></span></td> 
 </tr>
  <td valign="top" align="right"><span class="copy"><strong>Invoice Notes:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='text' name='invoicenotes' size='40' value="<?=$invoicenotes ?>"> </span></td> 
 </tr>
   <?php } 
else
{
 ?>
 <input type='hidden' name='coursefee' value="<?=$coursefee ?>" >
 <input type='hidden' name='invoiceno' value="<?=$invoiceno ?>" >
 <input type='hidden' name='ponumber' value="<?=$ponumber ?>" >
<!-- <input type='hidden' name='invoicepaid' value="<?=$invoicepaid ?>" >-->
<?php 
}?>
 <tr><td colspan="2"><br></td></tr>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong><?=getSchoolStr( "School" )?>:</strong></span></td> 
 <td valign="top"><span class="copy"><a href='viewcompany.php?id=<?=$companyid?>'><?=$companyname?></a></span></td>
 </tr>
 <tr>
  <td valign="top" align="right"><span class="copy"><strong>
<?=getSchoolStr( "School" )?> Code:</strong></span></td> 
 <td valign="top"><span class="copy"><a href='viewcompany.php?id=<?=$companyid?>'><?=$schoolcode?></a></span></td>
 </tr>
<tr>
  <td valign="top" align="right"><span class="copy"><strong><?=getSchoolStr( "Location" )?>:</strong></span></td> 
 <td valign="top"><span class="copy"><?=$address?>, <?=$city?> <?=$zip?></span></td>
 </tr>
<?php if( $company["iscorp"] ){ ?>  
<tr><td class='copy' align='right'><b><?= $remote?"Participant":"Training"?> Address</b>:</td><td class='copy'><input class='copy' name='training_location' value="<?=$training_location ?>" size='40' ><br>
 
 <tr><td class='copy' align='right'><b>Training Room Number</b>:</td><td class='copy'><input class='copy' name='training_room_number' value="<?=$training_room_number ?>" size='10' ><br>
<tr><td class='copy' align='right' valign='top'><b> Training City, State Zip:</b></td><td class='copy'> <input class='copy' name='training_city' value="<?=$training_city ?>" size='15' >, <input class='copy' name='training_state' value="<?=$training_state ?>" size='2' > <input class='copy' name='training_zip' value="<?=$training_zip ?>" size='5' ><br>
<input type='checkbox' onClick='document.forms["clform"].training_location.value="<?=$address?><?= $floor?", $floor":""?>"; document.forms["clform"].training_city.value="<?=$city?>"; document.forms["clform"].training_state.value="<?=$state ?>";document.forms["clform"].training_zip.value="<?=$zip?>"; '> Same as headquarters
</td></tr>
<?php } ?>
<?php if( !$company["iscorp"] ){ ?>  <tr>
  <td valign="top" align="right"><span class="copy"><strong>Borough:</strong></span></td> 
 <td valign="top"><span class="copy"><?=$borough?></span></td> </tr>
<?php }?>
<?php if( $trainerreq ) { ?>
<tr> <td valign="top" align="right"><span class="copy"><strong>Requested Instructor:</strong></span></td> 
 <td valign="top"><span class="copy"><?=getFullname( $trainerreq )?></span></td></tr> 
<?php } ?>
<tr> <td valign="top" align="right"><span class="copy"><strong>Trainer:</strong></span></td> 
 <td valign="top"><span class="copy"><?=$trainerstr ?></span></td> </tr>
<?php if( isOverallAdmin() || strtolower( $session_userid ) == "stregistrar@ahrcnyc.org" ) { ?>
<tr> <td valign="top" align="right"><span class="copy"><strong>Num Trainers:</strong></span></td> 
 <td valign="top"><span class="copy"><input type='text' name='numtrainers' value='<?=$numtrainers ?>' size='3'></span></td> </tr>
<?php if( $session_iscorp != TRAININGSITES || 1 ) { ?>
<tr> <td valign="top" align="right"><span class="copy"><strong>TC Faculty:</strong></span></td> 
 <td valign="top"><span class="copy">
<select name='tcfacultyid'>
<option value=''></option>
<?php 
$trainers = getAllTrainers( );
 foreach( $trainers as $trow )
{
 if( !$trow["tcfaculty"] )
 continue;
$avon = availableOn( $startdate, $trow['id'], $id );
if( $avon )
{
$ext = $avon==2||$avon==3?" (AB)":"";

?>
<option <?=($trow["id"] ) == ($tcfacultyid )?"SELECTED":""?> value='<?=$trow["id"]?>'><?=$trow["first_name"]?> <?=$trow["last_name"]?> <?=$ext?></option>
<?php }
}?>
</select>
</span></td></tr>
<?php } ?>
<?php } else{ ?>
<input type='hidden' name='numtrainers' value='<?=$numtrainers ?>'>
<input type='hidden' name='tcfacultyid' value='<?=$tcfacultyid ?>'>
<?php 
$trainers = getAllTrainers( );

} ?>
</table>
   </td>
  </tr>
</table>
   <td align="right" valign="top" width="160">
<table border="0" cellpadding="0" cellspacing="6">
 
 <tbody>
<?php
$twoweeks = mktime( 0,0,0,date( "m" ) , date( "d" ) + 14, date( "Y" ) );
$within2weeks = ( strtotime( $startdate ) < $twoweeks ) ;

// if there's an instructor, no one can reschedule
if( count( $trainers ) ) $within2weeks = true;

if( !$within2weeks || $specialadmin )
{
?>
<tr>
  <td rowspan="5" bgcolor="#999999"><img src="images/dotclear.gif" height="2" width="1"></td>
  <!-- $company["iscorp"] -->
  <td valign="top"><a onClick='return checkAck()' href='https://<?=getUrlPrefix(0)?>.<?=URL_WITHOUT_SUBDOMAIN?>/reschedule_class.php?id=<?=$id?>'><img border=0 src="images/button_reschedule.gif"></a>
</td>
 </tr>
<tr>
  <td valign="top"><input type='button' onClick='javascript:document.location.href="class_edit.php?id=<?=$id?>&sendupdated=true"' value='Send Updated Mail'>
</td>
 </tr>
<?php if($specialadmin ) {?>
 <tr>
  <td valign="top"><a href="javascript:confirm_delete();"><img border=0 src="images/button_cancelclass.gif"></a><Br>
<input id="dontsendcancelmail" type='checkbox' name="dontsendcancelmail" value='1'>Don&apos;t Send Cancel Email</a></td>
 </tr>
 <tr>
  <td valign="top"><a href="javascript:confirm_covid_delete();">Covid Cancel</a></td>
 </tr>
<?php
if($deleted ) { ?>
 <tr>
  <td valign="top"><a href="class_undelete_thanks.php?id=<?=$id?>" class=copy>Readd Class</a></td>
 </tr>
<?php } ?>
<?php } ?> 
 <tr>
  <td valign="top"><span class="small"><font color="#666666"><strong>Important:</strong><br>
<input type='checkbox' name='ack' value='1'>
Requests to reschedule or cancel a class are subject to approval by ESI. You will be notified of whether your request is approved or denied.
<?php if(!$session_iscorp ) { ?>
<br><input type='checkbox' name='tradein' <?=$tradein?"CHECKED":""?> value='1'>
<b>Trade-In</b>
<?php }?>
</font></span>
</td>
 
 </tr>
<?php } ?>
</tbody></table>

   </td>
  </tr>
</table>
<?php if($specialadmin ) { ?>   
  <strong><span class="COPY">INSTRUCTOR NOTES:</span></strong>
  <hr>
  <table cellpadding="0" cellspacing="0" border="0" width="476">
  <tr>
 <td valign="top">
   <table cellpadding="0" cellspacing="4" border="0">
 <tr>
  <td valign="top"><textarea name="instructornotes" cols="70" rows="8" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"><?=$instructornotes ?></textarea></td>
 </tr>
</table>
<?php } else { ?>
<input type='hidden' name='instructornotes' value="<?=htmlentities($instructornotes )?>">
<?php } ?>

   
<p><br>
  
<input type="hidden" name="id" value="<?=$id?>">
<input type="hidden" name="cancelreason" value="">
  <input type="hidden" name="startdate" value="<?=$startdate?>">
<?php  if( isOverallAdmin() ) { ?>
  <strong><span class="COPY">UPDATE LOCATION</span></strong>
  <hr>
Update Class Location:
<?php if( $company["iscorp"] ){ ?>
<input type='hidden' name='borough' id='borough' value='other'>
<?php } else {

?>
Borough: <select id=borough name="borough" style="font-size: 10px;  font-family: verdana;">
 <option value=""></option>
<option value="Bronx">The Bronx</option>
<option value="Brooklyn">Brooklyn</option>
   <option value="Manhattan">Manhattan</option>
 <option value="Queens">Queens</option>
  <option value="Staten Island">Staten Island</option>
</select>
<?php } ?>

<input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='updateCompanies()'> <input type='button' value='Search' class=copy onClick='updateCompanies()'><br>
 <span class='copy'><i>Use keywords. For example, if your school is 10-X-475 John F. Kennedy School, search for "475" or "Kennedy" or "John".</span></i>
<span id='school_select'></span>
<br><input type='checkbox' name='updateattendees' value='1'> Update Attendees to this school
<input type='submit' name='updatelocation' value='Update Location'><br><br>
<?php } ?>
  <strong><span class="COPY">ATTENDEES</span></strong>
  <hr>
<?php if( !$company["iscorp"] ){ ?>
<font color='red'>Please note: at least 7 participants are required to request a class.</font><br>
  If fewer than 12 are listed, the remaining slots will be posted online for additional staff registration.<p>
<?php } ?>  
  
  <table cellpadding="0" cellspacing="0" border="0" width="470">
 <tr>
  <td valign="top">
  <table cellpadding="4" cellspacing="0" border="0" class="padding4">
  <?php if($specialadmin ) {?>
<tr><th></th><?php if( strtotime( $startdate ) < time() ) { ?><th>A?</th><th>C?</th><?php } ?><th>Name</th><?php if( isOverallAdmin() ) { ?><th>Timeslot</th><?php } ?><?php if( $company['iscorp'] ) { ?><th>Paid?</th><?php } ?>

</tr>
<?php } ?>
  <?php 
$notcompleted = "";
for ($i = 1; $i <= $class["maxattendees"]; $i++) {
  $arow = $attendees[$i];
   $completed =  $arow["responderid"] && isCompleted( $arow["responderid"], $id );
$bg = $completed? "bgcolor='#A1D490'":"";

$dontshow = false;
   ob_start();

?>
   <tr <?=$bg?>>
<td class='padding4' valign="top"><span class="copy"><nobr>XXXYYY</nobr></span></td>
  <?php if($specialadmin && strtotime( $startdate ) < time() ) {

  $attendee = get_attendee($arow["responderid"] );
   $attended = $arow["attended"];
  if($arow["responderid"]  )
  {
   echo( "<td class='padding4' ><input type='checkbox' name='attended[$i]' value='1' " . ($arow["attended"]?"CHECKED":"").">" );
   }
   else
{
 echo( "<td></td>" );
}
  if( $arow["responderid"] && !isCompleted( $arow["responderid"], $id ) )
  {
  ?>
<td class='padding4' >N</td>
   <?php }
  else if( $attendees[$i])
  {
   $completed = true;
  echo( "<td class='padding4' ><b>C</b></td>" );
 $dontshow = true;
}
   else
echo( "<td></td>" );
}
 if( !$dontshow )
{
  ?>
  <td class='padding4'  valign="top">
<select name="attendee<?=$i?>" style="max-width: 200px; font-size: 10px;  font-family: verdana;" onChange="javascript: return checkAlreadyInClass( this,'clform' )" >
  <?php list( $a, $b ) = get_teacher_dropdown($companyid, $arow, $company['iscorp']);
  echo( $a );
  ?>
</select>
  <?php
   if(isOverallAdmin())
   {
   echo( "<td><input type='text' id='timeslot$i' size='5' name='timeslot[$i]' value=\"".($arow['timeslot'] )."\"></td> " );
   }
   else
   {
   echo( "<input type='hidden' name='timeslot[$i]' value=\"".($arow['timeslot'] )."\">  " );
   }
   
 if( !$company['iscorp'] && $arow['responderid'])
{
if( checkNeedsValidation( $arow['responderid'] ) == 1 )
echo( "<font color='red'>Not Validated</font>" );
if( checkNeedsValidation( $arow['responderid'] ) == 2 )
echo( "<font color='red'>Not Listed</font>" );
}
?>
<?php } else {
$iden = $company['iscorp'] || isOverallAdmin()?" (#" . getIdentifier( $attendee ).")":"";
?>
<td class='padding4' ><span style="font-size: 10px;  font-family: verdana;"> <?=($attendee['firstname'] ) . " " . ($attendee['lastname'] ) . $iden?> <input name="attendee<?=$i?>" type='hidden' value='<?=$arow['responderid'] ?>'></span>
<?php 
if( canBeMerged( $arow['responderid'] , $attendee['firstname'] , $attendee['lastname']  ) )
{
 echo( "<a href='class_edit.php?id=$id&fixmergefirst=".urlencode( $attendee['firstname']  )."&fixmergelast=".urlencode( $attendee['lastname']  ) . "'><font color='red'>merge!</font></a>" );
}
?>
</td>
<?php } ?>
  <?=$b?>
</td>
<?php if( $company['iscorp'] ) { ?>
<?php if($specialadmin ) { ?>
<td class='padding4' ><input type='checkbox' name='paid[<?=$i?>]' value='1' <?= $arow["ispaid"]?"CHECKED":""?>></td>
<?php } else { ?>
<input type='hidden' name='paid[<?=$i?>]' value='<?=$arow["ispaid"]?"1":"0"?>'>
<?php } ?>
<?php } ?>
 </tr>
 <?php 
  $val = ob_get_contents();
  ob_end_clean();
  if( $completed )
  $completedrows[] = ( $val );
  else if( $attended )
  $attendedrows[] = ( $val );
  else
  $notcompletedrows[] = $val;  

}
$i = 1; 
if(is_array( $completedrows ) )
{
foreach( $completedrows as $cn )
{
echo( str_replace( "XXXYYY", $i . ". ", $cn ) );
$i++;
}
}
if(is_array( $attendedrows ) )
{
foreach( $attendedrows as $cn )
{
echo( str_replace( "XXXYYY", $i . ". ", $cn  )) ;
$i++;
}
}
if( is_array( $notcompletedrows ) )
{
foreach( $notcompletedrows as $cn )
{
echo( str_replace( "XXXYYY", $i . ". ", $cn  )) ;
$i++;
}
}

?>
</tbody>
</table>
<?php if( $specialadmin ) { ?>
<span class='copy'>Max # Attendees: <input type='text' name='maxattendees' size='4' value='<?=$class["maxattendees"] ?>'><input type='hidden' name='hiddenmaxattendees' value='<?=$class["maxattendees"] ?>'>
<?php if( $class["maxattendees"] > 12 ) { ?><br>
<span class='copy'>Split Class into 10 per class: <input type='checkbox' name='splitclass' size='4' value='1' >
<br>
<?php } ?>
<?php } else { ?>
<input type='hidden' name='maxattendees' value='<?=$class["maxattendees"] ?>'>
<?php } ?>
<?php if( isOverallAdmin() || ((strtolower( $session_userid ) == "adam.sterling@parks.nyc.gov" || strtolower( $session_userid ) == "tsmalls@ucpnyc.org" || strtolower( $session_userid ) == "ken.conyers@parks.nyc.gov" || strtolower( $session_userid ) == "venise.davis@nypd.org" || strtolower( $session_userid ) == "kmighty@adaptcommunitynetwork.org")) ) { ?>
<br><br>
<nobr>CSV File: <input type='file' name='namesfile'> <input type='submit' name='addnames' value='Upload Names From CSV'></nobr><i> Format: Last Name, First Name, PMS ID (optional), Email (optional), Job Title (optional), Phone (optional), Timeslot (optional)</i> <br>
<input type='submit' onClick='return confirm( "Are you sure you want to delete all names from this class? This procedure is irreversible." ); ' name="clearnames" value="Clear Names From Class">
<br><br>
<input type='submit' name='saveattendees' value='Save Attendees'>
<?php } ?>
<?php if( isOverallAdmin() && strtotime( $startdate ) <  time() ) { ?>
  <input type="submit" name='update' value='Save Changes to Attendees'>
<input type='submit' name='updatecompletions' value='Update Attendance'>
<input type='button' name='c' onClick='document.location.href="updatecompletions.php?id=<?=$id?>";' value='Update Certifications'>
<?php
}
if( isOverallAdmin() ) { ?>
<input type='button' name='enter' onClick='window.open( "updatephonenumbers.php?id=<?=$id?>" )' value='Enter Phone Numbers for Attendees'> 
<br><br>
<nobr>Upload Cards: <input type='file' name='cardfile'> <input type='submit' name='uploadcards' value='Upload'></nobr><?php if( file_exists( "classcards/$id.pdf" ) ) { echo( " <A href='classcards/$id.pdf'>View Card</a><Br>" ); }
if( file_exists( "classcards/$id.xls" ) ) { echo( " <A href='classcards/$id.xls'>View Card</a><Br>" ); }

$ls = shell_exec( "ls savedcardpdfs/{$id}_*.pdf" );
$ls = preg_split('/\s+/', trim($ls));
foreach( $ls as $l )
{
 $dt = explode( "_", $l );

 $dt = explode( ".", $dt[1]  );
 $dt = $dt[0] ;
 if( $dt )
  $dt = date( "Y-m-d", $dt );
 echo( "<a href='$l'>$dt</a><Br>" );
}

}
else
{
?>
<?php
}
?>

</td>
   <td valign="top" width="150">
<table cellpadding="0" cellspacing="7" border="0" width="150">
 <tr>
  <td valign="top" width="1" bgcolor="#000000"><img src="images/dotclear.gif" width="1" height="1"></td>
  <td valign="top"><span class="copy">
<?php if( count( $attendees ) < $class['maxattendees'] ) {  ?>
If the name of your attendee is not in the dropdown lists, you can add them by clicking here:</span><p>
<a href="#" onClick="if( !classfull() ) { MyWindow=window.open('add_attendee.php?overrideiscorp=<?=$company["iscorp"]?>&companyid=<?=$companyid?>&c=<?=$c?>&s=<?=$s?>&m=<?=$m?>&d=<?=$d?>&yr=<?=$y?>&currcnt=<?=count( $attendees )?>&maxcnt=<?=$class['maxattendees'] ?>','attendee','toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=400,height=700'); MyWindow.focus(); } return false;"><img src="images/button_addattendee.gif" border="0"></A>
<br><br>
<!--<span class=copy>or </span>
<a href="#" class=copy onClick="if( !classfull() ) { MyWindow=window.open('add_attendee_other.php?companyid=<?=$company["id"]?>&overrideiscorp=<?=$company["iscorp"]?>&c=<?=$c?>&s=<?=$s?>&m=<?=$m?>&d=<?=$d?>&yr=<?=$y?>','attendee','toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=400,height=700'); } return false;">Add attendee from other <?=($session_iscorp?"location":"school" )?></A><br><br>-->
<?php } else { ?>
<font color='red'>Class full.</font>
<?php } ?>
</td>
 </tr>
</table>
   </td>   
  </tr>
</table>
  
 <p><br>
 
  <strong><span class="COPY">ON-SITE CONTACT:</span></strong>
  
  <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
   <tr>
  <td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
  <tr>
   <td>
 <span class="copy">First Name:</span><br>
<input name="firstname" value="<?=$firstname ?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">MI:</span><br>
<input name="mi" value="<?=$mi ?>"type="text" id="" size="1" maxlength="1" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Last Name:</span><br>
<input name="lastname" value="<?=$lastname ?>"type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
   </td>
<td valign="top"><span class="copy">Title:</span><br>
<input name="contacttitle" type="text" id="" value="<?=$contacttitle ?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
   </td>
 </tr>
</table>
</td>
</tr>

   <tr> 
<td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
 <tr>
<td valign="top"><span class="copy">Phone Number:</span><br>
<input name="phone" value="<?=$phone ?>" type="text" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Ext:</span><br>
<input name="phone_ext" value="<?=$phone_ext ?>" type="text" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Fax:</span><br>
<input name="fax" value="<?=$fax ?>" type="text" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Email:</span><br>
<input name="email" value="<?=$email ?>" type="text" id="" size="20" maxlength="100" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
  </tr>
 <tr>
<td valign="top"><span class="copy">Cell Number:</span><br>
<input name="cellphone" value="<?=$cellphone ?>" type="text" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
 </table>
</td>
   </tr>   
  </table>

  <p><br>

  <strong><span class="COPY">ALTERNATE CONTACT:</span></strong>
  <hr>
   
  <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
   <tr>
  <td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
  <tr>
   <td>
 <span class="copy">First Name:</span><br>
<input name="alt_firstname" value="<?=$alt_firstname ?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">MI:</span><br>
<input name="alt_mi" value="<?=$alt_mi ?>" type="text" id="" size="1" maxlength="1" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Last Name:</span><br>
<input name="alt_lastname" value="<?=$alt_lastname ?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
   </td>
<td valign="top"><span class="copy">Title:</span><br>
<input name="altcontacttitle" type="text" id="" value="<?=$altcontacttitle ?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px">
   </td>
 </tr>
</table>
</td>
</tr>

   <tr> 
<td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
 <tr>
<td valign="top"><span class="copy">Phone Number:</span><br>
<input name="alt_phone" value="<?=$alt_phone ?>" type="text" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Ext:</span><br>
<input name="alt_phone_ext" value="<?=$alt_phone_ext ?>" type="text" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Fax:</span><br>
<input name="alt_fax" value="<?=$alt_fax ?>" type="text" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Email:</span><br>
<input name="alt_email" value="<?=$alt_email ?>" type="text" id="" size="20" maxlength="100" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
  </tr>
 <tr>
<td valign="top"><span class="copy">Cell Number:</span><br>
<input name="altcellphone" value="<?=$altcellphone ?>" type="text" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
 </table>
</td>
   </tr>
  </table>
<?php if( !$company["iscorp"] ){ ?>
  <br>
  <strong><span class="COPY">Emergency Contact:</span></strong>
  <hr>
  <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
   <tr>
  <td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
  <tr>
   <td>
 <span class="copy">Name:</span><br>
<input name="emergency_name" value="<?=$emergency_name ?>" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
 </tr>
 <tr>
<td valign="top"><span class="copy">Cell:</span><br>
<input name="emergency_cell" value="<?=$emergency_cell ?>" type="text" id="" size="30" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
  </tr>
 </table>
</td>
   </tr>   
  </table>
  
  <strong><span class="COPY">PRINCIPAL:</span></strong>
  <hr>
  <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
   <tr>
  <td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
  <tr>
   <td>
 <span class="copy">Name:</span><br>
<input name="principalname" value="<?=$principalname ?>" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
 </tr>
 <tr>
<td valign="top"><span class="copy">Email:</span>
<input name="principalemail" value="<?=$principalemail ?>" type="text" id="" size="40" maxlength="100" style="font-family: verdana; font-size: 11px; line-height: 13px">
</td>
  </tr>
<td valign="top"><span class="copy">Phone:</span>
<input name="principalphone" value="<?=$principalphone ?>" type="text" id="" size="15" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px">
</td>
  </tr>
 </table>
</td>
   </tr>   
  </table>
  
  <strong><span class="COPY">Custodian:</span></strong>
  <hr>
  <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
   <tr>
  <td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
  <tr>
   <td>
 <span class="copy">Name:</span><br>
<input name="custodian" value="<?=$custodian ?>" type="text" id="" size="40" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
 </tr>
 </tr>
<td valign="top"><span class="copy">Phone:
<input name="custodianphone" value="<?=$custodianphone ?>" type="text" id="" size="15" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px">
Ext: <input name="custodianext" value="<?=$custodianext ?>" type="text" id="" size="5" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px">
</td>
  </tr>
 </table>
</td>
   </tr>   
  </table>
  
<?php } ?>  
  <p><BR>
 
  <strong><span class="COPY">TRANSPORTATION INFO:</span></strong>
  <hr>
  
  
  <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
   <tr>
  <td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
   <tr>
<td valign="middle" colspan="3"><span class="copy">Parking space reserved for the educator:</span> <select name="parking_reserved" style="font-size: 10px;  font-family: verdana;">
<option value=''></option>

<option value="0">No</option>
   <option <?=$parking_selected?> value="1">Yes</option>
 </select>
</td>
</tr>  
<?php if( !$company["iscorp"] ){ ?>  
  <tr>
   <td>
 <span class="copy">Is there parking security?</span><br>
<textarea name="parking_security" cols=50 style="font-family: verdana; font-size: 11px; line-height: 13px"><?=$parking_security ?></textarea></td>
  </tr>
  <?php } ?>
  <tr>
<td valign="top"><span class="copy">Nearest Subway Line / Station:</span><br>
<input name="nearest_subway" value="<?=$nearest_subway ?>"  type="text" id="" size="60" maxlength="60" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>

 </tr>
</table>
</td>
</tr>
  </table>
  
  <p><br>
  
  <strong><span class="COPY">ADDITIONAL ITEMS:</span></strong>
  <hr>
  
  <table cellpadding="0" cellspacing="6" border="0" width="100%">
<tr><td class='copy'><?=getSchoolStr( "School Entrance" )?>: </td><td><textarea class='copy' name='school_entrance' cols='50'><?=$school_entrance ?></textarea></td></tr>
   <tr>
<?php if( !$company["iscorp"] ){ ?>  
<tr><td class='copy' align='right'><b>Training Address</b>:</td><td class='copy'><input class='copy' name='training_location' value="<?=$training_location ?>" size='40' ></td></tr>
<tr><td class='copy' align='right'><b>Training Room Number</b>:</td><td class='copy'><input class='copy' name='training_room_number' value="<?=$training_room_number ?>" size='10' ></td></tr>
<tr><td class='copy' align='right'><b>Training City, State Zip:</b></td><td class='copy'><input class='copy' name='training_city' value="<?=$training_city ?>" size='15' >, <input class='copy' name='training_state' value="<?=$training_state ?>" size='2' > <input class='copy' name='training_zip' value="<?=$training_zip ?>" size='5' >
</td></tr>
<?php } ?>
<?php if( isOverallAdmin() ){ ?>  
<tr><td class='copy'>Equipment Notes:</td><td class='copy'><textarea name='equipnotes' cols='50'><?=$equipnotes ?></textarea></td></tr>

<tr>
   <td class='copy'><span class="copy">Equipment Round Trip</span></td>
   <td class='copy'><input name="equip_roundtrip" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?= $equip_roundtrip?"CHECKED":""?>></td>
   <input name="was_equip_roundtrip" type="hidden" value="<?= $equip_roundtrip?"1":"0"?>">
</td>
 </tr>

   <tr>
   <td class='copy'><span class="copy">Equipment To Keep</span></td>
   <td class='copy'><input name="equip_tokeep" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?=$equip_tokeep?"CHECKED":""?>></td>
   <input name="was_equip_tokeep" type="hidden" value="<?=$equip_tokeep?"1":"0"?>">
</td>
 </tr>

   <tr>
   <td class='copy'><span class="copy">Heartsaver Interactive - Round Trip</span></td>
   <td class='copy'><input name="equip_hirt" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?= $equip_hirt?"CHECKED":""?>></td>
   <input name="was_equip_hirt" type="hidden" value="<?= $equip_hirt?"1":"0"?>">
</td>
 </tr>

   <tr>
   <td class='copy'><span class="copy">Heartsaver Interactive - Keep</span></td>
   <td class='copy'><input name="equip_hik" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?= $equip_hik?"CHECKED":""?>></td>
   <input name="was_equip_hik" type="hidden" value="<?= $equip_hik?"1":"0"?>">
</td>
 </tr>



<tr><td class='copy'>Equipment Shipment Status:</td><td class='copy'><textarea name='shipmentstatus' cols='50'><?=$shipmentstatus ?></textarea></td></tr>
<tr><td class='copy'>Equipment Number:</td><td class='copy'><input name='equipnumber' value="<?=$equipnumber ?>">
   A/V Equip Number:
   <?php $drop_rows = db_query_rows("select value from esioptionvalues where datatype='avequip' order by value"); ?>
<select name="avequip" style="font-size: 10px;  font-family: verdana;">
 <option value='<?=$avequip ;?>'><?=$avequip ;?></option>
 <?php foreach ($drop_rows as $d) { ?>
 <option value="<?=$d["value"];?>"><?=$d["value"];?></option>
 <?php } ?>
 </select>


   </td></tr>
<?php } ?>
<tr><td class='copy'>Equipment Scheduled For Delivery? <input <?=$specialadmin?"":"READONLY"?> type='checkbox' name='equipscheduled' value='1' <?=$equipscheduled?"CHECKED":""?>></td></tr>
<tr><td class='copy'>Equipment Returned? <input <?= $specialadmin?"":"READONLY"?> type='checkbox' name='equipreturned' value='1' <?=$equipreturned?"CHECKED":""?>></td></tr>
<tr>
  <td valign="middle" colspan='2'>
   <?php if( 1 == 0 ) { // removed 4/5/2021 ?> 
 <table cellpadding="0" cellspacing="2" border="0">
  <tr>
   <td valign="middle" colspan='4'><span class="copy">The AHA curriculum is video driven and is accessible via DVD or internet streaming. Please select the audio/visual equipment you have available for the training program. Select all that apply.</em></span></td>
 </tr>
   <tr>
   <td valign="middle"><input name="available_streaming" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?=$available_streaming?"CHECKED":""?>></td>
   <td valign="middle"><span class="copy">Computer/Projector with access to Wi-Fi and permission to stream</span></td>
 </tr>
   <tr>
   <td valign="middle"><input name="hasanydvd" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" <?=$checked_hasanydvd?"CHECKED":""?>></td>
   <td valign="middle"><span class="copy">DVD player with remote control capabilities </span></td>
 </tr>
   <tr>
   <td valign="middle"><input <?=$checked_computer?> name="available_computer" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"  ></td>
   <td valign="middle" colspan='4'><span class="copy">Computer running Windows Media Player with DVD drive and projector/monitor</span></td>   


<?php if( $company['iscorp']) { ?>
 </tr>
   <tr>
   <td valign="middle"><span class="copy">Power Point Available (for ALIVE! First Aid only)</span>
   <input <?=$checked_powerpoint?> name="available_powerpoint" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" ></td>
 <?php } ?>
  </tr>
 </table><br><br>
 <?php }?>
<strong> All of ESI's courses are video driven.  We require either the ability to use a USB drive, stream content from the internet, or attach a DVD player via HDMI to a TV monitor or projector. Please review the choices below and select ALL that apply and will be available to the instructor the day of the training.   
  
<?php $exp = explode( ";", $avail_technologies  );

$allposs = array( 
"Smartboard or Computer/Projector combination",
"Hardwired internet",
"Reliable WiFi network",
"Available USB port in your computer/laptop",
"TV/Monitor or Projector with HDMI hookup",
"We have no A/V capabilities at all. " );

?>
<div class="table1">
<table cellpadding="0" cellspacing="0" border="0">
 <?php foreach( $allposs as $a ){ ?>
<tr>
<td valign='middle'><input type='checkbox' name='availtechnologies[]' value='<?=$a?>' <?=in_array( $a, $exp )?"CHECKED":""?>></td>
<td valign='middle'><?=$a?></td>
</tr>
<?php } ?>
</table>
 
 <table cellpadding="0" cellspacing="0" border="0" bgcolor='#eeeeee'>
  <tr>
   <td valign="middle"><span class="copy">ARCHIVED Available Equipment <em>(check all that apply)</em></span></td>
   <td>&nbsp;&nbsp;</td>
   <td valign="middle"><input <?=$checked_tvdvd?> name="available_tvdvd" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" readonly onClick='return false'></td>
   <td valign="middle"><span class="copy">TV with DVD Player</span></td>
   <td>&nbsp;&nbsp;</td>
   <td valign="middle"><input <?=$checked_tvvcr?> name="available_tvvcr" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" readonly onClick='return false'></td>
   <td valign="middle"><span class="copy">TV ONLY</span></td>
<td>&nbsp;&nbsp;</td>
</tr><tr><td></td>
  <?php if( $company['iscorp']) { ?>
<!--   <td>&nbsp;&nbsp;</td>
   <td valign="middle"><input <?=$checked_powerpoint?> name="available_powerpoint" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" readonly onClick='return false'></td>
   <td valign="middle"><span class="copy">Power Point (for ALIVE! First Aid only)</span></td>-->
   <?php } ?>
   <td>&nbsp;&nbsp;</td>
   <td valign="middle"><input <?=$checked_smartboard?> name="available_smartboard" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" readonly onClick='return false'></td>
   <td valign="middle" colspan='4'><span class="copy">Smartboard</span></td>   
   <td>&nbsp;&nbsp;</td>
   <td>&nbsp;&nbsp;</td>
<td valign="middle"><input <?=$checked_noav?> name="noavavailable" type="checkbox" value="1" id="" style="font-family: verdana; font-size: 11px; line-height: 13px" readonly onClick='return false'></td>
   <td valign="middle" colspan='4'><span class="copy">None Available</span></td>   
  </tr>
 </table>
 <br><br>
</td>
   </tr>
<?php if( !$company["iscorp"] ){ ?>
   <tr>
<td valign="middle" colspan="3"><span class="copy">Reserved classroom of adequate size:</span> <select name="reserved_class_adequate" style="font-size: 10px;  font-family: verdana;">
<option value="0">No</option>
  <option <?=$reserved_selected?> value="1">Yes</option>
 </select>
</td>
</tr> 
   <tr>
<td valign="middle" colspan="3"><span class="copy">Building permit complete:</span> <select name="room_permit" style="font-size: 10px;  font-family: verdana;">
<option value="0">No</option>
  <option <?=$room_selected?> value="1">Yes</option>
 </select>
<span class='copy'>If yes, what is the number? <input type='text' name='room_permit_no' size='7' class='copy' value="<?=$room_permit_no ?>"></span>
</td>
</tr>
  <?php } ?>
  </table>
  
  <p><BR>
  
  
  <strong><span class="COPY">NOTES:</span></strong>
  <hr>
  Please give us any notes or additional information you feel is important.<br>
  <table cellpadding="0" cellspacing="0" border="0" width="476">
  <tr>
 <td valign="top">
   <table cellpadding="0" cellspacing="4" border="0">
 <tr>
  <td valign="top"><textarea name="notes" cols="70" rows="8" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"><?=$notes ?></textarea></td>
 </tr>
</table>
  <strong><span class="COPY">CPR Training Equipment Return Instructions:</span></strong>
  <hr>
<span class='copy'>  ESI's courier will pick up equipment from your site after training program.  <br>Where will this equipment be stored?<br></span>
  <table cellpadding="0" cellspacing="0" border="0" width="476">
  <tr>
 <td valign="top">
   <table cellpadding="0" cellspacing="4" border="0">
 <tr>
  <td valign="top"><textarea name="equipdelivinstr" cols="70" rows="3" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"><?=$equipdelivinstr ?></textarea></td>
 </tr>
</table>
   </td>   
  </tr>
</table>
  <strong><span class="COPY">CONFIRMATION NOTES:</span></strong> 
  <hr>
<?php if( isOverallAdmin() ) { ?>
<table> <tr>
  <td valign="top">Confirmed? <input type='checkbox' name='iscallconfirmed' value=1 <?=$iscallconfirmed?"CHECKED":""?> ></td>
 </tr></table>
<?php } ?>
  
ESI Notes: <br>
  <table cellpadding="0" cellspacing="0" border="0" width="476">
  <tr>
 <td valign="top">
   <table cellpadding="0" cellspacing="4" border="0">
<?php if( isOverallAdmin() ) { ?>
 <tr>
  <td valign="top"><textarea <?=$specialadmin?"":"READONLY"?> name="confirmationnotes" cols="70" rows="8" id="" style="font-family: verdana; font-size: 11px; line-height: 13px"><?=$confirmationnotes ?></textarea></td>
 </tr>
<?php } ?>
<?php if( isOverallAdmin() ){ ?>  
<tr><td class='copy'><b>Class Evaluation (General Comments)</b></td></tr><tr><td class='copy'><textarea name='classeval' rows=8 cols='50'><?=$classeval ?></textarea></td></tr>
<?php } ?>
</table>
<?php
if( count( $classinfo ) || $entershipping ) { 
?>
<a name='shippinginfo'></a>
<span <?=!isOverallAdmin()?"style='display:none'":""?>><b>Shipping Information</b></span>
<table <?=!isOverallAdmin()?"style='display:none'":""?>>
  <?php

list( $shippingfields, $sizes, $shippingcomments ) = getShippingFieldsForEdit( $class );
$rownum = 0; 
foreach( $shippingfields as $name=>$default )
{
$values = array();
// if( $name == "Service Level" || $name == "Return Service Lev" )
// {
// $values = getXPOShippingLevels();
// }
if( $name == "Bagset" )
{
$values = getBagsetValues( $crow );
}
if( $name == "Order Type" )
{
$values = getBirdieOrderTypeValues( $crow );
}
$shippingcomment = $shippingcomments[$name]?"<br><i>". $shippingcomments[$name] . "</i>":"";
outputShippingRow( $classinfo, $sizes, $rownum, $name, $default, $values );
$rownum++;
}
?>
</table>
  <?php if( $xpoid ) { ?>
Outgoing XPO ID: <?=$xpoid?><br>
<?php } ?>
  <?php if( $xpoid ) { ?>
Incoming XPO ID: <?=$returnxpoid?><br>
<?php } ?>
  <?php if($birdieid ) { ?>
Outgoing Birdie ID: <?=$birdieid?><br>
<?php } ?>
  <?php if($birdieid ) { ?>
Incoming Birdie ID: <?=$returnbirdieid?><br>
<?php } ?>
  <?php if( $returnxpoid || $xpoid ) { ?>
<a href='viewxpolog.php?classid=<?=$crow['id']?>'>View XPO Log</a><br>
<?php } ?>
  <?php if( $returnbirdieid || $birdieid ) { ?>
<a href='viewbirdielog.php?classid=<?=$crow['id']?>'>View Birdie Log</a><br>
<?php } ?>
  
  <?php } else { ?>
<span <?=!isOverallAdmin()?"style='display:none'":""?>><b><a href='class_edit.php?id=<?=$crow['id']?>&entershipping=1#shippinginfo'>Enter Shipping Information</a></b></span>
<?php } ?>
   </td>   
  </tr>
</table> 
   </td>   
  </tr>
</table>
   </td>   
  </tr>
</table>
 <P> 
<span style='display:none' id='finishedloading'>loaded</span>
  <input type="image" src="images/button_submitrequest.gif">
  </form>
   
   
  <BR><BR><BR><BR>
  
  <!--end center content-->
  
<?php include "ssi/footer.php" ; ?>
  
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

<script type="text/javascript">
function checkAck()
{
if( document.forms["clform"].ack.checked )
return true;
else
{
alert( "Please acknowledge the reschedule policy." );
return false;
}
}
function confirm_delete() {
   var c = confirm('Are you sure you want to cancel this class?');
   var promptval = prompt( "What is the reason for cancelling this class?", "" );
   var url = "class_delete_thanks.php?id=<?=$crow['id']?>";
//   alert( url );
//   alert( promptval );
   if (c) {
  dontsend = jQuery( "#dontsendcancelmail" ).is( ":checked" );
   document.location = url + "&dontsend=" + dontsend + "&cancelreason=" + escape( promptval );
   }
}
function confirm_covid_delete() {
   var c = confirm('Are you sure you want to COVID cancel this class?');
   var promptval = prompt( "What is the reason for cancelling this class?", "" );
   var url = "class_delete_thanks.php?covid=1&id=<?=$crow['id']?>";
//   alert( url );
//   alert( promptval );
   if (c) {
 document.location = url + "&cancelreason=" + escape( promptval );
   }
}
</script>