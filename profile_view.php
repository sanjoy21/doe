<?php
require_once('mysql.php');

// 1. SQL INJECTION MITIGATION: Ensure $session_id is sanitized/cast.
// Assuming $session_id is expected to be an integer (user ID).
$session_id_safe = (int)$session_id;

// Use a safe database query function (db_query_first_safe) that implements prepared statements.
$sql = "
SELECT u.*, c.*
FROM user AS u, company_esi AS c
WHERE u.id = ?
AND u.companyid = c.id
";

// Pass the query and the parameter(s) separately.
$user = db_query_first($sql, array($session_id_safe));

// Prepare all fields for safe HTML output (XSS Mitigation)
$user_safe = array();
if ($user) {
    // Map database fields to safe, HTML-escaped variables
    $user_safe['first_name']    = htmlspecialchars($user['first_name'] ?? '');
    $user_safe['mi']            = htmlspecialchars($user['mi'] ?? '');
    $user_safe['last_name']     = htmlspecialchars($user['last_name'] ?? '');
    $user_safe['companyname']   = htmlspecialchars($user['companyname'] ?? '');
    $user_safe['title']         = htmlspecialchars($user['title'] ?? '');
    $user_safe['department']    = htmlspecialchars($user['department'] ?? '');
    $user_safe['address']       = htmlspecialchars($user['address'] ?? '');
    $user_safe['city']          = htmlspecialchars($user['city'] ?? '');
    $user_safe['state']         = htmlspecialchars($user['state'] ?? '');
    $user_safe['zip']           = htmlspecialchars($user['zip'] ?? '');
    $user_safe['phone']         = htmlspecialchars($user['phone'] ?? '');
    $user_safe['phone_ext']     = htmlspecialchars($user['phone_ext'] ?? '');
    $user_safe['fax']           = htmlspecialchars($user['fax'] ?? '');
    $user_safe['contactphone']  = htmlspecialchars($user['contactphone'] ?? '');
    $user_safe['userid']        = htmlspecialchars($user['userid'] ?? ''); // Email
} else {
    // Handle case where user isn't found
    die("User profile not found.");
}

// Principal phone number with optional extension (using safe variables)
$phone_display = $user_safe['phone'];
if ($user_safe['phone_ext']) {
    $phone_display .= " x" . $user_safe['phone_ext'];
}

?>

<?php include "ssi/top.php"; ?>

<strong><span class="title">MY PROFILE</span></strong>
<BR><hr>
<BR>

<table cellpadding="0" cellspacing="5" border="0">
<tr>
<td valign="top"><span class="copy"><strong>Name:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['first_name']; ?> <?php echo $user_safe['mi']; ?> <?php echo $user_safe['last_name']; ?></span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong><?php echo getSchoolStr( "School" ); ?> Name:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['companyname']; ?></span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Title:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['title']; ?></span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Department:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['department']; ?></span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong><?php echo getSchoolStr( "School" ); ?> Address:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['address']; ?></span></td>
</tr>
<tr>
<td valign="top"></td>
<td valign="top"><span class="copy"><?php echo $user_safe['city']; ?>, <?php echo $user_safe['state']; ?> <?php echo $user_safe['zip']; ?></span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Phone Number:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $phone_display; ?></span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Fax Number:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['fax']; ?> </span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong><?php echo getSchoolStr( "School" ); ?> Phone Number:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['contactphone']; ?></span></td>
</tr>
<tr>
<td valign="top"><span class="copy"><strong>Email Address:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $user_safe['userid']; ?></span></td>
</tr>
</table>

<p>
<a href="profile_edit.php"><img border="0" src="images/button_editprofile.gif"></a>

<br><br><br><br>

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