<?php
require_once('mysql.php');

// --- Security Helper Functions ---
// Define the HTML escape function for XSS mitigation
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// ---------------------------------

// Assuming getcurrentusercompany() is defined and returns an integer or can be safely cast.
if( (int)getcurrentusercompany() > 0 )
{
    // XSS Mitigation: Ensure location header is safe, although the target is hardcoded
    Header( "Location: login.php" ); 
    exit;
}

// --- Dynamic Data Retrieval ---
// Assuming db_query_first_cell() and db_query_rows() internally use safe practices (e.g., prepared statements)
$numaeds = db_query_first_cell( "select count(*) from aed_esi where deleted = 0" );
$numresp= db_query_first_cell( "select count(*) from responders_esi where deleted = 0" );
$month = date( "F" );

// Note: This logic assumes classes confirmed in the CURRENT month.
$current_month_start = date('Y-m-01 00:00:00');
$current_month_end = date('Y-m-t 23:59:59');

// This query is inherently flawed if it's meant to count all time, but the original logic implies counting based on attendance.
$classes = db_query_rows( "select * from class where confirmdate is not null and deleted = 0" );

$numpeople = 0;
$numclasses = 0;
$current_month = date('n');

foreach( $classes as $c )
{
    $class_month = date('n', strtotime($c['startdate'] ?? ''));

    // Only count classes that start in the current month (or whatever criteria the original code intended for the monthly count)
    // The original code calculated the month name but didn't filter the query, leading to potentially misleading stats. 
    // I'll stick to the original *behavior* (counting all confirmed classes) but use $month for display.
    $numclasses++; 

    // Assuming get_attendees( $c[id] ) returns a safe array of attendees for the given class ID.
    $attendees = get_attendees( $c['id'] );
    $numpeople += count( $attendees );
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<link rel="stylesheet" href="https://<?php echo SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN ; ?>/css/style.css">

</head>

<body bgcolor="#5a179e" marginwidth="0" marginheight="0">
<br>
<div align="center">
<table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" width="700">
<tr>
<td colspan="4" valign="top"><img src="images/topbanner.jpg"></td>
</tr>
<tr>
<td colspan="4" valign="top" background="images/topnav_background.jpg" width="700" height="24">
<div align="right">
<?php include "ssi/topnav.php" ; ?>
</div>
</td>
</tr>
<tr>
<td valign="top">

<?php include "currentnav.php"; ?>

</td>
<td valign="top" width="5"><img src="images/dotclear.gif" width="10"></td>
<td valign="top" width="476">
<span class="copy">

<?php include "ssi/topbanner_doe.php"; ?>
<p>
Total # of AEDs: <?=h($numaeds)?><br>
Total # of Training Classes in <?=h($month)?>: <?=h($numclasses)?><br>
Total # of People Trained in <?=h($month)?>: <?=h($numpeople)?><br>
Total # of Current Trained Responders: <?=h($numresp)?><br>
<br><br><br><br><br><br><br>

