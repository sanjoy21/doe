<?php 
$nologinrequired = true;
require "mysql.php";

// 1. Sanitize incoming variables (assuming they come from $_POST)
$submit = $_POST['submit'] ?? null;
$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$building = $_POST['building'] ?? null;
$floor = $_POST['floor'] ?? null;

$err = null;

if ($submit) {
    if ($name) {
        // Get IP address and ensure it is sanitized for safe database insertion
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        db_query(
            "INSERT INTO nbcsignups (dateadded, ipaddress, name, email, building, floor) VALUES (NOW(), ?, ?, ?, ?, ?)",
            array($ip_address, $name, $email, $building, $floor)
        );
        $err = "<font color='red'>Thanks! Your interest has been noted.</font>";
    }
}

// 3. XSS MITIGATION: Prepare variables for HTML output
$name_safe = htmlspecialchars($name ?? '');
$email_safe = htmlspecialchars($email ?? '');
$floor_safe = htmlspecialchars($floor ?? '');
$building_safe = htmlspecialchars($building ?? '');
$err_safe = $err; // $err is currently hardcoded safely, but if it contained user input, htmlspecialchars would be needed here too.

?>
<?php include "ssi/top.php"; ?>
<script language='javascript'>
function checkSubmit()
{
return true;
}
</script>
<form name="myform" id="myform" method="post" onSubmit='return checkSubmit()'>
<strong><span class="title">NBC Signup</span></strong>

<?php if ($err_safe) { ?>
<?php echo $err_safe; ?>
<?php } else { ?>
<BR><hr>
I am interested in CPR/AED training:<br><br>
<table cellpadding="0" cellspacing="0" border="0" width="476" class="table3">

<table cellpadding="0" cellspacing="0" border="0" width="100%" id="tsiform">
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="6" border="0">
<tr>
<td valign="top"><span class="copy">Name:</span><br>
<input name="name" type="text" id="" value="<?php echo $name_safe; ?>" size="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
<td valign="top"><span class="copy">Email: </span><br>
<input name="email" type="text" id="" value="<?php echo $email_safe; ?>" size="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
<tr>

<td valign="top"><span class="copy">Building:</span><br>
<select style="font-family: verdana; font-size: 11px; line-height: 13px; width: 300px" name='building'>
<option value=''>Please Choose</option>
<option value='10 Rock' <?php if ($building_safe === '10 Rock') echo 'selected'; ?>>10 Rock</option>
<option value='30 Rock' <?php if ($building_safe === '30 Rock') echo 'selected'; ?>>30 Rock</option>
<option value='45 Rock' <?php if ($building_safe === '45 Rock') echo 'selected'; ?>>45 Rock</option>
<option value='1221 Ave of Americas' <?php if ($building_safe === '1221 Ave of Americas') echo 'selected'; ?>>1221 Ave of Americas</option>
<option value='584 Broadway' <?php if ($building_safe === '584 Broadway') echo 'selected'; ?>>584 Broadway</option>
<option value='5 Times Square' <?php if ($building_safe === '5 Times Square') echo 'selected'; ?>>5 Times Square</option>
<option value='620 Fifth' <?php if ($building_safe === '620 Fifth') echo 'selected'; ?>>620 Fifth</option>
</select>
<td valign="top"><span class="copy">Floor: </span><br>
<input name="floor" type="text" id="" value="<?php echo $floor_safe; ?>" size="40" style="font-family: verdana; font-size: 11px; line-height: 13px"></td>
</tr>
</table>
</td>
</tr>

<tr>
<td valign="top">
<input type='submit' name='submit' value='Signup' >
</td>
</tr>
</table>
<?php } ?>
<br><br><br><br>

<?php include "ssi/footer.php" ; ?>

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