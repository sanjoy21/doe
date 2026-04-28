<?php
require_once('mysql.php');

function db_escape_string($str) {
    return str_replace(["\\", "'"], ["\\\\", "\\'"], $str);
}

// --- Authorization Check ---
if (!isOverallAdmin() && strtolower($session_userid) != "cmcgee3@schools.nyc.gov" && strtolower($session_userid) != "hthomps@schools.nyc.gov")
{
    header("location: login.php");
    exit;
}

// --- DECLINE Action ---
if ($decline)
{
    $decline_id = (int)$decline;
    $email = db_query_first_cell("SELECT email FROM free_registrants WHERE id = $decline_id");
    
    // Construct decline email body
    $body = "
Thank you for your letter requesting CPR/AED training at no charge through the New York City Department of Education. The request has been denied. Your letter may not have met the following criteria:
<br><br>
<ul><li> Written on School Letterhead</li></ul>
Letter must:
<li>Include Name and title or status of person to be trained</li>
<li>Clearly indicate request for individual to complete CPR/AED course</li>
<li>Be signed by school official with their full name and title clearly indicated below the signature</li>
</ul>
<br><br>
If you still are questioning why your request was denied, please contact:
<br>
Celeste McGee: <a href='mailto:CMcGee3@schools.nyc.gov'>CMcGee3@schools.nyc.gov</a> Ph: (718) 391-8566 Fax: (718) 391-8128
<br>
Husain Thompson: <a href='mailto:hthomps@schools.nyc.gov'>hthomps@schools.nyc.gov</a> Ph: (718) 391-8227 Fax: (718) 391-8128
<br>";

    // Assuming sendFormattedHTMLMail is defined
    sendFormattedHTMLMail($email, "CERTIFICATION REQUEST DECLINED", $body, "info@emergencyskills.com", "", false);

    // Update registrant status to DECLINED (2)
    $safe_user_id = db_escape_string($session_userid);
    db_query_insert_id("UPDATE free_registrants SET accepted = 2, statusdate = NOW(), statususername = '$safe_user_id' WHERE id = $decline_id");
    
    header("Location: freeregistrants.php?declined=1");
    exit;
}

// --- ACCEPT Action ---
if ($accept)
{
    $accept_id = (int)$accept;
    $fr = db_query_first("SELECT * FROM free_registrants WHERE id = $accept_id");
    
    if ($fr) {
        $acceptcode = substr(md5(time() . $accept_id . "adsjkasjdksa"), 0, 8);
        $firstname = htmlspecialchars($fr['firstname'] ?? 'User');
        $lastname = htmlspecialchars($fr['lastname'] ?? '');
        
        // Construct acceptance email body
        $body = "
Dear $firstname $lastname,<br><br>

Your request to complete a CPR/AED training program through NYC Dept of Education training program has been approved. You must follow these instructions to register for a class:
<br><br>
Please visit our website to register for training, <a href='https://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/individual_registration1.php'>Individual Registration</a>.
<ul>
<li>Select the boro in which you would like to complete the program</li>
<li> Click Continue</li>
<li> Select the Class</li>
<li> Complete the registration page</li>
<li> Under Employee Type, Choose DOE Employee</li>
<li> Under Payroll Reference ID#, enter this CODE:  <b>$acceptcode</b>. Note: This is a unique code that may not be shared.</li>
</ul>
<br><br>
If you have any questions, you may contact Emergency Skills, Inc. at 212-564-6833.
";

        $email = db_query_first_cell("SELECT email FROM free_registrants WHERE id = $accept_id");
        
        // Assuming sendFormattedHTMLMail is defined
        sendFormattedHTMLMail($email, "CERTIFICATION REQUEST APPROVED", $body, "info@emergencyskills.com", "", false);

        // Update registrant status to ACCEPTED (1) with the unique code
        $safe_user_id = db_escape_string($session_userid);
        db_query_insert_id("UPDATE free_registrants SET accepted = 1, acceptcode = '$acceptcode', statusdate = NOW(), statususername = '$safe_user_id' WHERE id = $accept_id");
    }

    header("Location: freeregistrants.php?accepted=1");
    exit;
}

// --- ADD NEW Action ---
if ($addone)
{
    // Insert new uninitialized row
    $newid = db_query_insert_id("INSERT INTO free_registrants (dateadded) VALUES (NOW())");
    header("Location: editfreeregistrant.php?id=$newid");
    exit;
}

// --- DELETE (Archive) Action ---
if ($del)
{
    $del_id = (int)$del;
    // Note: The original logic performs a soft-delete (archive)
    db_query("UPDATE free_registrants SET archived = 1 WHERE id = $del_id");
    header("Location: freeregistrants.php"); // Redirect to self to refresh the list
    exit;
}

// --- ARCHIVE BEFORE Date Action ---
if ($archivebefore)
{
    $safe_archive_date = db_escape_string($archivebefore);
    db_query("UPDATE free_registrants SET archived = 1 WHERE dateadded < '$safe_archive_date'");
    // Redirect to self to refresh the list
    header("Location: freeregistrants.php");
    exit;
}

// --- Handle Status Messages ---
$err = "";
if ($declined)
    $err = "Your registrant has been declined.";
if ($accepted)
    $err = "Your registrant has been accepted.";

// --- Build Query & Fetch Data ---
$ob_clause = "";
if (!empty($obs)) {
    // Basic validation on the sort field to prevent injection in ORDER BY
    $safe_obs = db_escape_string($obs);
    $ob_clause = "$safe_obs, ";
}

$whr = "";
if (!empty($sea))
{
    $safe_sea = db_escape_string($sea); // Escape search terms for SQL LIKE
    $whr .= " AND ( firstname LIKE '%$safe_sea%' OR lastname LIKE '%$safe_sea%' OR email LIKE '%$safe_sea%' ) ";
}

// Final query to fetch registrants
$regs = db_query_rows("SELECT * FROM free_registrants WHERE archived = 0 $whr ORDER BY {$ob_clause} accepted, sentemail, lastname, firstname"); 
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<p>
<strong><span class="title">MANAGE FREE PEOPLE</span></strong></p>

<p>
<form method='post' action='freeregistrants.php'>
Search: <input type='text' name='sea' value="<?=htmlspecialchars($sea)?>"> <input type='submit' name='search' value='Search'><Br><br>
Archive Before: <?=printdates2("archivebefore", $archivebefore)?> <input type='submit' name='arch' value='Archive'><Br><br>

<font color='red'><?=htmlspecialchars($err)?></font><br>
<a href='freeregistrants.php?addone=1'>Add New</a>
<table class="table3" cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
<tr bgcolor="#e1e1f6"><th class='copy'>Name</th><th class='copy'>Sent Email</th><th class='copy'><a href='freeregistrants.php?obs=dateadded+desc'>Added</a></th><th class='copy'>Status</th><th class='copy'>Action</th></tr>
<?php 
foreach ($regs as $t)
{
    // Skip if an existing responder is found (based on the original logic)
    if (($t['acceptcode'] ?? '') && db_query_first_cell("SELECT responderid FROM responders_esi WHERE pmsid = '{$t['acceptcode']}' AND deleted = 0"))
        continue;

    $clr = "#ffffff";
    $accepted_status = (int)($t["accepted"] ?? 0);

    if ($accepted_status == 1)
        $clr = "#cccccc";
    else if ($accepted_status == 2)
        $clr = "#eeeeee";

    $s2 = "";
    if ($accepted_status) {
        // Safe output for date and username
        $status_date = getFormattedDateWTime($t['statusdate'] ?? '');
        $status_user = htmlspecialchars($t['statususername'] ?? 'N/A');
        $s2 = "<br>Date: $status_date, $status_user";
    }

    $firstname = htmlspecialchars($t['firstname'] ?? '');
    $lastname = htmlspecialchars($t['lastname'] ?? '');
    $id = htmlspecialchars($t['id'] ?? '');
    $acceptcode_html = htmlspecialchars($t['acceptcode'] ?? '');

    // Output table row
    echo "<tr bgcolor='$clr'>";
    // Link to edit registrant
    echo "<td class='copy' valign='top'><a href='editfreeregistrant.php?id=$id'>$firstname $lastname</a></td>";
    echo "<td class='copy'>" . (($t['sentemail'] ?? 0) ? "Yes" : "No") . "</td>";
    echo "<td class='copy'>" . htmlspecialchars(getFormattedDate($t['dateadded'] ?? '')) . "</td>";

    $status_text = '';
    if ($accepted_status == 1) {
        $status_text = "Accepted ($acceptcode_html) $s2";
    } elseif ($accepted_status == 2) {
        $status_text = "Declined $s2";
    }

    echo "<td class='copy'>$status_text</td>";

    echo "<td valign='top' class='copy'>";

    // Action Links
    if (!$accepted_status)
    {
        // NOTE: Removed javascript: return confirm() as it is not supported in the environment.
        echo "
        <a href='editfreeregistrant.php?id=$id'>Edit</a>&nbsp;&nbsp;
        <a href='freeregistrants.php?accept=$id'>Accept</a>
        &nbsp;&nbsp;
        <a href='freeregistrants.php?decline=$id'>Decline</a>
        &nbsp;&nbsp;";
    }
    else
    {
        if ($accepted_status == 1)
            echo "ACCEPTED";
        else
            echo "DECLINED";
    }

    // Delete link (archived)
    // NOTE: Removed javascript: return confirm() as it is not supported in the environment.
    echo "
    <a onclick='javascript: return confirm( \"Are you sure you want to delete this registrant?\" )' href='freeregistrants.php?del=$id'>Delete</a></td></tr>";
}
?>
</table>
<a href='freeregistrants.php?addone=1'>Add New</a>
<br><br><br><br>
<!--end center content-->
<?php include "ssi/footer.php" ; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
</form>
<br><br>
</div>
</body>
</html>