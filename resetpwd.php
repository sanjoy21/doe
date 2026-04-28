<?php
$nologinrequired = true;
$donewui = true;
require_once "mysql.php";

if( !isset($mobile_browser) || !$mobile_browser ) {
    include "ssi/top.php";
}

// Initialize variables to avoid undefined variable warnings
$err = '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$code = isset($_GET['code']) ? $_GET['code'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

// Check if the form was submitted
$form_submitted = isset($_POST['update']);

// Get reset code and email from GET parameters
if( isset($_GET['code']) ) {
    $code = $_GET['code'];
}
if( isset($_GET['email']) ) {
    $email = $_GET['email'];
}

// Escape the parameters for database query
$escaped_code = mysql_escape_string($code);
$escaped_email = mysql_escape_string($email);

$row = db_query_first("SELECT * FROM user WHERE resetcode = '" . $escaped_code . "' AND userid = '" . $escaped_email . "' AND inactive = 0");

if( $form_submitted && $password && isset($row["userid"]) && $row["userid"] ) {
    // Update password
    db_query("UPDATE user SET resetcode = '', password = '" . mysql_escape_string($password) . "' WHERE resetcode = '" . $escaped_code . "' AND userid = '" . $escaped_email . "' AND inactive = 0");
    $err = "<font color='red'>Your password has now been updated. Please return <a href='/login.php'>here</a> to log in.</font>";
} else if( !isset($row["userid"]) || !$row["userid"] ) {
    $err = "<font color='red'>No matching email/code found.</font>";
}
?>

<p class="Titles"><strong><br>ALIVE!net (new)</strong></p>
<table width="100%" border="0" cellspacing="0" cellpadding="4" class="table3">
    <tr>
        <td colspan='2'>
            <p>Enter your new password. <br><br><i>Note: It must be at least 10 characters long, contain at least one upper case letter, contain at least one lower case letter, and contain at least one digit.</i><br><br></p>
        </td>
    </tr>
    <?php if( empty($err) ) { ?>  
    <form method="post" onSubmit="return checkOK(this)">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
        <tr>
            <td><div align="right" class="TitlesCopy2">Password:</div></td>
            <td><input name="password" type="password" id="password" size="40"></td>
        </tr>
        <tr>
            <td><div align="right" class="TitlesCopy2">Confirm Password:</div></td>
            <td><input name="confirm_password" type="password" id="confirm_password" size="40"></td>
        </tr>
        <tr>
            <td><div align="right"></div></td>
            <td>
                <input type="submit" name="update" value="Update Password">
            </td>
        </tr>
    </form>
    <?php } else { ?>
    <tr><td colspan='2'><?php echo $err; ?></td></tr>
    <?php } ?>
</table>
<p>&nbsp;</p>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<script language='javascript'>
function checkOK(frm) {
    if (frm.password.value != frm.confirm_password.value) {
        alert("Passwords do not match.");
        return false;
    }
    if (frm.password.value.length < 10) {
        alert("Passwords must be at least 10 characters.");
        return false;
    }
    // Add password complexity validation
    // var password = frm.password.value;
    // var hasUpperCase = /[A-Z]/.test(password);
    // var hasLowerCase = /[a-z]/.test(password);
    // var hasDigit = /[0-9]/.test(password);
    
    // if (!hasUpperCase) {
    //     alert("Password must contain at least one upper case letter.");
    //     return false;
    // }
    // if (!hasLowerCase) {
    //     alert("Password must contain at least one lower case letter.");
    //     return false;
    // }
    // if (!hasDigit) {
    //     alert("Password must contain at least one digit.");
    //     return false;
    // }
    return true;
}
</script>

<?php 
if( (!isset($mobile_browser) || !$mobile_browser) ) {
    include "ssi/footer.php";
}
?>
</span>
</td>
<td valign="top" width="15"><img src="<?php echo WEB_ROOT; ?>/images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>