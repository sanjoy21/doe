<?php
$nologinrequired = true;
$donewui = true;
require_once "mysql.php";

require_once "class.phpmailer.php";

function createRandomPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    $pass = '';
    
    for ($i = 0; $i <= 20; $i++) {
        $num = random_int(0, strlen($chars) - 1);
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
    }
    
    return $pass;
}

function SendResetPasswordLink($urow) {
    $email = $urow['userid'];
    
    $mailer = new PHPMailer();
    $SITEURL = "https://" . $_SERVER["SERVER_NAME"];
    
    if (!empty($email) && !empty($urow['first_name']) && !empty($urow['last_name'])) {
        $mailer->AddAddress($email, $urow['first_name'] . " " . $urow['last_name']);
    } else {
        return false;
    }
    
    $mailer->Subject = "Your reset password request at Alive!Net";
    $mailer->From = $from ?? "info@emergencyskills.com";
    $mailer->FromName = "AliveNet";
    $mailer->IsHTML(true);
    $mailer->IsSMTP(); // Added later to fix html format by Sanjoy Dey
    $mailer->Host = 'localhost'; // Added later to fix html format by Sanjoy Dey
    $mailer->SMTPAuth = false; // Added later to fix html format by Sanjoy Dey
    $mailer->Port = 25; // Added later to fix html format by Sanjoy Dey
    
    $link = $SITEURL . '/resetpwd.php?email=' . urlencode($email) . '&code=' . urlencode($urow["resetcode"]);
    
    $mailer->Body = "Hello " . $urow['first_name'] . " " . $urow['last_name'] . ", \r\n\r\n<br><br>" .
        "There was a request to reset your password at <a href='" . $SITEURL . "'>" . $SITEURL . "</a>.\r\n<br><br>" .
        "Please click the link below to complete the request:<br> " .
        "<a href='$link'>$link</a><br><br> " .
        "Regards,<br>\r\n" .
        "Emergency Skills, Inc<br>\r\n";
    
    if (!$mailer->Send()) {
        return false;
    }
    return true;
}


// Initialize variables
$err = "";
$email = $_POST['email'];
$mobile_browser = $_GET['mobile_browser'];
$sent = false;

if ($email) {
    // Use prepared statements or proper escaping
    $escaped_email = mysql_escape_string($email); // Assuming you have this function in mysql.php
    $urow = db_query_first("SELECT * FROM user WHERE userid = '" . $escaped_email . "'");
    
    if (!empty($urow["userid"])) {
        $code = createRandomPassword();
        db_query("UPDATE user SET resetcode = '" . mysql_escape_string($code) . "' WHERE userid = '" . $escaped_email . "' AND inactive = 0");
        $urow["resetcode"] = $code;
        
        if (SendResetPasswordLink($urow)) {
            $sent = true;
            $err = "<font color='red'>An email was sent to that address if a matching user was found.</font><Br>";
        } else {
            $err = "<font color='red'>Mail could not be sent.</font><Br>";
        }
    } else {
        $err = "<font color='red'>No user was found matching that email.</font><Br>";
    }
}

if (!$mobile_browser) {
    include "ssi/top.php";
}
?>

<p class="Titles"><strong><br>ALIVE!net (new)</strong></p>
<table width="100%" border="0" cellspacing="0" cellpadding="4" class="table3">
    <tr>
        <td><div align="right" class="TitlesCopy">Send Password Reset Link to Email below:</div></td>
        <td>&nbsp;</td>
    </tr>
    <form method="post">
        <tr>
            <td colspan='2'><?php echo $err; ?></td>
        </tr>
        <tr>
            <td><div align="right" class="TitlesCopy2">E-MAIL:</div></td>
            <td><input name="email" type="text" id="email" size="40" value="<?php echo htmlspecialchars($email); ?>"></td>
        </tr>
        <tr>
            <td><div align="right"></div></td>
            <td>
                <input name="doSend" type="image" src="images/go_06.gif" width="53" height="25" border="0">
            </td>
        </tr>
    </form>
</table>
<p>&nbsp;</p>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>

<?php 
if (!$mobile_browser) {
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
<script type="text/javascript" src="webticker_lib.js" language="javascript"></script>
<script type='text/javascript'>
if (location.protocol != 'https:') {
    location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
}
</script>
</body>
</html>