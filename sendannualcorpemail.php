<?php
require_once('mysql.php');

// --- 1. Sanitize Inputs from $_REQUEST and Session ---

// Inputs (ID from URL, POST data, Session data)
$id = $_REQUEST['id'] ?? null;
$send = $_POST['send'] ?? null;
$fromname = $_POST['fromname'] ?? null;
$fromemail = $_POST['fromemail'] ?? null;
$to = $_POST['to'] ?? null;
$tonames = $_POST['tonames'] ?? null;
$subject = $_POST['subject'] ?? null;
$body = $_POST['body'] ?? null;
$err = $_REQUEST['err'] ?? null;

// SQL INJECTION MITIGATION: Ensure IDs are treated as integers before DB use
$id_int = (int)($id ?? 0);
$session_userid_int = (int)($session_userid ?? 0);
$session_id_int = (int)($session_id ?? 0); 

// Authorization check (kept as-is)
if( !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

$crow = null;
if( !$_POST )
{
    // WARNING: getCompanyRow($id) is still UNPROTECTED against SQLi 
    // IF it uses $id directly in an internal query.
    // The safest assumption is that the internal function MUST be updated
    // to use the sanitized $id_int or prepared statements.
    $crow = getCompanyRow( $id_int ); 

    // Set initial defaults only if we haven't posted (i.e., we are initializing the form)
    if (!$_POST) { 
        $initbody = 
            "Dear CONTACT, 

I hope this finds you well and that you are having a great year!

We are contacting you today because it has been nearly a year since we held CPR/AED training for you, so it is time to consider dates for this year's program. ESI requires clients for whom we provide state-mandated medical oversight and compliance services to offer training annually. This is an excellent time to afford those who took the program last year a chance to refresh their skills or to train individuals who may have missed last year's class or joined your staff in the interim. Please contact me if you have questions at all or are ready to schedule.

We look forward to working with you once again on this life saving project.

Thank you,
Dylan Zamos
Emergency Skills
Emergencyskills.com
212-564-6833
dzamos@emergencyskills.com
";
        $body = $initbody;
        $fromname = $fromname ?: "Emergency Skills";
        $fromemail = $fromemail ?: "dzamos@emergencyskills.com";
        $subject = $subject ?: "Annual CPR/AED Training - " . htmlspecialchars($crow['companyname'] ?? '');

        $tonames = $crow['contactname'] ?? '';
        $to = $crow['contactemail'] ?? '';

        if( $crow['contact2email'] )
        {
            $tonames .= "; {$crow['contact2name']}";
            $to .= "; {$crow['contact2email']}";
        }
        if( $crow['contact3email'] )
        {
            $to .= "; {$crow['contact3email']}";
            $tonames .= "; {$crow['contact3name']}";
        }
        // Hardcoded recipients
        $to .= "; barbara@emergencyskills.com; dzamos@emergencyskills.com";
        $tonames .= "; Barbara Kinter; Dylan Zamos";
    }
}

if( $send )
{
    $body_text = stripslashes( $body );
    $subject_text = stripslashes( $subject );
    require_once "class.phpmailer.php";

    $toarr = explode( ";", $to );
    $tonamesarr = explode( ";", $tonames );

    foreach( $toarr as $k=>$t )
    {
        $t_trimmed = trim( $t );
        if( $t_trimmed )
        {
            $name_for_body = $tonamesarr[$k] ?? 'CONTACT';
            $tmpbody = nl2br( str_replace( "CONTACT", $name_for_body, $body_text ) );
            
            // Assuming sendFormattedHTMLMail is responsible for secure email sending
            sendFormattedHTMLMail( $t_trimmed, $subject_text, $tmpbody, $fromemail, $fromname, false );
        }
    }
    
    // --- 2. SQL INJECTION MITIGATION (Database Updates) ---
    // Use $id_int, $session_userid_int, and $session_id_int in the queries
    
    // Update company_esi
    db_query( "UPDATE company_esi SET annualremindersent = NOW() WHERE id = '{$id_int}'" );
    
    // Insert annualremindersent log
    db_query( "INSERT INTO annualremindersent (companyid, whom, sentdate) VALUES('{$id_int}', '{$session_userid_int}', NOW())" );

    // Insert recertnotes log
    $thesd = mktime( 0,0,0, date( "m" ), date( "d" ) + 7 );
    $nextcalldate_db = fixdatefordb( date( "Y-m-d", $thesd ) );
    
    // Use single quotes for text fields and no quotes for the integer variables $session_id_int and $id_int
    // NOTE: This approach still relies on $session_id_int and $id_int being ONLY integers.
    // If they were not cast as integers, this would be highly vulnerable.
    db_query( "INSERT INTO recertnotes (recertificationnotes, recertdate, recertperson, companyid, nextcalldate, assignedto, tassignedto)
               VALUES ('Follow up to annual', NOW(), {$session_id_int}, {$id_int}, '{$nextcalldate_db}', '{$session_id_int}', '' )" );

    Header( "Location: viewcompany.php?id={$id_int}&sent=1" );
    exit;
}

// --- 3. XSS MITIGATION: Prepare all form data for display ---

$fromname_safe = htmlspecialchars($fromname ?? '');
$fromemail_safe = htmlspecialchars($fromemail ?? '');
$to_safe = htmlspecialchars($to ?? '');
$tonames_safe = htmlspecialchars($tonames ?? '');
$subject_safe_display = htmlspecialchars($subject ?? '');
$body_safe_display = htmlspecialchars($body ?? '');
$err_safe = htmlspecialchars($err ?? '');
$companyname_safe = htmlspecialchars($crow['companyname'] ?? 'N/A');

// Re-fetch company row if it wasn't loaded in the !send block
if (!$crow) {
    // WARNING: getCompanyRow($id_int) is still UNPROTECTED against SQLi 
    $crow = getCompanyRow($id_int); 
    $companyname_safe = htmlspecialchars($crow['companyname'] ?? 'N/A');
}

?>
<?php include "ssi/top.php"; ?>
<p>
<strong><span class="title">Confirm Names for <a href='viewcompany.php?id=<?php echo $id_int; ?>'><?php echo $companyname_safe; ?></a></span></strong>
<?php echo $err_safe; ?>
<p>
<form method='post'>
<table>
<tr><td>From Name: </td><td><input type='text' size='40' name='fromname' value="<?php echo $fromname_safe; ?>"></td></tr>
<tr><td>From Email: </td><td><input type='text' size='40' name='fromemail' value="<?php echo $fromemail_safe; ?>"></td></tr>
<tr><td>To: </td><td><input type='text' size='40' name='to' value="<?php echo $to_safe; ?>"></td></tr>
<tr><td>To Names: </td><td><input type='text' size='40' name='tonames' value="<?php echo $tonames_safe; ?>"></td></tr>
<tr><td>Subject: </td><td><input type='text' size='50' name='subject' value="<?php echo $subject_safe_display; ?>"></td></tr>
<tr><td>Body: </td></tr>
<tr><td colspan='2'>
<textarea name='body' rows='30' cols='80'><?php echo $body_safe_display; ?></textarea></td></tr>
<tr><td></td><td><input type='submit' name='send' value='Send Email'></td></tr>
</table>
<br><br>
</div>
</body>
</html>