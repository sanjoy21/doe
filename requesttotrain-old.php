<?php
$headernotfixed = 1;
$nologinrequired = true;
require_once "mysql.php";
// Safely define external variables assumed to exist

$trainerid = $trainerid ?? null;
$id = $id ?? null;
$confirm = $confirm ?? null;
$undo = $undo ?? null;
$deny = $deny ?? null;
$whynot = $whynot ?? null;
$thisusersrow = $thisusersrow ?? []; // Assumed to be populated by external authentication


$tid = $trainerid/1234;

if( !$trainerid && ($thisusersrow["usertype"]) == "trainer" )
{
$tid = $thisusersrow['id'];
$trainerid = ($thisusersrow['id']) * 1234;
}

$trow = db_query_first( "select * from user where id = $tid" );

if( $id )
{
$already = db_query_first_cell( "select id from requesttotrain where trainerid = $tid and classid = $id " );
if( !$already )
db_query( "insert into requesttotrain ( trainerid, classid, requestdate, done, updatedate ) values ( $tid, $id, now(), -4, now() )" );
$mydone = db_query_first_cell( "select done from requesttotrain where trainerid = $tid and classid = $id " );
if( $mydone == -6 )
{
db_query("update requesttotrain set done = -4, updatedate = now() where trainerid = $tid and classid = $id" );
}
}

if( $id && $trainerid && $confirm )
{
db_query( "update requesttotrain set done = 0, updatedate = now() where trainerid = $tid and classid = $id " );
exit;
}
if( $id && $trainerid && $undo )
{
db_query( "update requesttotrain set done = -4, whynot = null, updatedate = now() where trainerid = $tid and classid = $id " );
exit;
}
if( $id && $trainerid && $deny )
{
db_query( "update requesttotrain set done = -5, whynot = '$whynot', updatedate = now() where trainerid = $tid and classid = $id " );
exit;
}

if( $id )
{
$crow = getClassRow( $id );
$companyrow = getCompanyRow( $crow['companyid']);
$class_names = $allclass_names[$companyrow['iscorp']];
$alreadyval = db_query_first_cell( "select done from requesttotrain where trainerid = $tid and classid = $id " );
$alreadyreason = db_query_first_cell( "select whynot from requesttotrain where trainerid = $tid and classid = $id " );
if( $alreadyval != "-4" )
$id = "";
}

$threedays = date( "Y-m-d", mktime( 0,0,0,date( "m" ), date( "d" )+4, date("Y" ) ) );
$sql = ( "select class.* from class, company_esi where startdate > now() and startdate <= '$threedays 23:59:59' and accepted = 1 and canceldate is null and code not in ( 'MHFA', 'AEDI', 'Inspections', 'MHFA', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc' ) and iscorp <> 3 and companyid = company_esi.id and isnational = 0 and companyname not like 'Sample%' and companyname not like 'Open Registration' and numtrainers > ( select count(*) from trainer_to_class where classid = class.id ) and class.id not in ( select classid from trainer_to_class where trainerid = $tid ) " ); //
$threedaysfromnow = db_query_rows( $sql, "id" );
$sevendays = date( "Y-m-d", mktime( 0,0,0,date( "m" ), date( "d" )+7, date("Y" ) ) );
$sql = ( "select class.* from class, company_esi where startdate > '$threedays 23:59:59' and startdate <= '$sevendays 23:59:59' and accepted = 1 and canceldate is null and code not in ( 'MHFA', 'AEDI', 'Inspections', 'MHFA', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc' ) and iscorp <> 3 and companyid = company_esi.id and isnational = 0 and companyname not like 'Sample%' and companyname not like 'Open Registration' and class.id not in ( select classid from trainer_to_class where trainerid = $tid ) and numtrainers > ( select count(*) from trainer_to_class where classid = class.id ) " ); //
$sevendaysfromnow = db_query_rows( $sql, "id" );

$fourteendays = date( "Y-m-d", mktime( 0,0,0,date( "m" ), date( "d" )+21, date("Y" ) ) );
$sql = ( "select class.* from class, company_esi where startdate > '$sevendays 23:59:59' and startdate <= '$fourteendays 23:59:59' and accepted = 1 and canceldate is null and code not in ( 'MHFA', 'AEDI', 'Inspections', 'MHFA', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc' ) and iscorp <> 3 and companyid = company_esi.id and isnational = 0 and companyname not like 'Sample%' and companyname not like 'Open Registration' and class.id not in ( select classid from trainer_to_class where trainerid = $tid ) and numtrainers > ( select count(*) from trainer_to_class where classid = class.id ) " );//
$fourteendaysfromnow = db_query_rows( $sql, "id" );

$alreadydisplayed = array();

include "ssi/top.php"; 
?>

<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v2.1.1/mapbox-gl.js'></script>
<link href='https://api.tiles.mapbox.com/mapbox-gl-js/v2.1.1/mapbox-gl.css' rel='stylesheet' />

<style>
table.whatever td { padding: 2px }
#map {
position: absolute;
width: 100%;
border: 1px;
height: 500px;
width: 700px;
display: block;
}
#backme {
width: 100%;
border: 1px;
height: 500px;
width: 700px;
}

</style>
<?php
ob_start();
?>
Requests for <?php echo $trow['first_name']; ?> <?php echo $trow['last_name']; ?><br>
<?php if( count( $threedaysfromnow ) ) { ?>
<a href='#three'><font color='red'>Jump To Urgent Classes Within 4 Days Needing a Trainer</font></a>
<?php } ?>
<table class='whatever' cellpadding=2 cellspacing=0 border=1><tr><th>Class ID</th><th>Date</th><th>Class Type</th><th>Location</th><th>Training Address</th><th>Status</th></tr>
<?php if( $id )
{
?>
<?php if( $confirm ) { ?>
Thanks! You will be contacted soon.
<?php } else if( $deny ) { ?>
Thank you.
<?php } else {
$a = getTrainingAddress( $crow );
$gmap = "<A target=_blank href='http://maps.google.com/?q=$a'>$a</a>";
$snote2 = db_query_first_cell( "select dt from peakdates where dt = '".date( "Y-m-d", strtotime( $crow['startdate'] ) ). "'" );
if( $snote2 )
$ext = "<br><font color='red'>PEAK</font>";

$alreadydisplayed[$id] = $crow;
echo("
<tr>
<td colspan=\"20\" style=\"height: 1px;\"><a name='class".$crow["id"]."'></a></td></tr>
<tr>
<tr>

<td>".$crow["id"]."</td>

<td>
". date('l', strtotime(getFormattedDate($crow['startdate'])))  . " " .getFormattedDateWTime( $crow['startdate'])." " . getEndDateStr( $crow['enddate'])."$ext</td>
<td>".$class_names[$crow["code"]]."</td>
<td>".$companyrow['companyname']."</td>
<td>".(($crow['remote'])?"<b>REMOTE CLASS</b><br>":getSchoolStr( "Training Location" ) . ": ". $gmap. "<Br>" ) );
if($crow['parking_security'])
echo("Parking Information: ".$crow['parking_security']."<Br>");
if($crow['nearest_subway'])
echo("Subway Information: ".$crow['nearest_subway']."</td>");

$yesval = !$alreadyval?"SELECTED":"";
$noworking = $alreadyreason == "No, working for someone else"?"SELECTED":"";
$nopersonal = $alreadyreason == "No, personal"?"SELECTED":"";
$nopublic = $alreadyreason == "No, not accessible by public transport"?"SELECTED":"";
echo("
<td>
<select name='whynot' onChange=\"notavailtoteachclass( '$id', '$trainerid', this.options[this.selectedIndex].value )\">
<option value=''>Are you available to teach this class?</option>
<option $yesval value='Yes'>Yes</option>
<option $noworking value='No, working for someone else'>No, working for someone else</option>
<option $nopersonal value='No, personal'>No, personal</option>
<option $nopublic value='No, not accessible by public transport'>No, not accessible by public transport</option>
</select>
<span id='statusfor{$id}'>
</span>
</td>
</tr>
" );

// }
 // else if( !$alreadyval )
 // {
 //  echo( "<td>Available</td>" );
 // }
 // else if( $alreadyval == -5 )
 // {
 //  echo( "<td>Not Available</td>" );
 // }

}
}

$otherclasses = db_query_rows( "select class.* from requesttotrain, class where done in ( -4, -6 ) and requesttotrain.trainerid = $tid and class.id = requesttotrain.classid and startdate > now() and classid <> '$id' and accepted = 1 and canceldate is null and class.id not in ( select classid from trainer_to_class where trainerid = $tid ) order by class.startdate", "id" );
$othergrouping = array();
if( count( $threedaysfromnow ) )
{
$othergrouping["Urgent Classes"] = $threedaysfromnow;
}
$othergrouping[($id?"Other":"") . " Open Classes"] = $otherclasses;
if( count( $sevendaysfromnow ) )
{
$othergrouping["Classes within 1 Week Needing Trainers"] = $sevendaysfromnow;
}
if( count( $fourteendaysfromnow ) )
{
$othergrouping["Classes within 2 Weeks Needing Trainers"] = $fourteendaysfromnow;
}
foreach( $othergrouping as $sectiontype=>$otherclasses )
{
$any = false;
foreach( $otherclasses as $o )
{
$num = $o['numtrainers'];
$numexisting = db_query_first_cell( "select count(*) from trainer_to_class where classid = {$o['id']}" );
 if( $numexisting < $num )
{
$class_id = $o['id'];
if( $alreadydisplayed[$class_id]) continue;
$crow = getClassRow( $class_id );
$companyrow = getCompanyRow( $crow['companyid']);
$class_names = $allclass_names[$companyrow['iscorp']];
$a = getTrainingAddress( $crow );
$gmap = "<A target=_blank href='http://maps.google.com/?q=$a'>$a</a>";
$fnt = "";
if( strtotime( date( "Y-m-d" ) . "+ 7 days" ) > strtotime( $crow['startdate']) )
{
$fnt = "<font color='red'>" ;
}
$existingid = db_query_first_cell( "select id from requesttotrain where trainerid = $tid and classid = $class_id " );
if( $existingid )
{
$alreadyval = db_query_first_cell( "select done from requesttotrain where trainerid = $tid and classid = $class_id " );
$alreadyreason = db_query_first_cell( "select whynot from requesttotrain where trainerid = $tid and classid = $class_id " );
}
else
{
$alreadyreason = "";
$alreadyval = -4; // -4 is not yet responded
}
if( $id == 29557 )
echo( $id . ":"  . $alreadyval. "<br>" );

if( $alreadyval != -4 && strpos( $sectiontype, "within" ) !== false ) continue;
if( strpos( $sectiontype, "within" ) !== false )
{
// check if this trainer WOULD be sent the class
$inlist = requestTrainers( $class_id, false, false, $tid );
if( !in_array( $tid, $inlist ) )
continue;
}

if( !$any )
{
if( $sectiontype=="Urgent Classes" )
echo( "<a name='three'></a>" );
echo( "<tr><td colspan='20'><h3>".$sectiontype."</th></tr>" );
}
$any = 1;
$ext = "";
$snote2 = db_query_first_cell( "select dt from peakdates where dt = '".date( "Y-m-d", strtotime( $crow['startdate']) ). "'" );
if( $snote2 )
$ext = "<br><font color='red'>PEAK</font>";

$alreadydisplayed[$class_id] = $o;
echo( "
<tr>
<td colspan=\"20\" style=\"height: 1px;\"><a name='class".$crow["id"]."'></a></td></tr>
<tr>
<tr><td>".$crow["id"]."</td>
<td>".$fnt."
". date('l', strtotime(getFormattedDate($crow['startdate']))) . " " .getFormattedDateWTime( $crow['startdate'])." " . getEndDateStr( $crow['enddate'])."$ext</font></td>
<td>".$class_names[$crow['code']]."</td>
<td>".$companyrow['companyname']."</td>
<td>". (($crow['remote'])?"<b>REMOTE CLASS</b><br>":getSchoolStr( "Training Location" ) . ": ". $gmap. "<Br>" ) );
if( $crow['parking_security'] )
echo( "Parking Information: ".$crow['parking_security']."<Br>" );
if( $crow['nearest_subway'] )
echo( "Subway Information: ".$crow['nearest_subway']."</td>" );
$yesval = !$alreadyval?"SELECTED":"";
$noworking = $alreadyreason == "No, working for someone else"?"SELECTED":"";
$nopersonal = $alreadyreason == "No, personal"?"SELECTED":"";
$nopublic = $alreadyreason == "No, not accessible by public transport"?"SELECTED":"";
echo("
<td>
<select name='whynot' onChange=\"notavailtoteachclass( '$class_id', '$trainerid', this.options[this.selectedIndex].value )\">
<option value=''>Are you available to teach this class?</option>
<option $yesval value='Yes'>Yes</option>
<option $noworking value='No, working for someone else'>No, working for someone else</option>
<option $nopersonal value='No, personal'>No, personal</option>
<option $nopublic value='No, not accessible by public transport'>No, not accessible by public transport</option>
</select>
<span id='statusfor{$class_id}'>
</span>
</td></tr>
" );

}
}
}
?>
</table>
<br><br>
<br><br>
<script language='javascript'>
function submitWhyNot( ele )
{
if( ele.selectedIndex > 0 )
{
opt = ele.options[ele.selectedIndex].value;
if( opt == "Other" )
{
// Note: prompt is used as per original logic, but be aware of iframe limitations.
opt = prompt( "Please explain:" );
}
if( opt != "" && opt != null )
document.location.href="requesttotrain.php?deny=1&id=<?php echo $id; ?>&trainerid=<?php echo $trainerid; ?>&whynot=" + escape( opt );
else
{
// Replaced alert() with a console error and visual cue
console.error( "You must choose a reason." );
document.getElementById('statusfor<?php echo $id; ?>').innerHTML = "<font color='red'>Must choose reason.</font>";
}
}
}
function notavailtoteachclass( id, trainerid, whynot )
{
if( whynot == "Yes")
{
availtoteachclass( id, trainerid );
return;
}

// Deny / Not Available logic
var strURL = "requesttotrain.php?deny=1&id="+id+"&trainerid="+trainerid+"&whynot=" + encodeURIComponent( whynot );

// Undo logic (if user selects the empty option)
if( whynot == "" )
{
strURL = "requesttotrain.php?undo=1&id="+id+"&trainerid="+trainerid+"&whynot=" + encodeURIComponent( whynot );
}

$.ajax({
url: strURL,
})
.done(function( data ) {
$("#statusfor" + id).html("Saved");
});
return false;

}
function availtoteachclass( id, trainerid )
{
// Confirm / Available logic
var strURL = "requesttotrain.php?confirm=1&id="+id+"&trainerid="+trainerid;
$.ajax({
url: strURL,
})
.done(function( data ) {
$("#statusfor" + id).html("Saved. You will be contacted. ");
});
return false;

}
</script>
<?php
$cont = ob_get_contents();
ob_end_clean();
// if( $tid == 271 )
{
include "map-include.php";
}
echo( $cont );
?>

<?php 

include "ssi/footer.php"; 
?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>