<?php 
include "mysql.php"; 
$ids = array();
$mainrow = getCompanyRow( $id );
$ids[] = $id;
if( isset($mainrow["campusid"]) && $mainrow["campusid"] )
{
    $others = isset($mainrow["id"]) ? getSchoolsInCampus( $mainrow["campusid"], $mainrow["id"] ) : array();
    if( isset($others) && is_array($others) ) {
        foreach( $others as $o )
        {
            if( isset($o["id"]) ) {
                $ids[] = $o["id"];
            }
        }
    }
}

if( isset($update) && $update )
{
    $session_id_val = isset($session_id) ? $session_id : '';
    $session_userid_val = isset($session_userid) ? $session_userid : '';
    $body = "Request by ".getUserName( $session_id_val )." ($session_userid_val)\n\n";
    
    foreach( $ids as $i )
    {
        $crow = getCompanyRow( $i );
        $body .= "\n\nFor ".getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' ).":  $crow[companyname] Code: $crow[schoolcode]\n";
        
        if( isset($address[$i]) && $address[$i] )
        {
            $body .= "New Address: $address[$i]\n";
        }
        if( isset($phone[$i]) && $phone[$i] )
        {
            $body .= "New Phone: $phone[$i]\n";
        }
        if( isset($pname[$i]) && $pname[$i] )
        {
            $body .= "New Principal Name: $pname[$i]\n";
        }
        if( isset($pemail[$i]) && $pemail[$i] )
        {
            $body .= "New Principal Email: $pemail[$i]\n";
        }
    }

    $body .= "\nAEDs\n---------------\n";
    $aed_rows = db_query_rows("select * from aed_esi a where a.clientid in ( " . join( ",", $ids )." ) and a.deleted=0 order by serial");
    if( isset($aed_rows) && is_array($aed_rows) ) {
        foreach( $aed_rows as $a )
        {
            if( isset($a["aedid"]) && isset($aedloc[$a["aedid"]]) && $aedloc[$a["aedid"]] )
            {
                $body .= "For AED:  $a[serial]\n";
                $body .= "New Location: ".$aedloc[$a["aedid"]]."\n\n";
            }
        }
    }
    
    $body .= "\nResponders\n---------------\n";
    $responder_rows = db_query_rows("select responderid, firstname, lastname from responders_esi where clientid in ( ".join( ",", $ids )." ) and deleted=0 order by lastname");
    if( isset($responder_rows) && is_array($responder_rows) ) {
        foreach( $responder_rows as $r )
        {
            if( isset($r["responderid"]) && isset($toremove[$r["responderid"]]) && $toremove[$r["responderid"]] )
            {
                $body .= ( "Request to remove: ". (isset($r["firstname"]) ? $r["firstname"] : '') . " " . (isset($r["lastname"]) ? $r["lastname"] : '') . "\n" );
            }
        }
    }
    
    $body .= "\n";
    if( isset($newrname) && is_array($newrname) ) {
        foreach( $newrname as $nid=>$nname )
        {
            if( $nname )
            {
                $body .= "Request To Add: \n";
                $body .= "Name: $nname\n";
                $body .= "Location: " . (isset($newrloc[$nid]) ? $newrloc[$nid] : '') . "\n";
                $body .= "Contact Number: " . (isset($newnum1[$nid]) ? $newnum1[$nid] : '') . "-" . (isset($newnum2[$nid]) ? $newnum2[$nid] : '') . "-" . (isset($newnum3[$nid]) ? $newnum3[$nid] : '') . " " . (isset($newrext[$nid]) ? $newrext[$nid] : '') . "\n";
                $body .= "Email: " . (isset($newremail[$nid]) ? $newremail[$nid] : '') . "\n";
                $body .= "Class Type: " . (isset($newrtype[$nid]) ? $newrtype[$nid] : '') . "\n";
                $body .= "Exp Date: " . (isset($newrdate[$nid]) ? $newrdate[$nid] : '') . "\n";
                $body .= "\n";
            }
        }
    }

    // echo( "<pre>$body</pre>" );
    // exit;
    sendMail( "safetyplan@emergencyskills.com", getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' ) . " Plan Update", $body, "info@emergencyskills.com" );
    Header( "Location: schoolplanthanks.php" );
    exit;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<form method='post'>
<html>
<head>
<title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<link rel="stylesheet" href="https://<?php echo SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">
<style type="text/css">

body,td,th {
font-family: Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;
}
.style3 {font-size: 10px}

</style>
</head>
<body>

<table cellpadding="0" cellspacing="0" border="0" width="460">
        <tr>
        <td valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
    <td><strong><span class="title">EDIT SITE RESPONSE PLAN</span></strong><Br><br><i>This form is for school changes only. For individual user changes please email: <a href="mailto:sarahg@emergencyskills.com">sarahg@emergencyskills.com</a></i>.
</td>
<!--start center content-->
          </tr>
          <tr>
            <td><p>&nbsp;</p>
              <p><strong>EDIT <?php echo strtoupper( getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' ) )?> INFORMATION
              </strong></p>

<?php 
if( isset($ids) && is_array($ids) ) {
    foreach( $ids as $tmpid ) { 
        $row = getCompanyRow( $tmpid ); 
?>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
            <tr bgcolor="#e1e1f6">
            <td valign="top" width="120"><span class="small"><strong><?php echo getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' )?> Name:</strong></span></td>
<td valign="top" bgcolor='#ffffff' colspan='2' width="330"><span class="small"><?php echo isset($row["companyname"]) ? $row["companyname"] : ''?></span></td>
            </tr>
<tr bgcolor="#e1e1f6">
            <td valign="top"><span class="small"><strong><?php echo getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' )?> Number:</strong></span></td>
<td bgcolor='#ffffff' colspan='2' valign="top"><span class="small"><?php echo isset($row["schoolcode"]) ? $row["schoolcode"] : ''?></span></td>

            </tr>
<tr bgcolor="#e1e1f6">
            <td valign="top"><span class="small"><strong>Address:</strong></span></td>
<td bgcolor='#ffffff' valign="top"><span class="small"><?php echo isset($row["address"]) ? $row["address"] : ''?><br>
<?php echo isset($row["city"]) ? $row["city"] : ''?> <?php echo isset($row["state"]) ? $row["state"] : ''?>, <?php echo isset($row["zip"]) ? $row["zip"] : ''?></span></td>
<td bgcolor='#ffffff' valign="top"><span class="small"><textarea class='copy' name='address[<?php echo $tmpid?>]'></textarea></span></td>
            </tr>
<tr bgcolor="#e1e1f6">
            <td valign="top"><span class="small"><strong>Phone Number:</strong></span></td>
<td bgcolor='#ffffff' valign="top"><span class="small"><?php echo isset($row["schoolphone"]) ? $row["schoolphone"] : ''?></span></td>
<td bgcolor='#ffffff' valign="top"><span class="small"><input type='text' class='copy' name='phone[<?php echo $tmpid?>]' value=''></span></td>
            </tr>
<?php if( isset($mainrow["iscorp"]) && !$mainrow["iscorp"] ) { ?>
<tr bgcolor="#e1e1f6">
            <td valign="top"><span class="small"><strong>Principal Name & Email:</strong></span></td>
<td bgcolor='#ffffff' valign="top"><span class="small"><?php echo isset($row["principalname"]) ? $row["principalname"] : ''?><br><?php echo isset($row["principalemail"]) ? $row["principalemail"] : ''?></span></td>
<td bgcolor='#ffffff' valign="top"><span class="small">Principal Name:<br><input type='text' size='30' class='copy' name='pname[<?php echo $tmpid?>]' value=""><br>Principal Email:<Br><input type='text' class='copy' size='30' name='pemail[<?php echo $tmpid?>]' value=''></span></td>
            </tr>
<?php } ?>
            </table>
<?php 
    } 
}
?>
<p>

<strong>EDIT AED PLACEMENT AND QUANTITY</strong><br>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
            <tr bgcolor="#e1e1f6">
            <td valign="top"><span class="small"><strong>Serial Number</strong></span></td>
<td valign="top"><span class="small"><strong>Specific Location</strong></span></td>
                  <td valign="top"><span class="small"><strong>EDIT Specific Location</strong></span></td>
            </tr>
<?php 
$aed_rows = db_query_rows("select * from aed_esi a where a.clientid in ( " . join( ",", $ids )." ) and a.deleted=0 order by serial");
if( isset($aed_rows) && is_array($aed_rows) ) {
    foreach( $aed_rows as $a )
    {
?>
<tr bgcolor="#ffffff">
            <td valign="top"><span class="small"><?php echo isset($a["serial"]) ? $a["serial"] : ''?></span></td>
<td valign="top"><span class="small"><?php echo isset($a["location"]) ? $a["location"] : ''?></span></td>
                  <td valign="top"><span class="small">
                    <input name="aedloc[<?php echo isset($a["aedid"]) ? $a["aedid"] : ''?>]" type="text" size="25" maxlength="30" style="font-family: verdana; font-size: 11px; line-height: 13px" value="">
                  </span></td>
            </tr>
<?php 
    }
}
?>
</table>

<p>

<strong>REMOVE TRAINED RESPONDERS</strong><br>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999" width="470">
            <tr bgcolor="#e1e1f6">
            <td valign="bottom" width="300"><span class="small"><strong>Name</strong></span></td>
<td valign="bottom" align="center" width="85"><span class="small"><strong>Cert Type</strong><br>(CPR/AED (6 hours) or Coaches CPR update (2 hours))</span></td>
<td valign="bottom" align="center" width="85"><span class="small"><strong>Training<br>Expiration Date</strong></span></td>
                    <td align="center" valign="bottom"><span class="small"><strong>Remove Trained Responder </strong></span></td>
            </tr>
<?php 
$responder_rows = db_query_rows("select clientid, responderid, firstname, lastname from responders_esi where clientid in ( ".join( ",", $ids )." ) and deleted=0 order by lastname");

if( isset($responder_rows) && is_array($responder_rows) ) {
    foreach( $responder_rows as $r )
    {
        $mostcurrent = isset($r["responderid"]) ? db_query_first( "Select responder_training_dates.*, class.code from responder_training_dates left join class on classid = class.id where responderid = $r[responderid] order by trainingdate desc" ) : array();
        $twoyears = 24*60*365*2*60;
        $thedt = isset($mostcurrent["trainingdate"]) ? strtotime( $mostcurrent["trainingdate"]) + $twoyears : 0;
        if( $thedt < time() )
            continue;

        $mostcurrentdt2 = isset($mostcurrent["trainingdate"]) ? date( "m/d/y", $thedt ) : "N/A";
        if( isset($mostcurrent["code"]) && $mostcurrent["code"] )
        {
            // Added isset check for $class_names array
            $classtype = isset($class_names[$mostcurrent["code"]]) ? $class_names[$mostcurrent["code"]] : $mostcurrent["code"];
        }
        else if( isset($mostcurrent["program"]) && $mostcurrent["program"] )
        {
            // Added isset check for $class_names array
            $classtype = isset($class_names[$mostcurrent["program"]]) ? $class_names[$mostcurrent["program"]] : (isset($mostcurrent["program"]) ? $mostcurrent["program"] : '');
        }
        else
        {
            $classtype = "N/A";
        }
?>

<tr bgcolor="#ffffff">
            <td valign="top"><span class="small">
<strong><?php echo isset($r["lastname"]) ? $r["lastname"] : ''?>, <?php echo isset($r["firstname"]) ? $r["firstname"] : ''?></strong><br>
<strong><font color="#525151"><?php echo getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' )?> Name:</font></strong> <?php echo isset($r["clientid"]) ? getCompanyName( $r["clientid"] ) : ''?><br>
<strong><font color="#525151">Location in <?php echo getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' )?>:</font></strong> <?php echo isset($r["floor"]) ? $r["floor"] : ''?> Floor<br>
<strong><font color="#525151">Contact Number:</font></strong> <?php echo isset($r["dayphone"]) ? $r["dayphone"] : ''?></span></td>
<td valign="top" align="center"><span class="small"><?php echo $classtype?></span></td>
<td valign="top" align="center"><span class="small"><?php echo $mostcurrentdt2?></span></td> 
                   <td align="center" valign="top"><input name="toremove[<?php echo isset($r["responderid"]) ? $r["responderid"] : ''?>]" type="checkbox" value="1" ></td>
            </tr>
<?php 
    } 
}
?>
            </table>
<p>



</td>
        </tr>
        </table>
        <p><strong>ADD AED TRAINED RESPONDERS</strong><br>
          <em>NOTE: Please only add currently certified AED  responders. </em></p>
        <table cellpadding="4" cellspacing="1" border="0" width="" bgcolor="#999999">
          <tr bgcolor="#e1e1f6">
            <td valign="bottom"><span class="small"><strong>Name</strong></span></td>
            <td width="170" align="center" valign="bottom"><span class="small"><strong>Cert Type</strong><br>
            (CPR/AED (6 hours) or Coaches CPR update (2 hours))</span></td>
            <td align="center" valign="bottom"><span class="small"><strong>Training<br>
            Expiration Date</strong></span></td>
          </tr>

<?php for( $i = 0; $i < 5; $i ++ ) { ?>
          <tr bgcolor="#ffffff">
            <td valign="top">

<table border="0" cellspacing="1" cellpadding="0">
              <tr>
                <td><span class="style3">Name:</span></td>
              </tr>
              <tr>
                <td><input name="newrname[<?php echo $i?>]" type="text" size="30" maxlength="30" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
              </tr>
              <tr>
                <td><span class="style3">Location in <?php echo getSchoolStr( "School", isset($mainrow["iscorp"]) ? $mainrow["iscorp"] : '' )?>:</span></td>
              </tr>
              <tr>
                <td><input name="newrloc[<?php echo $i?>]" type="text" size="30" maxlength="30" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
              </tr>
              <tr>
                <td><span class="style3">Contact Number:</span></td>
              </tr>
              <tr>
                <td>
<input name="newnum1[<?php echo $i?>]" type="text" id="Input3433" size="3" maxlength="3" style="font-family: verdana; font-size: 11px; line-height: 13px">
              - 
              <input name="newnum2[<?php echo $i?>]" type="text" size="3" maxlength="3" style="font-family: verdana; font-size: 11px; line-height: 13px">
            - 
            <input name="newnum3[<?php echo $i?>]" type="text" size="5" maxlength="4" style="font-family: verdana; font-size: 11px; line-height: 13px">
            <span class="style3">ex</span>
            <input name="newrext[<?php echo $i?>]" type="text" size="3" maxlength="4" style="font-family: verdana; font-size: 11px; line-height: 13px">
</td>
              </tr>
              <tr>
                <td><span class="style3">Email Address:</span></td>
              </tr>
              <tr>
                <td><input name="newremail[<?php echo $i?>]" type="text" size="30" maxlength="30" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
              </tr>
            </table>       
        </td>
            <td width="170" align="center" valign="top"><select name="newrtype[<?php echo $i?>]" style="font-size: 10px;  font-family: verdana;">
              <option value= "0" selected>--make selection--</option>
              <option value="CPR/AED 6h">CPR/AED 6h</option>
              <option value="Coaches CPR update 2h">Coaches CPR update 2h</option>
            </select></td>
            <td align="center" valign="top"><table border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td><input name="newrdate[<?php echo $i?>]" value="mm/dd/yyyy" type="text" size="13" maxlength="10" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                  <td><img src="images/1x1.gif" width="3" height="1"><img src="images/calendar.gif" width="20" height="21"></td>
                </tr>
                        </table></td>
          </tr>
<?php } ?>
        </table></td>
                        </table></td>
          </tr>
          </tr>
          <tr>
            <td><div align="left">
              <p>&nbsp;</p>
              <p><input type='submit' name='update' value='Update'>
            </div></td>
          </tr>
        </table>

<script language='javascript'>

</script>
</body>
</html>