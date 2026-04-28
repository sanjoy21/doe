<?php
require_once('mysql.php');
$crow = getClassRow($id);
//print_r($crow);exit;
$corow = getCompanyRow($crow["companyid"]);
$attendees = get_attendees($id);
$mynames = $allclass_names[$corow["iscorp"]];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<link rel="stylesheet" href="https://<?php echo SUB_DOE. ".".URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">
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

<?php
$attarr = array();
foreach ($attendees as $k) {
    $att = $k["responderid"];
    $arow = getResponderRow($att);
    $name = $arow["lastname"] . ", " . $arow["firstname"];
    $attarr[$name] = $k; 
}
ksort($attarr);
$chunked = array_chunk($attarr, 10);
if (!$chunked) {
    $chunked = array();
    $chunked[] = array();
}
foreach ($chunked as $attarr) {
    $max = count($attarr);
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
  <tr>
    <td><img src="/images/1x1.gif" width="10" height="1"></td>
    <td>
  <table border="0" cellspacing="0" cellpadding="0">
          <tbody>
   <tr>
        <td><table cellpadding=0 cellspacing=0 border=0><tr><td><img src="images/roster_logo.jpg" height="80" /></td><td style="color: #097bb9;">emergency skills, inc<br>
305 7th Avenue, Suite 1100<br>
new york, ny 10001<br>
<span  style='font-size:8px'>212-564-6833 (tel)<br>
212-564-6793 (fax)<br></span>
<a style="color: #097bb9;" href='http://www.emergencyskills.com/'>www.emergencyskills.com</a></td></tr></table></td>
        <td class="copy" style="padding-left: 10px;">
     <table border="0" cellspacing="0" cellpadding="0">
      <tbody>
      <tr>
       <td>
    <strong><span class="style2"><?php echo getSchoolStr("Host School / Organization", $corow["iscorp"]); ?>:</strong><br>
       <?php echo $corow["companyname"]; ?>  <?php echo $corow["schoolcode"]; ?></span></td>
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
<?php if ($crow["iscorp"]) { ?>   </tr>   
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
 <td valign="middle"><strong>Instructor Name (Print Name): &nbsp;</strong></td>
     <td valign="middle">________________________________________________</td>
     <td valign="middle">

           <strong>Total Number of Students:</strong></td>
    <td valign="middle"><?php 
        if (isset($names) && $names) {
            echo count($attendees);
        } else {
            echo "___________";
        }
    ?></td>
</tr>
    <tr style="height: 14px;">
 <td valign="middle"><strong>Instructor Signature: &nbsp;</strong></td>
     <td valign="middle">________________________________________________</td>
   </tr>

</table>
          <table cellpadding="0" cellspacing="10" border="0" width="100%">
            <tbody>
            <tr><td colspan="3"><br></td></tr>

              </tbody>
          </table>
 
 
 <br>
  
          <table cellpadding="3" cellspacing="0" border="1" style="width:100%;">
            <tbody>
          <tr style="background-color: #ededed; height: 28px;">
              <td valign="middle" class="style3" style="width: 26%;"><strong>Name</strong></td>
              <td valign="middle" class="style3" style="width: 26%;"><strong>Signature</strong></td>
              <td valign="middle" class="style3" style="width: 6%;"><strong>Borough</strong></td>
              <td valign="middle" class="style3" style="width: 6%;"><strong>Region</strong></td>
              <td valign="middle" class="style3" style="width: 12%;"><strong>ID # </strong></td>
              <td valign="middle" class="style3" style="width: 12%;"><strong>Book Received (Initial)</strong></td>
              <td valign="middle" class="style3" style="width: 12%;"><strong>P/NR</strong></td>
            </tr>
<?php
$j = 1;
$vals = array_values($attarr);

if ($max < 10) {
    $max = 10;
}
for ($i = 0; $i < $max; $i++) {
    $att = isset($vals[$i]["responderid"]) ? $vals[$i]["responderid"] : "";
    $arow = "";
    $mycomrow = "";
    if ($att) {
        $arow = getResponderRow($att);
    }
?>
            <tr style="height: 28px;">
                 <td valign="middle" class="copy"><?php 
                 if (isset($arow["responderid"]) && $arow["responderid"]) {
                     echo $arow["firstname"] . ", " . $arow["lastname"];
                 }
     if (isset($arow["clientid"]) && $arow["clientid"]) {
         $mycomrow = getCompanyRow($arow["clientid"]);
     }
     ?>&nbsp;</td>
                   <td valign="middle" class="copy">&nbsp;</td>
                   <td valign="middle" class="copy">&nbsp;</td>
                   <td valign="middle" class="copy">&nbsp;</td>
                   <td valign="middle" class="copy">&nbsp;</td>
                   <td valign="middle" class="copy">&nbsp;</td>
                   <td valign="middle" class="copy">&nbsp;</td>
            </tr>
<?php } ?>
          </tbody>
      </table>
      
 <br><br>
      
          <table cellpadding="0" cellspacing="10" border="0" width="100%">
            <tbody>
   <tr >
    <td valign="middle"><b>Class Requested Date:</b> <?php echo fixdatefordisplay($crow["requestdate"], true); ?></td>
    <td valign="middle"><b>Confirmation Date:</b> <?php echo fixdatefordisplay($crow["confirmdate"], true); ?></td>
    <td valign="middle"><b>Class #</b> <?php echo $id; ?></td>
   </tr>  
            </tbody>
      </table>
   

  
    
    </td>
  </tr>
  </tbody>
</table>
<p class='breakhere'></p>
<?php } ?>

</body></html>