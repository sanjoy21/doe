<?php
require_once('mysql.php');

// 1. SQL INJECTION MITIGATION: Sanitize or cast the primary input ID.
// Assuming $id is a required integer class ID.
$id_safe = (int)($id ?? 0); 

// Use safe functions that utilize prepared statements internally.
$crow = getClassRow( $id_safe );
if (!$crow) {
    die("Class not found.");
}
$corow = getCompanyRow( $crow["companyid"] );

// 2. XSS MITIGATION: Prepare all database outputs for safe HTML display.
$companyname_safe = htmlspecialchars($corow["companyname"] ?? '');
$schoolcode_safe = htmlspecialchars($corow["schoolcode"] ?? '');
$contactname_safe = htmlspecialchars($corow["contactname"] ?? '');
$class_code_safe = htmlspecialchars($crow["code"] ?? '');
$class_name_safe = htmlspecialchars($class_names[$class_code_safe] ?? 'N/A');
$startdate_safe = htmlspecialchars(fixdatefordisplay( $crow["startdate"], true ));
$starttime_safe = htmlspecialchars(date( "h:i A", strtotime( $crow["startdate"] ?? 'now' ) ));
$endtime_safe = htmlspecialchars(date( "h:i A", getEndTime( $crow["startdate"] ?? 'now', $class_code_safe ) ));
$requestdate_safe = htmlspecialchars(fixdatefordisplay( $crow["requestdate"], true ));
$confirmdate_safe = htmlspecialchars(fixdatefordisplay( $crow["confirmdate"], true ));
$id_display_safe = htmlspecialchars($id_safe);


?>
<?php include "ssi/top.php"; ?>

<table border="0" cellspacing="4" cellpadding="0">
<tr>
<td><STRONG class="title"><nobr>HOST SCHOOL/ORGANIZATION: </STRONG> <?php echo $companyname_safe; ?> <?php echo $schoolcode_safe; ?></nobr></td>
</tr>
<tr>
<td><STRONG class="title">SITE CONTACT: </STRONG> <?php echo $contactname_safe; ?></td>
</tr>
</table>
<span class="copy">
<hr>

Program Type: <strong><?php echo $class_name_safe; ?></strong> </span>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td class="copy"><br>
Session  Date: <?php echo $startdate_safe; ?> Start Time: <?php echo $starttime_safe; ?> End Time: <?php echo $endtime_safe; ?>
</span></td>
</tr>
</table></td>
</tr>
<tr>
<td class="copy">Instructor  Name: 
<?php
$trainers = getTrainers( $id_safe ); // Pass safe ID
$any = false;
$trainer_names = [];
foreach( $trainers as $trainerid=>$trow ) {
    // 3. XSS MITIGATION: Escape trainer names
    $trainer_names[] = htmlspecialchars(getFullname( $trainerid ) ?? '');
    $any = true;
}

if( count($trainer_names) > 0 ) {
    echo implode(", ", $trainer_names);
} else {
    echo "_____________________";
}
?>
Total  # of students: __________ 
</td>
</tr>
</table>
<span class="copy">
<p>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
<tr bgcolor="#e1e1f6">
<td valign="top" class="copy"><span class="copy"><strong>Name/Title
<br>
File#/Last 4 digits of Social
</strong></span></td> 
<td valign="top" class="copy"><span class="copy"><strong>School Name & Address</strong></span></td>
<td valign="top"><span class="copy"><strong>Signature</strong></span></td>
<td valign="top" class="copy"><span class="copy"><strong>Pass/Fail</strong></span></td>
</tr>
<?php for( $i = 0;$i < 15; $i++ ) { ?>
<tr bgcolor="#ffffff">
<td height="60" valign="top" class="copy">&nbsp;</td>
<td valign="top" class="copy">&nbsp;</td>
<td valign="top">&nbsp;</td>
<td align="center" valign="middle" class="copy">&nbsp;</td>
</tr>
 <?php } ?>
</table>

<br>
</span>
<table cellpadding="0" cellspacing="0" border="0" width="100%" >
<tr>
<td height="1"><img src="images/1x1.gif" width="1" height="30"></td>
<td height="1">&nbsp;</td>
<td height="1">&nbsp;</td>
</tr>
<tr>
<td height="1"><table width="80%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td height="1" bgcolor="#000000"><img src="images/1x1.gif" width="1" height="1"></td>
</tr>
</table></td>
<td height="1"><table width="80%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#000000"><img src="images/1x1.gif" width="1" height="1"></td>
</tr>
</table></td>
<td height="1"><table width="80%" border="0" cellpadding="0" cellspacing="0" bgcolor="#000000">
<tr>
<td><img src="images/1x1.gif" width="1" height="1"></td>
</tr>
</table></td>
</tr>
<tr>
<td class="copy">Site Supervisor Signature</td>
<td class="copy">Print Name and Title</td>
<td class="copy">Date</td>
</tr>
<tr>
<td class="copy">&nbsp;</td>
<td class="copy">&nbsp;</td>
<td class="copy">&nbsp;</td>
</tr>
<tr>
<td class="copy"><img src="images/1x1.gif" width="1" height="30"></td>
<td class="copy">&nbsp;</td>
<td class="copy">&nbsp;</td>
</tr>
<tr>
<td class="copy"><table width="80%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td height="1" bgcolor="#000000"><img src="images/1x1.gif" width="1" height="1"></td>
</tr>
</table></td>
<td class="copy"><table width="80%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td height="1" bgcolor="#000000"><img src="images/1x1.gif" width="1" height="1"></td>
</tr>
</table></td>
<td class="copy"><table width="80%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td height="1" bgcolor="#000000"><img src="images/1x1.gif" width="1" height="1"></td>
</tr>
</table></td>
</tr>
<tr>
<td class="copy">Instructor&rsquo;s  Signature</td>
<td class="copy">Print  Name</td>
<td class="copy">Date</td>
</tr>
</table>
<span class="copy"><br>
</span>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
<tr>
<td><ul>
<li class="copy">Please  call the office with accurate count of participants</li>
<li class="copy">Mail roster to office in provided envelope immediately following completion of program.</li>
</ul></td>
</tr>
<tr>
<td><img src="images/1x1.gif" width="1" height="10"></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td><img src="images/1x1.gif" width="20" height="1"><img src="images/1x1.gif" width="20" height="1"></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">

<tr>
<td colspan="3" ><hr class="dotTop">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
<td><strong class="copy">Class Request Date:</strong> <?php echo $requestdate_safe; ?></td>
<td><strong class="copy">Confirmation Date:</strong> <?php echo $confirmdate_safe; ?> </td>
<td><div align="right"><strong class="copy">Class #: <?php echo $id_display_safe; ?> </strong></div></td>
</tr>
</table></td>
</tr>
<tr>
<td colspan="3"><p class="copy">&nbsp;</p>
<p class="copy"><strong>For office use only:</strong>  <br>
<em>Enter Date and Initials upon completion</em> </p>
<p class="copy">&nbsp;</p></td>
</tr>
<tr>
<td><strong class="copy">Roster  Rec: _______ </strong></td>
<td class="copy"><strong>Roster  Entered:</strong> _________ </td>
<td class="copy"><div align="right"><strong>Cards  Mailed:</strong> _______ </div></td>
</tr>
</table>
 <br>
 <br>
<img src="images/button_print.gif" width="120" height="20"><br>
<br>
<br>
<br>
<br>
<br>

<?php include "ssi/footer.php"; ?> 
</span> </td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>