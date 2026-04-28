<?php
include "mysql.php"; 

if( isset($update) && $update )
{
    if( isset($drillmonitoringdate) && $drillmonitoringdate )
    {
        $drillmonitoringdate = fixdate( $drillmonitoringdate );
    }
    
    if( isset($drillmonitoringid) && $drillmonitoringid )
    {
        $oldscrow = db_query_first( "select * from drillmonitoring where drillmonitoringid = " . intval($drillmonitoringid) );
        $thecid = isset($oldscrow['companyid']) ? $oldscrow['companyid'] : '';
        
        $drillmonitoringdate_safe = isset($drillmonitoringdate) ? $drillmonitoringdate : '';
        $nextdrillmonitoringdate_safe = isset($nextdrillmonitoringdate) ? $nextdrillmonitoringdate : '';
        
        db_query( "update drillmonitoring set drillmonitoringdate = '$drillmonitoringdate_safe', nextdrillmonitoringdate = '$nextdrillmonitoringdate_safe' where drillmonitoringid = " . intval($drillmonitoringid) );
    }
    else
    {
        $thecid = isset($id) ? $id : '';
        
        $id_safe = isset($id) ? intval($id) : 0;
        $drillmonitoringdate_safe = isset($drillmonitoringdate) ? $drillmonitoringdate : '';
        $nextdrillmonitoringdate_safe = isset($nextdrillmonitoringdate) ? $nextdrillmonitoringdate : '';
        
        $drillmonitoringid = db_query_insert_id( "insert into drillmonitoring ( trainerid, drillmonitoringdate, nextdrillmonitoringdate ) values ('$id_safe','$drillmonitoringdate_safe','$nextdrillmonitoringdate_safe') " );
    }

    if( isset($redirect) && $redirect )
    {
        header( "location: " . $redirect );
        exit;
    }
}

if( isset($delete) && $delete && isset($drillmonitoringid) && $drillmonitoringid )
{
    db_query( "delete from drillmonitoring where drillmonitoringid = " . intval($drillmonitoringid) );
    if( isset($redirect) && $redirect )
    {
        header( "location: " . $redirect );
        exit;
    }
}

//get info for the form
if( isset($drillmonitoringid) && $drillmonitoringid )
{
    $drillmonitoring_row = db_query_first( "select * from drillmonitoring where drillmonitoringid = " . intval($drillmonitoringid) );
    $id = isset($drillmonitoring_row["trainerid"]) ? $drillmonitoring_row["trainerid"] : '';
}

if( !isset($drillmonitoring_row) || !$drillmonitoring_row )
{
    $drillmonitoring_row = array();
}

if( isset($id) && $id )
{
    $trainer_row = getUserRow( $id );
}
else
{
    $trainer_row = array();
}

$noleftnav = 1;

include "ssi/top.php";
include "getschooldropdown.php"; 

?>
<script LANGUAGE="JavaScript">

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
if( !isset($redirect) || !$redirect )
{
    $redirect = "/trainer_view.php?tid=" . (isset($id) ? $id : '');
}
?>

<form method="post">
<input type="hidden" name ="redirect" value="<?php echo isset($redirect) ? htmlspecialchars($redirect) : ''; ?>">
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="drillmonitoringid" value="<?php echo isset($drillmonitoringid) ? htmlspecialchars($drillmonitoringid) : ''; ?>">
<input type="hidden" name ="id" value="<?php echo isset($id) ? htmlspecialchars($id) : ''; ?>">
<?php if( isset($specialadmin) && $specialadmin ) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="trainers.php">&laquo; Back to Admin Main</a></strong></span></td>
</tr>
</table>
<?php } ?>
<strong>THIS DRILL MONITORING IS FOR:</strong><br>
<?php 
        if( isset($id) && $id && isset($trainer_row['first_name']) && isset($trainer_row['last_name']) )
        {
            echo  "<a href='trainer_view.php?tid=" . htmlspecialchars($id) . "'>" . htmlspecialchars($trainer_row['first_name']) . " " . htmlspecialchars($trainer_row['last_name']) . "</a>";
        }
        ?>
<br><br>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><Strong>Drill Monitoring Information</strong></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Drill Monitoring Date*:</strong> <input type="text" size="12" VALUE="<?php echo isset($drillmonitoring_row['drillmonitoringdate']) ? htmlspecialchars($drillmonitoring_row['drillmonitoringdate']) : ''; ?>" maxlength="50" name="drillmonitoringdate" style="font-size: 10px;  font-family: verdana;"> <strong></strong> </span><br>
<strong>Next Drill Monitoring Date:</strong> <input type='text' name='nextdrillmonitoringdate' value='<?php echo isset($drillmonitoring_row['nextdrillmonitoringdate']) ? htmlspecialchars($drillmonitoring_row['nextdrillmonitoringdate']) : ''; ?>' size='12' style="font-size: 10px;  font-family: verdana;"> YYYY-MM-DD</td>
</tr>

<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
<br>
<?php if( !isset($readonly) || !$readonly ) { ?>
                <div align="center">
                    <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if( isset($drillmonitoringid) && $drillmonitoringid ){ ?>
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
<?php } ?>
                </div>
<?php } else if( isset($drillmonitoringid) && $drillmonitoringid && isset($drillmonitoring_row["drillmonitoringdate"]) && !$drillmonitoring_row["drillmonitoringdate"] ) { ?>
                <div align="center">
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                </div>
<?php } ?>
</td></tr></table>
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