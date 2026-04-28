<?
include "mysql.php";
$type = $session_schedule;
if( $year ){
$session1date = "$year-$month-$day 00:00:00";
if( $endyear )
{
	$session2date = "'$endyear-$endmonth-$endday 00:00:00'";
}
else $session2date = "NULL";

if( !$id )
{

$sql = "insert into schedule ( type, session1starttime, session1endtime, session1starttimeampm, session1endtimeampm, session1date, session2starttime, session2endtime, session2starttimeampm, session2endtimeampm, session2date, region, attendboth, mollyguard, price, recertification, classtype ) values  ( '$type', '".$session1starthour.":".$session1startminute . " " . $session1startampm. "', '".$session1endhour.":".$session1endminute . " " . $session1endampm. "', '" . $session1startampm. "', '" . $session1endampm. "', '$session1date', '".($session2starthour?($session2starthour.":".$session2startminute . " " . $session2startampm):""). "', '".($session2endhour?($session2endhour.":".$session2endminute . " " . $session2endampm):"") ."', '" . $session2startampm. "', '" . $session2endampm. "', $session2date, '$region', '$attendboth', '$mollyguard', '$price', '$recertification', '$classtype' )";
//echo( $sql );
mysql_query( $sql ) or die( mysql_error() );
}
else
{
$sql = ( "update schedule set classtype = '$classtype', session2starttimeampm = '$session2startampm', session2endtimeampm = '$session2endampm', session1starttimeampm = '$session1startampm', session1endtimeampm = '$session1endampm', session1starttime = '".$session1starthour.":".$session1startminute . " " . $session1startampm. "', session1endtime = '".$session1endhour.":".$session1endminute . " " . $session1endampm. "', session1date = '$session1date', session2starttime = '".($session2starthour?($session2starthour.":".$session2startminute . " " . $session2startampm):""). "', session2endtime = '".($session2endhour?($session2endhour.":".$session2endminute . " " . $session2endampm ):""). "', session2date = $session2date, region = '$region', attendboth = '$attendboth', mollyguard = '$mollyguard', price = '$price', recertification = '$recertification' where id = $id" ) or die( mysql_error() );
mysql_query( $sql ) or die( mysql_error() );
}
Header( "Location: addnew_done.php?type=$session_schedule" );
}

if( $id )
{
$result = mysql_query( "select * from schedule where id = $id" );
$row = mysql_fetch_array( $result );
$session1starttime = $row["session1starttime"];
$session1endtime = $row["session1endtime"];
$session1date = $row["session1date"];
$session2starttime = $row["session2starttime"];
$session2endtime = $row["session2endtime"];
$session2date = $row["session2date"];

if( $session2date )
{
$arr1 = split( " ", $session2date );
$session2arr = split( "-", $arr1[0] );
$endmonth = $session2arr[1];
$endyear = $session2arr[0];
$endday = $session2arr[2];
$session2date = $arr1[0];
}

$arr1 = split( " ", $session1date );
$sessionarr = split( "-", $arr1[0] );
$month = $sessionarr[1];
$year = $sessionarr[0];
$day = $sessionarr[2];
$session1date = $arr[0];

$region = $row["region"];
$attendboth = $row["attendboth"];
$mollyguard = $row["mollyguard"];
$price = $row["price"];
$classtype = $row["classtype"];
$recertification = $row["recertification"]; 
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Schedule Update</title>
<link rel="stylesheet" href="css/style.css">
<script language="Javascript" src="calendar.js"></script>
</head>

<body bgcolor="#F0F0FE">
<form method='post'>
<? if( $id ) { ?>
<input type='hidden' name='id' value='<?=$id?>'>
<? } ?>
<table cellpadding="6" cellspacing="4" border="0" width="700">

<? if( $session_schedule == "dd" ) {
?>
   <tr>
    <td valign="top" align="right"><span class="copy">Class Type:</span></td>
	<td valign="top"><span class="copy">
	<select name="classtype" style="font-size: 10px;  font-family: verdana;">
<? $result = mysql_query( "select name from classes where schedtype = '$session_schedule' order by priority" ); ?>
<? while( $row = mysql_fetch_array( $result ) ) { ?>
<option value="<?=$row["name"]?>" <?=$classtype==$row["name"]?"SELECTED=on":""?>><?=$row["name"]?></option>
<? } ?>
	</select></span></td>
  </tr>
<? 
}
else if( $session_schedule == "reg" )
{
?>
   <tr>
    <td valign="top" align="right"><span class="copy">Class Type:</span></td>
	<td valign="top"><span class="copy">
	<select name="classtype" style="font-size: 10px;  font-family: verdana;">
<? $result = mysql_query( "select name from classes where schedtype = '$session_schedule' order by priority" ); ?>
<? while( $row = mysql_fetch_array( $result ) ) { ?>
<option value="<?=$row["name"]?>" <?=$classtype==$row["name"]?"SELECTED=on":""?>><?=$row["name"]?></option>
<? } ?>
	</select></span></td>
  </tr>
<?
} else {
?>
   <tr>
    <td valign="top" align="right"><span class="copy">City:</span></td>
	<td valign="top"><span class="copy">
	<select name="schedule" style="font-size: 10px;  font-family: verdana;" onChange="document.location.href='addnew.php?schedule=' + this.options[this.selectedIndex].value;">
<? $result = mysql_query( "select name, shortcut from cities order by name" );
while( $row = mysql_fetch_array( $result ) ) { ?>
<option <?=$session_schedule==$row["shortcut"]?"SELECTED":""?> value="<?=$row["shortcut"]?>">Emergency Skill/TSI <?=$row["name"]?></option>
<? } ?>
	</select></span></td>
  </tr>
  
   <tr>
    <td valign="top" align="right"><span class="copy">Region:</span></td>
	<td valign="top"><span class="copy">
	
	<? $result = mysql_query( "select name from regions where city = '$session_schedule' order by name " );?>
	<select name="region" style="font-size: 10px;  font-family: verdana;">
<? while( $row = mysql_fetch_array( $result ) ) { ?>
		<option value="<?=$row["name"]?>" <?=$region==$row["name"]?"SELECTED":""?>><?=$row["name"]?></option>
<? } ?>
	</select>
	</span></td>
  </tr>
  
   <tr>
    <td valign="top" align="right"><span class="copy">Class Type:</span></td>
	<td valign="top"><span class="copy">
	<select name="classtype" style="font-size: 10px;  font-family: verdana;">
<? $result = mysql_query( "select name from classes where schedtype = '$session_schedule' order by priority" ); ?>
<? while( $row = mysql_fetch_array( $result ) ) { ?>
<option value="<?=$row["name"]?>" <?=$classtype==$row["name"]?"SELECTED=on":""?>><?=$row["name"]?></option>
<? } ?>
	</select></span></td>
  </tr>
  

<?
}
?>
</td>
  </tr>
  
  
  <tr>
    <td valign="top" align="right"><span class="copy">Session 1:</span></td>
	<td valign="bottom">
		<table cellpadding="0" cellspacing="0" border="0">
            <tr>
               <td valign="middle"><span class="copy">Date:</span></td>
			   <td valign="middle">
                <?
if( !$month )
{
$month = date( "m" );
$day = date( "d" );
$year = date( "Y" );
}
?>
                <nobr> 
                <select class=drop2 size=1 name=month>
                  <option value="01" <?=$month=="01"?"SELECTED":""?> >January</option>
                  <option value="02" <?=$month=="02"?"SELECTED":""?> >February</option>
                  <option value="03" <?=$month=="03"?"SELECTED":""?> >March</option>
                  <option value="04" <?=$month=="04"?"SELECTED":""?> >April</option>
                  <option value="05" <?=$month=="05"?"SELECTED":""?> >May</option>
                  <option value="06" <?=$month=="06"?"SELECTED":""?> >June</option>
                  <option value="07" <?=$month=="07"?"SELECTED":""?> >July</option>
                  <option value="08" <?=$month=="08"?"SELECTED":""?> >August</option>
                  <option value="09" <?=$month=="09"?"SELECTED":""?> >September</option>
                  <option value="10" <?=$month=="10"?"SELECTED":""?> >October</option>
                  <option value="11" <?=$month=="11"?"SELECTED":""?> >November</option>
                  <option value="12" <?=$month=="12"?"SELECTED":""?> >December</option>
                </select>
                <select class=drop2 size=1 name=day>
                  <option <?=$day=="1"?"SELECTED":""?> value=1>1</option>
                  <option <?=$day=="2"?"SELECTED":""?> value=2>2</option>
                  <option <?=$day=="3"?"SELECTED":""?> value=3>3</option>
                  <option <?=$day=="4"?"SELECTED":""?> value=4>4</option>
                  <option <?=$day=="5"?"SELECTED":""?> value=5>5</option>
                  <option <?=$day=="6"?"SELECTED":""?> value=6>6</option>
                  <option <?=$day=="7"?"SELECTED":""?> value=7>7</option>
                  <option <?=$day=="8"?"SELECTED":""?> value=8>8</option>
                  <option <?=$day=="9"?"SELECTED":""?> value=9>9</option>
                  <option <?=$day=="10"?"SELECTED":""?> value=10>10</option>
                  <option <?=$day=="11"?"SELECTED":""?> value=11>11</option>
                  <option <?=$day=="12"?"SELECTED":""?> value=12>12</option>
                  <option <?=$day=="13"?"SELECTED":""?> value=13>13</option>
                  <option <?=$day=="14"?"SELECTED":""?> value=14>14</option>
                  <option <?=$day=="15"?"SELECTED":""?> value=15>15</option>
                  <option <?=$day=="16"?"SELECTED":""?> value=16>16</option>
                  <option <?=$day=="17"?"SELECTED":""?> value=17>17</option>
                  <option <?=$day=="18"?"SELECTED":""?> value=18>18</option>
                  <option <?=$day=="19"?"SELECTED":""?> value=19>19</option>
                  <option <?=$day=="20"?"SELECTED":""?> value=20>20</option>
                  <option <?=$day=="21"?"SELECTED":""?> value=21>21</option>
                  <option <?=$day=="22"?"SELECTED":""?> value=22>22</option>
                  <option <?=$day=="23"?"SELECTED":""?> value=23>23</option>
                  <option <?=$day=="24"?"SELECTED":""?> value=24>24</option>
                  <option <?=$day=="25"?"SELECTED":""?> value=25>25</option>
                  <option <?=$day=="26"?"SELECTED":""?> value=26>26</option>
                  <option <?=$day=="27"?"SELECTED":""?> value=27>27</option>
                  <option <?=$day=="28"?"SELECTED":""?> value=28>28</option>
                  <option <?=$day=="29"?"SELECTED":""?> value=29>29</option>
                  <option <?=$day=="30"?"SELECTED":""?> value=30>30</option>
                  <option <?=$day=="31"?"SELECTED":""?> value=31>31</option>
                </select>
                <select 
            class=drop2 size=1 name=year>
                  <option <?=$year=="2003"?"SELECTED":""?> value=2003>2003</option>
                  <option <?=$year=="2004"?"SELECTED":""?>  value=2004>2004</option>
                  <option <?=$year=="2005"?"SELECTED":""?>  value=2005>2005</option>
                  <option <?=$year=="2006"?"SELECTED":""?>  value=2006>2006</option>
                  <option <?=$year=="2007"?"SELECTED":""?>  value=2007>2007</option>
                </select>&nbsp;<a href="javascript: void(0);" onClick="return getCalendar(document.forms[0].month, document.forms[0].day, document.forms[0].year );"><img src="images/cal.gif" border="0"></a></td>
			   <td valign="middle" colspan="4" align="left">    &nbsp;            </td>
			</tr>
			<tr><td><img src="images/dotclear.gif" height="5"></td></tr>
			<tr>
			   <td valign="middle"><span class="copy">Start Time:&nbsp;</span></td>	   
			   <td valign="middle" colspan="4">
<? $ftime = strtotime( $session1date . " " . $session1starttime ); ?>
<select name="session1starthour">
<? for( $i = 1; $i <= 12; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=date( "h", $ftime)==$i?"SELECTED=on":""?>><?=$val?></option>
<? } ?>
</select> :
<select name="session1startminute">
<? for( $i = 0; $i < 60; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=date( "i", $ftime)==$i?"SELECTED=on":""?> ><?=$val?></option>
<? } ?>
</select> 
<select name="session1startampm">
<option <?=date( "A", $ftime)=="AM"?"SELECTED=on":""?> value="AM">AM</option>
<option <?=date( "A", $ftime)=="PM"?"SELECTED=on":""?> value="PM">PM</option>
</select> 
 <span class="copy">End Time:&nbsp;</span>
<? $ftime = strtotime( $session1date . " " . $session1endtime ); ?>
<select name="session1endhour">
<? for( $i = 1; $i <= 12; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=date( "h", $ftime)==$i?"SELECTED=on":""?>><?=$val?></option>
<? } ?>
</select> :
<select name="session1endminute">
<? for( $i = 0; $i < 60; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=date( "i", $ftime)==$i?"SELECTED=on":""?> ><?=$val?></option>
<? } ?>
</select> 
<select name="session1endampm">
<option <?=date( "A", $ftime)=="AM"?"SELECTED=on":""?> value="AM">AM</option>
<option <?=date( "A", $ftime)=="PM"?"SELECTED=on":""?> value="PM">PM</option>
</select> 
</td>
            </tr>
		</table>
	</td>	
  </tr>
  
  <!--Note: It's possible that ESI may input a second session DATE, but may not input TIME.  In this case, the second date will show, but not the time.  See this page for example: http://www.emergencyskills.com/defensive_driving.shtml -->

  <tr>
    <td valign="top" align="right"><span class="copy">Session 2 (if any):</span></td>
	<td valign="bottom">
		<table cellpadding="0" cellspacing="0" border="0">
            <tr>
               <td valign="middle"><span class="copy">Date:</span></td>
			   <td valign="middle">

<nobr>              <SELECT class=drop2 size=1 name=endmonth>
<option></option>
                <OPTION value=01 <?=$endmonth=="01"?"SELECTED":""?> >January</OPTION>
                <OPTION value=02 <?=$endmonth=="02"?"SELECTED":""?> >February</OPTION>
                <OPTION value=03 <?=$endmonth=="03"?"SELECTED":""?> >March</OPTION>
                <OPTION value=04 <?=$endmonth=="04"?"SELECTED":""?> >April</OPTION>
                <OPTION value=05 <?=$endmonth=="05"?"SELECTED":""?> >May</OPTION>
                <OPTION value=06 <?=$endmonth=="06"?"SELECTED":""?> >June</OPTION>
                <OPTION value=07 <?=$endmonth=="07"?"SELECTED":""?> >July</OPTION>
                <OPTION value=08 <?=$endmonth=="08"?"SELECTED":""?> >August</OPTION>
                <OPTION value=09 <?=$endmonth=="09"?"SELECTED":""?> >September</OPTION>
                <OPTION value=10 <?=$endmonth=="10"?"SELECTED":""?> >October</OPTION>
                <OPTION value=11 <?=$endmonth=="11"?"SELECTED":""?> >November</OPTION>
                <OPTION value=12 <?=$endmonth=="12"?"SELECTED":""?> >December</OPTION>
              </SELECT>
              <SELECT class=drop2 size=1 name=endday>
<option></option>
                <OPTION <?=$endday=="01"?"SELECTED":""?> value=01>1</OPTION>
                <OPTION <?=$endday=="02"?"SELECTED":""?> value=02>2</OPTION>
                <OPTION <?=$endday=="03"?"SELECTED":""?> value=03>3</OPTION>
                <OPTION <?=$endday=="04"?"SELECTED":""?> value=04>4</OPTION>
                <OPTION <?=$endday=="05"?"SELECTED":""?> value=05>5</OPTION>
                <OPTION <?=$endday=="06"?"SELECTED":""?> value=06>6</OPTION>
	        <OPTION <?=$endday=="07"?"SELECTED":""?> value=07>7</OPTION>
	        <OPTION <?=$endday=="08"?"SELECTED":""?> value=08>8</OPTION>
	        <OPTION <?=$endday=="09"?"SELECTED":""?> value=09>9</OPTION>
	        <OPTION <?=$endday=="10"?"SELECTED":""?> value=10>10</OPTION>
	        <OPTION <?=$endday=="11"?"SELECTED":""?> value=11>11</OPTION>
	        <OPTION <?=$endday=="12"?"SELECTED":""?> value=12>12</OPTION>
	        <OPTION <?=$endday=="13"?"SELECTED":""?> value=13>13</OPTION> 
	        <OPTION <?=$endday=="14"?"SELECTED":""?> value=14>14</OPTION>  
	        <OPTION <?=$endday=="15"?"SELECTED":""?> value=15>15</OPTION>  
	        <OPTION <?=$endday=="16"?"SELECTED":""?> value=16>16</OPTION>  
	        <OPTION <?=$endday=="17"?"SELECTED":""?> value=17>17</OPTION>  
	        <OPTION <?=$endday=="18"?"SELECTED":""?> value=18>18</OPTION>  
	        <OPTION <?=$endday=="19"?"SELECTED":""?> value=19>19</OPTION>  
	        <OPTION <?=$endday=="20"?"SELECTED":""?> value=20>20</OPTION>  
	        <OPTION <?=$endday=="21"?"SELECTED":""?> value=21>21</OPTION>  
	        <OPTION <?=$endday=="22"?"SELECTED":""?> value=22>22</OPTION>  
	        <OPTION <?=$endday=="23"?"SELECTED":""?> value=23>23</OPTION>  
	        <OPTION <?=$endday=="24"?"SELECTED":""?> value=24>24</OPTION>  
	        <OPTION <?=$endday=="25"?"SELECTED":""?> value=25>25</OPTION>  
	        <OPTION <?=$endday=="26"?"SELECTED":""?> value=26>26</OPTION>  
	        <OPTION <?=$endday=="27"?"SELECTED":""?> value=27>27</OPTION>  
	        <OPTION <?=$endday=="28"?"SELECTED":""?> value=28>28</OPTION>  
	        <OPTION <?=$endday=="29"?"SELECTED":""?> value=29>29</OPTION>  
	        <OPTION <?=$endday=="30"?"SELECTED":""?> value=30>30</OPTION>  
	        <OPTION <?=$endday=="31"?"SELECTED":""?> value=31>31</OPTION>
	    </SELECT>
              <SELECT 
            class=drop2 size=1 name=endyear>
<option></option>
                <OPTION <?=$endyear=="2003"?"SELECTED":""?> value=2003>2003</OPTION>
                <OPTION <?=$endyear=="2004"?"SELECTED":""?>  value=2004>2004</OPTION>
                <OPTION <?=$endyear=="2005"?"SELECTED":""?>  value=2005>2005</OPTION>
                <OPTION <?=$endyear=="2006"?"SELECTED":""?>  value=2006>2006</OPTION>
                <OPTION <?=$endyear=="2007"?"SELECTED":""?>  value=2007>2007</OPTION>
              </SELECT>
</nobr>&nbsp;<a href="javascript: void(0);" onclick="return getCalendar(document.forms[0].endmonth, document.forms[0].endday, document.forms[0].endyear );"><img src="images/cal.gif" border="0" /></a>
</td>
			   <td valign="middle" colspan="4" align="left">    &nbsp;       </td>
			</tr>
			<tr><td><img src="images/dotclear.gif" height="5"></td></tr>
			<tr>
			   <td valign="middle"><span class="copy">Start Time:&nbsp;</span></td>	   
			   <td valign="middle" colspan="4">
<? $ftime = $session2starttime?strtotime( $session2date . " " . $session2starttime ):"";
?>
<select name="session2starthour">
<option value=''></option>
<? for( $i = 1; $i <= 12; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=$ftime&& date( "h", $ftime)==$i?"SELECTED=on":""?>><?=$val?></option>
<? } ?>
</select> :
<select name="session2startminute">
<option value=''></option>
<? for( $i = 0; $i < 60; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=$ftime&& date( "i", $ftime)==$i?"SELECTED=on":""?> ><?=$val?></option>
<? } ?>
</select> 
<select name="session2startampm">
<option value=''></option>
<option <?=$ftime&& date( "A", $ftime)=="AM"?"SELECTED=on":""?> value="AM">AM</option>
<option <?=$ftime&& date( "A", $ftime)=="PM"?"SELECTED=on":""?> value="PM">PM</option>
</select> 
 <span class="copy">End Time:&nbsp;</span>
<? $ftime = $session2endtime?strtotime( $session2date . " " . $session2endtime ):""; ?>
<select name="session2endhour">
<option value=''></option>
<? for( $i = 1; $i <= 12; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=$ftime&& date( "h", $ftime)==$i?"SELECTED=on":""?>><?=$val?></option>
<? } ?>
</select> :
<select name="session2endminute">
<option value=''></option>
<? for( $i = 0; $i < 60; $i++ ) {
$val = ($i>=10?$i:"0".$i);
?>
<option value=<?=$val?> <?=$ftime&& date( "i", $ftime)==$i?"SELECTED=on":""?> ><?=$val?></option>
<? } ?>
</select> 
<select name="session2endampm">
<option value=''></option>
<option <?=$ftime&& date( "A", $ftime)=="AM"?"SELECTED=on":""?> value="AM">AM</option>
<option <?=$ftime&& date( "A", $ftime)=="PM"?"SELECTED=on":""?> value="PM">PM</option>
</select> 
</td>
            </tr>
		</table>
	</td>	
  </tr>
<? include "recert.php" ; ?>
</table>

</body>
</html>
