<?php 
require "mysql.php";

// Safely retrieve the external variable $id (the class ID)
$id = $_REQUEST['id'] ?? ($id ?? null);
$db_link = $GLOBALS['link'] ?? $link; 
$safe_id = (int)$id;

// Assuming these functions exist and are safe: getClassRow, getCompanyRow
$class = getClassRow( $safe_id );
$comrow = getCompanyRow( $class['companyid'] ?? null );

// Safely retrieve the assumed global class names array
$allclass_names = $allclass_names ?? [];

// --- 1. Fetch Class Data and Attendees ---
$attendees = db_query_rows( "SELECT 
                                r.lastname, r.firstname, r.responderid 
                             FROM 
                                responder_to_class rtc, responders_esi r 
                             WHERE 
                                rtc.responderid = r.responderid 
                                AND rtc.classid = {$safe_id} 
                             ORDER BY 
                                lastname, firstname " );

// Extract company name, stripping text after '[' (e.g., stripping school code suffix)
$full_companyname = $comrow['companyname'] ?? 'Unknown Location';
$companyname_parts = explode( "[", $full_companyname );
$companyname = trim(array_shift( $companyname_parts ));

// Safety for Output
$companyname_safe = htmlspecialchars($companyname);
$class_code = $class['code'] ?? null;
$iscorp_flag = $comrow['iscorp'] ?? 0;
$class_name = htmlspecialchars($allclass_names[$iscorp_flag][$class_code] ?? 'Training');
$startdate_raw = $class['startdate'] ?? 'now';
$month_year_safe = strtoupper( date( "F Y", strtotime( $startdate_raw ) ) );
$class_date_safe = date( "m/d/Y", strtotime($startdate_raw) );
?>

<!DOCTYPE html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title></title>
    <meta name="generator" content="OpenOffice.org 3.1  (Linux)">
    <meta name="author" content="Maggie">
    <meta name="created" content="20100222;14580000">
    <meta name="changed" content="20100222;14580000">
    <meta name="appversion" content="12.0000">
    <meta name="company" content="">
    <meta name="docsecurity" content="0">
    <meta name="hyperlinkschanged" content="false">
    <meta name="linksuptodate" content="false">
    <meta name="scalecrop" content="false">
    <meta name="sharedoc" content="false">

</head>
<body lang="en-US" text="#000000" dir="LTR">
<p align=center style="margin-bottom: 0.14in"><font face="Times New Roman, serif"><font size=4 style="font-size: 16pt"><b><?php echo $companyname_safe; ?></b></font></font></p>
<p align=center style="margin-bottom: 0.14in"><br><br>
</p>
<p align=center style="margin-bottom: 0.14in"><font face="Times New Roman, serif"><font size=4 style="font-size: 16pt"><b>TRAINED
RESPONDERS IN</b></font></font></p>
<p align=center style="margin-bottom: 0.14in"><font face="Times New Roman, serif"><font size=4 style="font-size: 16pt"><b>AMERICAN
HEART ASSOCIATION</b></font></font></p>
<p align=center style="margin-bottom: 0.14in"><font face="Times New Roman, serif"><font size=4 style="font-size: 16pt"><b><?php echo $class_name; ?></b></font></font></p>
<p align=center style="margin-bottom: 0.14in"><br><br>
</p>
 <p align=center style="margin-bottom: 0.14in"><font face="Times New Roman, serif"><font size=4 style="font-size: 16pt"><b><?php echo $month_year_safe; ?></b></font></font></p>
<p align=center style="margin-bottom: 0.14in"><br><br>
</p>
<center>
<table width=366 border=1 bordercolor="#00000a" cellpadding=7 cellspacing=0>
<col width=99>
<col width=124>
<col width=99>
<tr valign=bottom>
<td width=99 height=2 bgcolor="#ffffff">
<p><font face="Arial, serif"><font size=2><b>LAST NAME</b></font></font></p>
</td>
 <td width=124 bgcolor="#ffffff">
 <p><font face="Arial, serif"><font size=2><b>FIRST NAME</b></font></font></p>
</td>
<td width=99 bgcolor="#ffffff">
<p><font face="Arial, serif"><font size=2><b>CLASS DATE</b></font></font></p>
</td>
</tr>
<?php 
foreach( $attendees as $arow )
{
    $responder_id = $arow['responderid'] ?? null;

    // Assuming isCompleted() exists and is safe
    if( !isCompleted( $responder_id, $safe_id ) ) 
        continue;
    
    $lastname_safe = htmlspecialchars($arow['lastname'] ?? '');
    $firstname_safe = htmlspecialchars($arow['firstname'] ?? '');
?>
<tr valign=bottom>
<td width=99 height=3 bgcolor="#ffffff">
<p><font face="Arial, serif"><font size=2><?php echo $lastname_safe; ?></font></font></p>
 </td>
<td width=124 bgcolor="#ffffff">
<p><font face="Arial, serif"><font size=2><?php echo $firstname_safe; ?></font></font></p>
 </td>
<td width=99 bgcolor="#ffffff">
<p><font face="Arial, serif"><font size=2><?php echo $class_date_safe; ?></font></font></p>
 </td>
 </tr>
<?php } ?>
</table>
</center>
<p align=center style="margin-bottom: 0.14in"><br><br>
</p>
<p style="margin-bottom: 0.14in"><br><br>
</p>
</body>
</html>