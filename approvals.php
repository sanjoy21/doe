<?php 
require "mysql.php";

$url_without_subdomain = URL_WITHOUT_SUBDOMAIN ;
$sub_doe = SUB_DOE ;
$db_link = $link;

if( $toaccept && is_array($toaccept) )
{
    foreach( $toaccept as $tmpid => $accept )
    {
        // Safety: Ensure $tmpid and $accept are integers
        $safe_id = $tmpid;
        $safe_accept = $accept;
        
        // --- Get user row before updating ---
        $therow = db_query_first( "SELECT userid, first_name, last_name FROM user WHERE id = {$safe_id}" );
        
        $subject = "";
        $body = "";
        $recipient = $therow['userid']; // Assuming userid is the email address
        
        if( $safe_accept > 0 )
        {
            // --- Action: Accept User ---
            $subject = "Your Emergency Skills account has been approved!";
            $body = "Congratulations, your Emergency Skills account has been approved. Please click here: 
https://{$sub_doe}.{$url_without_subdomain}
To log in. Thank you!";
            
            // mail() function is used as in the original code
            mail( $recipient, $subject, $body, "From: info@emergencyskills.com" );
        }
        else if( $safe_accept == -1 )
        {
            // --- Action: Deny User (with instructions for DOE email) ---
            $subject = "Your Emergency Skills account has not been approved";
            $body = "Your request to create a user log in has been received. At this time, the DOE requires that you use your official DOE email address to create a log in. Please click this link to create a user profile using your DOE email address.

Click here to create a new account: https://{$sub_doe}.{$url_without_subdomain}/login.php
If you need further assistance, please call ESI directly at: 212-564-6833

Thank You.
ESI
";
            // mail() function is used as in the original code
            mail( $recipient, $subject, $body, "From: info@emergencyskills.com" );
        }
        
        // --- Update user's approval status ---
        db_query( "UPDATE user SET approved = {$safe_accept} WHERE id = {$safe_id}" );
    }
    $err = "<font color='red'>Users updated.</font><br>";
}

// Initialize arrays (though unused later, preserving structure)
$alreadytrain = array();
$trainerarr = array();

include "ssi/top.php";
?>
<h3>Open User Requests</h3>
<?= $err ?>
<form method='post'>
<input type='submit' name='updateusers' value='Update users'><br><br>
<table border=1 cellpadding=4 cellspacing=0 class="table3">
<tr><th>User</th><th>Email</th><th>School</th><th>Action</th></tr>
<?php
// --- Fetch unapproved users ---
$cids = db_query_rows( "SELECT id, first_name, last_name, userid, companyid FROM user WHERE approved = 0 ORDER BY last_name, first_name " );

// Assuming getCompanyName() exists and is safe
foreach( $cids as $crow )
{
    // PHP 8.2 Fix: Quote array keys and use htmlspecialchars for output
    $tmpid = $crow['id'];
    $first_name_safe = htmlspecialchars($crow['first_name']);
    $last_name_safe = htmlspecialchars($crow['last_name']);
    $userid_safe = htmlspecialchars($crow['userid']);
    $company_name = htmlspecialchars(getCompanyName( $crow['companyid']));
    
    // Output HTML Table Row
    echo( "<tr><td valign='top'><A target=_blank href='edituser.php?id={$tmpid}'>{$first_name_safe} {$last_name_safe}</a></td>" );
    echo( "<td valign='top'><A target=_blank href='edituser.php?id={$tmpid}'>{$userid_safe}</a></td>" );
    echo( "<td valign='top'><A target=_blank href='edituser.php?id={$tmpid}'>{$company_name}</a></td>" );
    
    // Radio buttons for action
    echo( "<td><nobr><input type='radio' name='toaccept[{$tmpid}]' value='1'> Accept <input type='radio' name='toaccept[{$tmpid}]' value='-1'> Deny</nobr></td></tr>" );
}
?>
</table>
<input type='submit' name='updateusers' value='Update users'><br><br>

<?php include "ssi/footer.php" ; ?>
</span>
</td>
<td valign="top" width="15"><img src="../images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>