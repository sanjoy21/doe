<?php
include "mysql.php"; 

if($delete )
{
    db_query("delete from campus where id = " . intval($campusid));
    if($backto)
    {
        $redirect = "viewcompany.php?id=" . intval($backto);
    }
    else
    {
        $redirect = isset($redirect) ? $redirect : 'campuses.php';
    }
    Header("location: " . $redirect);
    exit;
}

if($update)
{
    if( $campusid)
    {
        db_query("update campus set name = '" . $name . "', contactname = '" . $contactname . "', contactemail = '" . $contactemail . "', zipcode = '" . $zipcode . "' where id = " . intval($campusid));
    }
    else
    {
        $campusid = db_query_insert_id("insert into campus (name, contactname, contactemail, zipcode, iscorp) values ('" . $name . "','" . $contactname . "','" . $contactemail . "','" . $zipcode . "', '" . intval($session_iscorp) . "')");
    }
    
    if( $backto)
    {
        $redirect = "viewcompany.php?id=" . intval($backto);
    }
    else
    {
        $redirect = isset($redirect) ? $redirect : 'campuses.php';
    }
    
    Header("location: " . $redirect);
    exit;
}

$redirect = isset($redirect) ? $redirect : "campuses.php";

//get info for the form
$campus_row = array();
if($campusid)
{
    $campus_row = db_query_first("select * from campus where id = " . intval($campusid));
}

if(!isset($campus_row) || !is_array($campus_row))
{
    $campus_row = array();
}

?>

<?php
$noleftnav = 1;
include "ssi/top.php"; ?>
<!--start center content-->
<form method="post">
<input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
<input type="hidden" name="update" value="true">
<input type="hidden" name="campusid" value="<?php echo htmlspecialchars($campusid); ?>">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

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
if($backto)
{
    $redirect = "viewcompany.php?id=" . intval($backto);
}

if(!$redirect)
{
    $redirect = "campuses.php";
}
?>

<?php if($specialadmin) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%"  class="table3">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="campuses.php">&laquo; Back to <?php echo getSchoolStr("Campuses"); ?></a></strong></span></td>
</tr>
</table>
<?php } ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%"  class="table3">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong><?php echo getSchoolStr("Campus"); ?> Information</strong></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong><?php echo getSchoolStr("Campus"); ?> Name*:</strong><br><input type="text" size="40" VALUE="<?php echo htmlspecialchars(isset($campus_row['name']) ? $campus_row['name'] : ''); ?>" name="name" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Contact Name*:</strong><br><input type="text" size="40" VALUE="<?php echo htmlspecialchars(isset($campus_row['contactname']) ? $campus_row['contactname'] : ''); ?>"  name="contactname" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Contact Email:</strong><br><input type="text" size="40" VALUE="<?php echo htmlspecialchars(isset($campus_row['contactemail']) ? $campus_row['contactemail'] : ''); ?>" maxlength="50" name="contactemail" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Zip Code:</strong><br><input type="text" size="10" VALUE="<?php echo htmlspecialchars(isset($zip) && $zip ? $zip : (isset($campus_row['zipcode']) ? $campus_row['zipcode'] : '')); ?>" maxlength="50" name="zipcode" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
<br>
<?php if(!$readonly) { ?>
<div align="center">
    <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if($campusid){ ?>
     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
<?php } ?>
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