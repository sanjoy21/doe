<?php 
$nologinrequired = true;
require "mysql.php";
$nologinrequired = true; // Added later by Sanjoy Dey

$classid = intval( $classid );
$nonewschool = true;
$class = getClassRow( $classid );

   $attendees = get_attendees( $class["id"] );
    if( count( $attendees ) >= $class["maxattendees"] )
    {
Header( "Location: individual_registration1.php" );
    exit;
    }

$crow = getCompanyRow( $class["companyid"] );
//print_r( $crow );exit;
$istsi = isTSI( $crow );
if( isset($crow["iscorp"]) && isset($session_iscorp) && $crow["iscorp"] != $session_iscorp )
{   
    Header( "Location: https://".getUrlPrefix( 0 ).".".URL_WITHOUT_SUBDOMAIN."/individual_registration3.php?classid=$classid&borough=$borough" );
    exit;
}
// set getUrlPrefix( $crow["iscorp"] ) to getUrlPrefix( 0 ) by Sanjoy Dey

$class_names = $allclass_names[$crow["iscorp"]];
//print_r( $class_names );
$overrideiscorp = $crow["iscorp"];
if( isset($overrideiscorp) && $overrideiscorp && $overrideiscorp != AGING )
$specialgroup = $crow["campusid"];
if( isset($overrideiscorp) && $overrideiscorp )
$specialregion = $crow["region"];
$extrachange = " onChange='javascript:updateBuildings( this );'";
?>
<?php include "ssi/top.php"; ?>
<?php
$session_iscorp = $overrideiscorp;
include "getschooldropdown_ajax.php"; ?>
<!--start center content-->
<script language='javascript'>
function isPhoneNumber(str) 
{
 var phone2 = /^(\+\d)*\s*(\(\d{3}\)\s*)*\d{3}(-{0,1}|\s{0,1})\d{2}(-{0,1}|\s{0,1})\d{2}$/; 
if (str.match(phone2)) {
   return true;
 } else {
          alert("Please enter a valid phone number");
 return false;
 }
}

// Declaring required variables
var digits = "0123456789";
// non-digit characters which are allowed in phone numbers
var phoneNumberDelimiters = "()- ";
// characters which are allowed in international phone numbers
// (a leading + is OK)
var validWorldPhoneChars = phoneNumberDelimiters + "+";
// Minimum no of digits in an international phone no.
var minDigitsInIPhoneNumber = 10;

function isInteger(s)
{   var i;
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag)
{   var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character isn't whitespace.
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function checkInternationalPhone(strPhone){
s=stripCharsInBag(strPhone,validWorldPhoneChars);
return (isInteger(s) && s.length >= minDigitsInIPhoneNumber);
}

function checkSubmit()
{
<?php if( $classid != 6240 && $classid != 6241 && $classid != 6264 ) { ?>
if (checkInternationalPhone(document.myform.dayphone.value)==false){
alert("Please Enter a Valid Phone Number")

return false;
}
<?php } ?>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
    if( document.myform.terms.checked == false )
    {
        alert("Please agree to our terms and conditions." );
        return false;
    }
<?php } ?>
    if( document.myform.firstname.value == "" )
    {
        alert("First name is required." );
        return false;
    }
    if( document.myform.lastname.value == "" )
    {
        alert("Last name is required." );
        return false;
    }
    if( document.myform.email.value == "" )
    {
        alert("Email is required." );
        return false;
    }
<?php if( $classid != 6240 && $classid != 6241 && $classid != 6264 ) { ?>
//     if( document.myform.employeeid && document.myform.employeeid.value == "" )
//     {
//         alert("Employee ID is required." );
//         return false;
//     }
<?php if( $istsi )  { ?>
    if( document.myform.employeeid && document.myform.employeeid.value == "" )
    {
        alert("Kronos File Number is required." );
        return false;
    }
<?php } ?>

    <?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
    if( document.myform.address1.value == "" )
    {
        alert("Address is required." );
        return false;
    }
    if( document.myform.city.value == "" )
    {
        alert("City is required." );
        return false;
    }
    if( document.myform.state.value == "" )
    {
        alert("State is required." );
        return false;
    }
    if( document.myform.zip.value == "" )
    {
        alert("Zip code is required." );
        return false;
    }
    <?php } else { ?>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
    if( document.myform.department.value == "" )
    {
        alert("<?=isset($crow["region"]) && $crow["region"] == "OR"?"Company Name":"Department"?> is required." );
        return false;
    }
<?php } 
if( isset($crow["region"]) && !in_array( $crow["region"] , array( "WSC", "PSC", "NYSC", "BSC" ) )  )
{
?>
    if( document.myform.floor.value == "" )
    {
        alert("Floor is required." );
        return false;
    }
<?php } ?>
        <?php } ?>
        <?php } ?>
    if( document.myform.email.value != document.myform.email2.value )
    {
        alert("Email addresses do not match." );
        return false;
    }
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
    if( document.myform.pmsid.value == "" )
    {
        alert("<?=getSchoolStr( "PMS ID" )?> is required." );
        return false;
    }
<?php } ?>
<?php if( $classid != 6240 && $classid != 6241 && $classid != 6264 ) { ?>
    if( document.myform.borough.selectedIndex == 0 )
    {
        alert("You must choose your borough." );
        return false;
    }
    if( !document.myform.companyid )
    {
        alert("You must choose your <?=getSchoolStr( "school", isset($crow["iscorp"])?$crow["iscorp"]:"" )?>." );
        return false;
    }
//    alert( $("[name=companyid]").val() );
    if( $("[name=companyid]").val() == "" )
    {
        alert("You must choose your <?=getSchoolStr( "school", isset($crow["iscorp"])?$crow["iscorp"]:"" )?>. " );
        return false;
    }
<?php } ?>
return true;
}
</script>

<form name="myform" id="myform" method="post" action="individual_registration_thanks.php" onSubmit='return checkSubmit()'>
<input type='hidden' name='classid' value='<?=$classid?>'>
<input type='hidden' name='mode' value='create'>

<strong><span class="title">CLASS REGISTRATION - Step 3 of 3</span></strong>
<BR><hr>
You are registering for the following class:<br><br>
<table cellpadding="0" cellspacing="0" border="0" width="476"  class="table3">
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="4" border="0" >
<tr>
<td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td> 
<td valign="top"><span class="copy"><?=$class_names[$class["code"]]?></span></td>
</tr>
<tr>
<td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td> 
<td valign="top"><span class="copy"><?=date( "l, M. d, Y", strtotime( $class["startdate"] ) )?></span></td>
</tr>
<tr>
<td valign="top" align="right"><span class="copy"><strong>Time:</strong></span></td> 
<td valign="top"><span class="copy"><?=date( "h:i a", strtotime( $class["startdate"] ) )?> <?php echo " - "; ?><?=date( "h:i a", strtotime( $class["enddate"] ) )?>
<?php if( isset($session_iscorp) && $session_iscorp ) { ?>
 - <?=date( "h:i a", strtotime( $class["enddate"] ) )?>
<?php } ?>
</span></td>
            </tr>
<!--<tr>
            <td valign="top" align="right"><span class="copy"><strong>Trainer:</strong></span></td> 
<td valign="top"><span class="copy">
<?php $trainers =getTrainers( $class["id"] );
foreach( $trainers as $trow ) { ?>
        <span class="copy"><?=getFullname( $trow["trainerid"] )?></span>
<?php } ?>
</span></td>
            </tr>
-->
           <tr><td colspan="2"><br></td></tr>
            <tr>
            <td valign="top" align="right"><span class="copy"><strong>
<?=getSchoolStr( "School", isset($crow["iscorp"])?$crow["iscorp"]:"" )?>:</strong></span></td>
<td valign="top"><span class="copy"><?=$crow["companyname"]?></span></td>
            </tr>
    <?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ) { ?>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Location:</strong></span></td> 
<td valign="top"><span class="copy"><?=getTrainingAddress( $class )?></span></td>
            </tr>
<?php } ?>
    <?php if( isset($crow["iscorp"]) && $crow["iscorp"] ) { ?>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Training Location:</strong></span></td> 
<td valign="top"><span class="copy"><?=getTrainingAddress( $class )?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>On-Site Contact:</strong></span></td> 
<td valign="top"><span class="copy"><?=$class["firstname"]?> <?=$class["lastname"]?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Phone:</strong></span></td> 
<td valign="top"><span class="copy"><?=$class["phone"]?></span></td>
            </tr>
        <?php } else { ?>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Borough:</strong></span></td> 
<td valign="top"><span class="copy"><?=$crow["borough"]?></span></td>
            </tr>
        <?php } ?>

            </table>
</td>

          </tr>
        </table>

<BR><hr>
<span class="title"><strong>Your Personal Information:</strong></span><BR><BR>

<?php
if( $istsi ) {
?>
<span class="title"><strong>Please enter your coupon code to continue: <input type='text' id='couponcode' name='couponcode' value=''> <button id='populate'>Submit</button></strong></span><BR><BR>
<span id="ccerr"></span>
<?php
}
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" id="tsiform" <?php if( $istsi ) { ?> style="display:none" <?php } ?>>
        <!--row 1-->
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
                    <tr>
        <td valign="top"><span class="copy">First Name: *<?=!isset($overrideiscorp) || !$overrideiscorp?"<br><b>as it appears on pay stub</b>":""?></span><br>
<input name="firstname" type="text" id="" value="<?=isset($firstname)?$firstname:""?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Last Name: *<?=!isset($overrideiscorp) || !$overrideiscorp?"<br><b>as it appears on pay stub</b>":""?></span><br>
<input name="lastname" type="text" id="" value="<?=isset($lastname)?$lastname:""?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
                    </tr>
                    </table>
</td>
           </tr>
<!--end row 1-->

<!--row 2-->
<?php if( isset($session_iscorp) && $session_iscorp ) { ?>
<tr><td>                                
<table cellpadding="0" cellspacing="6" border="0" >
<tr>
<td valign="top"><span class="copy">
<?php 
if( $istsi ) 
{
echo( "Kronos File Number: *" );
}
else
{
echo( "Employee ID Number:" );
}

?>
</span><br>
<input name="employeeid" type="text" id="" size="20" value="<?=isset($employeeid)?$employeeid:""?>" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php if( $istsi ) { ?>
<td>Primary Club GM&apos;s Name: </span><br>
<input name="managername" type="text" id="" size="20" value="<?=isset($managername)?$managername:""?>" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php } ?>
</tr></table></td></tr>
<?php } ?>

<tr>
<td>
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top"><span class="copy">Cell Phone Number: *</span><br>
<input name="dayphone" value="<?=isset($dayphone)?$dayphone:""?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
    <?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
    <td valign="top"><span class="copy">Fax Number:</span><br>
<input name="fax" type="text" id="" size="10" value="<?=isset($fax)?$fax:""?>" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Job Title:</span><br>
<input name="title" type="text" id="" size="20" maxlength="20" value="<?=isset($title) && $title?$title:(isset($_COOKIE["regtype"])?$_COOKIE["regtype"]:"")?>" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php } else { ?>
<td valign="top"><span class="copy"><?=isset($crow["region"]) && $crow["region"] == "OR" ?"Company Name":"Department"?>:</span><br>
<input name="department" type="text" id="" size="20" value="<?=isset($department)?$department:""?>" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<input type='hidden' name='title' value='<?=isset($_COOKIE["regtype"])?$_COOKIE["regtype"]:""?>'>
<?php } ?>
</tr>
</table>
</td>
</tr>
<tr>
<td>
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top"><span class="copy"><?php if( isset($session_iscorp) && $session_iscorp ) { echo( "Office Street" ); } ?> Address: <?=isset($session_iscorp) && $session_iscorp?"":"*"?></span><br>
<input name="address1" type="text" id="" value="<?=isset($address1)?$address1:""?>" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<td valign="top"><span class="copy">Apt:</span><br>
<input name="address2" type="text" id="" value="<?=isset($address2)?$address2:""?>" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>

<?php } else { ?>
<td valign="top"><span class="copy">Floor: <?php if( isset($crow["region"]) && !in_array( $crow["region"] , array( "WSC", "PSC", "NYSC", "BSC" ) )  ){ ?>*<?php } ?>
</span><br>
<input name="floor" type="text" id="" value="<?=isset($floor)?$floor:""?>" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php } ?>
</tr>
</table>
</td>
</tr>
<tr>
<td>
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top"><span class="copy">City: <?=isset($session_iscorp) && $session_iscorp?"":"*"?></span><br>
<input name="city" value="<?=isset($city)?$city:""?>" type="text" id="" size="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">State: <?=isset($session_iscorp) && $session_iscorp?"":"*"?></span><br>
<input name="state" value="<?=isset($state)?$state:""?>" type="text" id="" size="5" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td colspan='3' valign="top"><span class="copy">Zip Code: <?=isset($session_iscorp) && $session_iscorp?"":"*"?></span><br>
<input name="zip" value="<?=isset($zip)?$zip:""?>" type="text" id="" size="10" maxlength="10" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ) { ?>
<td valign="middle"><span class="copy">Employee Type: (required)</span><br><select name="emptype"  style="font-size: 10px;  font-family: verdana;">
<option  value='DOE Employee'>DOE Employee</option>
<option <?=isset($emptype) && $emptype=="Charter School Employee"?"SELECTED":""?> value='Charter School Employee'>Charter School Employee</option>
<option <?=isset($emptype) && $emptype=="SSA"?"SELECTED":""?> value='SSA'>SSA</option>
<option <?=isset($emptype) && $emptype=="Custodial Staff"?"SELECTED":""?> value='Custodial Staff'>Custodial Staff</option>
<?php if( isOverallAdmin() ) { ?>
<option <?=isset($emptype) && $emptype=="Non DOE"?"SELECTED":""?> value='Non DOE'>Non DOE</option>
<?php } ?>
</select>
</td>
<?php } ?>
    
</tr>
</table>
</td>
</tr>
<!--end row 2-->

<!--row 3-->
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="bottom"><span class="copy">Email Address: *</span><br>
<input name="email" type="text" id="" value="<?=isset($email)?$email:""?>" size="20" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="bottom"><span class="copy">Re-Type Email Address:</span><br>
<input name="email2" type="text" id="" value="<?=isset($email2)?$email2:""?>" size="20" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ) { ?>
<td valign="bottom"><span class="copy"><a href='images/NYC001.jpg' target='blank'><b><?=getSchoolStr( "PMS ID" )?></b> (help)</a>:<?=!isset($overrideiscorp) || !$overrideiscorp?"<br><b>as it appears on pay stub</b>":""?></span><br>
<input name="pmsid" value="<?=isset($pmsid)?$pmsid:""?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<?php } ?>
</tr>
</table>
</td>
</tr>
<tr><td>* Required</td></tr>
<!--end row 3-->
<?php if( !in_array( $classid, array( 6240, 6241, 6264 ) ) )  { ?>

<?php if( isset($crow["region"]) && $crow["region"] == "OR" ) {?>
            <input type='hidden' name='companyid' id='companyid' value='14669'>
</table>
<?php } else if( isset($crow["region"]) && $crow["region"] == "DOH-S" ) {?>
            <input type='hidden' name='companyid' id='companyid' value='13373'>
</table>
<?php } else if( isset($crow["region"]) && $crow["region"] == "Black" ) {?>
<input type='hidden' name='companyid' id='companyid' value='11954'>
</table>
<?php } else { ?> 
<tr>
<td valign="top">
<hr>
<span class="title"><strong>Your <?=getSchoolStr( "School", isset($crow["iscorp"])?$crow["iscorp"]:"" )?> Information:</strong></span><br><br>
</td>
</tr>

<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top">
                                 
<?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ){ ?>
<table  class="table3">
<tr>
<td>
<span class="copy"><B>First, Choose Your <?=getSchoolStr( "School", isset($crow["iscorp"])?$crow["iscorp"]:"" )?>'s Borough:</b></span>
</td>
<tr><td>
 <select id=borough name="borough" style="font-size: 10px;  font-family: verdana;">
<option value=""></option>
<option value="Bronx">The Bronx</option>
<option value="Brooklyn">Brooklyn</option>
<option value="Manhattan">Manhattan</option>
<option value="Queens">Queens</option>
<option value="Staten Island">Staten Island</option>
</select>
</td></tr>
<?php } else { ?>
<input type='hidden' name='borough' id="borough" value='other'>
<table id="searchtable" class="table3">
<?php } ?>
<?php if( isset($crow["region"]) && $crow["region"] != "VIR" ) { ?>
<tr><td class='copy'><b><?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ) { ?>Next,<?php } ?> Enter your <?=getSchoolStr( "school", isset($crow["iscorp"])?$crow["iscorp"]:"" )?>'s name or number and click Search:</b>
<br>
<?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ) { ?>
<b><font color='red'>TIP:  Enter only a portion of your school name or number. <br>Example: For John F. Kennedy School, search for "123" or "Kennedy" or "John".</font></b>
<?php } ?>

</td></tr>
<tr><td>
<input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='updateCompanies()'>
</td></tr>
<tr><td><input type='button' value='Search' class=copy onClick='updateCompanies()'></td></tr>
</table>
</td></tr>
<?php } ?>
<tr>
<td valign="top" id='school_select'>
</td>

</tr>
<?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ){ ?>
<tr><td class='copy'><span id='building_div'></span>
</select>
</td></tr>
<?php } ?>
</table>
</td>
</tr>
</table>
</td>
</tr>
<?php } // end if open registration ?>

<?php } ?>

<?php if( !isset($crow["iscorp"]) || !$crow["iscorp"] ) { ?>
<tr>
<td>
<td valign="top" class='copy' ><input type='checkbox' name='terms' value='1'> I agree to the ESI <a href='#' onClick='javascript:window.open( "terms.php", "_blank", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600" )'>Terms and conditions</a>
</td>
</tr>
<?php } ?>
<tr>
<td>
<td valign="top">
<input type='image' id="registerimg" name='submit' src="images/button_register.gif" <?php if( $istsi ) { ?> style="display:none" <?php } ?>>
</td>
</tr>
</table>

<br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
<script language='javascript'>
<?php if( isset($crow["iscorp"]) && $crow["iscorp"] && isset($crow["region"]) && $crow["region"] != "OR") { ?>
updateCompanies();
<?php } ?>

$("#populate" ).click( function() {
      var val = $("#couponcode").val();
//          alert( val );
        if( val > "" ) { 
        populateCouponCode( $("#couponcode").val() );
      }
      return false;
}
);

</script>
</table>
<br><br>
</div>
</body>
</html>