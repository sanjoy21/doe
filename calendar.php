<?php
require_once('mysql.php');

if( getcurrentusertype() != 'principal' )
{
Header( "location: login.php" );
exit;
}
$companyid = get_companyid($session_id);
//echo $companyid;exit;
$iscalendar = 1;
$today_month = date("n");
$today_year = date("Y");

if (!isset($month) || !$month) {
$month = $today_month;
}

if (!isset($year) || !$year) {
$year = $today_year;
}

${"selected_".$month} = "SELECTED";
${"selected_".$year} = "SELECTED";

if( !isset($showdeleted) || !$showdeleted )
{
$sh = "AND class.deleted = 0";
}

//sendHTMLMail( "rachelc@vireo.org", "who knows", "will this send", "info@emergencyskills.com" );
//sendHTMLMail( "rachelc@gmail.com", "who knows", "will this send", "info@emergencyskills.com" );

$qs = "month=$month&year=$year";
$withoutmonthqs = "";
foreach( array( "confroom", "showcompleted", "showcards",  "doh", "showdeleted" ) as $tmp )
{
    if( isset( $_GET[$tmp] ) )
    {
        $qs .= "&$tmp=" . $_GET[$tmp];
        $withoutmonthqs .= "&$tmp=" . $_GET[$tmp];
    }
}

if( isset($confroom) && $confroom )
{
$sh .= " and isconferenceroom = 1";
}
if( isset($doh) && $doh )
{
$sh .= " and company_esi.campusid = 3235";
}
if( !isset($showinspections) || !$showinspections )
{
$sh .= " and class.code <> 'Inspections'";
}

if( isset($ashi) && $ashi )
{
$sh .= " and class.code like 'ASHI%'";
}

if( isset($remote) && $remote )
{
if( $remote == -1 )
$sh .= " and class.remote = '0'";
else
$sh .= " and class.remote = '1'";
}

if( isset($blendedonly) && $blendedonly )
{
$sh .= " and class.blendedlearning = '1'";
}

if( isset($noconfirmed) && $noconfirmed )
{
$sh .= " and class.iscallconfirmed = '0'";
}

if( isset($adapt) && $adapt )
{
$sh .= " and company_esi.campusid = '2437'";
}

if( isset($aging) && $aging )
{
$sh .= " and company_esi.region = 'Aging'";
}

if( isset($parks) && $parks )
{
$sh .= " and company_esi.campusid = '3565'";
}

if( isset($companyid) && $companyid && intval( $companyid ) == $companyid )
{
$campusid = db_query_first_cell( "select campusid from company_esi where id = '$companyid'" );
//    echo( $campusid );
    $other = "";
if( $campusid )
{
        $incampus = getSchoolsInCampus( $campusid );
//        print_r( $incampus );
        if( count( $incampus ) )
        {
            foreach( $incampus as $i )
                $other .= " or company_esi.id = '$i[id]' ";
        }
    }
}

if( isset($thisusersrow["visibleregion"]) && $thisusersrow["visibleregion"] )
$regionstr = " and company_esi.region in ( " . getRegionDisp($thisusersrow["visibleregion"]) . " ) " ;


if( isset($thisusersrow["districts"]) && $thisusersrow["districts"] )
$regionstr .= getDistrictString( $thisusersrow["districts"]);

if( isset($thisusersrow["singleschoolid"]) && $thisusersrow["singleschoolid"] )
$regionstr .= " and company_esi.id = " . $thisusersrow["singleschoolid"];

//echo( $regionstr );
if( isset($session_iscorp) && $session_iscorp == TRAININGSITES )
$corpstr = "iscorp = '$session_iscorp' and ";
else if( isset($session_iscorp) && $session_iscorp == AGING )
$corpstr = "iscorp = '$session_iscorp' and ";
else
$corpstr = "iscorp <> " . TRAININGSITES . " and ";

if( !isOverallAdmin() )
$corpstr .= isset($session_iscorp) && $session_iscorp < 1?"iscorp = '$session_iscorp' and ":"";

if( isset( $_GET["iscorp"] ) )
{
$corpstr = "iscorp = '" . $_GET["iscorp"] . "' and ";
$iscstr = "&iscorp=" . $_GET["iscorp"];
}

$next = mktime( 0,0,0,$month+1, 1, $year );
$nextyear = date( "Y", $next );
$nextmonth = date( "n", $next );

$sql = "
SELECT 
code, 
companyid,
accepted,
blendedlearning,
remote,
numtrainers,
city as borough,
enddate,
class.canceldate,
class.tcfacultyid,
class.confirmationnotes,
class.isnational,
class.isconferenceroom,
class.lasttrainerreqdate,
class.iscallconfirmed,
class.training_location,
class.deleted,
class.ecardssent,
class.cardsnotneeded,
class.ebookssent,
class.id, 
startdate,
date_format(startdate, '%e') as startday,
date_format(startdate, '%I:%i %p') as starttime
FROM class, company_esi
WHERE 
$corpstr ( companyid like '$companyid' $other )
and class.companyid = company_esi.id
$visi
$sh
$regionstr
AND (  date_format( startdate, '%Y-%c' ) = '$year-$month' or  date_format( startdate, '%Y-%c' ) = '$nextyear-$nextmonth' )
order by startdate, companyname
";
$classes = db_query_rows($sql);
//echo( $sql );
// exit;
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<strong><span class="title">CALENDAR</span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if( isset($specialadmin) && $specialadmin ) { ?>
<?php if( (!isset($showcompleted) || !$showcompleted) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&showcompleted=1<?=isset($iscstr)?$iscstr:""?>'>Show Completed</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($showcards) || !$showcards) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&showcards=1<?=isset($iscstr)?$iscstr:""?>'>Show Cards/Books Sent</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($shownames) || !$shownames) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&shownames=1<?=isset($iscstr)?$iscstr:""?>'>Show Names</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($ashi) || !$ashi) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&ashi=1<?=isset($iscstr)?$iscstr:""?>'>ASHI</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($remote) || !$remote) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&remote=1<?=isset($iscstr)?$iscstr:""?>'>Remote Only</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (isset($remote) && $remote != "-1") && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&remote=-1<?=isset($iscstr)?$iscstr:""?>'>Hide Remote</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($blendedonly) || !$blendedonly) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&blendedonly=1<?=isset($iscstr)?$iscstr:""?>'>Blended Only</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($noaccepted) || !$noaccepted) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&noconfirmed=1<?=isset($iscstr)?$iscstr:""?>'>Unconfirmed Only</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($adapt) || !$adapt) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&adapt=1<?=isset($iscstr)?$iscstr:""?>'>Adapt</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($aging) || !$aging) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&aging=1<?=isset($iscstr)?$iscstr:""?>'>Aging</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( (!isset($parks) || !$parks) && isOverallAdmin() ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&parks=1<?=isset($iscstr)?$iscstr:""?>'>Parks</a> | 
<?php } else if( isOverallAdmin() ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( !isset($showdeleted) || !$showdeleted ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&showdeleted=1<?=isset($iscstr)?$iscstr:""?>'>Show Deleted</a> | 
<?php } else if( isOverallAdmin() && (!isset($showcompleted) || !$showcompleted) ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'>Show All</a> | 
<?php } ?>
<?php if( !isset($doh) || !$doh ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&doh=1<?=isset($iscstr)?$iscstr:""?>'>DOH Only </a> | 
<?php } else { ?>
<?php } ?>
<?php if( !isset($showinspections) || !$showinspections ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&showinspections=1<?=isset($iscstr)?$iscstr:""?>'>Show Service Calls</a> | 
<?php } else { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&showinspections=0<?=isset($iscstr)?$iscstr:""?>'>Hide Service Calls</a> | 
<?php } ?>
<?php if( !isset($confroom) || !$confroom ) {  ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?>&confroom=1<?=isset($iscstr)?$iscstr:""?>'><nobr>Conf Room Only</nobr></a> | 
<?php } else { ?>
<?php } ?>
<?php if( (isset($confroom) && $confroom) || (isset($doh) && $doh) || (isset($showdeleted) && $showdeleted) ) { ?>
<a href='calendar.php?month=<?=$month?>&year=<?=$year?><?=isset($iscstr)?$iscstr:""?>'><b>All</b></a> 
<?php } ?>
<?php } ?>
<?php if( isOverallAdmin() ) { ?>
&nbsp;&nbsp;<img src='images/check.png'> Trainer Requested<br>
<?php } ?>

<table width='100%'>
<tr><td class="copy" width='80%'>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<br><font color='red'><b>
***Please Note: A Code Blue Drill will be performed at your school as part
of the training program. Please accommodate the instructor appropriately.
<br><br>
</b></font>
<?php } ?>
</td><td align='right' class='copy'>
</td></tr></table>
<form method='post'> 
<p>
<table cellpadding="0" cellspacing="2" border="0" width="100%" >
<tr>
<td valign="top"><a href="calendar.php#today" class="copy"><strong>Go to Today</strong></a></td>
<td valign="top"><span class="copy">Go to: </span>
<select name="month" style="font-size: 10px;  font-family: verdana;">
<option <?=isset($selected_1)?$selected_1:""?> value="1">January</option>
<option <?=isset($selected_2)?$selected_2:""?> value="2">February</option>
<option <?=isset($selected_3)?$selected_3:""?> value="3">March</option>
<option <?=isset($selected_4)?$selected_4:""?> value="4">April</option>
<option <?=isset($selected_5)?$selected_5:""?> value="5">May</option>
<option <?=isset($selected_6)?$selected_6:""?> value="6">June</option>
<option <?=isset($selected_7)?$selected_7:""?> value="7">July</option>
<option <?=isset($selected_8)?$selected_8:""?> value="8">August</option>
<option <?=isset($selected_9)?$selected_9:""?> value="9">September</option>
<option <?=isset($selected_10)?$selected_10:""?> value="10">October</option>
<option <?=isset($selected_11)?$selected_11:""?> value="11">November</option>
<option <?=isset($selected_12)?$selected_12:""?> value="12">December</option>
</select>
<select name="year" style="font-size: 10px;  font-family: verdana;">
<option <?=isset($selected_2006)?$selected_2006:""?> value="2006">2006</option>
<option <?=isset($selected_2007)?$selected_2007:""?> value="2007">2007</option>
<option <?=isset($selected_2008)?$selected_2008:""?> value="2008">2008</option>
<option <?=isset($selected_2009)?$selected_2009:""?> value="2009">2009</option>
<option <?=isset($selected_2010)?$selected_2010:""?> value="2010">2010</option>
<option <?=isset($selected_2011)?$selected_2011:""?> value="2011">2011</option>
<option <?=isset($selected_2012)?$selected_2012:""?> value="2012">2012</option>
<option <?=isset($selected_2013)?$selected_2013:""?> value="2013">2013</option>
<option <?=isset($selected_2014)?$selected_2014:""?> value="2014">2014</option>
<option <?=isset($selected_2015)?$selected_2015:""?> value="2015">2015</option>
<option <?=isset($selected_2016)?$selected_2016:""?> value="2016">2016</option>
<option <?=isset($selected_2017)?$selected_2017:""?> value="2017">2017</option>
<option <?=isset($selected_2018)?$selected_2018:""?> value="2018">2018</option>
<option <?=isset($selected_2019)?$selected_2019:""?> value="2019">2019</option>
<option <?=isset($selected_2020)?$selected_2020:""?> value="2020">2020</option>
<option <?=isset($selected_2021)?$selected_2021:""?> value="2021">2021</option>
<option <?=isset($selected_2022)?$selected_2022:""?> value="2022">2022</option>
<option <?=isset($selected_2023)?$selected_2023:""?> value="2023">2023</option>
<option <?=isset($selected_2024)?$selected_2024:""?> value="2024">2024</option>
<option <?=isset($selected_2025)?$selected_2025:""?> value="2025">2025</option>
<option <?=isset($selected_2026)?$selected_2026:""?> value="2026">2026</option>
<option <?=isset($selected_2027)?$selected_2027:""?> value="2027">2027</option>
</select>
<input type='submit' value='Go' style='position: relative; top:2px;font-size:12px; height:24px;'></td>

<td valign="top" align="right">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top">
<?php if( isOverallAdmin() && (!isset($session_iscorp) || $session_iscorp !== TRAININGSITES) ) { ?>
<a href='/trainer_avail_calendar.php'>Trainer Avail Calendar</a> | 
<a <?=isset( $_GET["iscorp"] )  && !$_GET["iscorp"]?"class='chosen'":""?> href='calendar.php?<?=$qs?>&iscorp=0'>DOE</a> 
<a <?=isset( $_GET["iscorp"] )  && $_GET["iscorp"]?"class='chosen'":""?>href='calendar.php?<?=$qs?>&iscorp=1'>Corp</a> 
<a <?=isset( $_GET["iscorp"] )  && $_GET["iscorp"]?"class='chosen'":""?>href='calendar.php?<?=$qs?>&iscorp=4'>AGING</a> 
<a <?=!isset( $_GET["iscorp"] ) && !isset($_GET["iscorp"])?"class='chosen'":""?> href='calendar.php?<?=$qs?>'>All</a> 
<?php } ?>
</td>
</tr>
<?php if( isOverallAdmin() ) { ?>
<tr>
<td valign="top">
<font color='red'>Cancelled</font> | 
<font color='purple'>Quick Sched</font> | 
<font color='gray'>Not Accepted</font> | 
<font color='orange'>Not Enough Trainers</font> | <br>
<font color="red" style="font-size: 14px"><b>*</b></font> Non Heartsaver</font> |
<font color='brown'>National</font> | 
<font color='blue'>DOE</font> | 
<font color='green'>Corp</font> |
<font color='maroon'>Aging</font> |

</td>
</tr>
<?php } ?>
</table>

</td>
</tr>
</table>
</form>

<?php
show_big_calendar($classes, $month, $year); 
?>

<br><br><br>
<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
<?php include "popupjs.php" ;?>

</html>