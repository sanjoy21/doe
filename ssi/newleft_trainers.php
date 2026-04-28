<div id="col-left">
<a href="index.php"><img src="newimages/dashboard-icon.gif" border="0"></a><br><br>
<ul class="subnav">

<?php if( !($thisusersrow["national"]) ) { ?>
<li class="subnav-title">Instructor Information</li>
<li><a href="https://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/viewpasswords.php" class="doenav">Passwords</a></li>
<?php

$doctypes = array();
$doctypes['HR'] = "Human Resources";
$doctypes['II'] = "Instructor Information";
$doctypes['GI'] = "General Information";
$doctypes['CS'] = "Classroom Specifics";

$alldocs = db_query_rows( "select * from esidocuments where type<> 'HR' order by case when type = 'GI' then 1 when type = 'II' then 0 else 2 end, type, displayname, orderby" ); 
$lasttype = "";
foreach( $alldocs as $t )
{

if( $t["type"] != $lasttype )
{

echo( "<li><b>" . $doctypes[$t["type"]] . "</b></li>" );
$lasttype = $t["type"];
}

echo( "<li><a target=_blank href='uploadedpdfs/" . ($t["path"]) . "'>" . ($t["displayname"]) . "</a></li>" );
}
?>

<?php } ?>
<li class="subnav-title">HR</li>
<li ><a href="/humanresources.php">Human Resources</a></li>
<li class='subnav-last'><a href="/trainerinfo.php">Information From ESI</a></li>
<li class="subnav-title">Calendar</li>
<li><a href="requesttotrain.php" class="doenav"><font color='red'>Click Here Daily to Respond to Open Trainer Requests</font></a></li>
<li><a href="tcalendar.php" class="doenav">Go To Calendar</a></li>
<?php if( !($thisusersrow["national"]) ) { ?>
<li><a href="print_daily_schedule.php" class="doenav">Print Today's Schedule</a></li>
<li class="subnav-last"><a href="fax_registration.php" class="doenav">Schedule by Fax</a></li>

<li class="subnav-title">My Information</li>
<?php if( $thisusersrow["tcfaculty"] ) { ?>
<li><a href="https://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/trainers.php" class="doenav">My Trainers</a></li>
<?php } ?>

<li><a href="trainer_profile_view.php" class="doenav">Profile</a></li>
<li><a href="trainer_availability.php" class="doenav">Availability</a></li>
<?php if( $thisusersrow["viewschools"] ) { ?>
<li><a href="drillcalendar.php" class="doenav">Code Blue Drill Availability</a></li>
<li><a href="schools.php" class="doenav">Manage Schools</a></li>

<?php

if( $visi ) { ?>
<li class="subnav-title">Reports</li>
<?php
if( $visi ) {
$inyourarea = db_query_first_cell( "select count(aedid) from aed_esi a, company_esi where iscorp = 0 and company_esi.isactive = 1 and company_esi.deleted = 0 and a.deleted = 0 and newinstall = 1 and clientid = company_esi.id " . $visi );

$numschools = db_query_first_cell( "select count(id) from company_esi where iscorp = 0 and isactive = 1  and showsondrillreports = 1 and deleted = 0 " . $visi );

if( $inyourarea || $numschools ) {
?>
<li>
Schools: <?= $numschools ?><br>
<?php if( $inyourarea ) { ?>
<a href='newinstalls.php'><font color='red'><b><?= $inyourarea ?> new installations in your area!</font></b></a> <A href='newinstalls.php?xls=true'><font color='red'><b>(xls)</b></font></a></font>
</li>
<?php }  }
}?>
<?php if( $thisusersrow["viewschools"] ) { ?>
<li><a href="drills.php" class="doenav">Drill Tracking</a></li>
<?php } ?>
<li><a href="allschools.php?nodrills=true&go=true&onscreen=true" class="doenav">Schools w/o Drills</a> <a href="allschools.php?nodrills=true&go=true" class="doenav">(xls)</a></li>
<li><a href="faileddrillreport.php?concat=true" class="doenav">Failed Drill Report</a> <a href="faileddrillreport.php?xls=true&concat=true" class="doenav">(xls)</a></li>
<li><a href="followupdrillreport.php?concat=true" class="doenav">Followup Drill Report</a> <a href="followupdrillreport.php?xls=true&concat=true" class="doenav">(xls)</a></li>
<li><a href="expiringreporttrainer.php" class="doenav">Expired AEDs In Your Area</a> <a href="expiringreporttrainer.php?xls=true" class="doenav">(xls)</a></li>
<li><a href="aedpadreport.php">Pads Forecast Report</a> <a href="aedpadreport.php?xls=1">(xls)</a></li>
<?php } ?>
<?php } ?>

<?php } ?>
<li><a href="http://<?php echo SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN; ?>/login.php">Log out</a></li>

<?php if( $showaedlink ) { ?>
<?php if( $id ) { ?>
<li><span class='copy'><img src='fr.jpg' height='60'><br>
Check your own AED. <br><a href='pdfs/monthly<?= ($session_iscorp) ? "_corp" : "" ?>.pdf' target=_blank><font class='body'>Print Monthly Checklist</font></a>
<span class='copy'><br><a href='printaedsign.php?id=<?= $id ?>'>Print AED Sign</a><br><br></span></li>
<?php }?>
<?php }?>
</ul>
</div> <!--end col-left-->