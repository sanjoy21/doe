<?php
include "mysql.php";

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if(isset($markcompleted) && $markcompleted)
{
    db_query("update accessoryrequests set completed = '1' where id = " . intval($accessoryrequestid));
}

if(isset($update) && $update)
{
    $requestdate = isset($requestdate) ? fixdate($requestdate) : '';
    $itemtype = isset($itemtypearr) && is_array($itemtypearr) ? join(",", $itemtypearr) : '';
    $already = false;
    
    if(isset($aedserial) && $aedserial)
    {
        $tryit = true;
        if(isset($accessoryrequestid) && $accessoryrequestid)
        {
            $oldaccessoryrequestrow = db_query_first("select * from accessoryrequests where id = '" . intval($accessoryrequestid) . "'");
            if(isset($oldaccessoryrequestrow["aedserial"]) && $oldaccessoryrequestrow["aedserial"] == trim($aedserial))
                $tryit = false;
        }
        if($tryit)
        {
            $already = db_query_first_cell("select aedid from aed_esi where serial = '" . db_escape_string($aedserial) . "' and deleted = 0");
        }         
    }
    
    if(!$already)
    {
        if(isset($accessoryrequestid) && $accessoryrequestid)
        {
            $oldaccessoryrequestrow = db_query_first("select * from accessoryrequests where id = '" . intval($accessoryrequestid) . "'");
            $thecid = isset($oldaccessoryrequestrow['companyid']) ? $oldaccessoryrequestrow['companyid'] : 0;
            $ext = ''; // Initialize $ext variable
            
            db_query("update accessoryrequests set requestdate = '" . db_escape_string($requestdate) . "', completed = '" . db_escape_string(isset($completed) ? $completed : '') . "', aedserial = '" . db_escape_string(isset($aedserial) ? $aedserial : '') . "', description = '" . db_escape_string(isset($description) ? $description : '') . "', servicecallid = '" . db_escape_string(isset($servicecallid) ? $servicecallid : '') . "', drillid = '" . db_escape_string(isset($drillid) ? $drillid : '') . "', trackingno = '" . db_escape_string(isset($trackingno) ? $trackingno : '') . "', esifieldrep = '" . db_escape_string(isset($esifieldrep) ? $esifieldrep : '') . "' $ext where id = " . intval($accessoryrequestid));
        }
        else
        {
            $thecid = isset($id) ? $id : 0;
            $session_id = isset($session_id) ? $session_id : 0;
            $accessoryrequestid = db_query_insert_id("insert into accessoryrequests (companyid, requestdate, description, servicecallid, drillid, trackingno, itemtype, esifieldrep, completed, aedserial, dateadded, addedby) values ('" . intval($thecid) . "','" . db_escape_string($requestdate) . "','" . db_escape_string(isset($description) ? $description : '') . "','" . db_escape_string(isset($servicecallid) ? $servicecallid : '') . "','" . db_escape_string(isset($drillid) ? $drillid : '') . "','" . db_escape_string(isset($trackingno) ? $trackingno : '') . "','" . db_escape_string($itemtype) . "','" . db_escape_string(isset($esifieldrep) ? $esifieldrep : '') . "','" . db_escape_string(isset($completed) ? $completed : '') . "','" . db_escape_string(isset($aedserial) ? $aedserial : '') . "', now(), '" . intval($session_id) . "')");
        }
        
        if(isset($oldaccessoryrequestrow["aedserial"]) && $oldaccessoryrequestrow["aedserial"] != trim($aedserial))
        {
            $id = isset($id) && $id ? $id : (isset($oldaccessoryrequestrow["companyid"]) ? $oldaccessoryrequestrow["companyid"] : 0);
            $sql = "insert into aed_esi (clientid, serial, newinstall) values ('" . intval($id) . "','" . db_escape_string($aedserial) . "',1)";
            db_query($sql);
        }
        
        if(isset($oldaccessoryrequestrow["completed"]) && $oldaccessoryrequestrow["completed"] != isset($completed) && $completed)
        {
            db_query("update accessoryrequests set completedby = '" . intval(isset($session_id) ? $session_id : 0) . "', completiondate = now() where id = " . intval($accessoryrequestid));
        }
        
        $os = "";
        
        if(!isset($notifydoe) || !$notifydoe)
        {
            Header("location: " . (isset($redirect) ? $redirect : ''));
            exit;
        }
    }
    else
    {
        $err = "<font color='red'>Sorry, this serial is a duplicate!</font>";
    }
}

if(isset($notifydoe) && $notifydoe && !isset($err))
{
    $ar_row = db_query_first("select * from accessoryrequests where id = " . intval($accessoryrequestid));
    $crow = getCompanyRow(isset($ar_row['companyid']) ? $ar_row['companyid'] : 0);

    $initial = "The following school has requested:\n" . (isset($ar_row["itemtype"]) ? str_replace(",", ", ", $ar_row["itemtype"]) : '') . "\n" . (isset($ar_row["description"]) ? $ar_row["description"] : '') . "\n";
    $battreq = 1;
    
    db_query("update accessoryrequests set lastnotified = now() where id = " . intval($accessoryrequestid));        
    
    $emailBody = $initial . " \n " . (isset($ar_row['requestdate']) ? $ar_row['requestdate'] : '') . " \n " . (isset($crow['companyname']) ? $crow['companyname'] : '') . " \n " . (isset($crow['schoolcode']) ? $crow['schoolcode'] : '') . "\n " . (isset($crow['address']) ? $crow['address'] : '') . " " . (isset($crow['city']) ? $crow['city'] : '') . ", " . (isset($crow['zip']) ? $crow['zip'] : '') . " \n Principal Name: " . (isset($crow['principalname']) ? $crow['principalname'] : '') . " \n Principal Email: " . (isset($crow['principalemail']) ? $crow['principalemail'] : '') . " \n School Phone: " . (isset($crow['schoolphone']) ? $crow['schoolphone'] : '') . " \n\nEmergency Skills, Inc.";
    
    mail("hthomps@schools.nyc.gov,cmcgee3@schools.nyc.gov", "AED/Accessory Request", $emailBody, "From:sarahg@emergencyskills.com\nCc:sarahg@emergencyskills.com");
}

if(isset($delete) && $delete)
{
    db_query("delete from accessoryrequests where id = " . intval($accessoryrequestid));
    Header("location: " . (isset($redirect) ? $redirect : ''));
    exit;
}

//get info for the form
if(isset($accessoryrequestid) && $accessoryrequestid)
{
    $accessoryrequest_row = db_query_first("select * from accessoryrequests where id = " . intval($accessoryrequestid));
    $id = isset($accessoryrequest_row["companyid"]) ? $accessoryrequest_row["companyid"] : 0;
    
    if(!isset($accessoryrequest_row['id']) || !$accessoryrequest_row['id'])
    {
        echo "No accessoryrequest " . htmlspecialchars($accessoryrequestid);
        exit;
    }
}

if(!isset($accessoryrequest_row) || !is_array($accessoryrequest_row))
{
    $accessoryrequest_row = array();
}

$company_row = getCompanyRow(isset($id) ? $id : 0);
?>

<?php 
include "ssi/top.php"; ?>
<?php echo isset($err) ? $err : ''; ?>
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
if(!isset($redirect) || !$redirect)
    $redirect = "/viewcompany.php?id=" . (isset($id) ? $id : '');
?>

<form method="post" name="arform">
<input type="hidden" name ="redirect" value="<?php echo htmlspecialchars(isset($redirect) ? $redirect : ''); ?>">
<input type="hidden" name ="accessoryrequestid" value="<?php echo htmlspecialchars(isset($accessoryrequestid) ? $accessoryrequestid : ''); ?>">
<input type="hidden" name ="id" value="<?php echo htmlspecialchars(isset($id) ? $id : ''); ?>">
<br>
<?php if(isset($specialadmin) && $specialadmin) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="schools.php">&laquo; Back to Admin Main</a></strong></span></td>
</tr>
</table>
<?php } ?>
<strong>THIS ACCESSORY REQUEST IS FOR:</strong><br>
<?php echo "<a href='viewcompany.php?id=" . (isset($id) ? htmlspecialchars($id) : '') . "'>" . (isset($company_row['companyname']) ? htmlspecialchars($company_row['companyname']) : '') . "</a><br>" . (isset($company_row['address']) ? htmlspecialchars($company_row['address']) : '') . "<br>" . (isset($company_row['floor']) ? htmlspecialchars($company_row['floor']) : '') . "<br>" . (isset($company_row['city']) ? htmlspecialchars($company_row['city']) : '') . ", " . (isset($company_row['state']) ? htmlspecialchars($company_row['state']) : '') . " " . (isset($company_row['zip']) ? htmlspecialchars($company_row['zip']) : '') . "<br>" . (isset($company_row['contactname']) ? htmlspecialchars($company_row['contactname']) : '') . " " . (isset($company_row['contactphone']) ? htmlspecialchars($company_row['contactphone']) : ''); ?>
<br><br>
<table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>ACCESSORY REQUEST Information</strong></span></td>
</tr>

<?php if(isset($accessoryrequest_row['id']) && $accessoryrequest_row['id']) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Date Added:</strong></td><td bgcolor="#E2DFDF"><?php echo htmlspecialchars(isset($accessoryrequest_row['dateadded']) ? $accessoryrequest_row['dateadded'] : ''); ?> by <?php echo htmlspecialchars(isset($accessoryrequest_row["addedby"]) ? getUserName($accessoryrequest_row["addedby"]) : ''); ?></span></td>
</tr>
<?php } ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Request Date*:</strong></td><td bgcolor="#E2DFDF"><?php echo printdates2("requestdate", isset($accessoryrequest_row['requestdate']) ? $accessoryrequest_row['requestdate'] : ''); ?> </span></td>
</tr>

<tr><td class='copy' bgcolor="#E2DFDF">
<strong>Status: </strong></td><td bgcolor="#E2DFDF" class='copy'><select class='copy' name='completed' >
<option value='0' > Requested</option>
<option value='1' <?php echo isset($accessoryrequest_row["completed"]) && $accessoryrequest_row["completed"]==1 ? "SELECTED" : ""; ?>> Completed</option>
</select>
<input type='checkbox' name='notifydoe' value='1'> Notify DOE (Last Notified: <?php echo isset($accessoryrequest_row["lastnotified"]) && $accessoryrequest_row["lastnotified"] ? htmlspecialchars($accessoryrequest_row["lastnotified"]) : "N/A"; ?>)
</td></tr>
<?php if(isset($accessoryrequest_row["completed"]) && $accessoryrequest_row["completed"]) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Completion Date*:</strong></td><td bgcolor="#E2DFDF"> <?php echo htmlspecialchars(isset($accessoryrequest_row['completiondate']) ? $accessoryrequest_row['completiondate'] : ''); ?> by <?php echo htmlspecialchars(isset($accessoryrequest_row["completedby"]) ? getUserName($accessoryrequest_row["completedby"]) : ''); ?> </span></td>
</tr>
<?php } ?>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Service Call ID:</strong></td><td bgcolor="#E2DFDF"><input type='text' name="servicecallid" size='10' maxlength='10' style="font-size: 10px;  font-family: verdana;" value="<?php echo htmlspecialchars(isset($accessoryrequest_row['servicecallid']) && $accessoryrequest_row['servicecallid'] ? $accessoryrequest_row['servicecallid'] : (isset($sid) ? $sid : '')); ?>"></span> <?php echo isset($accessoryrequest_row["servicecallid"]) && $accessoryrequest_row["servicecallid"] ? "<a href='editservicecall.php?servicecallid=" . htmlspecialchars($accessoryrequest_row["servicecallid"]) . "' target=_blank>View</a>" : ""; ?></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Drill ID:</strong></td><td bgcolor="#E2DFDF"><input type='text' name="drillid" size='10' maxlength='10' style="font-size: 10px;  font-family: verdana;" value="<?php echo htmlspecialchars(isset($accessoryrequest_row['drillid']) && $accessoryrequest_row['drillid'] ? $accessoryrequest_row['drillid'] : (isset($did) ? $did : '')); ?>"></span> <?php echo isset($accessoryrequest_row["drillid"]) && $accessoryrequest_row["drillid"] ? "<a href='editdrill.php?drillid=" . htmlspecialchars($accessoryrequest_row["drillid"]) . "' target=_blank>View</a>" : ""; ?></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>AED serial number:</strong></td><td bgcolor="#E2DFDF"><input name="aedserial" style="font-size: 10px;  font-family: verdana;" value="<?php echo htmlspecialchars(isset($accessoryrequest_row["aedserial"]) ? $accessoryrequest_row["aedserial"] : ''); ?>"> <?php if(isset($accessoryrequest_row["aedserial"]) && $accessoryrequest_row["aedserial"]) { ?>
<a target=_blank href='editaed.php?aedid=<?php echo htmlspecialchars(db_query_first_cell("select aedid from aed_esi where serial = '" . db_escape_string($accessoryrequest_row["aedserial"]) . "'")); ?>'>View</a>
<?php } ?>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>ESI Field Representative:</strong></td><td bgcolor="#E2DFDF"><select name="esifieldrep" style="font-size: 10px;  font-family: verdana;" >
<option value=''>Please Choose</option>
<option <?php echo isset($accessoryrequest_row["esifieldrep"]) && $accessoryrequest_row["esifieldrep"] ? "SELECTED" : ""; ?> value="<?php echo htmlspecialchars(isset($accessoryrequest_row["esifieldrep"]) ? $accessoryrequest_row["esifieldrep"] : ''); ?>"><?php echo htmlspecialchars(isset($accessoryrequest_row["esifieldrep"]) ? $accessoryrequest_row["esifieldrep"] : ''); ?></option>
<?php 
$opts = db_query_array("select value from esioptionvalues where datatype = 'trainer' order by priority, value", "value", "value");
if(isset($opts) && is_array($opts))
{
    foreach($opts as $o) 
        echo "<option value=\"" . htmlspecialchars($o) . "\">" . htmlspecialchars($o) . "</option>";
}
?>
</select>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Item Requested:</strong></td><td bgcolor="#E2DFDF">

<?php if(isset($accessoryrequestid) && $accessoryrequestid) { 
    echo htmlspecialchars(isset($accessoryrequest_row["itemtype"]) ? $accessoryrequest_row["itemtype"] : '');
} 
else
{ ?>
<select name="itemtypearr[]" style="font-size: 10px;  font-family: verdana;" multiple>

<?php 
$itemtypes = db_query_array("select value from esioptionvalues where datatype='areqs' order by value", "value", "value");
$spl = isset($accessoryrequest_row["itemtype"]) ? explode(",", $accessoryrequest_row["itemtype"]) : array();

if(isset($itemtypes) && is_array($itemtypes))
{
    foreach($itemtypes as $i)
    {
        $selected = in_array($i, $spl) ? "SELECTED" : "";
        echo "<option value='" . htmlspecialchars($i) . "' $selected>" . htmlspecialchars($i) . "</option>";
    }
}
?>

</select>
<?php } ?>
</span></td>
</tr>

<tr>
<td valign="top" colspan='2' bgcolor="#E2DFDF">
                <span class="copy"><strong>Description:</strong><br><textarea rows="5" cols='50' name="description" style="font-size: 10px;  font-family: verdana;"><?php echo htmlspecialchars(isset($accessoryrequest_row['description']) ? $accessoryrequest_row['description'] : ''); ?></textarea></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Tracking Number:</strong></td><td bgcolor="#E2DFDF"><input type='text' name="trackingno" size='30' maxlength='40' style="font-size: 10px;  font-family: verdana;" value="<?php echo htmlspecialchars(isset($accessoryrequest_row['trackingno']) ? $accessoryrequest_row['trackingno'] : ''); ?>"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
<br>
<?php if(!isset($readonly) || !$readonly || (isset($session_userid) && ($session_userid == "hthomps@schools.nyc.gov" || $session_userid == "3349" || strtolower($session_userid) == "cmcgee3@schools.nyc.gov"))) { ?>
<div align="center">
<input type="submit" name="update" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if(isset($accessoryrequestid) && $accessoryrequestid && isOverallAdmin()){ ?>
<input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">

<?php } ?>
                </div>
<?php } else if(isset($accessoryrequestid) && $accessoryrequestid && (!isset($accessoryrequest_row["completiondate"]) || !$accessoryrequest_row["completiondate"])) { ?>
                <div align="center">
                    <input type="submit" value="Mark Completed" name="markcompleted">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                </div>
<?php } ?>
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