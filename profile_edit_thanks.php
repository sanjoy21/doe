<?php
require_once('mysql.php');

$action = $action ?? null;
$session_id = $session_id ?? null; // Assumed to be set by the session
$old_password = $_POST['old_password'] ?? null;
$new_password = $_POST['password'] ?? null;
$confirm_password = $_POST['confirm_password'] ?? null;

if ($action == "edit") {

    // --- Start Password Validation Block ---
    if ($old_password) {
        
        // 1. Check Old Password
        // Use prepared statements for security (or at least escape strings)
        $safe_session_id = (int)$session_id; 
        $safe_old_password = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $old_password);
        
        $sql = "SELECT password FROM user WHERE id = '{$safe_session_id}' AND password = '{$safe_old_password}'";
        
        $user_verify = db_query_first($sql);

        // Old password doesn't match db
        if (empty($user_verify)) {
            header('Location: profile_edit.php?err=old_password');
            exit;
        }

        // 2. Check Password Confirmation
        if ($new_password !== $confirm_password) {
            header('Location: profile_edit.php?err=password');
            exit;
        }

        // 3. Check for Blank New Password
        if (!$new_password) {
            header('Location: profile_edit.php?err=blank_password');
            exit;
        }
    }
    // --- End Password Validation Block ---

    // --- Build Fields for Update ---
    $fields = array(
        "salutation",
        "first_name",
        "mi",
        "last_name",
        "companyid",
        "title",
        "department",
        "phone",
        "phone_ext",
        "fax",
        "userid",
        "address1", // Added common profile fields that should be updatable
        "address2",
        "city",
        "state",
        "zip",
        "cell",
        "otherphone",
        "otherphoneext"
    );
    
    // Add password field only if a new password was provided and passed checks
    if ($new_password && $old_password) {
        $fields[] = "password";
    }
    
    $sql = get_sql_update("user", $fields, $_POST, "id", $safe_session_id);
    
    $ret = db_query($sql);
    // echo $sql;exit;
}
?>

<?php include "ssi/top.php"; ?>

 <strong><span class="title">Thank you.</span></strong><p>
Your profile has been modified. <a href='calendar.php'>Click here</a> to go to the calendar page.
 <br><br><br><br> <br><br><br><br> <br><br><br><br> <br><br><br><br> <br><br><br><br> <br><br><br><br>
<?php include "ssi/footer.php" ; ?>
 </span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>