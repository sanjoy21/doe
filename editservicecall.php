<?php 
require_once "mysql.php"; 

if( isset($moveaeds) && $moveaeds )
{
        if( isset($newschoolid) && $newschoolid )
        {
            foreach( $serials as $smove )
            {
                $ar = db_query_first( "select aedid, clientid from aed_esi where serial = '$smove'" );
                $schoolid = $ar["clientid"] ?? '';
                $smove = $ar["aedid"] ?? '';
                db_query( "insert into oldaedschools ( aedid, clientid, movedate, movedby ) values ( '$smove', '$schoolid', Now(), '$session_userid') " );
                db_query( "update aed_esi set clientid = $newschoolid where aedid = $smove" );
            }
        }
}

if( isset($update) && $update && !isset($delete) )
{
$s["servicecalldate"] = fixdate( $s["servicecalldate"] ?? '' );
    if( isset($servicecallid) && $servicecallid )
    {
        $oldscrow = db_query_first( "select * from servicecall where servicecallid = '$servicecallid'" );
        $thecid = $oldscrow["companyid"] ?? '';
        $next = isset($s["nextservicecalldate"]) && $s["nextservicecalldate"] ? "'". date( "Y-m-d", strtotime( $s["nextservicecalldate"] ) ). "'" : "NULL";
        db_query( "update servicecall set servicecalldate = '{$s['servicecalldate']}', fromdrill = '{$s['fromdrill']}', servicecalltime = '{$s['servicecalltime']}', nextservicecalldate = $next, inspector = '{$s['inspector']}', completed = '{$s['completed']}', newinstall = '{$s['newinstall']}', qainspection = '{$s['qainspection']}', actionneeded = '{$s['actionneeded']}', invoiced = '{$s['invoiced']}', invoiceno = '{$s['invoiceno']}', callnumber = '{$s['callnumber']}', comments = '{$s['comments']}', reason = '{$s['reason']}' where servicecallid = $servicecallid " );
    }
    else
    {
        $thecid = $id ?? '';
        $servicecallid = db_query_insert_id( "insert into servicecall ( companyid, servicecalldate, servicecalltime, nextservicecalldate, comments, reason, callnumber, invoiced, invoiceno, completed, newinstall, qainspection, fromdrill, inspector ) values ('{$s['id']}','{$s['servicecalldate']}','{$s['servicecalltime']}','{$s['nextservicecalldate']}','{$s['comments']}','{$s['reason']}','{$s['callnumber']}','{$s['invoiced']}','{$s['invoiceno']}','{$s['completed']}','{$s['newinstall']}','{$s['qainspection']}','{$s['fromdrill']}','{$s['inspector']}') " );
    }

    if( isset($session_userid) && $session_userid == "sarahg@emergencyskills.com" )
    {
        db_query( "update servicecall set sherrycomments = '{$s['sherrycomments']}' where servicecallid = $servicecallid" );
    }
        
if( isOverallAdmin() && isset( $assocdrillid ) )
{
        db_query( "update servicecall set assocdrillid = '$assocdrillid' where servicecallid = $servicecallid" );
}

    db_query( "delete from aed_to_servicecall where servicecallid = '$servicecallid'" );
    if(isset($oldscrow["companyid"]) && isset($o)) {
        db_query( "insert into drill_to_companyid ( drillid, companyid ) values ( '{$oldscrow['companyid']}', '$o' )" );
    }
    
    if(isset($s["serials"]) && is_array($s["serials"])) {
        foreach( $s["serials"] as $ser )
        {
            db_query( "insert into aed_to_servicecall (servicecallid, serial) values ( $servicecallid, '$ser' ) " );
        }
    }
    
//     $os = "";
// if( count( $otherschools ) )
//     {
//         $os = "," . join( ",", $otherschools ) . ",";
//     }
// db_query( "update servicecall set otherschools = '$os' where servicecallid = $servicecallid" );
    db_query( "delete from servicecall_to_companyid where servicecallid = '$servicecallid'" );
    db_query( "insert into servicecall_to_companyid ( servicecallid, companyid ) values ( '$servicecallid', '$thecid' )" );
    
if( isset($s["otherschools"]) && is_array($s["otherschools"]) && count( $s["otherschools"] ) )
    {
        foreach( $s["otherschools"] as $o )
            {
                db_query( "insert into servicecall_to_companyid ( servicecallid, companyid ) values ( '$servicecallid', '$o' )" );
            }
//        $os = "," . join( ",", $otherschools ) . ",";
    }

if( isset($s["notifysherry"]) && $s["notifysherry"] )
    {
        sendMail( "sarahg@emergencyskills.com", "Action Needed", "Service Call $servicecallid needs action.\n https://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/editservicecall.php?servicecallid=$servicecallid\n (From: $session_userid)", "info@emergencyskills.com" );
$crow = getCompanyRow( $thecid );
if( isset($crow["iscorp"]) && $crow["iscorp"] )
    {
sendMail( "michael@emergencyskills.com", "Action Needed", "Service Call $servicecallid needs action.\n https://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/editservicecall.php?servicecallid=$servicecallid\n", "info@emergencyskills.com" );
    }
    }


    if( isset($s["notifyemily"]) && $s["notifyemily"] )
    {
        $servicecall_row = db_query_first( "select * from servicecall where servicecallid=$servicecallid" );
        $crow = getCompanyRow( $id ?? '' );

        db_query( "update servicecall set lastnotified = now() where servicecallid = $servicecallid");        
        mail( "michael@emergencyskills.com", "Accessory Request", "{$s['initial']}  \n {$servicecall_row['servicecalldate']} \n {$crow['companyname']}\n {$crow['address']} {$crow['city']}, {$crow['zip']} \n \n\nEmergency Skills, Inc.", "From:info@emergencyskills.com\nCc:info@emergencyskills.com");
    }

    if( !isset($dontredirect) && isset($redirect) )
    {
        Header( "location: $redirect " );
        exit;
    }
    if( isset($dontredirect) && $dontredirect ) $err = "<br><font color='red'>Saved.</font><br>";
}



if( isset($delete) && $delete )
{
    db_query( "delete from servicecall where servicecallid = $servicecallid " );
    if( !isset($dontredirect) )
        {
        Header( "location: $redirect " );
        exit;
        }
}


//get info for the form
if( isset($servicecallid) && $servicecallid )
{
    $servicecall_row = db_query_first( "select * from servicecall where servicecallid = $servicecallid" );
$id = $servicecall_row["companyid"] ?? '';
}
if( !isset($servicecall_row) || !$servicecall_row )
{
    $servicecall_row = array();
}
$company_row= getCompanyRow( $id ?? '' );
?>
<?php
$noleftnav = 1;
$overridecname = "newschoolid";

if( !isset($noheaderorfooter) || !$noheaderorfooter )  {

 include "ssi/top.php";
}
 include "getschooldropdown.php"; 

?>
<!--start center content-->



<script LANGUAGE="JavaScript">
<!--
function confirmDelete()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree) {
return true ;
}
else
{
return false ;
}
}
// -->
</script>

</head>


<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>
<script language="JavaScript">
<!--
function validateUSPersonalInfo(form)
{ 
return true;
}
//-->
</script>


<script language="JavaScript">


function validRequired(formField,fieldLabel)
{
var result = true;

if (formField.value == "")
{
alert('Please enter a value for the "' + fieldLabel +'" field.');
formField.focus();
result = false;
}

return result;
}

function allDigits(str)
{
return inValidCharSet(str,"0123456789");
}

function inValidCharSet(str,charset)
{
var result = true;

for (var i=0;i<str.length;i++)
if (charset.indexOf(str.substr(i,1))<0)
{
result = false;
break;
}

return result;
}

function isValidShortDate(formField,fieldLabel,required)
{
    if (required && (formField.value.length>7))
    {
        alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
formField.focus();
return false;
    }
var result = true;
var formValue = formField.value;

if (required && !validRequired(formField,fieldLabel))
result = false;
  
 if (result && (formField.value.length>0))
 {
 var elems = formValue.split("/");
 
 result = (elems.length == 2); // should be two components
 var expired = false;
 
 if (result)
 {
 var month = parseInt(elems[0],10);
 var year = parseInt(elems[1],10);
 
 if (elems[1].length == 2)
 year += 2000;
 
 var now = new Date();
 
 var nowMonth = now.getMonth() + 1;
 var nowYear = now.getFullYear();
 
 
 
result = allDigits(elems[0]) && (month > 0) && (month < 13) &&
 allDigits(elems[1]) && ((elems[1].length == 2) || (elems[1].length == 4));
 }
 
  if (!result)
 {
 alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
formField.focus();
}
} 
return result;
}
</script>

<?php
if( !isset($redirect) )
    $redirect="/viewcompany.php?id=" . ($id ?? '');
?>

<?php if( !isset($noheaderorfooter) || !$noheaderorfooter ) { ?>
<form onsubmit="return validateUSPersonalInfo(this)"  method="post">
<input type="hidden" name ="redirect" value="<?php echo $redirect; ?>">
<input type="hidden" name ="id" value="<?php echo $id ?? ''; ?>">
<input type="hidden" name ="s[id]" value="<?php echo $id ?? ''; ?>">
           <?php } ?>
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="servicecallid" value="<?php echo $servicecallid ?? ''; ?>">
    <?php if( !isset($noheaderorfooter) || !$noheaderorfooter ) { ?>
<?php if( isset($specialadmin) && $specialadmin ) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="schools.php">&laquo; Back to Admin Main</a></strong></span></td>
</tr>
</table>
<?php } ?>
<strong>THIS SERVICE CALL IS FOR:</strong><br>
<?php echo  "<a href='viewcompany.php?id=" . ($id ?? '') . "'>" . ($company_row['companyname'] ?? '') . "</a><br>" . ($company_row['address'] ?? '') . "<br>" . ($company_row['floor'] ?? '') . "<br>" . ($company_row['city'] ?? '') . ", " . ($company_row['state'] ?? '') . " " . ($company_row['zip'] ?? ''); ?>
<br><br>
        <?php if( isset($servicecall_row["appid"]) && $servicecall_row["appid"] ) { ?>
    <A href='appservicecall.php?id=<?php echo $servicecall_row["appid"]; ?>'><b>View Service Call Worksheet from App</b></a><br><br>
    <?php } ?>
    <?php
$scid = $servicecall_row["assocdrillid"] ?? '';
if( !$scid )
{
    $scid = db_query_first_cell( "select drillid from drill where companyid = '{$servicecall_row['companyid']}' and drilldate = '{$servicecall_row['servicecalldate']}' and companyid > ''" );
    if( $scid ){
        db_query( "update servicecall set assocdrillid = '$scid' where servicecallid = '$servicecallid'" );
    }
}
if( $scid ){
?>
        <A href='editdrill.php?drillid=<?php echo $scid; ?>'><b>View Related Drill</b></a><br><br>
<?php
if( isOverallAdmin() ) { 
?> 
Assoc Drill ID: <input type="text" name ="assocdrillid" value="<?php echo $scid; ?>" size=5>
<?php 
}
else
{
?> 
<input type="hidden" name ="assocdrillid" value="<?php echo $row['assocdrillid'] ?? ''; ?>">
<?php 
}
}
}
else
{ ?>
            <?php if( isset($servicecall_row["appid"]) && $servicecall_row["appid"] ) { ?>
    <A href='appservicecall.php?id=<?php echo $servicecall_row["appid"]; ?>'><b>View Service Call Worksheet from App</b></a><br><br>
            <?php } ?>
            <?php } ?>
<?php echo $err ?? ''; ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><Strong>Service Call Information</strong></span></td>
</tr>


<tr>
<td valign="top" bgcolor="#E2DFDF">
    <span class="copy"><strong>Service Call Date*:</strong> <input type="text" size="12" VALUE="<?php echo $servicecall_row['servicecalldate'] ?? ''; ?>" maxlength="50" name="s[servicecalldate]" style="font-size: 10px;  font-family: verdana;"> <strong>Time:</strong> <input type="text" size="12" VALUE="<?php echo $servicecall_row['servicecalltime'] ?? ''; ?>" maxlength="50" name="s[servicecalltime]" style="font-size: 10px;  font-family: verdana;"></span><br>
<strong>Next Service Call Date:</strong> <input type='text' name='s[nextservicecalldate]' value='<?php echo $servicecall_row['nextservicecalldate'] ?? ''; ?>' size='12' style="font-size: 10px;  font-family: verdana;"> YYYY-MM-DD</td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
    <span class="copy"><strong>From Drill?:</strong>&nbsp;&nbsp;&nbsp;<input type="checkbox" size="5" VALUE="1" <?php echo (isset($servicecall_row['fromdrill']) && $servicecall_row['fromdrill'])?"CHECKED":""; ?> name="s[fromdrill]" ></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Call Number:</strong><br><input type="text" size="5" VALUE="<?php echo isset($servicecall_row['callnumber']) && $servicecall_row['callnumber'] ? $servicecall_row['callnumber'] : ($servicecall_row['servicecallid'] ?? ''); ?>" maxlength="50" name="s[callnumber]" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>ESI Staff/Inspector:</strong><br><input type="text" size="50" VALUE="<?php echo $servicecall_row['inspector'] ?? ''; ?>" name="s[inspector]" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Reason:</strong><br><textarea name="s[reason]" row="5" cols="50" style="font-size: 10px;  font-family: verdana;"><?php echo $servicecall_row['reason'] ?? ''; ?></textarea></span></td>
</tr>
<?php
        $myaeds = getAedsForServiceCall( $servicecallid ?? '' );
//print_r( $myaeds );
$aed_rows = getAedRows( $id ?? '', false, isset($company_row["iscorp"]) && $company_row["iscorp"] ? "" : ($company_row["campusid"] ?? '') ); 
//print_r( $aed_rows );
?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>AED:</strong><br><select  style="font-size: 10px;  font-family: verdana; height: 100px" name="s[serials][]" multiple class=copy>
<option value=''>Please Choose</option>
<?php
$already = array();
if(isset($myaeds) && is_array($myaeds)) {
foreach( $myaeds as $ser => $throwaway )
{
$already[$ser] = 1;
    if( !trim( $ser ) ) continue;
printOption( $ser, $ser, $ser );
}
}
// don't auto select any of these
if(isset($aed_rows) && is_array($aed_rows)) {
foreach( $aed_rows as $a )
{
    if( isset($already[$a["serial"]]) || !trim( $a["serial"] ?? '' ) )
        continue;
printOption( $a["serial"], $a["serial"] );
}
}
if(isset($myaeds) && is_array($myaeds)) {
foreach( $myaeds as $s )
{
    $found = false;
    if(isset($aed_rows) && is_array($aed_rows)) {
    foreach( $aed_rows as $a )
    {
        if( $a["serial"] == $s )
            $found = true;
    }
    }
    if( !trim( $s ) ) continue;
    if( !$found )
        printOption( $s, $s, $myaeds[$s] ?? '');
        
}
}
?>
</select></span></td>
</tr>
<?php if( isset($servicecallid) && $servicecallid ) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Accessory Request:</strong>
<?php 
$f = db_query_first( "select * from accessoryrequests where servicecallid = $servicecallid" );
if( $f )
{
echo( "<a href='editaccessoryrequest.php?accessoryrequestid=$f[id]'>AR$f[id]</a>" );
}   
else
{
echo( "<a target=_blank href='editaccessoryrequest.php?id=" . ($servicecall_row['companyid'] ?? '') . "&sid=" . ($servicecall_row['servicecallid'] ?? '') . "'>add new</a>" );
}
?>
</span></td>
</tr>
<?php } ?>
<?php
$scho = getSchoolsInCampus( $company_row["campusid"] ?? '', $company_row["id"] ?? '' );
if( isset($scho) && count( $scho ) )
{
?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Other participating <?php echo getSchoolStr( "Schools" ); ?>:</strong><br>
<?php
 $otherschools = db_query_array( "select companyid from servicecall_to_companyid where servicecallid = '$servicecallid'", "companyid",  "companyid" );
if(isset($scho) && is_array($scho)) {
foreach( $scho as $s )
{
    if( $s["id"] != ($servicecall_row['companyid'] ?? '') )
    {
        $already = isset($otherschools[$s["id"]]) && $otherschools[$s["id"]] ? "CHECKED" : "";
        echo( "<input type='checkbox' name='s[otherschools][]' value='$s[id]' $already > <a href='viewcompany.php?id=$s[id]'>$s[companyname]</a><br>" );
    }
}
}
?>
</span></td>
</tr>
      <?php } ?>
<tr><td class=copy bgcolor="#E2DFDF">
<strong>New Install:</strong> <input type='checkbox' class='copy' name='s[newinstall]' value="1" <?php echo (isset($servicecall_row["newinstall"]) && $servicecall_row["newinstall"])?"checked":""; ?>><br>
<strong>QA Inspection:</strong> <input type='checkbox' class='copy' name='s[qainspection]' value="1" <?php echo (isset($servicecall_row["qainspection"]) && $servicecall_row["qainspection"])?"checked":""; ?>><br>
<strong>Completed:</strong> <input type='checkbox' class='copy' name='s[completed]' value="1" <?php echo (isset($servicecall_row["completed"]) && $servicecall_row["completed"])?"checked":""; ?>><br>
<strong>Action Needed:</strong> <input type='checkbox' class='copy' name='s[actionneeded]' value="1" <?php echo (isset($servicecall_row["actionneeded"]) && $servicecall_row["actionneeded"])?"checked":""; ?>> &nbsp;&nbsp;&nbsp;&nbsp;<strong>Notify Sarah/Michael:</strong> <input type='checkbox' name='s[notifysherry]' value='1'><br>
<?php if( isset($company_row["iscorp"]) && $company_row["iscorp"] ) { ?>
<strong>Notify Michael:</strong> <nobr><input type='checkbox' name='s[notifyemily]' value='1'> (Last Notified: <?php echo isset($servicecall_row["lastnotified"]) && $servicecall_row["lastnotified"] ? $servicecall_row["lastnotified"] : "N/A"; ?>)<br>
<?php } ?>
<strong>Invoiced:</strong> <input type='checkbox' class='copy' name='s[invoiced]' value="1" <?php echo (isset($servicecall_row["invoiced"]) && $servicecall_row["invoiced"])?"checked":""; ?>><br>


</td></tr>
<tr><td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Invoice No:</strong><br><input type="text" size="10" VALUE="<?php echo isset($servicecall_row['invoiceno']) && $servicecall_row['invoiceno'] ? $servicecall_row['invoiceno'] : ($servicecall_row['invoiceno'] ?? ''); ?>" maxlength="10" name="s[invoiceno]" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Comments:</strong><br><textarea rows="5" cols='50' name="s[comments]" style="font-size: 10px;  font-family: verdana;"><?php echo $servicecall_row['comments'] ?? ''; ?></textarea></span></td>
</tr>
                <?php if( isset($session_userid) && strtolower( $session_userid ) == "sarahg@emergencyskills.com" ) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Comments For Sarah:</strong><br><textarea rows="5" cols='50' name="s[sherrycomments]" style="font-size: 10px;  font-family: verdana;"><?php echo $servicecall_row['sherrycomments'] ?? ''; ?></textarea></span></td>
</tr>
                                                                           <?php } ?>


<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
                <br>
<?php if( !isset($readonly) || !$readonly ) { ?>
                <div align="center">
                    <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if( isset($servicecallid) && $servicecallid ){ ?>
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
<?php } ?>
                </div>
<?php } else if( isset($servicecallid) && $servicecallid && !isset($servicecall_row["servicecalldate"]) ) { ?>
                <div align="center">
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                </div>
<?php } ?>
<?php if( !isset($noheaderorfooter) || !$noheaderorfooter )  {
 ?>
<?php if( isset($specialadmin) && $specialadmin ) {
?><br><br>
                <div align="center">
Set New <?php echo getSchoolStr( "School" ); ?>:
 <select id=borough name="borough" onChange="changeBorough();" style="font-size: 10px;  font-family: verdana;">
    <option value=""></option>
<?php if( isset($session_iscorp) && $session_iscorp ) { ?>
                                        <option value="other">Other</option>
<?php  } ?>

                                        <option value="Bronx">The Bronx</option>
                                        <option value="Brooklyn">Brooklyn</option>
                            <option value="Manhattan">Manhattan</option>
   <option value="Queens">Queens</option>
     <option value="Staten Island">Staten Island</option>
                    </select>

                            <span class='copy'><?php echo getSchoolStr( "School" ); ?> Name: </span> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='changeBorough()'> <input type='button' value='Search' class=copy onClick='changeBorough()'>
<span id='school_select'>

</span>
                                           <input type='submit' name='moveaeds' value='Move Selected AEDs to This School'>

    <?php } ?>
    <?php } ?>
                </td>
</tr>
</table>
<br><br>
<br><br>
<?php if( !isset($noheaderorfooter) || !$noheaderorfooter )  {
 ?>
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
              <?php } ?>