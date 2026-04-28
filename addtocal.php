<?php
require_once('mysql.php');

$crow = getClassRow( $id );
$comrow = getCompanyRow( $crow["companyid"] );
$class_names = $allclass_names[$comrow["iscorp"]];

// 1. Set the correct headers for this file
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=' . ($filename ?? 'class_event.ics')); // Added null check for $filename

// 2. Define helper functions

// Converts a unix timestamp to an ics-friendly format
// NOTE: "Z" means that this timestamp is a UTC timestamp.
// Also note that we are using "H" instead of "g" because iCalendar's Time format
// requires 24-hour time.
function dateToCal($timestamp) {
    
    if (!$timestamp || !is_numeric($timestamp)) {
        // Return current time as fallback if date parsing failed
        $timestamp = time(); 
    }
    return date('Ymd\THis\Z', $timestamp);
}

// Escapes a string of characters
function escapeString($string) {
    return preg_replace('/([\,;])/','\\\$1', $string);
}

// Calculate start date/time
$dtstart = strtotime( $crow["startdate"] );

// Calculate end date/time
$end = $crow["enddate"];
if( !$end ) 
{
    // Use quoted array key and null check
    if( ($crow["code"] ?? '') == "dd" )
        $end = $crow["startdate"]. " + 2 hours";    
    else
        $end = $crow["startdate"]. " + 6 hours";    
}
else
{
    // Use calculated $dtstart timestamp to get date part
    $end = date( "Y-m-d", $dtstart ) . " " . $end;
}
$dtend = strtotime( $end );


$uri = "http://".getUrlPrefix( $comrow["iscorp"] ) . "." .URL_WITHOUT_SUBDOMAIN. "/class_detail.php?id=" . ($id ?? '');
$description = "Class at emergency skills";
$summary = "You are supposed to attend a class at emergency skills";

$address = ($comrow["address"] ?? '') . " " . ($comrow["city"] ?? '') . ",  ". ($comrow["zip"] ?? '');

// 3. Echo out the ics file's contents
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:<?= dateToCal($dtend) ?>
UID:<?= uniqid() ?>
DTSTAMP:<?= dateToCal(time()) ?>
LOCATION:<?= escapeString($address) ?>
DESCRIPTION:<?= escapeString($description) ?>
URL;VALUE=URI:<?= escapeString($uri) ?>
SUMMARY:<?= escapeString($summary) ?>
DTSTART:<?= dateToCal($dtstart) ?>
END:VEVENT
END:VCALENDAR