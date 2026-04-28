<?php
require_once('mysql.php');

// Safely retrieve external variables
$session_userid = $session_userid ?? null; // Assumed global session variable
$cancelreason = $_REQUEST['cancelreason'] ?? ($cancelreason ?? null);
$id = $_REQUEST['id'] ?? ($id ?? null);
$covid = $_REQUEST['covid'] ?? ($covid ?? null);
$dontsend = $_REQUEST['dontsend'] ?? ($dontsend ?? null);
$url_without_subdomain = $GLOBALS['URL_WITHOUT_SUBDOMAIN'] ?? URL_WITHOUT_SUBDOMAIN ?? 'example.com';
$sub_doe = $GLOBALS['SUB_DOE'] ?? SUB_DOE ?? 'example.com';
$link = $GLOBALS['link'] ?? $link; // Assuming the mysqli link is available globally

$db_link = $link; // Use mysqli connection link

// Safety: Escape user input for SQL
$safe_session_userid = mysqli_real_escape_string($db_link, $session_userid);
$safe_cancelreason = mysqli_real_escape_string($db_link, $cancelreason);
$safe_id = (int)$id;

// --- 1. Update Class Status (Secure SQL) ---
$sql = "UPDATE class 
        SET cancelledby = '{$safe_session_userid}', 
            deleted = '1', 
            cancelreason = '{$safe_cancelreason}', 
            canceldate = NOW() 
        WHERE id = '{$safe_id}' 
        LIMIT 1";

$ret = mysqli_query($db_link, $sql);

// --- 2. Fetch necessary data for emails ---
// Assuming these functions exist and are safe: getClassRow, getCompanyRow, getTrainers, get_attendees, getResponderRow, getUserEmail, sendMail
$classrow = getClassRow( $safe_id );
$comrow = getCompanyRow( $classrow['companyid'] ?? null );
$trainers = getTrainers( $safe_id );
$attendees = get_attendees( $safe_id );

$body = "";
$class_startdate = htmlspecialchars($classrow['startdate'] ?? 'a scheduled date');
$company_name = htmlspecialchars($comrow['companyname'] ?? 'The Company');
$school_code = htmlspecialchars($comrow['schoolcode'] ?? '');

if( $covid )
{
    // --- COVID Cancellation Email Body ---
    $body = "Your school is currently in an area of concern for COVID transmissions. Out of an abundance of caution, respect to the safety of your staff and our employees, your CPR class on {$class_startdate} at {$company_name} {$school_code} has been cancelled. Please contact our office to reschedule your CPR training to a time when the school is open, and the area is no longer on the watch list.

Thank you, 
Emergency Skills, Inc.
";

}
else
{
    // --- Standard Cancellation Email Body ---
    $body = "{$company_name} has cancelled the CPR/AED scheduled for {$class_startdate} at {$company_name} {$school_code}. Please visit our website https://{$sub_doe}.{$url_without_subdomain} to reschedule.

Thank you, 
Emergency Skills, Inc.
";
}

// --- 3. Send Notifications ---
if( !$dontsend )
{
    // A. Email Attendees (if not corporate)
    if( !($comrow['iscorp'] ?? false) )
    {
        foreach( $attendees as $arow )
        {
            $rrow = getResponderRow( $arow['responderid'] ?? null );
            sendMail( $rrow['email'] ?? null, "CPR/AED Training Cancelled", $body, "info@emergencyskills.com" ); 
        }
    }
    
    // B. Email Assigned Trainers
    foreach( $trainers as $trow )
    {
        $em = getUserEmail( $trow['trainerid'] ?? null );
        sendMail( $em, "CPR/AED Training Cancelled", $body, "info@emergencyskills.com" ); 
    }
    
    // C. Email Company/Principal Contacts
    $em = $classrow['email'] ?? null; 
    $em2 = getUserEmail( $comrow['addedby'] ?? null );
    
    foreach( array( $em2, $em, $classrow['principalemail'] ?? null ) as $email )
    {
        if( $email ) // dontsend check is redundant here since it's already wrapped, but preserved logic: if( $email && !$dontsend )
        {
            sendMail( $email, "CPR/AED Training Cancelled", $body, "info@emergencyskills.com" );
        }
    }
    
    // D. Email internal staff/admins (always sent)
    $internal_body = $body . "\n\nView the class here: https://{$sub_doe}.{$url_without_subdomain}/class_detail.php?id={$safe_id}";
    sendMail( "sarahg@emergencyskills.com, barbara@emergencyskills.com, kevin@emergencyskills.com, dfunnye@emergencyskills.com, rebekah@emergencyskills.com", "CPR/AED Training Cancelled", $internal_body, "info@emergencyskills.com" ); 
}
?>
<?php include "ssi/top.php"; ?>
<strong><span class="title">Thank you.</span></strong><p>
Your class has been cancelled.

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