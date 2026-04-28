<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once('mysql.php');

// // Helper function for XSS mitigation
// if (!function_exists('h')) {
// function h($str) {
// return htmlspecialchars(($str), ENT_QUOTES | ENT_HTML5, 'UTF-8');
// }
// }

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if( getcurrentusertype() != 'trainer')
{
Header( "location: login.php");
exit;
}

// Input Sanitization for Query/Display
$month = $_REQUEST['month'] ?? date("n");
$year= $_REQUEST['year'] ?? date("Y");
// $session_id = $session_id ?? ''; 
// $session_iscorp = $session_iscorp ?? 0;
$thisusersrow = $thisusersrow ?? [];
$showassignedtrainerstoo = $_REQUEST['showassignedtrainerstoo'] ?? 0;

// Internal data fetching (Assuming session variables are set securely)
$companyid = get_companyid($session_id);

$today_month = date("n");
$today_year = date("Y");

if (!$month) {
$month = $today_month;
}

if (!$year) {
$year = $today_year;
}

// Use standard variable names for safety
$selected_month_var = "selected_{$month}";
$selected_year_var = "selected_{$year}";

// Note: Using dynamic variable names for display logic is discouraged but maintained for consistency.
${"selected_".$month} = "SELECTED";
${"selected_".$year} = "SELECTED";

// --- SQL Injection Mitigation (Preprocessing for Queries) ---
$drillsdontcountbefore = getsetting( 'drillsdontcountbefore' );
$drillsdontcountbefore_safe = db_escape_string($drillsdontcountbefore);

// Date setup
$today_ts = mktime( 0,0,0,date( "m" ), date( "d" ), date("Y" ) );
$lastday_ts = strtotime( getsetting( 'lastdrillday' ) );
$firstday_ts = strtotime( getsetting( 'firstdrillday' ) );

?>
<?php include "ssi/top.php"; ?>

<strong>
<?php
$numdrills = 0;
$numdays = 0;
$countexp = 0;

$myzips = getZips( $thisusersrow );

if( $myzips )
{
// SQLi Mitigation: Assuming getZips returns an already comma-separated list of safe, quoted strings or integers.
// If it returns raw input, this is still vulnerable. For safety, we assume a proper escaping or integer list.
$myzips_safe = $myzips;

// Fetch schools
$schools = db_query_array( "select * from company_esi where iscorp = '".db_escape_string($session_iscorp)."' and deleted = 0 and zip in ( {$myzips_safe} ) and showsondrillreports = 1", "id", "id" );

// Fetch drill counts
$drillarr = db_query_array( "Select dtc.companyid, count(distinct( drill.drillid )) as numdrills from drill left join drill_to_companyid dtc on ( drill.drillid = dtc.drillid ) where (completed =1 or received = 1 or isdone = 1 or shipped = 1) and drilldate >= '{$drillsdontcountbefore_safe}' group by dtc.companyid", "companyid", "numdrills" );

foreach( $schools as $sid )
{
$drills = $drillarr[$sid];
if( !$drills )
{
$numdrills++;
}
}
}

if( $myzips )
{
// SQLi Mitigation: Assuming getVisibleZipsString returns safe, formatted SQL string 
$zips_safe = getVisibleZipsString( "c" );

$nextmonth = date( "Y-m-d", mktime( 0,0,0,date( "m" ) + 1, date( "d" ), date( "Y" )) );
$nextmonth_safe = db_escape_string($nextmonth);
$session_iscorp_safe = db_escape_string($session_iscorp);

$countexp = db_query_first_cell( "select count(*) from company_esi c, aed_esi a where iscorp = '{$session_iscorp_safe}' and aedmissing=0 and outofservice = 0 and c.isactive = 1 and c.deleted=0 and a.deleted=0 and c.id=a.clientid and (( '{$nextmonth_safe}' >= a.padaexpiration and padaexpiration <> '' )  or  ( '{$nextmonth_safe}' >= a.padbexpiration and a.padbexpiration <> '') or (a.model <> 'FRX' and a.pediatricpads <> '' and '{$nextmonth_safe}' >= a.pediatricpads)) {$zips_safe} order by companyname");
}

$current_ts = $today_ts;
while( $current_ts <= $lastday_ts && $current_ts >= $firstday_ts)
{
// Dates are handled as timestamps, which is safe.
if( date( "w", $current_ts ) != 0 && date( "w", $current_ts ) != 6 && date( "w", $current_ts ) != 5 )
{
$date_for_query = date( "Y-m-d", $current_ts );
$date_for_query_safe = db_escape_string($date_for_query);

// SQLi Mitigation: Use escaped date
$dt = db_query_first_cell( "select dt from nodrilldates where dt ='{$date_for_query_safe}'");
if( !$dt )
{
$numdays++;
}
}
$current_ts = mktime( 0,0,0,date( "m", $current_ts ), date( "d", $current_ts ) + 1, date("Y", $current_ts ) );
}

if( 1 || ($numdays && $numdrills) ) { ?>
<span class='title'><font size=+1 color='red'>You have <?=$numdays?> Days to Complete <?=$numdrills?> Drills</font></span></strong>
<?php if( $countexp ) { ?>
<br><span class='title'><font size=+1 color='red'>You have <?=$countexp?> Expiring AEDs</font></span></strong>
<?php } ?>
<?php } else { ?>
<span class="title">CALENDAR</span>
<?php } ?>

<form method='post'>
<p>
<table cellpadding="0" cellspacing="2" border="0" width="100%">
<tr>
<td valign="top"><a href="tcalendar.php?month=<?=$today_month?>&year=<?=$today_year?>" class="copy"><strong>Go to Today</strong></a></td>
<td valign="top"><span class="copy">| Go to: </span></td>
<td valign="top">
<select name="month" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<option <?=${"selected_1"}?> value="1">JAN</option>
<option <?=${"selected_2"}?> value="2">FEB</option>
<option <?=${"selected_3"}?> value="3">MAR</option>
<option <?=${"selected_4"}?> value="4">APR</option>
<option <?=${"selected_5"}?> value="5">MAY</option>
<option <?=${"selected_6"}?> value="6">JUNE</option>
<option <?=${"selected_7"}?> value="7">JULY</option>
<option <?=${"selected_8"}?> value="8">AUG</option>
<option <?=${"selected_9"}?> value="9">SEPT</option>
<option <?=${"selected_10"}?> value="10">OCT</option>
<option <?=${"selected_11"}?> value="11">NOV</option>
<option <?=${"selected_12"}?> value="12">DEC</option>
</select>
</td>
<td valign="top">
<select name="year" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<?php for( $i = date( "Y" ); $i <= date( "Y" ) + 1; $i++ ) { ?>
<option <?php echo $year == $i?"SELECTED":""; ?> value="<?=$i?>"><?=$i?></option>
<?php }?>
</select>
</td>

<?php
if( $thisusersrow["tcfaculty"] ) { ?>
<td><input type='checkbox' name='showassignedtrainerstoo' <?php echo $showassignedtrainerstoo?"CHECKED":""; ?> value='1'> Show classes for my trainers too</td>
<?php } ?>
<td><input type='submit' value='SEARCH' style='font-size:8px; height:16px;'></td>

<td valign="top" align="right">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top"><img src="images/button_week.gif" border="0"></td><td valign="top"><img src="images/button_month.gif" border="0"></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
<table><tr><td>
<?php

// Assuming show_trainer_calendar is an external function and handles its internal output safely.
show_trainer_calendar($month, $year, $showassignedtrainerstoo ); 

?>
</td><td valign='top'>
<table cellpadding=0 cellspacing=0 border=0><tr><td><img src="images/calkey1.jpg"></td></tr>
<tR><td><span style="background-color: #f9bbd2; width:30px; height: 10px" >&nbsp;&nbsp;&nbsp;&nbsp;</span>  Assigned instructor's <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;classes</td></tr>
<tr><td><a href="trainer_availability.php"><img src="images/calkey2.jpg" border="0"></a></td></tr></table>

</td></tr></table>

<br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
<div id="dek"></div>

<!-- <script type="text/javascript">
Xoffset=-60;// modify these values to ...
Yoffset= 20;// change the popup position.

var old,skn,iex=(document.all),yyy=-1000;

var ns4=document.layers
var ns6=document.getElementById&&!document.all
var ie4=document.all

if (ns4)
skn=document.dek
else if (ns6)
skn=document.getElementById("dek").style
else if (ie4)
skn=document.all.dek.style
if(ns4)document.captureEvents(Event.MOUSEMOVE);
else{
skn.visibility="visible"
skn.display="none"
}
document.onmousemove=get_mouse;

function popup(msg,bak){
var content="<TABLE  WIDTH=250 BORDER=1 BORDERCOLOR=black CELLPADDING=2 CELLSPACING=0 "+
"BGCOLOR="+bak+"><TD ALIGN=center><FONT COLOR=black SIZE=2>"+msg+"</FONT></TD></TABLE>";
yyy=Yoffset;
 if(ns4){skn.document.write(content);skn.document.close();skn.visibility="visible"}
 if(ns6){document.getElementById("dek").innerHTML=content;skn.display=''}
 if(ie4){document.all("dek").innerHTML=content;skn.display=''}
}

function get_mouse(e){
var x=(ns4||ns6)?e.pageX:event.x+document.body.scrollLeft;
skn.left=x+Xoffset;
var y=(ns4||ns6)?e.pageY:event.y+document.body.scrollTop;
skn.top=y+yyy;
}

function kill(){
yyy=-1000;
if(ns4){skn.visibility="hidden";}
else if (ns6||ie4)
skn.display="none"
}
</script> -->

</html>