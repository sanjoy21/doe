<?php 
include "mysql.php"; 

if( $delete )
{
    db_query( "delete from zip_to_territory where territoryid = $id " );
    db_query( "delete from territory where id = $id " );
    Header( "location: $redirect " );
    exit;
}

if( $update )
{
    $name_safe = isset($_POST['name']) ? $_POST['name'] : '';
    $trainerid_safe = isset($_POST['trainerid']) ? $_POST['trainerid'] : '';
    $zips = isset($_POST['zips']) ? $_POST['zips'] : array();
    
    db_query( "update territory set name = '$name_safe', trainerid = '$trainerid_safe' where id = $id " );
    db_query( "delete from zip_to_territory  where territoryid = $id " );
    
    if( is_array($zips) ) {
        foreach( $zips as $z )
        {
            if( trim( $z) )
                db_query( "insert into zip_to_territory( zip, territoryid ) values ( '".trim($z)."', '$id' )" );
        }
    }
    
    Header( "location: $redirect " );
    exit;
}

//get info for the form
$t_row = db_query_first( "select * from territory where id = $id" );
?>
<?php
include "ssi/top.php"; ?>
<!--start center content-->

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
if( !$redirect )
    $redirect="territories.php";
?>
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

<form method="post">
<input type="hidden" name ="redirect" value="<?php echo htmlspecialchars($redirect)?>">
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="id" value="<?php echo htmlspecialchars($id)?>">
<?php if( isset($specialadmin) && $specialadmin ) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%"  class="table3">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="territories.php">&laquo; Back to Territories</a></strong></span></td>
</tr>
</table>
<?php } ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%"  class="table3">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>TERRITORY Information</strong></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Name*:</strong><br><input type="text" size="40" VALUE="<?php echo isset($t_row['name']) ? htmlspecialchars($t_row['name']) : ''?>" name="name" style="font-size: 10px;  font-family: verdana;"></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Trainer*:</strong><br>
<select name='trainerid'>
<?php 
$trainers = getAllTrainers();
echo "<option value='-1'></option>";
if( isset($trainers) && is_array($trainers) ) {
    foreach( $trainers as $trow )
    {
        $selected = (isset($t_row['trainerid']) && isset($trow['id']) && $t_row['trainerid'] == $trow['id']) ? "SELECTED" : "";
        $first_name = isset($trow['first_name']) ? $trow['first_name'] : '';
        $last_name = isset($trow['last_name']) ? $trow['last_name'] : '';
        echo "<option $selected value='".(isset($trow['id']) ? $trow['id'] : '')."'>$first_name $last_name</option>";
    }
}
?>
</select>
</span></td>
</tr>
<tr><td valign='top' bgcolor="#E2DFDF" class='copy'><strong>Zips:</strong><br>
<?php
$zones = getZipsForTerritory( $id );
if( isset($zones) && is_array($zones) ) {
    foreach( $zones as $z )
    {
        echo( "<input type='text' name='zips[]' size=7 value='".htmlspecialchars($z)."'><br>" );
    }
}
for( $i = 0; $i < 10; $i++ )
{
    echo( "<input type='text' name='zips[]' size=7 value=''><br>" );
}
?>
<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
<br>
<?php if( !isset($readonly) || !$readonly ) { ?>
<div align="center">
<input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if( $id ){ ?>
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