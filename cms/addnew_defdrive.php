<?
include "mysql.php";
$type = "dd";
if( $state ){
mysql_query( "insert into schedule ( type, session1starttime, session1endtime, session1date, session2starttime, session2endtime, session2date, region, attendboth, mollyguard, price, recertification ) values  ( '$type', '$session1starttime', '$session1endtime', '$session1date', '$session2starttime', '$session2endtime', '$session2date', '$region', '$attendboth', '$mollyguard', '$price', '$recertification' )" ) or die( mysql_error() );

Header( "Location: addnew_defdrive_done.php" );
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Schedule Update</title>
<link rel="stylesheet" href="css/style.css">

</head>

<body bgcolor="#F0F0FE">
<form method='post'>
<table cellpadding="6" cellspacing="4" border="0">
   <tr>
    <td valign="top" align="right"><span class="copy">Class Type:</span></td>
	<td valign="top"><span class="copy">
	<select name="state" style="font-size: 10px;  font-family: verdana;">
		<option value="Defensive Driving">Defensive Driving</option>
	</select></span></td>
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
                </select>&nbsp;</td>
			   <td valign="middle" colspan="4" align="left">                <a href="javascript: void(0);" onClick="return getCalendar(document.forms[0].month, document.forms[0].day, document.forms[0].year );"><img src="images/cal.gif" border="0"></a></td>
			</tr>
			<tr><td><img src="images/dotclear.gif" height="5"></td></tr>
			<tr>
			   <td valign="middle"><span class="copy">Start Time:&nbsp;</span></td>	   
			   <td valign="middle"><input name="session1starttime" type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0">&nbsp;&nbsp;</td>
			   <td valign="middle"><span class="copy">End Time:&nbsp;</span></td>	   
			   <td valign="middle"><input name="session1endtime" type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0"></td>
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
			   <td valign="middle"><input name="session2date" type="text" size="10" style="font-size: 10px;  font-family: verdana;">&nbsp;</td>
			   <td valign="middle" colspan="4" align="left"><img src="images/cal.gif" border="0"></td>
			</tr>
			<tr><td><img src="images/dotclear.gif" height="5"></td></tr>
			<tr>
			   <td valign="middle"><span class="copy">Start Time:&nbsp;</span></td>	   
			   <td valign="middle"><input name="session2starttime" type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0">&nbsp;&nbsp;</td>
			   <td valign="middle"><span class="copy">End Time:&nbsp;</span></td>	   
			   <td valign="middle"><input name="session2endtime" type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0"></td>
            </tr>
		</table>
	</td>	
  </tr>
<? include "recert.php" ; ?>
</table>

</body>
</html>
