<?php
// Note: Assuming session_start() is called in 'mysql.php' or 'ssi/top.php'

$nologinrequired = true;
require "mysql.php";

if( $region && $session_iscorp )
{
    // session_unregister( "session_region" ) is replaced by unset($_SESSION['session_region'])
    unset($_SESSION['session_region']);
    
    // session_register( "session_region" ) is replaced by assignment to $_SESSION
    $_SESSION['session_region'] = $region;
}
?>

<?php include "ssi/top.php"; ?>

<?php if( !$session_iscorp ) { ?>
<DIV style="text-align: center"> 
<b><font size=+1 color='red'>
<br>Face Masks and a health self assessment are required for all ESI training class.
<Br><br>
If you think you may have Covid-19, please post pone your training and call ESI to reschedule. </b></font>
</DIV><br><br>
<?php } ?>

 <strong><span class="title">CLASS REGISTRATION</span></strong> &nbsp; &nbsp; &nbsp;<span class="copy"><em>(Step 1 of 3)</em></span>
 <br><hr>


<?php if( $session_iscorp || 1 == 1 ) { // The '1 == 1' makes this block always execute ?>

 <form name="myform" action="individual_registration2.php" method="post" onsubmit='return checkForm( this )' >

 <table cellpadding="0" cellspacing="0" border="0" width="100%" class="table3">

<?php if( !$session_iscorp ) { ?>
<tr>
<td valign="top">
 <table cellpadding="0" cellspacing="6" border="0" class="table3">
 <tr>
 <td valign="top"><span class="copy">Select the borough where you would like to take the class:</span><br>
<select name="borough" style="font-size: 10px; font-family: verdana;">
<option value="">Any</option>
 <option value="Bronx">The Bronx</option>
 <option value="Brooklyn">Brooklyn</option>
 <option value="Manhattan">Manhattan</option>
 <option value="Queens">Queens</option>
<option value="Staten Island">Staten Island</option>
</select>
 </td>
 </tr>
</table>
</td>
 </tr>
<?php } ?>
<tr>
<td valign="top">
<?php if( !$session_iscorp ) { ?>
<font color='red'><i>This training does not meet the requirements for school nurses. <br>For nurses and healthcare provider training information, please click <a href='https://<?php echo SUB_DOE. ".". URL_WITHOUT_SUBDOMAIN;?>/nurses.php'>here</a><br> <a href='https://<?php echo SUB_DOE. ".". URL_WITHOUT_SUBDOMAIN;?>/nurses.php'>https://<?php echo SUB_DOE. ".". URL_WITHOUT_SUBDOMAIN;?>/nurses.php</a></i></font>
<?php } ?>
</td></tr>
<tr>
 <td valign="top">
 <table cellpadding="0" cellspacing="6" border="0">
 <tr>
 <td valign="top"><span class="copy">Select the class you would like to take:<br>
 <?php 
// Set default if no class is selected
if( !$class ) {
 $selected_reg = "CHECKED";
}

foreach ($class_names as $code => $name) { 
    $is_allowed = true;
    
    // Non-corp user restriction logic
    if( !$session_iscorp ) {
        // Skip certain codes unless they are 'reg'
        if( $code != 'reg' ) {
            if( $code == "dd" ) {
                $is_allowed = false; // "dd" (No AED) is only for admin/internal use outside this public form
            }
            if( !isOverallAdmin() ) { // Assumed function
                $is_allowed = false;
            }
        }
    }
    
    if( !$is_allowed ) {
        continue;
    }
    
    // Determine if the radio button should be checked
    $checked_attr = ($class == $code || ($code == 'reg' && !$class)) ? 'CHECKED' : '';
    $class_name_safe = htmlspecialchars($name);
?>
<input type='radio' name='classname' <?php echo $checked_attr; ?> value="<?php echo htmlspecialchars($code); ?>"><?php echo $class_name_safe; ?><br>
<?php } ?>
<a href='#' onClick='javascript:window.open( "classhelp.html", "_blank", "width=400,height=600,scrollbars=yes" )'><span class='copy'>Course Descriptions</span></a>

<p><input type='image' name='go' border=0 src="../images/button_continue.gif" alt="Continue">
 </td>
 </tr>
 </table>
 </td>
 </tr>
 </table>
</form>
<?php } else { ?>
<br><br>Sorry, registration is not available at this time. Please try again later. Please call 212-564-6833 with any questions.
<?php } ?>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
 </td>
<td valign="top" width="15"><img src="../images/dotclear.gif" width="10" alt=""></td>
 </tr>
</table>
<br><br>
</div>
<script language='javascript'>
// Assuming getSelectedRadioValue is a function defined in an included script or elsewhere
function checkForm( frm )
{
var val = getSelectedRadioValue( frm.elements["classname"] );
if( !val )
 {
 alert( "Please choose a class." );
 return false;
 }

 if( val == "dd" )
 {
 // Note: This confirmation is currently unreachable for non-admin/non-corp users due to PHP logic,
// but it remains here for completeness based on the original script's JS.
 if( confirm( "Please note: This class does not include AED training. " ) == false ) 
 return false;
}
return true;
}
</script>
<script type="text/javascript" src="webticker_lib.js"></script>
</body>
</html>