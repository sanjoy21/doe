<?php
require_once('mysql.php');

if( getcurrentusercompany() > 0 )
{
header( "location: login.php" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">REPORTS</span></strong>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
        <tr height="23" bgcolor="#e1e1f6"><td valign="top"><span class="copy"><strong>View Reports:</strong></span></td></tr>
<tr bgcolor="#ffffff"><td valign="bottom"><span class="copy">

<form method='post' action='report.php'>
<table cellpadding="3" cellspacing="0" border="0" class="table3" >
            <tr>
            <td valign="middle"><span class="copy"><input type='hidden' name='xls' value='true'>View </span></td>
<td valign="middle"><span class="copy"><select class=copy name='data'><option value='company'>Schools</option></select></span></td>
<td valign="middle"><span class="copy">by</span></td>
<td valign="middle"><span class="copy"><select class=copy name='ob'><option value='borough, region, zip'>Borough, Region, Zip Code</option></select></span></td>
<td valign="middle"><input type='submit' name='go' value='Go'></td></form>
            </tr>
            </table>
</form>
<form method='post' action='reportresponders.php'><input type='hidden' name='order' value='order by lastname, firstname'>
<table cellpadding="3" cellspacing="0" border="0" class="table3" >
            <tr>
            <td valign="middle"><span class="copy">View Responders </span></td>
<td valign="middle"><span class="copy">By <select class=copy name='fieldname'><option value='lastname'>Last Name</option><option value='borough'>Borough</option></select>: <input size='10' class=copy name='fieldvalue'></span></td>
<Td><span class='copy'><input type='checkbox' value='x' name='xls'> XLS?</td>
<td valign="middle"><input type='submit' name='go' value='Go'></td></form>
            </tr>
            </table>
</form>   
<form method='get' action='reportactions.php'>
<table cellpadding="3" cellspacing="0" border="0" class="table3" >
<tr>
<td valign="middle"><table><tr><td><span class="copy">View <select class=copy name='type'><option value='drill'>Drills</option><option value='servicecall'>Service Calls</option></select><br>
<span class='copy'><nobr>Borough:<select class='copy' name='borough'>
<option value=''>Any</option>
 <option value="Bronx">The Bronx</option>
 <option value="Brooklyn">Brooklyn</option>
 <option value="Manhattan">Manhattan</option>
 <option value="Queens">Queens</option>
 <option value="Staten Island">Staten Is.</option>
</select></nobr><br>
<span class='copy'><input type='checkbox' value='1' name='showcampus'> <?php echo getSchoolStr( "School" ); ?>/<?php echo getSchoolStr( "Campus" ); ?><br>
</td><td align='right' class=copy > From: <input size='10' class=copy  name='fieldfrom'><br> To: <input class=copy size='10' name='fieldto'><br>YYYY-MM-DD</span></td></tr></table></td>

<td>
<table><tr><td valign='top'>
<?php if( isOverallAdmin() ) { ?>
<span class='copy'><input type='checkbox' value='1' name='newinstall'> New Installs Only?<br>
<span class='copy'><input type='checkbox' value='1' name='notcompleted'> Not Completed Only?<br>
<span class='copy'><input type='checkbox' value='1' name='excludewithdrill'> Exclude "With Drill"<br>
</td>
<td valign='top'>
<span class='copy'><input type='checkbox' value='1' name='notinvoiced'> Not Invoiced Only<br>
<span class='copy'><input type='checkbox' value='1' name='actionneeded'> Action Needed?<br>

<?php } ?>
<span class='copy'><input type='checkbox' value='1' name='xls'> XLS?</td>
</td></tr></table>
<td valign="middle"><input type='submit' name='go' value='Go'></td></form>
</tr>
</table>
</form>
<?php if( 1 == 0 ) { ?>
<form name='tstest' method='post'><span class='copy'>
Number of people trained between:  <?php echo printdates2( "trainfrom", isset($trainfrom) ? $trainfrom : '' ); ?> and <?php echo printdates2( "trainto", isset($trainto) ? $trainto : '' ); ?>
 <input type='submit' name='gonumtrained' value='Go'>
<?php 
if( isset($gonumtrained) && $gonumtrained ) {  
$sql = "select count( distinct( responders_esi.responderid ) ) from responder_training_dates, responders_esi, company_esi where company_esi.id = responders_esi.clientid and responders_esi.responderid = responder_training_dates.responderid  and iscorp = '$session_iscorp' and responders_esi.deleted = 0 ";

if( isset($trainfrom) && $trainfrom )
{
$sql .= " and responder_training_dates.trainingdate >= '". fixdate( $trainfrom ) . "' ";
}
if( isset($trainto) && $trainto )
{
$sql .= " and responder_training_dates.trainingdate <= '". fixdate( $trainto ) . "' ";
}
//echo( $sql );
$numtrained = db_query_first_cell( $sql );

$sql = "select count( distinct( responder_training_dates.id ) ) from responder_training_dates, responders_esi, company_esi where company_esi.id = responders_esi.clientid and iscorp = '$session_iscorp' and responders_esi.responderid = responder_training_dates.responderid and responders_esi.deleted = 0 ";

if( isset($trainfrom) && $trainfrom )
{
$sql .= " and responder_training_dates.trainingdate >= '". fixdate( $trainfrom ) . "' ";
}
if( isset($trainto) && $trainto )
{
$sql .= " and responder_training_dates.trainingdate <= '". fixdate( $trainto ) . "' ";
}
//oecho( $sql );
$numtrained2 = db_query_first_cell( $sql );

$sql = "select count( distinct( responder_to_class.id ) ) from responder_to_class, responders_esi, class, company_esi where company_esi.id = responders_esi.clientid and iscorp = '$session_iscorp' and responder_to_class.responderid = responders_esi.responderid and class.deleted = 0 and accepted = 1 and canceldate is null and class.id = classid ";

if( isset($trainfrom) && $trainfrom )
{
$sql .= " and class.startdate >= '". fixdate( $trainfrom ) . "' ";
}
if( isset($trainto) && $trainto )
{
$sql .= " and class.startdate <= '". fixdate( $trainto ) . "' ";
}
//echo( $sql );
$numtrained3 = db_query_first_cell( $sql );

?>
people: <font color='red'><?php echo isset($numtrained) ? $numtrained : ''; ?></font><br>
instances: <font color='red'><?php echo isset($numtrained2) ? $numtrained2 : ''; ?></font><br>
people in classes: <font color='red'><?php echo isset($numtrained3) ? $numtrained3 : ''; ?></font>
<?php } ?>
</form>

<form name='tstest2' method='post'><span class='copy'>
Number of people scheduled for training between:  <?php echo printdates2( "trainingfrom", isset($trainingfrom) ? $trainingfrom : '' ); ?> and <?php echo printdates2( "trainingto", isset($trainingto) ? $trainingto : '' ); ?>
 <input type='submit' name='goschedtraining' value='Go'>
<?php 
if( isset($goschedtraining) && $goschedtraining ) {  
$sql = "select sum( maxattendees ) as cnt, class.code, group_concat( distinct( id ) ) as classes  from class where accepted = 1 ";
//$sql = "select count( responders_esi.responderid ) as cnt, class.code, group_concat( distinct( classid ) ) as classes  from responder_to_class, responders_esi, class, company_esi where company_esi.id = responders_esi.clientid and responders_esi.responderid = responder_to_class.responderid  and responders_esi.deleted = 0 and class.id = classid and accepted = 1 ";

if( isset($trainingfrom) && $trainingfrom )
{
$sql .= " and class.startdate >= '". fixdate( $trainingfrom ) . "' ";
}
if( isset($trainingto) && $trainingto )
{
$sql .= " and class.startdate <= '". fixdate( $trainingto ) . "' ";
}

$sql .= " group by code" ;
//echo( $sql );
$numtrained3 = db_query_rows( $sql );
echo( "<br><br><table border='1' cellspacing=0><tr><th>Type</th><th>#</th><th>Classes</th></tr>" );
foreach( $numtrained3 as $row )
{
$cl = isset($row['classes']) ? explode( ",", $row['classes'] ) : array();
$clarr = array();
foreach( $cl as $cid )
{
//$isco = db_query_first_cell( "select iscorp from company_esi, class where companyid = company_esi.id and class.id = $cid" );
$clarr[] = "<a target=_blank href='class_detail.php?id=$cid'>$cid</a>";
}
$clname = isset($allclass_names[1][$row['code']]) ? $allclass_names[1][$row['code']] : (isset($allclass_names[0][$row['code']]) ? $allclass_names[0][$row['code']] : '');
echo( "<tr><td>" . $clname . "</td><td>" . $row['cnt'] . "</td><td>" . join( ", ", $clarr ) . "</td></tr>" );
}
?>
</table>
<?php } ?>
</form>
<?php } ?>

<form method='post' >
<p><span class="copy">
<table border=1 cellpadding=4 cellspacing=0><tr><td>
<b>Schools</b>
<table>

<tr><td><a href="allschools.php">All <?php echo getSchoolStr( "Schools" ); ?></a></td></tr>
<tr><td><a href="schoolsresponders.php"><?php echo getSchoolStr( "School" ); ?> Trained Responders Summary</a> <a href="schoolsresponders.php?xls=true">(xls)</a> </td></tr>
<tr><td><a href="faileddrillreport.php?concat=true">Failed Drill Report</a> <a href="faileddrillreport.php?xls=true&concat=true">(xls)</a></td></tr>
<?php if( isOverallAdmin() ) { ?>
<tr><td><a href="aednobuildingcode.php">AEDs with no building code</a> </td></tr>
<tr><td><a href="buildingcodenoaeds.php?noaeds=true">Buildings with No AEDs</a> <a href="buildingcodenoaeds.php?noaeds=true&xls=1">(xls)</a> </td></tr>
<tr><td><a href="allschools.php?nodrills=true&go=true&onscreen=true">Schools With No Drills </a> <a href="allschools.php?nodrills=true&go=true">(xls)</a></td></tr>
<tr><td><a href="allschools.php?nodrillsneeded=true&go=true&onscreen=true">Schools With "Drill Reports" Unchecked  </a> <a href="allschools.php?nodrillsneeded=true&go=true">(xls)</a></td></tr>
<tr><td><a href="aedpadreport.php">Pads Forecast Report</a> <a href="aedpadreport.php?xls=1">(xls)</a></td></tr>
<tr><td><a href="corporateinspectionreport.php?onscreen=1">Corporate Inspection Report</a> <a href="corporateinspectionreport.php?xls=1">(xls)</a></td></tr>
<tr><td>        <a href='companyemails.php'>Company Email Report</a></td></tr>
<tr><td>        <a href='responders.php'>Responder Search</a></td></tr>
<?php } ?>

</table>
</td><td valign='top'>
<b>Responders</b>
<table>
<?php if( isOverallAdmin() ) { ?>
<tr><td><a href="respondersbyregion.php">Responders By Region</a></td></tr>
<tr><td>        <a href='sendspecifictrainers.php'>Send Trainer Requests for Specific Date</a></td></tr>
<tr><td>        <a href='trainers.php?expdateexport=1'>Export Trainer Expiration Dates</a>  <a href='trainers.php?expdateexport=1&expired=1'>(expired only)</a> </td></tr>
<tr><td><a href="next3.php"><?php echo getSchoolStr( "Schools" ); ?> with all responders expiring in the next 3 months</a> <a href="next3.php?&xls=true">(xls)</a></td></tr>
<?php } ?>
<tr><td><a href="allexpired.php"><?php echo getSchoolStr( "Schools" ); ?> with No Current Trained Responders</a> <a href="allexpired.php?xls=true">(xls)</a></td></tr>
<tr><td><a href="standalonereport.php">Stand alone schools with ALL responders expiring before Dec 1 2021 <a href="standalonereport.php?xls=true">(xls)</a></td></tr>
<tr><td><a href="allexpired.php?minnum=5"><?php echo getSchoolStr( "Schools" ); ?> with Fewer than Six Trained Responders</a> <a href="allexpired.php?minnum=5&xls=true">(csv)</a></td></tr>
<?php if( !$session_iscorp ) { ?>
<?php if( $session_userid == "sarahg@emergencyskills.com" || $session_userid == "rebekah@emergencyskills.com" || $session_userid == "noah@emergencyskills.com" ) { ?>
<tr><td><a href="prekreport.php">Pre-K Report</a></td></tr>
<tr><td><a href="monthlyreminderreport.php">Monthly Reminder Report</a></td></tr>
<tr><td><a href="monthlyreminders.php">Monthly Reminders</a></td></tr>
<?php } ?>

<?php } ?>
<?php if( isOverallAdmin() ) { ?>
<tr><td><a href="upcomingattendees.php">Upcoming Attendees By Region</a></td></tr>
<?php } ?>

</table></td></tr>
<tr><td colspan='2'><br><br></td></tr>
<tr><td valign='top'><b>AEDs</b>
<table>
<tr><td><a href="reportaeds.php?order=order+by+serial">AEDs by Serial Number</a> <a href="reportaeds.php?order=order+by+serial&xls=true">(xls)</a>
<!--<a href="reportaeds.php?order=order+by+serial&csv=true">(csv)</a>-->
</td></tr>
<?php if( isOverallAdmin() ) { ?>
<tr><td><a href="reportaeds.php?order=order+by+serial&psal=true">PSAL AEDs by Serial Number</a> <a href="reportaeds.php?psal=true&order=order+by+serial&xls=true">(xls)</a></td></tr>
<?php } ?>
<tr><td><a href="reportaeds.php?expired=true&order=order+by+serial">Expired AEDs</a> <a href="reportaeds.php?expired=true&order=order+by+serial&xls=true">(xls)</a></td></tr>
<tr><td><a href="reportaeds.php?missing=true&order=order+by+serial">Missing AEDs</a> <a href="reportaeds.php?missing=true&order=order+by+serial&xls=true">(xls)</a></td></tr>
<tr><td><a href="reportaeds.php?stolen=true&order=order+by+serial">Stolen AEDs</a> <a href="reportaeds.php?stolen=true&order=order+by+serial&xls=true">(xls)</a></td></tr>
<?php if( isOverallAdmin() ) { ?>
<tr><td><a href="reportaeds.php?newinstall=true&order=order+by+serial">Newly Installed AEDs</a> <a href="reportaeds.php?newinstall=true&order=order+by+serial&xls=true">(xls)</a></td></tr>
<tr><td><a href="reportaeds.php?installcomplete=true&order=order+by+serial">Completed but not Invoiced AEDs</a> <a href="reportaeds.php?installcomplete=true&order=order+by+serial&xls=true">(xls)</a></td></tr>
<?php } ?>
<?php if( !$session_iscorp ) { ?>
<tr><td><a href="nowarranty.php">Schools with all out of warranty AEDs</a></td></tr>
<tr><td><a href="reportaeds.php?notwithinyear=true&order=order+by+serial">AEDs not inspected in the last year</a> <a href="reportaeds.php?notwithinyear=true&order=order+by+serial&xls=true">(xls)</a></td></tr>
<?php } else { ?>
<tr><td><a href="imeiaeds.php">IMEI Report</a> <a href="imeiaeds.php?xls=true">(xls)</a></td></tr>
<?php } ?>
<?php if( isOverallAdmin() ) { ?>
<tr><td><a href="aednotavailablereport.php">Not Available AED Report</a></td></tr>
<tr><td><a href="monthlyaedchecklistdata.php">Monthly AED Inspection Report</a></td></tr>
<tr><td><a href="aedeventreport.php">AED Event Report</a> <a href="aedeventreport.php?xls=true">(xls)</a></td></tr>
<?php } ?>
</table></td>
<td valign='top'>
<b>Other</b>
<table>
<?php if( isOverallAdmin() ) {  ?>
<tr><td><a href="classreport.php"><b>Class Summary</b></a></td></tr>
<tr><td><a href="programsummaryreport.php">Program Summary</a></td></tr>
<tr><td><a href="equipmentstatus.php">Equipment Status</a></td></tr>
<tr><td><a href="equipmentshipsummary.php">Equipment Shipping Summary</a></td></tr>
<tr><td><a href="rosterreceived.php">Roster Received</a> <a href="rosterreceived.php?xls=1">(xls) </a></td></tr>
<tr><td><a href="cardsmailed.php">Cards Mailed</a> <a href="cardsmailed.php?xls=1">(xls) </a></td></tr>
<tr><td><a href="notesreport.php">Notes Report</a> <a href="notesreport.php?xls=true">(xls)</a></td></tr>
<?php if( !$session_iscorp ) { ?>
<tr><td><a href="notifydoereport.php">Notify DOE Report</a></td></tr>
<?php } ?>
<?php } ?>
<?php if( isOverallAdmin() ) { ?>
   <?php if( !$session_iscorp ) { ?>
<tr><td><a href="coachesreport.php">Coaches Report</a></td></tr>
<tr><td><a href="bic.php">BIC Report</a> <a href="bic.php?xls=1">(xls)</a></td></tr>
<tr><td><a href="writedoereport.php">RESMCO Report</a></td></tr>
<tr><td><a href="fieldrepreport.php">Field Representative Report</a></td></tr>
<tr><td><a href="instructorsreport.php">Instructors Report</a></td></tr>
<?php if( $session_userid == "sarahg@emergencyskills.com" ) { ?>
<tr><td valign='top'><a href="#" onClick='showFollow(0)' class="doenav">Followup Drill Report</a> <a  onClick='showFollow(1)' href='#' class="doenav">(xls)</a></td></tr>
<?php } ?>
<?php } else { ?>
<?php if( isset($session_iscorp) && $session_iscorp == AGING ) { ?>
<tr><td><a href="writedoereport.php">RESMCO Report</a></td></tr>
<?php } ?>
<tr><td><a href="recertreport.php">Recertification Report</a></td></tr>
<tr><td><a href="duedatereport.php">Service Call Due Date Report</a></td></tr>
<tr><td><a href="exportblackstone.php">Blackstone Interest Poll</a></td></tr>
<?php } ?>
<?php }?>
</table></td></tr>
</table>
</span>
<br><br></td></tr>

</td></tr>
</table>

<br><br><br><br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
<script language='javascript'>
function showFollow( xls )
{
var fromdate = prompt( "What starting date (mm/dd/yyyy)?" );
var todate = prompt( "What ending date (mm/dd/yyyy)?" );
document.location.href = 'followupdrillreport.php?xls=' + xls + '&concat=true&all=true&fieldfrom=' + fromdate + '&fieldto=' + todate;
}

</script>
</div>
</body>
</html>