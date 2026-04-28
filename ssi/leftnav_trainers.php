<?php if( 1 || $thisusersrow["newui"] ) {
include "ssi/newleft_trainers.php";
}

else { ?>

<table cellpadding="0" cellspacing="0" border="0" background="images/swoosh.gif">
<tr height="240">
<td width="202" valign="top"><br>
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top" width="10"><img src="images/dotclear.gif" width="10"></td>
<td valign="top" width="150">
<table cellpadding="0" cellspacing="2" border="0">
<tr>
<td class='copy' valign='top'>
Welcome <?= getUserNameFirstOnly( $session_id ) ?>!
</td>
</tr>

<?php if( !($thisusersrow["national"]) ) { ?>
<tr>
<td><b>Downloadable Info.</b></td>
</tr>
<tr>
<td valign="top"><a href="/pdfs/Equipment Bags Contents.pdf">Equipment Bag Contents</a>
</td>
</tr>
<tr>
<td valign="top"><a href="/pdfs/May June 2012 Conference Call Notes.pdf" class="doenav">NOTES FROM CONFERENCE CALL,MAY/JUNE 2012</a>
</td>
</tr>
<tr><td valign="top"><a href="/metalstrip.php">Metal Strip Placement</a></td>
</tr>
<tr><td valign="top"><a href="pdfs/CodeBluePolicies-Nov08.pdf" class="doenav">Code Blue Policies</a></td>
</tr>

<tr><td valign="top"><a href="https://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/drillinspection.php" class="doenav">Print Blank Drill Worksheet</a></td>
</tr>

<?php if( ($thisusersrow["tcfaculty"]) ) { ?>
<tr><td valign="top"><a href="https://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/trainers.php" class="doenav">My Trainers</a></td>
</tr>
<?php } ?>

<tr><td valign="top" bgcolor="#b2b1b1"><img src="images/dotclear.gif" height="1" width="1"></td></tr>

<?php } ?>

<tr><td><b>Payroll</b></td></tr>
<tr><td valign="top"><a href="pdfs/2013_PAYROLL_SCHEDULE.pdf" class="doenav">2013 Payroll Schedule</a></td></tr>
<tr><td valign="top"><a href="pdfs/Sample Invoice.pdf" class="doenav">Sample Invoice</a></td></tr>

<?php if( !($thisusersrow["national"]) ) { ?>
<tr><td valign="top"><a href="DrillInvoiceTemplate.doc" class="doenav">Drill Invoice Template</a></td>
<?php } ?>

<tr><td valign="top" bgcolor="#b2b1b1"><img src="images/dotclear.gif" height="1" width="1"></td></tr>
<tr><td><b>Calendar</b></td></tr>
<tr><td valign="top"><a href="requesttotrain.php" class="doenav"><font color='red'>Click Here Daily to Respond to Open Trainer Requests</font> </a></td></tr>
<tr><td valign="top"><a href="tcalendar.php" class="doenav">Go To Calendar</a></td></tr>

<?php if( !($thisusersrow["national"]) ) { ?>
<tr><td valign="top"><a href="print_daily_schedule.php" class="doenav">Print Today's Schedule</a></td></tr>
<tr><td valign="top"><a href="fax_registration.php" class="doenav">Schedule by Fax</a></td></tr>
<tr><td valign="top" bgcolor="#b2b1b1"><img src="images/dotclear.gif" height="1" width="1"></td></tr>
<?php } ?>

<tr><td><b>My Information</b></td></tr>
<tr><td valign="top"><a href="trainer_profile_view.php" class="doenav">Profile</a></td></tr>
<tr><td valign="top"><a href="trainer_availability.php" class="doenav">Availability</a></td></tr>

<?php if( ($thisusersrow["viewschools"]) ) { ?>
<tr><td valign="top"><a href="drillcalendar.php" class="doenav">Code Blue DRILL Availability</a></td>
<tr><td valign="top"><a href="schools.php" class="doenav">Manage Schools</a></td></tr>
</tr><tr><td valign="top" bgcolor="#b2b1b1"><img src="images/dotclear.gif" height="1" width="1"></td></tr>

<?php
$vis = getVisibleZips( $thisusersrow["id"] );
if( $vis ) { ?>
<tr><td><b>Reports</b></td></tr>
<?php 
$visib = $vis;
if( $visib ) {
$inyourarea = db_query_first_cell( "select count(aedid) from aed_esi a, company_esi c where iscorp = 0 and c.isactive = 1 and c.deleted = 0 and a.deleted = 0 and newinstall = 1 and clientid = c.id and zip in ( " . $visib . " )" );

$numschools = db_query_first_cell( "select count(id) from company_esi c where iscorp = 0 and c.isactive = 1  and showsondrillreports = 1 and c.deleted = 0 and zip in ( " . $visib . " )" );

if( $inyourarea || $numschools ) {
?>
<tr><td class='copy' valign='top'>
Schools: <?= $numschools ?? 0 ?><br>
<?php if( $inyourarea ) { ?>
<a href='newinstalls.php'><font color='red'><b><?= $inyourarea ?> new installations in your area!</font></b></a> <A href='newinstalls.php?xls=true'><font color='red'><b>(xls)</b></font></a></font>
</td></tr>
<?php }}
}?>

<tr><td valign="top"><a href="allschools.php?nodrills=true&go=true&onscreen=true" class="doenav">Schools w/o Drills</a> <a href="allschools.php?nodrills=true&go=true" class="doenav">(xls)</a></td></tr>
<tr><td valign='top'><a href="faileddrillreport.php?concat=true" class="doenav">Failed Drill Report</a> <a href="faileddrillreport.php?xls=true&concat=true" class="doenav">(xls)</a></td></tr>
<tr><td valign='top'><a href="followupdrillreport.php?concat=true" class="doenav">Followup Drill Report</a> <a href="followupdrillreport.php?xls=true&concat=true" class="doenav">(xls)</a></td></tr>
<tr><td valign="top"><a href="expiringreporttrainer.php" class="doenav">Expired AEDs In Your Area</a> <a href="expiringreporttrainer.php?xls=true" class="doenav">(xls)</a></td></tr>
<tr><td valign="top" bgcolor="#b2b1b1"><img src="images/dotclear.gif" height="1" width="1"></td></tr>
<tr><td><a href="aedpadreport.php">Pads Forecast Report</a> <a href="aedpadreport.php?xls=1">(xls)</a></td></tr>
<?php } ?>
<?php } ?>

</tr>
</table>
<?php } ?>

<!-- <tr><td valign="top" bgcolor="#b2b1b1"><img src="images/dotclear.gif" height="1" width="1"></td></tr>
<tr><td valign="top"><a href="login.php" class="doenav">Log Out</a></td></tr>
<tr><td valign="top" bgcolor="#b2b1b1"><img src="images/dotclear.gif" height="1" width="1"></td></tr> -->

<?php
$showaedlink = true;
$id = $id ?? 0;
if( $showaedlink ) { ?>
<?php if( $id ) { ?>
<span class='copy'><img src='fr.jpg' height='60'><br>
Check your own AED. <br><a href='pdfs/monthly<?= ($session_iscorp) ? "_corp" : "" ?>.pdf' target=_blank><font class='body'>Print Monthly Checklist</font></a>
<span class='copy'><br> <a href='printaedsign.php?id=<?= $id ?? '' ?>'>Print AED Sign</a><br><br></span>
<?php } ?>
<?php } ?>

</td> 
</tr>
</table>
</td>
</tr>
</table>