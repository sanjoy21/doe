<?php
require_once('mysql.php');

if( getcurrentusertype() != 'trainer' )
{
Header( "location: login.php" );
        exit;
}

$companyid = get_companyid($session_id ?? '');

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

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->



<strong>
<?php 
$numdrills = 0;
$today = mktime( 0,0,0,date( "m" ), date( "d" ), date("Y" ) );
$lastday = strtotime( getsetting( 'lastdrillday' ) );// mktime(0,0,0,1,30,2009);
$firstday = strtotime( getsetting( 'firstdrillday' ) ); //mktime(0,0,0,9,2,2008);
$numdays = 0;

$drillsdontcountbefore = getsetting( 'drillsdontcountbefore' ); 
$myzips = getZips( $thisusersrow ?? array() );
if( $myzips )
{
    $schools = db_query_array( "select * from company_esi where iscorp = '$session_iscorp' and deleted = 0 and zip in ( ".$myzips." ) and showsondrillreports = 1", "id", "id" );
    $drillarr = db_query_array( "Select dtc.companyid, count(distinct( drill.drillid )) as numdrills from drill left join drill_to_companyid dtc on ( drill.drillid = dtc.drillid ) where (completed =1 or received = 1 or isdone = 1 or shipped = 1) and drilldate >= '$drillsdontcountbefore' group by dtc.companyid", "companyid", "numdrills" );
    if(isset($schools) && is_array($schools)) {
        foreach( $schools as $sid )
        {
            $drills = $drillarr[$sid] ?? 0;
            if( !$drills )
            {
                $numdrills++;
            }
        }
    }
}


if( $myzips )
{
    $zips = getVisibleZipsString( "c" );

    $nextmonth = date( "Y-m-d", mktime( 0,0,0,date( "m" ) + 1, date( "d" ), date( "Y" )) );
    $countexp = db_query_first_cell( "select count(*) from company_esi c, aed_esi a where iscorp = '$session_iscorp' and aedmissing=0 and outofservice = 0 and c.isactive = 1 and c.deleted=0 and a.deleted=0 and c.id=a.clientid and (( '$nextmonth' >= a.padaexpiration and padaexpiration <> '' )  or  ( '$nextmonth' >= a.padbexpiration and a.padbexpiration <> '') or (a.model <> 'FRX' and a.pediatricpads <> '' and '$nextmonth' >= a.pediatricpads)) $zips order by companyname");
}

while( $today <= $lastday && $today >= $firstday)
{
    if( date( "w", $today ) != 0 && date( "w", $today ) != 6 && date( "w", $today ) != 5 )
    {
        $dt = db_query_first_cell( "select dt from nodrilldates where dt ='".date( "Y-m-d", $today ) ."'" );
        if( !$dt )
        {
            $numdays++;
        }
    }
    $today = mktime( 0,0,0,date( "m", $today ), date( "d", $today ) + 1, date("Y", $today ) );
}
if( 1 || ($numdays && $numdrills) ) { ?>
<span class='title'><font size=+1 color='red'>You have <?php echo $numdays; ?> Days to Complete <?php echo $numdrills; ?> Drills</font></span></strong>
<?php if( isset($countexp) && $countexp ) { ?>
<br><span class='title'><font size=+1 color='red'>You have <?php echo $countexp; ?> Expiring AEDs</font></span></strong>
<?php } ?>
<?php } else { ?>
<span class="title">CALENDAR</span> 
<?php } ?>

<form method='post'>
<p>
<table cellpadding="0" cellspacing="2" border="0" width="100%">
        <tr>
        <td valign="top"><a href="tcalendar.php?month=<?php echo $today_month; ?>&year=<?php echo $today_year; ?>" class="copy"><strong>Go to Today</strong></a></td>
<td valign="top"><span class="copy">| Go to: </span></td>
<td valign="top">
               <select name="month" style="font-size: 10px;  font-family: verdana;">
    <option value=""></option>
                                        <option <?php echo ${"selected_1"} ?? ''; ?> value="1">JAN</option>
                                        <option <?php echo ${"selected_2"} ?? ''; ?> value="2">FEB</option>
                                        <option <?php echo ${"selected_3"} ?? ''; ?> value="3">MAR</option>
                                        <option <?php echo ${"selected_4"} ?? ''; ?> value="4">APR</option>
                                        <option <?php echo ${"selected_5"} ?? ''; ?> value="5">MAY</option>
                                        <option <?php echo ${"selected_6"} ?? ''; ?> value="6">JUNE</option>
                                        <option <?php echo ${"selected_7"} ?? ''; ?> value="7">JULY</option>
                                        <option <?php echo ${"selected_8"} ?? ''; ?> value="8">AUG</option>
                                        <option <?php echo ${"selected_9"} ?? ''; ?> value="9">SEPT</option>
                                        <option <?php echo ${"selected_10"} ?? ''; ?> value="10">OCT</option>
                                        <option <?php echo ${"selected_11"} ?? ''; ?> value="11">NOV</option>
                                        <option <?php echo ${"selected_12"} ?? ''; ?> value="12">DEC</option>                                                                          
                    </select>
</td>
<td valign="top">
               <select name="year" style="font-size: 10px;  font-family: verdana;">
    <option value=""></option>
<?php for( $i = date( "Y" ); $i <= date( "Y" ) + 1; $i++ ) { ?>
                                        <option <?php echo isset($year) && $year == $i ? "SELECTED" : ""; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option> 
<?php }?>
                    </select>
</td>

<?php if( isset($thisusersrow["tcfaculty"]) && $thisusersrow["tcfaculty"] ) {  ?>
               <td><input type='checkbox' name='showassignedtrainerstoo' <?php echo isset($showassignedtrainerstoo) && $showassignedtrainerstoo ? "CHECKED" : ""; ?> value='1'> Show classes for my trainers too</td>
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
<table> <tr><td>
<?php 

show_trainer_calendar($month, $year, $showassignedtrainerstoo ?? false ); 

?>
</td><td valign='top'>
<table cellpadding=0 cellspacing=0 border=0><tr><td><img src="images/calkey1.jpg"></td></tr>
<tR><td><span style="background-color: #f9bbd2; width:30px; height: 10px" >&nbsp;&nbsp;&nbsp;&nbsp;</span>  Assigned instructor's <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;classes</td></tr>
<tr><td><a href="trainer_availability.php"><img src="images/calkey2.jpg" border="0"></a></td></tr></table>

</td></tr></table>

<br><br><br>
<!--end center content-->
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
<DIV ID="dek"></DIV>

<SCRIPT TYPE="text/javascript">
<!--
Xoffset=-60;    // modify these values to ...
Yoffset= 20;    // change the popup position.

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

//-->
</SCRIPT>
</html>