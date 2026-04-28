<?
include "mysql.php";
$type = "reg";
if( $state ){
mysql_query( "insert into schedule ( type, session1starttime, session1endtime, session1date, session2starttime, session2endtime, session2date, region, attendboth, mollyguard, price, recertification ) values  ( '$type', '$session1starttime', '$session1endtime', '$session1date', '$session2starttime', '$session2endtime', '$session2date', '$region', '$attendboth', '$mollyguard', '$price', '$recertification' )" );

Header( "Location: addnew_reg_done.php" );
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
		<option value="CPR for the Healthcare Provider">CPR for the Healthcare Provider</option>
		<option value="HeartSaver (adult) CPR">HeartSaver (adult) CPR</option>
		<option value="HeartSaver AED (with adult CPR)">HeartSaver AED (with adult CPR)</option>
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
			   <td valign="middle"><input name="session2starttime" type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0">&nbsp;&nbsp;</td>
			   <td valign="middle"><span class="copy">End Time:&nbsp;</span></td>	   
			   <td valign="middle"><input name="session2endtime" type="text" size="10" style="font-size: 10px;  font-family: verdana;"></td>
			   <td valign="middle"><img src="images/clock.gif" border="0"></td>
            </tr>
		</table>
	</td>	
  </tr>
  
<? include "recert.php"; ?>
</table>

</body>
</html>
