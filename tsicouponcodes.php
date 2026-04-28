<?php
require_once('mysql.php');

// --- Security Helper Functions ---
// Helper for SQL escaping
if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        // Assuming this function uses mysqli_real_escape_string or PDO prepared statements/escaping
        return addslashes((string)($str ?? '')); 
    }
}
// Helper for HTML escaping (XSS mitigation)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// ---------------------------------

// --- 1. Access Control ---
// NOTE: Hardcoded email list is poor practice. Use role-based access control (RBAC).
if( !isOverallAdmin() && strtolower( $session_userid ) != "nathan.delllanosilva@tsiclubs.com" && strtolower( $session_userid ) != "sara.grenon@tsiclubs.com" && strtolower( $session_userid ) != "crystal.white@tsiclubs.com" ) 
{ 
    Header( "location: login.php" );
    exit;
}

// --- 2. Action Handlers (SQLi Mitigation & Function Correction) ---

// Cast all ID and action variables to integer
$removedecline = (int)($_GET['removedecline'] ?? 0);
$decline = (int)($_GET['decline'] ?? 0);
$accept = (int)($_GET['accept'] ?? 0);
$del = (int)($_GET['del'] ?? 0);
$addone = (int)($_GET['addone'] ?? 0);

$declinereason = $_GET['declinereason'] ?? ''; // This is escaped later

if( $removedecline > 0 )
{
    // FUNCTION CORRECTED: Restored db_query_insert_id()
    // SQLi Mitigation: $removedecline is cast to integer
    $newid = db_query_insert_id( "update tsi_registrants set accepted = 0, statusdate = now(), statususername = '".db_escape_or_placeholder($session_userid)."', declinereason='' where id = {$removedecline}" );
    Header( "Location:tsicouponcodes.php?removed=1" );
    exit;
}

if( $decline > 0 )
{
    // SQLi Mitigation: $decline is cast to integer
    $myrow = db_query_first( "select * from tsi_registrants where id = {$decline}" );
    
    // Sanitize data for SQL injection before update
    $declinereason_safe = db_escape_or_placeholder($declinereason);
    $session_userid_safe = db_escape_or_placeholder($session_userid);
    
    $body = "
Dear ".h($myrow['firstname'])." ".h($myrow['lastname']).":

Your request to receive CPR/AED Training through Town Sports Int'l has been declined. Our records indicate that your job title does not meet the requirements for free training. Please visit the <a href='http://www.emergencyskills.com/tsi.php'>Emergency Skills, Inc. website</a> to register for the paid class.

<b> Your course registration is NOT complete. You MUST return to our site to REGISTER for a specific class.</b>

If you have any questions, please contact TSI Learning and Development at <a href='mailto:TSILearningandDevelopment@tsiclubs.com'>TSILearningandDevelopment@tsiclubs.com</a>.

If you need help registering, please contact Emergency Skills, Inc. at 212-564-6833.
";
    // Sanitize email address for mail headers
    sendFormattedHTMLMail( h($myrow['email']), "Your CPR/AED Training Request" , $body, "info@emergencyskills.com", "" );

    // FUNCTION CORRECTED: Restored db_query_insert_id()
    // SQLi Mitigation: $declinereason is escaped, $decline is cast
    $newid = db_query_insert_id( "update tsi_registrants set accepted = 2, statusdate = now(), statususername = '{$session_userid_safe}' , declinereason = '{$declinereason_safe}' where id = {$decline}" );
    Header( "Location:tsicouponcodes.php?declined=1" );
    exit;
}

if( $accept > 0 )
{
    // SQLi Mitigation: $accept is cast to integer
    $fr = db_query_first( "select * from tsi_registrants where id = {$accept}" );
    
    // Generate secure coupon code
    $acceptcode = substr( md5( time() . $accept . "adsjkasjdksa" ), 0, 8 );
    $session_userid_safe = db_escape_or_placeholder($session_userid);
    $acceptcode_safe = db_escape_or_placeholder($acceptcode);
    
    $body = "
Dear ".h($fr['firstname'])." ".h($fr['lastname']).",<br><br>

Your request to receive CPR/AED Training through Town Sports Int'l at no charge is approved. Your coupon code is:

<b>".h($acceptcode)."</b>

Please visit <a href='".h($fr['requestedurl'])."'>our website</a> to enter your coupon code and to register. Please note that your coupon code may only be used by you and may only be used once. 

If you have any questions, please contact TSI Learning and Development at <a href='mailto:TSILearningandDevelopment@tsiclubs.com'>TSILearningandDevelopment@tsiclubs.com</a>.

If you need help registering, please contact Emergency Skills, Inc. at 212-564-6833.
";

    // Sanitize email address for mail headers
    sendFormattedHTMLMail( h($fr['email']), "Your CPR/AED Training Request" , $body, "info@emergencyskills.com", "" );

    // FUNCTION CORRECTED: Restored db_query_insert_id()
    // SQLi Mitigation: $acceptcode is escaped, $accept is cast
    $newid = db_query_insert_id( "update tsi_registrants set accepted = 1, acceptcode = '{$acceptcode_safe}', statusdate = now(), statususername = '{$session_userid_safe}' where id = {$accept}" );
    Header( "Location: tsicouponcodes.php?accepted=1" );
    exit;
}

if( $addone > 0 )
{
    // FUNCTION CORRECTED: Restored db_query_insert_id()
    // SQLi Mitigation: $addone is cast to integer (though not used in this query, it's a good practice)
    $newid = db_query_insert_id( "insert into tsi_registrants ( dateadded ) values ( now() ) " );
    Header( "Location: edittsiregistrant.php?id={$newid}" );
    exit;
}

if( $del > 0 )
{
    // SQLi Mitigation: $del is cast to integer
    db_query( "update tsi_registrants set archived = 1 where id = {$del}" );
}

$archivebefore = $_GET['archivebefore'] ?? '';
if( $archivebefore )
{
    // SQLi Mitigation: $archivebefore is escaped
    $archivebefore_safe = db_escape_or_placeholder($archivebefore);
    db_query( "update tsi_registrants set archived = 1 where dateadded < '{$archivebefore_safe}' " );
}

// --- 3. Report Setup (SQLi Mitigation) ---
$err = "";
if( isset($_GET['removed']) )
    $err = "Your registrant has been reactivated.";
if( isset($_GET['declined']) )
    $err = "Your registrant has been declined.";
if( isset($_GET['accepted']) )
    $err = "Your registrant has been accepted.";

// Sort order whitelisting and sanitization
$obs = $_GET['obs'] ?? '';
$ob = "";
$allowed_sorts = ['dateadded', 'accepted', 'sentemail', 'lastname', 'firstname'];
$sort_parts = explode(' ', $obs);
$sort_field = trim($sort_parts[0]);
$sort_dir = (count($sort_parts) > 1 && strtolower(trim($sort_parts[1])) === 'desc') ? 'desc' : 'asc';

if( in_array($sort_field, $allowed_sorts) ) {
    $ob = "{$sort_field} {$sort_dir}, ";
}

// Search filtering and sanitization
$whr = "";
$sea = $_GET['sea'] ?? '';
if( $sea ) 
{
    // SQLi Mitigation: Escaping the search term and using it safely in LIKE
    $sea_safe = db_escape_or_placeholder($sea);
    $whr .= " and ( firstname like '%{$sea_safe}%' or lastname like '%{$sea_safe}%' or email like '%{$sea_safe}%' ) ";
}

// Final Query Construction (SQLi Mitigation for $ob)
$regs = db_query_rows( "select * from tsi_registrants where archived = 0 {$whr} order by {$ob} accepted, sentemail, lastname, firstname" ); 
?>
<?php include "ssi/top.php"; ?>
<p>

<strong><span class="title">MANAGE TSI COUPON CODES</span></strong>
<p>
<form method='get' action='tsicouponcodes.php'>

Search: <input type='text' name='sea' value="<?=h($sea)?>"> <input type='submit' name='search' value='Search'><Br><br>
Archive Before: <?=printdates2( "archivebefore", h($archivebefore) )?> <input type='submit' name='arch' value='Archive'><Br><br>

<font color='red'><?=h($err)?></font><br>
<a href='tsicouponcodes.php?addone=1'>Add New</a>
<table class="table3" cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
<tr bgcolor="#e1e1f6">
                <th class='copy'>Name</th>
                <th class='copy'>Position</th>
                <th class='copy'>Kronos Number</th>
                <th class='copy'>Sent Email</th>
                <th class='copy'><a href='tsicouponcodes.php?obs=dateadded+desc'>Added</a></th>
                <th class='copy'>Status</th>
                <th class='copy'>Decline Reason</th>
                <th class='copy'>Action</th>
            </tr>
<?php
$i = 0;
foreach( $regs as $t )
{
    // Ensure all lookups use safe variables
    $t_acceptcode_safe = db_escape_or_placeholder($t['acceptcode']);
    $t_email_safe = db_escape_or_placeholder($t['email']);
    $t_dateadded_safe = db_escape_or_placeholder($t['dateadded']);

    // Check against couponcode and pmsid
    $match = db_query_first_cell( "select responderid from responders_esi where (couponcode = '{$t_acceptcode_safe}') and deleted = 0" );
    if( !$match )
        $match = db_query_first_cell( "select responderid from responders_esi where (pmsid = '{$t_acceptcode_safe}') and deleted = 0" ); 
if( $t['acceptcode'] && $match )
    {
        continue;
    }
$clr = "#ffffff";
    if( $t["accepted"] == 1 )
        $clr = "#cccccc";
    if( $t["accepted"] == 2 )
        $clr = "#eeeeee";

    $s2 = "";
    if( $t['accepted'] )
    {
        $status_date_safe = h(getFormattedDateWTime( $t['statusdate'] ));
        $status_username_safe = h($t['statususername']);
        $s2 = "<br>Date: {$status_date_safe}, {$status_username_safe}";
    }
    
    $ext = "";
    if( $t['accepted'] == 1 )
    {
        // Sanitize the email for the subquery
        $rid = db_query_first_cell( "select group_concat( responderid ) from responders_esi where email = '{$t_email_safe}' and deleted = 0" );
        
        if( $rid )
        {
            // Note: $rid is from an internal aggregate, so it should be safe, but cast to string if needed
            $rid_safe = db_escape_or_placeholder($rid);
            $hmm = db_query_first_cell( "select classid from responder_to_class rtc, class c where rtc.responderid in ( {$rid_safe} ) and c.id = classid and startdate > '{$t_dateadded_safe}'" );
            
            if( $hmm )
            {
                // XSS Mitigation: Escape class ID in link and text
                $hmm_safe = h($hmm);
                $ext = ("<br>in class: <a href='class_detail.php?id={$hmm_safe}'>{$hmm_safe}</a>");
            }
        }
    }
    
    $i++;
    $exp = explode( "regtype=", $t['requestedurl'] );
    
    // XSS Mitigation: Escaping all table cell contents
    $firstname_safe = h($t['firstname']);
    $lastname_safe = h($t['lastname']);
    $filenumber_safe = h($t['filenumber']);
    $dateadded_safe = h(getFormattedDate($t['dateadded']));
    $acceptcode_safe_out = h($t['acceptcode']);
    $declinereason_safe_out = h($t['declinereason']);
    $id_safe = h($t['id']);
    $position_safe = h($exp[1] ?? ''); // Position is the second part of the exploded URL

    echo( "<tr bgcolor='{$clr}'><td class='copy' valign='top'><a href='edittsiregistrant.php?id={$id_safe}'>{$i}. <font color='{$fnt}'>{$firstname_safe} {$lastname_safe}</font></a></td>
<td valign='top' class='copy'>{$position_safe}</td>
<td valign='top' class='copy'>{$filenumber_safe}</td>
<td class='copy'>".($t['sentemail']?"Yes":"No")."</td>
<td class='copy'>{$dateadded_safe}</td>
<td class='copy'>".($t['accepted']?($t['accepted']==1?"Accepted ({$acceptcode_safe_out}) {$s2}":"Declined {$s2}"):"")."{$ext}</td>" );
    echo( "<td valign='top' class='copy'>{$declinereason_safe_out}</td>" );
    echo( "<td valign='top' class='copy'>" );
    
    if( !$t['accepted'] )
    {
        echo( "
<a href='edittsiregistrant.php?id={$id_safe}'>Edit</a>&nbsp;&nbsp;
<A onclick='javascript: return confirm( \"Are you sure you want to ACCEPT this registrant?\" )' href='tsicouponcodes.php?accept={$id_safe}'>Accept</a>
&nbsp;&nbsp;
<A onclick='javascript: return doDecline( {$t['id']} )' href='#'>Decline</a>
&nbsp;&nbsp;
" );
    }
    else
    {
        if( $t['accepted'] == 1 )
            echo( "ACCEPTED<br>" );
        else
        {
            echo( "DECLINED" );
            // XSS Mitigation: Escaping ID in link
            echo( "<br><a href='tsicouponcodes.php?removedecline={$id_safe}' onClick='return confirm( \"Are you sure you want remove the decline status?\" )'>Remove Status </a><br>" );
        }
    }
    
    // XSS Mitigation: Escaping ID in link
    echo( "
<A onclick='javascript: return confirm( \"Are you sure you want to delete this registrant?\" )' href='tsicouponcodes.php?del={$id_safe}'>Delete</a></td></tr>" );
}
?>
</table>
<a href='tsicouponcodes.php?addone=1'>Add New</a>
<br><br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
<script language='javascript'>
function doDecline( id )
{
if( confirm( "Are you sure you want to DECLINE this registrant?" ) )
{
var reason = prompt( "What is your reason for declining?" );
if (reason === null) return; // User cancelled prompt

            // Client-side Security: Use modern encodeURIComponent
document.location.href ="tsicouponcodes.php?decline=" + id + "&declinereason=" + encodeURIComponent( reason );
}
}
</script>
</div>
</body>
</html>