<?php
require_once('mysql.php');
$crow = getClassRow($id);
$corow = getCompanyRow($crow["companyid"]);
if ($names) {
    $attendees = get_attendees($id, true);
} else {
    $attendees = array();
}
?>
<?php
$mynames = $allclass_names[$corow["iscorp"]];
?>
<?php if (!$nostyles) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0055)http://doe.emergencyskills.com/roster_print.php?id=7345 -->
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<style type="text/css">
html {
margin: 0;
}
body {
margin: 0;
}

.dotTop { border: 1pt dashed #666666;
}

</style>
<link rel="stylesheet" href="https://<?php echo SUB_DOE.".".URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">
<style type="text/css">

P.breakhere {page-break-before: always}

body {
 margin-top: 0px;
 margin-bottom: 0px;
 margin-left: 0px;
 margin-right: 0px;
}
.style2 {
 font-family: arial;
 font-size: 13px;
}
.style3 {
 font-family: arial;
 font-size: 14px;
 color: #0066cc;
}

</style>
</head>

<body>
<?php } ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
  <tr>
    <td><img src="/images/1x1.gif" width="10" height="1"></td>
    <td>
  <table border="0" cellspacing="0" cellpadding="0">
          <tbody>
   <tr>
        <td><table cellpadding=0 cellspacing=0 border=0><tr><td><img src="images/roster_logo.jpg" height="80" /></td><td style="color: #097bb9">emergency skills, inc<br>
305 7th Avenue, Suite 1100
<br>
new york, ny 10001<br>
<span  style='font-size:8px'>212-564-6833 (tel)<br>
212-564-6793 (fax)<br></span>
<a style="color: #097bb9" href='http://www.emergencyskills.com/'>www.emergencyskills.com</a></td></tr></table></td>

        <td class="copy" style="padding-left: 10px;">
     <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
      <tr>
       <td>
    <strong><span class="style2"><?php echo getSchoolStr("Host School / Organization", $corow["iscorp"]); ?>:</strong><br>
       <?php echo $corow["companyname"]; ?>  <?php echo $corow["schoolcode"]; ?></span></td>
<?php if ($crow["getsecards"]) {  ?>
<td rowspan='2' style="color: #097bb9; font-size:20px">

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>ECARDS</b>
</td>
<?php } ?>
<?php if ($crow["getsebooks"]) {  ?>
<td rowspan='2' style="color: #097bb9; font-size:20px">

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>EBOOKS</b>
</td>
<?php } ?>
      </tr>
      <tr>
       <td style="padding-top: 7px;">
       <strong><span class="style2">Site Contact:</strong><br>
       <?php echo $corow["contactname"]; ?></span></td>
      </tr>
      </tbody>
     </table>
        </td>
              </tr>
              </tbody>
            </table>
                
 <span class="copy">
 <hr>
    </span>

  <table border="0" cellspacing="0" cellpadding="5">
            <tbody>
            <tr style="height: 14px;">
                  <td valign="middle"><strong>Program Type:</strong> </td>
     <td valign="middle"><?php echo $mynames[$crow["code"]]; ?></td>
<?php if ($crow["iscorp"]) { ?>
</tr>
   <tr style="height: 14px;">
<?php } ?>
                  <td valign="middle">
                         <strong>Session Date &amp; Time:</strong></td>
     <td valign="middle"><?php echo fixdatefordisplay($crow["startdate"], true); ?> <?php echo date("h:i A", strtotime($crow["startdate"])); ?> to <?php 
        if ($crow["enddate"]) {
            echo $crow["enddate"];
        } else {
            echo date("h:i A", getEndTime($crow["startdate"], $crow["code"]));
        }
     ?></td>
              </tr>
    <tr style="height: 14px;">
 <td valign="middle"><strong>Instructor  Name:</strong></td>
     <td valign="middle"><?php
$trainers = getTrainers($id);
$any = false;
foreach ($trainers as $trainerid => $trow) {
    echo ($any ? ", " : "");
    echo getFullname($trainerid) . " (" . $trow["ahaid"] . ")";
    $any = true;
}
if (!$any) {
    echo "_____________________";
}
?></td>
<?php if ($crow["iscorp"]) { ?>
   </tr>
   <tr style="height: 14px;">
<?php } ?>
     <td valign="middle">

           <strong>Total Number of Students:</strong></td>
    <td valign="middle"><?php 
        if ($names) {
            echo count($attendees);
        } else {
            echo "___________";
        }
    ?></td>
<td valign='middle'><strong>Class #:</strong></td>
    <td valign="middle">    <?php echo $id; ?></td>
   </tr>
              </tbody>
          </table>
<br>
          <table cellpadding="3" cellspacing="0" border="1" style="width:100%;">
            <tbody>
          <tr style="background-color: #ededed; height: 28px;">
              <td valign="middle" class="style3" style="width: 5%;"><strong>Office Use Only></td>
              <td valign="middle" class="style3" style="width: 40%;"><strong><?php 
                if ($corow["iscorp"]) {
                    echo "Name / Employee ID#";
                } else {
                    echo "Name /Title / Payroll Reference #";
                }
              ?></strong></td>
              <td valign="middle" class="style3" style="width: 45%;"><strong><?php echo getSchoolStr("School", $corow["iscorp"]); ?> Name &amp; Address</strong></td>
              <td valign="middle" class="style3" style="width: 15%;"><strong>Pass / Fail</strong></td>
            </tr>
<?php
$j = 1;
if ($names) {
    $max = $crow["maxattendees"];
} else {
    $max = $crow["maxattendees"] + 2;
}
if ($max < 12) {
    $max = 12;
}
for ($i = 0; $i < $max; $i++) {
    $att = isset($attendees[$i]["responderid"]) ? $attendees[$i]["responderid"] : "";
    $arow = "";
    $mycomrow = "";
    if ($att) {
        $arow = getResponderRow($att);
    }
?>
            <tr style="height: 28px;">
                   <td valign="middle" class="copy">&nbsp;</td>
                 <td valign="middle" class="copy"><?php
                 if (isset($arow["responderid"]) && $arow["responderid"]) {
                     echo "$arow[firstname] $arow[lastname]";
                 }
     if (isset($arow["title"]) && $arow["title"] && $corow["iscorp"]) {
         echo ", $arow[title]";
     }
     if (isset($arow["pmsid"]) && $arow["pmsid"]) {
         echo ", #$arow[pmsid]";
     } else {
         if (isset($arow["filenumber"]) && $arow["filenumber"]) {
             echo ", #$arow[filenumber]";
         }
     }
     if (isset($arow["clientid"]) && $arow["clientid"]) {
         $mycomrow = getCompanyRow($arow["clientid"]);
     }
     
     ?>&nbsp;</td>
             <td valign="middle" class="copy"><?php 
                if (isset($mycomrow["id"]) && $mycomrow["id"]) {
                    echo "$mycomrow[companyname]<br>$mycomrow[address], $mycomrow[city], $mycomrow[state] $mycomrow[zip]";
                } else {
                    echo "&nbsp;<Br>&nbsp;";
                }
             ?></td>
                   <td valign="middle" class="copy">&nbsp;</td>
            </tr>
<?php } ?>
          </tbody>
      </table>
      
 <br><br>
      
          <table cellpadding="0" cellspacing="10" border="0" width="100%">
            <tbody>
            <tr>
              <td class="copy" style="border-top: 1px solid #666666; width:45%;">Site Supervisor Signature</td>
              <td class="copy" style="border-top: 1px solid #666666; width:40%;">Print Name and Title</td>
              <td class="copy" style="border-top: 1px solid #666666; width:15%;">Date</td>
            </tr>
            <tr><td colspan="3"><br></td></tr>
            <tr>
              <td class="copy" style="border-top: 1px solid #666666;">Instructor's  Signature</td>
              <td class="copy" style="border-top: 1px solid #666666;">Print  Name</td>
              <td class="copy" style="border-top: 1px solid #666666;">Date</td>
            </tr>
   <tr >
    <td valign="middle"><b>Class Requested Date:</b> <?php echo fixdatefordisplay($crow["requestdate"], true); ?></td>
    <td valign="middle"><b>Confirmation Date:</b> <?php echo fixdatefordisplay($crow["confirmdate"], true); ?></td>
   </tr>  
            </tbody>
      </table>
    </td>
  </tr>
  </tbody>
</table>

<p class='breakhere'></p>

</body></html>