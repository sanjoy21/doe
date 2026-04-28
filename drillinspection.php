<?php
require_once "mysql.php";

if ($all4) {
    // Adding this so we can do multiple at once
    $companyids = array();
    $spl = explode(",", $companyid);
    foreach ($spl as $comid) {
        $q = db_query_rows("select * from aed_esi where clientid = '$comid' and deleted = 0 and newinstall = 1");
        
        // PHP 8 count() requires a countable array/object
        $newscid_local = 0;
        if (is_array($q) && count($q)) {
            $newscid_local = db_query_insert_id("insert into servicecall ( companyid, singleaedid, newinstall ) values ( '$comid', '$aedid', 1 )");
            foreach ($q as $qrow) {
                // Fix: String interpolation with curly braces and quoted keys
                db_query("insert into aed_to_servicecall ( serial, servicecallid ) values ( '{$qrow['serial']}', '$newscid_local' )");
            }
        }
        $newid = db_query_insert_id("insert into drill ( companyid ) values ( '$comid' )");
        db_query("insert into drill_to_companyid ( drillid, companyid, showed ) values ( '$newid', '$comid', 1 )");

        $company_row = getCompanyRow($comid);
        $scho = getSchoolsInCampus($company_row["campusid"], $company_row["id"]);
        
        if (is_array($scho)) {
            foreach ($scho as $s) {
                db_query("insert into drill_to_companyid ( drillid, companyid, showed ) values ( '$newid', '{$s['id']}', 1 )");
            }
        }

        $companyids[$comid] = array("drillid" => $newid, "newscid" => $newscid_local);
    }
} else {
    $companyids = array($companyid => array("drillid" => $drillid, "newscid" => $newscid));
}

if ($newdrill) {
    if (!$session_iscorp) {
        $q = db_query_rows("select * from aed_esi where clientid = '$companyid' and deleted = 0 and newinstall = 1");
        if (is_array($q) && count($q)) {
            $newscid = db_query_insert_id("insert into servicecall ( companyid, singleaedid, newinstall ) values ( '$companyid', '$aedid', 1 )");
            foreach ($q as $qrow) {
                db_query("insert into aed_to_servicecall ( serial, servicecallid ) values ( '{$qrow['serial']}', '$newscid' )");
            }
        }
    }
    $newid = db_query_insert_id("insert into drill ( companyid ) values ( '$companyid' )");
    db_query("insert into drill_to_companyid ( drillid, companyid, showed ) values ( '$newid', '$companyid', 1 )");

    $company_row = getCompanyRow($companyid);
    $scho = getSchoolsInCampus($company_row["campusid"], $company_row["id"]);
    
    if (is_array($scho)) {
        foreach ($scho as $s) {
            db_query("insert into drill_to_companyid ( drillid, companyid, showed ) values ( '$newid', '{$s['id']}', 1 )");
        }
    }

    Header("Location: drillinspection.php?companyid=$companyid&drillid=$newid&newscid=$newscid&aedsigntoo=$aedsigntoo");
    exit;
}

foreach ($companyids as $companyid => $tmparr) {
    $newscid = $tmparr["newscid"];
    $drillid = $tmparr["drillid"];
    
    if ($companyid) {
        $crow = getCompanyRow($companyid);
    }
    
    if (!$noheader) {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Drill Inspection - Drill D<?=$drillid?></title>
 <style type="text/css">
    td {font-family: arial; font-size: 11px; color: #000000; height: 23px;}
    td.rowA1 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
  td.rowA2 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
  td.rowB1 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
  td.rowB2 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
  td.rowAB1 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
  td.rowAB2 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; padding: 3px;}
    .fontBig {font-size: 12px; font-weight: bold;}
  /* Fixed typo from '1 8px' to '18px' */
  .fontMed {font-size: 18px; font-weight: bold;} 
  
 </style>
</head>

<body>
<?php } else { ?>
 <style type="text/css">
    td {font-family: arial; font-size: 11px; color: #000000; height: 23px;}
    td.rowA1 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 5px;}
  td.rowA2 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 5px;}
  td.rowB1 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 5px;}
  td.rowB2 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 5px;}
    .fontBig {font-size: 12px; font-weight: bold;}
  .fontMed {font-size: 18px; font-weight: bold;}
  
 </style>
<?php } ?>
<table cellpadding="0" cellspacing="0" border="0" width="650">
  <tr>
    <td valign="top" style="padding-bottom: 5px;">    
      <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
          <td valign="middle" width="70" style="padding-right: 10px;"><img src="images/servicecalllogo.jpg"></td>
          <td valign="middle" style="color:#333333; font-size:12px;"><b>Emergency Skills, Inc. <?php if (empty($crow['iscorp'])) { ?>/ NYC Department of Education<?php } ?>
<br />AED Semi-Annual Drill</b></td>
          <td valign="middle" align="right" style="color:#333333; font-size:12px;"><b>Drill/Inspection</b><br />#&nbsp;<input type="text" value="D<?=$drillid?>" style="font-family: arial; font-size: 11px; color: #333333; width:70px;"></td>
          <td valign="middle" align="center" style="color:#333333; font-size:12px;"><b>Date</b><br /><input type="text" value="" style="font-family: arial; font-size: 11px; color: #333333; width:70px;"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td valign="top">
      <table cellpadding="5" cellspacing="0" border="0" style="width: 100%; background-color: #eff5f9; border: 1px #83afcc solid;">
        <tr>
          <td valign="top" colspan="3"><span class="fontBig"><?=!empty($crow['iscorp']) ? $crow['displayname'] : $crow['companyname']?></span></td>
        </tr>
        <tr>
          <td valign="top" style="width: 33%">
            <b>ADDRESS &AMP; PHONE</b><br />
<?=$crow["address"]?><br />
          <?=$crow["city"]?> <?=$crow["state"]?>, <?=$crow["zip"]?>
            <?=!empty($crow['iscorp']) ? $crow["contactphone"] : $crow['schoolphone']?>
<?php if (empty($crow['iscorp'])) {  ?><br /><br /> <b>SCHOOL CODE:</b> <?=$crow['schoolcode']?><?php } ?>
          </td>
<?php if (empty($crow["iscorp"])) { ?>

          <td valign="top" style="width: 33%">
            <b>PRINCIPAL</b><br />
            <?=$crow["principalname"]?><br />
<?=$crow["principalemail"]?>
          </td>
<?php } ?>
          <td valign="top" style="width: 33%">
            <b>AED CONTACT</b><br />
            <?=$crow["contactname"]?><br />
            <?=$crow["contactemail"]?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table width="650" border="0"  cellspacing="0" cellpadding="0">
<tr><td><b><?=getSchoolStr("Schools")?> Participating in Drill</b></td></tr>
<tr><td>
<?php

if ($companyid) {
    // Fix: Quoted keys for 'campusid' and 'id'
    $scho = getSchoolsInCampus($crow["campusid"], $crow['id']);
    $scho[] = getCompanyRow($crow['id']);
    $scho[] = array("companyname" => "Other");
}
$i = 0;
echo("<table cellpadding=0 cellspacing=0 border=0>");
foreach ($scho as $s) {
    if (!$i)
        echo("<tr>");
    // Fix: Quoted key inside string interpolation
    echo("<td><input type='checkbox'> {$s['companyname']}</td>");
    $i++;
    if ($i == 3) {
        echo("</tr>");
        $i = 0;
    }
}
 ?>
</table></td></tr>
  <tr>
    <td><table cellpadding="0" cellspacing="0" border="1" width="600" bgcolor="#999999">
      <tr bgcolor="#eff5f9">
        <td valign="top">&nbsp;</td>
        <td valign="top"><span class="small"><strong>Steps</strong></span></td>
        <td align="center" valign="middle"><img src="images/checkbox.gif" width="15" height="15" /></td>
        <td valign="top" ><strong>Pts</strong></td>
        <td valign="top" ><strong>Time</strong></td>
        <td valign="top"><strong>Comments</strong></td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">&nbsp;</td>
        <td valign="top">Collapse (Start Stop Watch)</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint20x20.jpg" width="20" height="20" /></td>
        <td valign="top">&nbsp;</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top"><img src="images/1x1.gif" width="150" height="1" /></td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top"><span class="small">1.</span></td>
        <td valign="top">Call Code Blue (Method: PA, Cell Phone, or Radio)</td>
        <td valign="top" >&nbsp;</td>
        <td valign="top" align='center'>2</td>
        <td bgcolor="#FFFFFF">&nbsp;</td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top"><span class="small">2.</span></td>
        <td valign="top">Arrival of 1st responder (less than 60s) Verbalizes that the scene is safe</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>1</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top"><span class="small">3.</span></td>
        <td valign="top">Check for Response - Tap and Shout</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>1</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top"><span class="small">4.</span></td>
        <td valign="top">Yells for help. Tells someone to phone 911 and get an AED</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>2</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">5.</td>
        <td valign="top">Checks for no breathing or only gasping (Minimum 5s; maximum 10s)</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>1</td>
        <td align="center" valign="middle" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">6.</td>
                                     <td valign="top">Bares victim&apos;s chest and locates hand position</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>1</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">7.</td>
        <td valign="top">Delivers first set of compressions (30 compressions in 18 seconds or less).
</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>3</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">8.</td>
        <td valign="top">Gives 2 breaths</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>1</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">9.</td>
        <td valign="top">AED arrives (less than 90s).Turns AED on</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>2</td>
        <td bgcolor="#FFFFFF">&nbsp;</td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">10.</td>
        <td valign="top">Selects proper AED pads and place pads correctly</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>3</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">Location: </td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">11.</td>
        <td valign="top">Clears victim to analyze (visible and verbal check)</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>0</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">12.</td>
        <td valign="top">Clears victim to shock/presses shock button <br />
          (visible and verbal check) <br />
          Max   time to shock from AED arrival &lt;90s</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>1</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">13.</td>
        <td valign="top">Deliver second set of compressions (at least 23 out of 30 in the correct chest location</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" align='center'>3</td>
        <td align="center" valign="middle" bgcolor="#e3dfdf"><img src="images/bgPrint25.jpg" width="50" height="25" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">14.</td>
        <td valign="top">Determine return of spontaneous breathing</td>
        <td valign="top">&nbsp;</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">&nbsp;</td>
        <td valign="top">Leave pads attached (until EMS tells you otherwise)</td>
        <td valign="top">&nbsp;</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
        <td valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top" bgcolor="#ffffff">&nbsp;</td>
        <td colspan="5" valign="top" bgcolor="#ffffff">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td><strong>Score:</strong>
                <input name="Input3422" type="text" id="Input3422" size="5" maxlength="2" style="font-family: verdana; font-size: 11px; line-height: 13px" />
/21 </td>
              <td>&nbsp;</td>
              <td><span class="style3">16-21: Excellent; <br />
11-15:
      Satisfactory (Internal Review needed); <br />
10 or less: Needs Improvement (Return in 3-4 weeks) </span></td>
            </tr>
          </table></td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td><table cellpadding="2" cellspacing="1" border="0" width="600" bgcolor="#999999">
      <tr bgcolor="#ffffff">
        <td valign="top">Total  # of responders (minimum 2): _____ </td>
        <td valign="top">Total  # of AEDs responding:          _____</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table cellpadding="2" cellspacing="1" border="0" width="600" bgcolor="#999999">
      <tr bgcolor="#ffffff">
        <td valign="top">Main Entrance AED sign in posted?</td>
        <td valign="top">Yes: <input type='radio' name='mainentrancesignposted' value='1'></td>
  <td valign="top">No: <input type='radio' name='mainentrancesignposted' value='-1'> </td>
      </tr>
    </table></td>
  </tr>
  <tr>
  <td>Who responded? Main  responders (i.e AED and CPR responders)</td>
  </tr>
  <tr>
    <td><table cellpadding="0" cellspacing="0" border="1" width="600" bgcolor="#999999">
      <tr bgcolor="#eff5f9">
<td>&nbsp;</td>
        <td valign="top">&nbsp;<span class="small"><strong>Full Name</strong></span></td>
        <td width="100" valign="top"><span class="small">&nbsp;<strong>School</strong></span></td>
<td>&nbsp;</td>
        <td valign="top">&nbsp;<span class="small"><strong>Full Name</strong></span></td>
        <td width="100" valign="top"><span class="small">&nbsp;<strong>School</strong></span></td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">1.</td>
        <td width="200" valign="top">&nbsp;</td>
        <td width="100" valign="top">&nbsp;</td>
        <td valign="top">2.</td>
        <td width="200" valign="top">&nbsp;</td>
        <td width="100" valign="top">&nbsp;</td>
      </tr>
      <tr bgcolor="#ffffff">
        <td valign="top">3.</td>
        <td width="200" valign="top">&nbsp;</td>
        <td width="100" valign="top">&nbsp;</td>
        <td valign="top">4.</td>
        <td width="200" valign="top">&nbsp;</td>
        <td width="100" valign="top">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
<td><img src="images/1x1.gif" width="1" height="4" /></td>
  </tr>
  <tr>    
  </tr>
</table>
<p class='breakhere'></p>
<?php
    $noheader = 1;
    
    if ($newscid) {
        include "servicecallsheet.php";
        $newscid = "";
        echo("<p class='breakhere'></p>");
    }
    // $sc = "";
    $newscid = "";
    $servicecallid = "";
    $showallaeds = 1;
    include "servicecallsheet.php";
    $showallaeds = 0;

    if ($aedsigntoo) {
        echo("<p class='breakhere'></p>");
        $id = $companyid;
        include "printaedsign.php";
        echo("<p class='breakhere'></p>");
        // Fix: Quoted key 'iscorp'
        include "response_plan" . (!empty($crow['iscorp']) ? "_corp" : "") . ".php";
    }
    echo("<p class='breakhere'></p>");

}
?>