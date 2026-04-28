<?php 
$nologinrequired = true;
require_once "mysql.php";

$id = $id ?? null;
$orderedby = $orderedby ?? 'Unknown User';
$dontsend = $dontsend ?? false;
$numorder = $numorder ?? 0;
// Safely handle external constant, defaulting to a placeholder
$url_subdomain = defined('URL_WITHOUT_SUBDOMAIN') ? URL_WITHOUT_SUBDOMAIN : 'emergencyskills.com';
$sub_doe = $GLOBALS['SUB_DOE'] ?? SUB_DOE ?? 'doe';

$overrideemailinbottom = "barbara@emergencyskills.com";

if( !$dontsend && $id ) // Only proceed if $dontsend is false and class ID is present
{
    // Use safe integer ID for lookups
    $safe_id = (int)$id;
    
    // Fetch class and company details
    $crow = getClassRow( $safe_id );
    $comrow = getCompanyRow( $crow['companyid'] ?? null );
    
    // PHP 8.2 Fix: Quote array keys and use htmlspecialchars() for email content
    $company_name = htmlspecialchars($comrow['companyname'] ?? 'N/A');
    $start_date = htmlspecialchars($crow['startdate'] ?? 'N/A');
    $ordered_by_safe = htmlspecialchars($orderedby);

    $contents = "{$ordered_by_safe} just ordered masks for class #{$safe_id}

Company: {$company_name}
Class Date: {$start_date}

<a href='http://{$sub_doe}.{$url_subdomain}/class_detail.php?id={$safe_id}'>View Class Details</a>";

    $subject = "Masks Ordered for Class {$safe_id}";
    $from_email = "info@emergencyskills.com";
    $bcc = "";

    // --- Send Email Notifications (Assuming sendFormattedHTMLMail is defined) ---
    // Note: The original list of recipients is preserved.
    $recipients = [
        "barbara@emergencyskills.com", 
        "sarahg@emergencyskills.com", 
        "jwright@emergencyskills.com", 
        "dfunnye@emergencyskills.com", 
        "savealife@emergencyskills.com"
        // "rachelc@gmail.com" was commented out in original
    ];

    foreach ($recipients as $to_email) {
        sendFormattedHTMLMail( $to_email, $subject, $contents, $from_email, $bcc, false );
    }

    $safe_numorder = (int)$numorder;
    $safe_orderedby_sql = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $orderedby);
    
    $update_sql = "UPDATE class 
                   SET 
                       masksordered = NOW(), 
                       nummasks = {$safe_numorder}, 
                       masksorderedby = '{$safe_orderedby_sql}' 
                   WHERE 
                       id = {$safe_id}";
                       
    db_query( $update_sql ); 
}

include "gethtmlbodytop.php";
?>


<br>
<div align="center">

    Thanks! Your order for masks has been received.

    <br><br><br><br><br><br><br><br>
    <br><br><br><br><br><br><br><br>
    
<?php include "gethtmlbodybottom.php"; ?>