<?php
$nologinrequired = true;
require_once('mysql.php');

// --- Input Sanitization for HTML Output ---
// Use null-coalescing operator (??) for safety and apply htmlspecialchars for XSS prevention.
$err = $_GET['err'] ?? $_POST['err'] ?? '';
$userid_display = htmlspecialchars($_GET['userid'] ?? $_POST['userid'] ?? '');
$salutation_display = htmlspecialchars($_POST['salutation'] ?? '');
$borough_display = htmlspecialchars($_POST['borough'] ?? '');
$companyid_display = htmlspecialchars($_POST['companyid'] ?? '');

$first_name_display = htmlspecialchars($_POST['first_name'] ?? '');
$mi_display = htmlspecialchars($_POST['mi'] ?? '');
$last_name_display = htmlspecialchars($_POST['last_name'] ?? '');
$title_display = htmlspecialchars($_POST['title'] ?? '');
$department_display = htmlspecialchars($_POST['department'] ?? '');
$phone_display = htmlspecialchars($_POST['phone'] ?? '');
$phone_ext_display = htmlspecialchars($_POST['phone_ext'] ?? '');
$fax_display = htmlspecialchars($_POST['fax'] ?? '');
$email2_display = htmlspecialchars($_POST['email2'] ?? '');

$confirm = '';
if ($err === "duplicate") {
// Sanitize the $userid variable for the URL parameter as well
$safe_link_userid = urlencode($userid_display);
$confirm = "<div id='error'>The email address you specified already exists. Click <a href='mailpass.php?userid={$safe_link_userid}'>here</a> to have your password emailed to you. </div>";
}

if ($err === "password") {
$confirm = "<div id='error'>The password and confirmation password did not match.</div>";
}

if ($err === "nocompany") {
$confirm = "<div id='error'>You did not select the school that you belong to.</div>";
}

// Prepare SELECTED attributes securely
$selected_Mr = ($salutation_display === 'Mr') ? 'SELECTED' : '';
$selected_Mrs = ($salutation_display === 'Mrs') ? 'SELECTED' : '';
$selected_Ms = ($salutation_display === 'Ms') ? 'SELECTED' : '';
$selected_Miss = ($salutation_display === 'Miss') ? 'SELECTED' : '';
$selected_Dr = ($salutation_display === 'Dr') ? 'SELECTED' : '';

$selected_Bronx = ($borough_display === 'Bronx') ? 'SELECTED' : '';
$selected_Brooklyn = ($borough_display === 'Brooklyn') ? 'SELECTED' : '';
$selected_Manhattan = ($borough_display === 'Manhattan') ? 'SELECTED' : '';
$selected_Queens = ($borough_display === 'Queens') ? 'SELECTED' : '';
$selected_Staten_Island = ($borough_display === 'Staten Island') ? 'SELECTED' : '';

// Dynamic variable names should use the variable syntax correctly
$selected_company = 'selected_' . $companyid_display;
${$selected_company} = 'SELECTED';

?>

<?php include "ssi/top.php"; ?>
<?php include "getschooldropdown.php"; ?>

<strong><span class="title">CREATE YOUR PROFILE</span></strong>

<?php echo $confirm; ?>
<BR><BR>
<hr>
<strong>Contact Information:</strong><BR><BR>

<form name="myform" method="post" action="create_profile_thanks.php" onSubmit='return checkSubmit()'>
<input type="hidden" name="action" value="create">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top"><span class="copy">Salutation:</span><br>
<select name="salutation" style="font-size: 10px; font-family: verdana;">
<option <?php echo $selected_Mr; ?> value="Mr">Mr.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
<option <?php echo $selected_Mrs; ?> value="Mrs">Mrs.</option>
<option <?php echo $selected_Ms; ?> value="Ms">Ms.</option>
<option <?php echo $selected_Miss; ?> value="Miss">Miss</option>
<option <?php echo $selected_Dr; ?> value="Dr">Dr.</option>
</select>
</td>
<td valign="top"><span class="copy">First Name:</span><br>
<input name="first_name" value="<?php echo $first_name_display; ?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">MI:</span><br>
<input name="mi" value="<?php echo $mi_display; ?>" type="text" id="" size="1" maxlength="1" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Last Name:</span><br>
<input name="last_name" value="<?php echo $last_name_display; ?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
</table>
</td>
</tr>

<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top"><span class="copy">Your Borough:</span><br>

<select name="borough" id='borough' onChange="changeBorough();" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<option <?php echo $selected_Bronx; ?> value="Bronx">The Bronx</option>
<option <?php echo $selected_Brooklyn; ?> value="Brooklyn">Brooklyn</option>
<option <?php echo $selected_Manhattan; ?> value="Manhattan">Manhattan</option>
<option <?php echo $selected_Queens; ?> value="Queens">Queens</option>
<option <?php echo $selected_Staten_Island; ?> value="Staten Island">Staten Island</option>

</select>
</td>
<td> <span class='copy'><?php echo getSchoolStr("School"); ?> Name: </span><br> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='changeBorough()'> <input type='button' value='Search' class=copy onClick='changeBorough()'>

<td valign="top" id='school_select'>

</td>

</tr>
</table>
</td>
</tr>

<tr>
<td valign="top">
<table border="0" cellpadding="0" cellspacing="6">
<tbody>
<tr>
<td valign="top"><span class="copy">Your Title:</span><br>
<input name="title" value="<?php echo $title_display; ?>" type="text" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>

<td valign="top"><span class="copy">Department:</span><br>
<input name="department" value="<?php echo $department_display; ?>" id="" size="20" maxlength="30" style="font-family: verdana; font-size: 11px; line-height: 13px;" type="text"></td>
<td valign="top"><span class="copy">Phone Number:</span><br>
<input name="phone" type="text" value="<?php echo $phone_display; ?>" id="" size="15" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Ext:</span><br>
<input name="phone_ext" value="<?php echo $phone_ext_display; ?>" id="" size="4" maxlength="6" style="font-family: verdana; font-size: 11px; line-height: 13px;" type="text"></td>

</tr>

</tbody>
</table>

</td>
</tr>

<tr>
<td>
<table border="0" cellpadding="0" cellspacing="6">
<tbody>
<tr>
<td valign="bottom"><span class="copy">Fax Number:</span><br>

<input name="fax" value="<?php echo $fax_display; ?>" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px;" type="text"></td>
<td valign="bottom"><span class="copy">Email Address:</span><br>
<input name="userid" value="<?php echo $userid_display; ?>" type="text" id="" size="20" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="bottom"><span class="copy">Re-type Email Address:</span><br>
<input name="email2" value="<?php echo $email2_display; ?>" type="text" id="" size="20" maxlength="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>

</tr>

</tbody>
</table>
</td>
</tr>
<tr>
<td valign="top">

<hr>

</td>
</tr>

<tr>
<td valign="top">
<span class="copy"><strong>Log In Information:</strong>
<br>
Please create a password to log in on the Emergency Skills website.</span><BR><BR>
</td>
</tr>

<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top" align="right"><span class="copy">Password:</span></td>
<td><input name="password" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<tr>
<td valign="top" align="right"><span class="copy">Re-type Password:</span></td>
<td><input name="confirm_password" type="password" id="" size="20" maxlength="20" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
</table>
</td>
</tr>

<tr>
<td valign="top">

<hr>

</td>
</tr>

<tr>
<td valign="top" class='copy'><input type='checkbox' name='terms' value='1'> I agree to the ESI <a href='#' onClick='javascript:window.open( "terms.php", "_blank", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600" )'>Terms and conditions</a><br><br>
</td>
</tr>
<tr>
<td valign="top">
<input type="image" src="images/button_createprofile.gif">
</td>
</tr>
</table>
</form>

<br><br><br><br>

<?php include "ssi/footer.php"; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>

<script type="text/javascript">
function isPhoneNumber(s) {
// (Existing function, kept as-is)
return true;
}
var b = document.myform.borough.value;
if (b) {
changeBorough();
}

function checkSubmit() {
if (document.myform.terms.checked == false) {
alert("Please agree to our terms and conditions.");
return false;
}
if (document.myform.first_name.value == "") {
alert("First name is required.");
return false;
}
if (document.myform.last_name.value == "") {
alert("Last name is required.");
return false;
}
if (document.myform.userid.value == "") {
alert("Email is required.");
return false;
}
if (document.myform.userid.value != document.myform.email2.value) {
alert("Email addresses do not match.");
return false;
}
if (document.myform.phone.value == "") {
alert("Please provide a valid phone number.");
return false;
}
if (document.myform.password.value == "") {
alert("Please provide a password.");
return false;
}
if (document.myform.confirm_password.value == "") {
alert("Please provide a confirmation password.");
return false;
}
if (document.myform.confirm_password.value != document.myform.password.value) {
alert("Your passwords do not match.");
return false;
}
if (document.myform.borough.selectedIndex <= 0) {
alert("Please provide your borough.");
return false;
}
return true;
}
</script>

</body>

</html>