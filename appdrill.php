<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require_once "mysql.php";

$appuploadrow = getAppUploadRow($id);
// Quoted array key: 'uploader'
$uploader = db_query_first( "select * from user where userid = '" . $appuploadrow['uploader'] . "'" );
$values = getAppUploadValues( $id );
// Quoted array key: 'id'
$companyid = $values["id"];
$crow = getCompanyRow( $companyid );
$steprows = getAppDrillRows( $id );
$drillid = $values["drillid"];

if( $savedata )
{
    db_query( "update drill set appid = $id where drillid = $drillid" );
    if( $acceptschool )
    {
        $uplvalues = array();
        $uplvalues["address"] =             $values["address"];
        $uplvalues["city"] =             $values["city"];
        $uplvalues["state"] =             $values["state"];
        $uplvalues["zip"] =             $values["zip"];
        $uplvalues["schoolphone"] =             $values["phone"];
        $uplvalues["principalname"] =             $values["principal"];
        $uplvalues["principalemail"] =             $values["principalemail"];
        $uplvalues["contactname"] =             $values["contact"];
        $uplvalues["contactemail"] =             $values["contactemail"];
        $uplvalues["contactphone"] =             $values["contactphone"];

        foreach( $uplvalues as $colname=>$value )
        {
            
            db_query( "update company_esi set $colname = '" . mysqli_real_escape_string($link, $value ) . "' where id = $companyid" );
        }

        $crow = getCompanyRow( $companyid );

    }


    if( $acceptdrill )
    {
        $uplvalues = array();
        $uplvalues["score"] = $values["totalpoints"];
        // Using isset() check on $faileddrill for modern PHP practice
        $values["isdrillfailed"] = (isset($faileddrill) && $faileddrill) ? "yes" : "no";
        if( "yes" == $values["isdrillfailed"] )
        {
            $uplvalues["notrained"] = 1;
            // Assuming $faileddrilltext is defined when $faileddrill is set
            $uplvalues["other"] = $faileddrilltext;
            // Quoted array key: 'faileddrill'
            db_query( "update appuploaddata set value = '" . mysqli_real_escape_string($link,$faileddrilltext) . "' where uploadid = $id and name = 'faileddrill'" );
            $values['faileddrill'] = $faileddrilltext;
        }
        else
        {
            db_query( "update appuploaddata set value = 'no' where uploadid = $id and name = 'isdrillfailed'" );
        }
        $resp = "";
        for( $i = 1; $i <=6 ; $i++ )
        {
            if( isset($values["respondername$i"]) && $values["respondername$i"] )
            {
                $resp .= $values["respondername$i"] . " - " . (isset($values["responderschool$i"]) ? $values["responderschool$i"] : '') . "\n";
            }
        }
        $uplvalues["participants"] = $resp;
        $uplvalues["isdone"] = 1;
        $uplvalues["mainentrancesignposted"] = $values["mainentrancesignposted"]=="yes"?1:0;
        $uplvalues["drilldate"] = date( "Y-m-d", strtotime( $appuploadrow["dateinupload"] ));
        $uplvalues["nextdate"] = date( "Y-m-d", strtotime( $appuploadrow["dateinupload"] . " + 6 months" ));
        $uplvalues["drilltime"] = date( "H:i:s", strtotime( $appuploadrow["dateinupload"] ));
        $uplvalues["inspector"] = $uploader["first_name"] . " " . $uploader["last_name"];

        $codeblueother = "";
        // Quoted array key: 'comments'
        $tmpcode = strtoupper( $steprows[1]["comments"] );
        if( strpos($tmpcode, "PA") !== false )
        {
            $codeblue = "PA";
        }
        else if( strpos($tmpcode, "CELL") !== false )
        {
            $codeblue = "Cell Phone";
        }
        else if( strpos($tmpcode, "RADIO") !== false )
        {
            $codeblue = "Radio";
        }
        else
        {
            $codeblue = "Other";
            // Quoted array key: 'comments'
            $codeblueother = $steprows[1]["comments"];
        }
        $uplvalues["codeblueother"] = $codeblueother;
        $uplvalues["codeblue"] = $codeblue;


        if( $values["request_doe_send_spare_battery"]=="yes" )
        {
            // Escape for insertion (assuming mysqli_real_escape_string is available)
            $escaped_companyid = mysqli_real_escape_string($link,$companyid);
            $escaped_dateinupload = mysqli_real_escape_string($link,$appuploadrow["dateinupload"]);
            $escaped_id = mysqli_real_escape_string($link,$id);
            $escaped_drillid = mysqli_real_escape_string($link,$drillid);
            $escaped_session_id = mysqli_real_escape_string($link,$session_id);

            $accessoryrequestid = db_query_insert_id( "insert into accessoryrequests ( companyid, requestdate, description, servicecallid, drillid, trackingno, itemtype, esifieldrep, completed, aedserial, dateadded, addedby ) values ('$escaped_companyid','$escaped_dateinupload','new spare batteryt needed','$escaped_id','$escaped_drillid','','','','0','', now(), '$escaped_session_id' ) " );

        }
        foreach( $uplvalues as $colname=>$value )
        {
            db_query( "update drill set $colname = '" . mysqli_real_escape_string($link, $value ) . "' where drillid = $drillid" );
        }

        db_query( "delete from drill_to_companyid where drillid = '$drillid'" );
        // Quoted array key: 'id'
        db_query( "insert into drill_to_companyid ( drillid, companyid, showed ) values ( '$drillid', '{$crow['id']}', 1 )" );
        
        $spl = $otherschools; // Assuming $otherschools is an array of IDs or similar structure
        
        // Quoted array key: 'campusid'
        $scho = getSchoolsInCampus( $crow["campusid"], $crow['id'] );
        // Quoted array key: 'id'
        $scho[] = getCompanyRow( $crow['id'] );
        foreach( $scho as $s )
        {
            // Quoted array keys: 'id' and 'campusid'
            $showed = ($s['id'] == $crow['id'] || (isset($spl[$s['id']]) && $spl[$s['id']]) )?"1":"-1";
            db_query( "insert into drill_to_companyid ( drillid, companyid, showed ) values ( '$drillid', '{$s['id']}', $showed )" );
        }

    }

    if( isset($uplvalues["notrained"]) && $uplvalues["notrained"] )
    {
        $drill_row = db_query_first( "select * from drill where drillid = '$drillid'" );
        // Quoted array keys: 'companyname', 'principalname', 'principalemail', 'schoolphone', 'schoolcode'
        $subject = "{$crow['companyname']} ALIVE!net ALERT: Failed Code Blue Drill";
        $initial = "The following school has failed a code blue drill.  We recommend immediate training for this location.";
        $initial .= "Failure Comments: ".$uplvalues["other"] . "\n";

        $body = "$initial
{$uplvalues['drilldate']}
{$crow['companyname']}
{$crow['schoolcode']}
{$crow['address']} {$crow['city']}, {$crow['zip']}
ESI Drill/Inspector: {$drill_row['inspector']}
Principal Name: {$crow['principalname']}
Principal Email: {$crow['principalemail']}
School Phone: {$crow['schoolphone']}
Emergency Skills, Inc.";

        // Headers formatted correctly for mail()
        $headers = "From: rebekah@emergencyskills.com\nCc: rebekah@emergencyskills.com";
        
        // Note: mail() is often unreliable. Using a proper mailer library (like PHPMailer) is recommended.
        mail( "hthomps@schools.nyc.gov, cmcgee3@schools.nyc.gov", $subject, $body, $headers);
    }


    $err = "<font color='red'>Data saved. <a href='editdrill.php?drillid=$drillid'>Click here</a> to view.</font><br><br>";

}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?=date( "Y-m-d", strtotime( $appuploadrow["dateinupload"] ))?>-D<?=$drillid?> (<?=$uploader["first_name"] . " " . $uploader["last_name"]?>)</title>
 
<style type="text/css">
td {
font-family: arial; font-size: 11px; color: #000000; height: 23px;}
td.rowA1 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
td.rowA2 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
td.rowB1 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
td.rowB2 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
td.rowAB1 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
td.rowAB2 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; padding: 3px;}
.fontBig {font-size: 12px; font-weight: bold;}
.fontMed {font-size: 1 8px; font-weight: bold;}
/* 

P.breakhere {page-break-before: always}
body {
margin-top: 0px;
margin-bottom: 0px;
}
.style1 {
color: #5a179e;
font-weight: bold;
font-size: 12;
}
.style3 {font-size: 10px} */

</style> 

</head>

<body>
<form method='post' onSubmit='return okToSubmit()'>
<script language='javascript'>
var oktosubmit  = true;
function okToSubmit()
{
if( oktosubmit )
{
oktosubmit = false;
return true;
}

return false;
}
</script>
<?php
// Quoted array keys: 'dateinupload', 'schoolid'
if( !isOverallAdmin() && !$printable )
{
?>
<script language=javascript>
alert( "redirecting" );
document.location.href = 'billingworksheet.php?d=<?=date( "Y-m-d", strtotime( $appuploadrow['dateinupload'] ))?>&schoolid=<?=$appuploadrow["schoolid"]?>&printable=true';
</script>
<?php
exit;
}
?>
<?=$err?>
<?php if( $savedata ) { ?>we got here?
<?php

} ?>
<table cellpadding="0" cellspacing="0" border="0" width="650">
<?php if( !$nosave ) { ?>
<tr><td><input type='button' name='whatever' value='View Printable Version' onClick='document.location.href="billingworksheet.php?d=<?=date( "Y-m-d", strtotime( $appuploadrow['dateinupload'] ))?>&schoolid=<?=$appuploadrow["schoolid"]?>&printable=true"; '>
</td></tr>
         <?php } ?>
<tr>
<td valign="top" style="padding-bottom: 5px;">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="middle" width="70" style="padding-right: 10px;"><img src="images/EmergencySkillsLogo-Purple.png" width='100'></td>
<td valign="middle" style="color:#333333; font-size:12px;"><b>Emergency Skills, Inc. <?php if( !$crow['iscorp'] ) { ?>/ NYC Department of Education<?php } ?>
<br />AED Semi-Annual Drill</b></td>
<td valign="middle" align="left" style="color:#333333; font-size:12px;"><b>Drill/Inspection</b><br />#&nbsp;D<?=$drillid?>&nbsp;&nbsp;&nbsp;
<?php if( !$nosave && isOverallAdmin() ) { ?><a href='editdrill.php?drillid=<?=$drillid?>' target=_blank>View</a><?php } ?>
</td>
<td valign="middle" align="center" style="color:#333333; font-size:12px;"><b>Date</b><br /><?=date( "m/d/Y", strtotime( $appuploadrow["dateinupload"] ))?></td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign="top">
<table cellpadding="5" cellspacing="0" border="0" style="width: 100%; background-color: #eff5f9; border: 1px #83afcc solid;">
<tr>
<td valign="top" colspan="3"><span class="fontBig"><?=$crow['iscorp']?$crow['displayname']:$crow['companyname']?></span></td>
</tr>
<tr>
<td valign="top" style="width: 33%">
<b>ADDRESS &amp; PHONE</b><br />
<?=$values["address"]?> <?=getDifferent( "address" )?><br />
<?=$values["city"]?><?=getDifferent( "city" )?> <?=$values["state"]?><?=getDifferent( "state" )?>, <?=$values["zip"]?><?=getDifferent( "zip" )?>
<?=$values["phone"]?><?=getDifferent( "phone", "schoolphone" )?>
<?php if( !$crow['iscorp'] ){ ?><br /><br /> <b>SCHOOL CODE:</b> <?=$crow['schoolcode']?><?php } ?>
<?php if( !$nosave && isOverallAdmin() ) { ?>
<br><input type='checkbox' name='acceptschool' value=1> Accept School Data?
<?php } ?>
</td>
<?php if( !$crow["iscorp"] ) { ?>

<td valign="top" style="width: 33%">
<b>PRINCIPAL</b><br />
<?=$values["principal"]?><?=getDifferent( "principal", "principalname" )?><br/>
<?=$values["principalemail"]?><?=getDifferent( "principalemail" )?>
</td>
<?php } ?>
<td valign="top" style="width: 33%">
<b>AED CONTACT</b><br />
<?=$values["contact"]?><?=getDifferent( "contact", "contactname" )?><br/>
<?=$values["contactemail"]?><?=getDifferent( "contactemail" )?><br>
<?=$values["contactphone"]?><?=getDifferent( "contactphone" )?>
</td>
</tr>
</table>
</td>
</tr>
</table>
<table width="650" border="0"  cellspacing="0" cellpadding="0">
<tr><td><b><?=getSchoolStr( "Schools" )?> Participating in Drill</b></td></tr>
<tr><td>
<?php

if( $companyid )
{
// Quoted array key: 'campusid'
$scho = getSchoolsInCampus( $crow["campusid"], $crow['id'] );
// Quoted array key: 'id'
$scho[] = getCompanyRow( $crow['id'] );
$scho[] = array( "companyname"=>"Other" );
}
$i = 0;
echo( "<table cellpadding=0 cellspacing=0 border=0>" );
$spl = explode( ",", $values["Other_school_participating"] );
foreach( $scho as $s )
{
if(!$i )
echo( "<tr>" );
// Quoted array key: 'id'
$checked = ($s['id'] == $crow['id'] || in_array( $s["companyname"], $spl ))?"CHECKED":"";
echo( "<td><input type='checkbox' name='otherschools[{$s['id']}]' value='1' $checked> {$s['companyname']}</td>" );
$i++;
if( $i == 3 )
{
echo( "</tr>" );
$i= 0;
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
<td valign="top" ><?=$steprows["1"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[1]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[1]["comments"]?></td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top"><span class="small">2.</span></td>
<td valign="top">Arrival of 1st responder (less than 60s) Verbalizes that the scene is safe</td>
<td valign="top"><?=$steprows["2"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[2]["points"]?></td>
<td valign="top"><?=$steprows[2]["time"]?>&nbsp;</td>
<td valign="top"><?=$steprows[2]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top"><span class="small">3.</span></td>
<td valign="top">Check for Response - Tap and Shout</td>
<td valign="top"><?=$steprows["3"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[3]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[3]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top"><span class="small">4.</span></td>
<td valign="top">Yells for help. Tells someone to phone 911 and get an AED</td>
<td valign="top"><?=$steprows["4"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[4]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[4]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">5.</td>
<td valign="top">Checks for no breathing or only gasping (Minimum 5s; maximum 10s)</td>
<td valign="top"><?=$steprows["5"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[5]["points"]?></td>
<td align="center" valign="middle" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[5]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">6.</td>
<td valign="top">Bares victim&apos;s chest and locates hand position</td>
<td valign="top"><?=$steprows["6"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[6]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[6]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">7.</td>
<td valign="top">Delivers first set of compressions (30 compressions in 18 seconds or less).
</td>
<td valign="top"><?=$steprows["7"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[7]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[7]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">8.</td>
<td valign="top">Gives 2 breaths</td>
<td valign="top"><?=$steprows["8"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[8]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[8]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">9.</td>
<td valign="top">AED arrives (less than 90s).Turns AED on</td>
<td valign="top"><?=$steprows["9"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[9]["points"]?></td>
<td bgcolor="#FFFFFF"><?=$steprows[9]["time"]?>&nbsp;</td>
<td valign="top"><?=$steprows[9]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">10.</td>
<td valign="top">Selects proper AED pads and place pads correctly</td>
<td valign="top"><?=$steprows["10"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[10]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top">Location: <?=$steprows[10]["comments"]?></td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">11.</td>
<td valign="top">Clears victim to analyze (visible and verbal check)</td>
<td valign="top"><?=$steprows["11"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[11]["points"]?></td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[11]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">12.</td>
<td valign="top">Clears victim to shock/presses shock button <br/>
(visible and verbal check) <br/>
Max time to shock from AED arrival &lt;90s</td>
<td valign="top"><?=$steprows["12"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[12]["points"]?></td>
<td bgcolor="#FFFFFF"><?=$steprows[12]["time"]?>&nbsp;</td>
<td valign="top"><?=$steprows[12]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">13.</td>
<td valign="top">Deliver second set of compressions (at least 23 out of 30 in the correct chest location</td>
<td valign="top"><?=$steprows["13"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[13]["points"]?></td>
<td align="center" valign="middle" bgcolor="#e3dfdf"><img src="images/bgPrint25.jpg" width="50" height="25" /></td>
<td valign="top"><?=$steprows[13]["comments"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">14.</td>
<td valign="top">Determine return of spontaneous breathing</td>
<td valign="top"><?=$steprows["14"]["ischecked"]?'<img src="images/checkbox.gif" width="15" height="15" />':'&nbsp;'?></td>
<td valign="top" align='center'><?=$steprows[14]["points"]?>&nbsp;</td>
<td valign="top" bgcolor="#e3dfdf"><img src="images/bgPrint.jpg" width="50" height="20" /></td>
<td valign="top"><?=$steprows[14]["comments"]?>&nbsp;</td>
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
___<?=$values["totalpoints"]?>___
/21 </td>
<td>&nbsp;</td>
<td><span class="style3">16-21: Excellent; <br />
11-15:
Satisfactory (Internal Review needed); <br />
10 or less: Needs Improvement (Return in 3-4 weeks) </span></td>
</tr>
<?php if( ("yes" == $values["isdrillfailed"]) || (isset($values["faileddrill"]) && $values["faileddrill"])) { ?>
<tr>
<td><strong><Font color='red'>FAILED DRILL - <input type='checkbox' name='faileddrill' value='1' <?=$values["isdrillfailed"]=="yes"?"CHECKED":""?>></font></strong></td>
<td>&nbsp;</td>
<td><span class="style3"><Font color='red'><input type='text' name='faileddrilltext' size='40' value="<?php if (isset($values["faileddrill"])) { echo $values["faileddrill"]; } ?>"></font></span></td>
</tr>
<?php } ?>
</table></td>
</tr>
</table></td>
</tr>
<tr>
<td><table cellpadding="2" cellspacing="1" border="0" width="600" bgcolor="#999999">
<tr bgcolor="#ffffff">
<td valign="top">Main Entrance AED sign in posted?</td>
<td valign="top">Yes: <input type='radio' name='mainentrancesignposted' value='1' <?=$values["mainentrancesignposted"]=="yes"?"CHECKED":""?>></td>
<td valign="top"><?=$values["mainentrancesignposted"]=="no"?"<font color='red'>":""?>No: <input type='radio' name='mainentrancesignposted' value='-1' <?=$values["mainentrancesignposted"]=="no"?"CHECKED":""?>></font> </td>
</tr>
</table></td>
</tr>
<tr>
<td><table cellpadding="2" cellspacing="1" border="0" width="600" bgcolor="#999999">
<tr bgcolor="#ffffff">
<td valign="top">Total  # of responders (minimum 2): _<?=$values["number_of_responders"]?>__ </td>
<td valign="top">Total  # of AEDs responding: _<?=$values["number_of_aed_responding"]?>_</td>
</tr>
</table></td>
</tr>
<tr>
<td>Who responded? Main  responders (i.e AED and CPR responders)</td>
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
<td width="200" valign="top"><?=$values["respondername1"]?>&nbsp;</td>
<td width="100" valign="top"><?=$values["responderschool1"]?>&nbsp;</td>
<td valign="top">2.</td>
<td width="200" valign="top"><?=$values["respondername2"]?>&nbsp;</td>
<td width="100" valign="top"><?=$values["responderschool2"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">3.</td>
<td width="200" valign="top"><?=$values["respondername3"]?>&nbsp;</td>
<td width="100" valign="top"><?=$values["responderschool3"]?>&nbsp;</td>
<td valign="top">4.</td>
<td width="200" valign="top"><?=$values["respondername4"]?>&nbsp;</td>
<td width="100" valign="top"><?=$values["responderschool4"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">5.</td>
<td width="200" valign="top"><?=$values["respondername5"]?>&nbsp;</td>
<td width="100" valign="top"><?=$values["responderschool5"]?>&nbsp;</td>
<td valign="top">6.</td>
<td width="200" valign="top"><?=$values["respondername6"]?>&nbsp;</td>
<td width="100" valign="top"><?=$values["responderschool6"]?>&nbsp;</td>
</tr>
</table></td></tr>
<tr><td>&nbsp;</td></tr>
<?php if( $appuploadrow["name"] ) { ?>
<tr>
<td><table cellpadding="0" cellspacing="0" border="1" width="600" bgcolor="#999999">

<tr bgcolor="#ffffff">
<td valign="top">School Rep Name:</td>
<td width="200" colspan='6' valign="top"><?=$appuploadrow["name"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">School Rep Signature:</td>
<td width="200" colspan='6' valign="top"><img src='signatures/<?=$values["media_file"]?>' style="width:450px">&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">ESI Rep Name:</td>
<td width="200" colspan='6' valign="top"><?=$appuploadrow["esi_repname"]?>&nbsp;</td>
</tr>
<tr bgcolor="#ffffff">
<td valign="top">ESI Rep Signature:</td>
<td width="200" colspan='6' valign="top"><img src='signatures/<?=$values["media_file_esr"]?>' style="width:450px">&nbsp;</td>
</tr>
<?php } ?>

</table></td>
</tr>
<?php if( !$nosave && isOverallAdmin() ) { ?>
<tr><td><br><input type='checkbox' name='acceptdrill' value=1> Accept Drill Data?
<br><input type='submit' name='savedata' value='Save Data'>
</td></tr>
<tr><td><input type='button' name='whatever' value='View Printable Version' onClick='document.location.href="billingworksheet.php?d=<?=date( "Y-m-d", strtotime( $appuploadrow['dateinupload'] ))?>&schoolid=<?=$appuploadrow["schoolid"]?>&printable=true"; '>
</td></tr>
<?php } ?>

<tr>
<td><img src="images/1x1.gif" width="1" height="4" /></td>
</tr>
<tr>
</tr>
</table>
 <?php if( !$nosave )
{

//echo("select * from servicecall where fromdrill = 1 and assocdrillid = '$drillid' " );
$assoc = db_query_first("select * from servicecall where fromdrill = 1 and assocdrillid = '$drillid' " );
$id = $assoc["appid"];
if( $id )
{
$hasdrillformalready = 1;
//  echo( "the servicecall app id is: $id" );
include "appservicecall.php";
}

}?>