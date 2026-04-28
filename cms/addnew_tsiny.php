<?
include "mysql.php";
$type = $session_schedule;

if( $schedule )
{
session_unregister( 'session_schedule' );
$session_schedule = $schedule;
session_register( 'session_schedule' );
}

if( $state ){
mysql_query( "insert into schedule ( type, session1starttime, session1endtime, session1date, session2starttime, session2endtime, session2date, region, attendboth, mollyguard, price, recertification ) values  ( '$type', '$session1starttime', '$session1endtime', '$session1date', '$session2starttime', '$session2endtime', '$session2date', '$region', '$attendboth', '$mollyguard', '$price', '$recertification' )" );

Header( "Location: addnew_tsiny_done.php" );
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Schedule Update</title>
<link rel="stylesheet" href="css/style.css">

</head>

<body bgcolor="#F0F0FE">

<table cellpadding="6" cellspacing="4" border="0">
   <tr>
    <td valign="top" align="right"><span class="copy">City:</span></td>
	<td valign="top"><span class="copy">
	<select name="state" style="font-size: 10px;  font-family: verdana;" onChange="document.location.href='addnew_tsiny.php?schedule=' + this.elements[this.selectedIndex].value;">
		<option <?=$session_schedule == "ny"?"SELECTED=true":""?> value="New York">New York</option>
		<option <?=$session_schedule == "bo"?"SELECTED=true":""?> value="Boston">Boston</option>
		<option <?=$session_schedule == "ph"?"SELECTED=true":""?> value="Philadelphia">Philadelphia</option>
		<option <?=$session_schedule == "dc"?"SELECTED=true":""?> value="Washington">Washington</option>
	</select></span></td>
  </tr>
  
   <tr>
    <td valign="top" align="right"><span class="copy">Region:</span></td>
	<td valign="top"><span class="copy">
	
<!--Note: When the user picks a city, it would be good if the REGION menu that matches that city appears.-->
	
	<? if( $session_schedule == "ny" ) { ?>
	<select name="region" style="font-size: 10px;  font-family: verdana;">
		<option value="Wall Street">Wall Street</option>
		<option value="Syosset">Syosset</option>
		<option value="1st Ave. Studio">1st Ave. Studio</option>
		<option value="Springfield">Springfield</option>
		<option value="Ramsey">Ramsey</option>
		<option value="Stamford-Commerce">Stamford-Commerce</option>
	</select>
	<? } else if( $session_schedule == "bo" ) { ?>
	
	<select name="region" style="font-size: 10px;  font-family: verdana;">
		<option value="Allston">Allston</option>
		<option value="Waltham">Waltham</option>
	</select>

	<? } else if( $session_schedule == "ph" ) { ?>
	
	<select name="region" style="font-size: 10px;  font-family: verdana;">
		<option value="Market Street">Market Street</option>
		<option value="Chalfont/Highpoint">Chalfont/Highpoint</option>
	</select>
	
	<? } else if( $session_schedule == "dc" ) { ?>
		
	<select name="region" style="font-size: 10px;  font-family: verdana;">
		<option value="F Street">F Street</option>
		<option value="Fairfax">Fairfax</option>
		<option value="Bethesda">Bethesda</option>
	</select>
	
	<? } ?>
	
	
	
	
	
	
	</span></td>
  </tr>
  
   <tr>
    <td valign="top" align="right"><span class="copy">Class Type:</span></td>
	<td valign="top"><span class="copy">
	<select name="classtype" style="font-size: 10px;  font-family: verdana;">
		<option value="Heartsaver AED/CPR Certification">Heartsaver AED/CPR Certification</option>
	</select></span></td>
  </tr>
  
  
  <tr>
    <td valign="top" align="right"><span class="copy">Session 1:</span></td>
	<td valign="bottom">
		<table cellpadding="0" cellspacing="0" border="0">
            <tr>
               <td valign="middle"><span class="copy">Date:</span></td>
			   <td valign="middle"><input name="session1date" type="text" size="10" style="font-size: 10px;  font-family: verdana;">&nbsp;</td>
			   <td valign="middle" colspan="4" align="left"><img src="images/cal.gif" border="0"></td>
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
			   <td valign="middle"><input type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0">&nbsp;&nbsp;</td>
			   <td valign="middle"><span class="copy">End Time:&nbsp;</span></td>	   
			   <td valign="middle"><input type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0"></td>
            </tr>
		</table>
	</td>	
  </tr>
  
<? include "recert.php" ; ?>
</table>

</body>
</html>
