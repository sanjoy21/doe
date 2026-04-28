<? include "mysql.php";

if( $city == "bo" )
{
$citystr = "Boston";
}
if( $city == "ny" )
{
$citystr = "New York";
}
if( $city == "ph" )
{
$citystr = "Philadelphia";
}
if( $city == "dc" )
{
$citystr = "Washington D.C.";
}

$result = mysql_query( "select * from schedule where type ='$city' order by session1date" );

?>

	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR,AED,AED training,AED Sales,Defibrillator,Automated External Defibrillator,Emergency Skills,<?=$citystr?>,cardiac arrest,heart attack, public access defibrillation,defib sales,defib training,Cardio Pulmonary Resuscitation, pocket mask,Defensive Driving,Road Rage">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the <?=$citystr?> metro area.">

<SCRIPT LANGUAGE="JavaScript">
<!---------- JavaScript begins...
function ChangeImage (ImageName,FileName) {
	document[ImageName].src = FileName;
}
// JavaScript ends ---------->
</SCRIPT>	


<script language="JavaScript">
<!-- hide from JavaScript-challenged browsers
function openWindow1() {  popupWin = window.open('downloads/brochure.shtml', 'figure1', 'scrollbars,resizable,width=700,height=550')
}
// done hiding -->
</script>

<!--<A HREF="javascript:openWindow29();">-->

<STYLE TYPE="text/css">
<!--
BODY {margin:0}
-->
</STYLE>	


<STYLE TYPE="text/css">
a:link { color: #330099; text-decoration: none }
a:active { color: #330099; text-decoration: none }
a:visited { color: #330099; text-decoration: none }
a:hover { color: #330099; text-decoration: none }
</STYLE> 

<link rel="stylesheet" href="../css/style.css">

</head>

<body bgcolor="#000066" marginwidth="0" marginheight="0" link="blue" visited="blue">

<br>
<div align="center">
<table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" width="700">
	<tr>
		<td colspan="4" valign="top"><img src="../images/topbanner.jpg" width="700"></td>
	</tr>
	<tr>
		<td colspan="4" valign="top" background="../images/topnav_background.jpg" width="700" height="24"><div align="right">
			
						<table cellpadding="0" cellspacing="0" border="0">
				<tr>	
					<td><img src="../images/dotclear.gif" height="5"></td>
				</tr>
				<tr>
					<td>
						<span class="topnav"><strong><a href="../index.shtml"><span class="topnav">Home</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="../about.shtml"><span class="topnav">About Us</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="../news.shtml"><span class="topnav">ESI News</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="../contact.shtml"><span class="topnav">Contact Us</span></a>&nbsp;&nbsp;&nbsp;
</span></strong>
					</td>
				</tr>
			</table>
			
			
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top">
		
				<!--begin collapsed nav-->
			<table cellpadding="0" cellspacing="0" border="0" width="204" background="../images/swoosh.gif">
				<tr height="240">
					<td width="202" valign="top">
						<table cellpadding="0" cellspacing="0" border="0" width="204">
				<tr>
					<td valign="top"><A HREF="../training_programs.shtml" onMouseover="ChangeImage ('training_programs','../images/lncol_trainingprog_on.gif')" onMouseout="ChangeImage ('training_programs','../images/lncol_trainingprog_off.gif')"><IMG SRC="../images/lncol_trainingprog_off.gif" NAME="training_programs" BORDER=0></A></td>
				</tr>
				<tr>
					<td valign="top"><A HREF="http://www.emergencyskills.com/tsi/" onMouseover="ChangeImage ('training_schedule','../images/lncol_trainingsched_on.gif')" onMouseout="ChangeImage ('training_schedule','../images/lncol_trainingsched_off.gif')"><IMG SRC="../images/lncol_trainingsched_off.gif" NAME="training_schedule" BORDER=0></A></td>
				</tr>	
				<tr>
					<td valign="top"><A HREF="../defensive_driving.shtml" onMouseover="ChangeImage ('defensive_driving','../images/lncol_defdriving_on.gif')" onMouseout="ChangeImage ('defensive_driving','../images/lncol_defdriving_off.gif')"><IMG SRC="../images/lncol_defdriving_off.gif" NAME="defensive_driving" BORDER=0></A></td>
				</tr>
				<tr>
					<td valign="top"><A HREF="../heartstart.shtml" onMouseover="ChangeImage ('heartstart','../images/lncol_heartstart_on.gif')" onMouseout="ChangeImage ('heartstart','../images/lncol_heartstart_off.gif')"><IMG SRC="../images/lncol_heartstart_off.gif" NAME="heartstart" BORDER=0></A></td>
				</tr>			
				<tr>
					<td valign="top"><A HREF="../shop.shtml" onMouseover="ChangeImage ('shop','../images/lncol_shop_on.gif')" onMouseout="ChangeImage ('shop','../images/lncol_shop_off.gif')"><IMG SRC="../images/lncol_shop_off.gif" NAME="shop" BORDER=0></A></td>
				</tr>	
				
				<tr>
					<td valign="top"><br><IMG SRC="../images/lncol_corpsales.gif" NAME="Buy AEDs" BORDER=0 hspace="8"></A></td>
						</tr>	
					</table>				
				 </td>
				</tr>
			</table>
			<br>
			<table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td valign="top"><a HREF="../defensive_driving.shtml"><img SRC="../images/stoplight.gif" hspace="10" border="0"></a></td>
              </tr>
            </table>
		<br><br><br><br>
		<!--end collapse nav-->
		
		
		</td>
		<td valign="top" width="5"><img src="../images/dotclear.gif" width="10"></td>
		<td valign="top" width="476"><br>

		<span class="copy">		
	<!--begin center content-->

	

	
		<table cellpadding="0" cellspacing="0" border="0" width="100%">
          <tr>
            <td valign="top"><br><span class="copy"><strong>You selected <?=$citystr?> Sports Club.<p>
Below is the schedule for all Heartsaver AED/CPR Programs in your region.</strong></span></td>
			<td valign="top"><img src="../images/tsi_nysc.gif" hspace="0" border="0"></td>
		
			</tr>
        </table>

<br><hr><br>

<span class="copy">
<strong><span class="title">Heartsaver AED/CPR Certification</span></strong><br><br>
<a href="http://www.emergencyskills.com/tsi/registration_form.pdf" target="_blank"><img align="right" src="../images/registration_button.gif" alt="Register by fax today! Click here!" width="113" height="92" hspace="5" border="0"></a>American Heart Association Heartsaver AED/CPR Program, 4 hour program
In this 4-hour program learn how to perform CPR, the choking maneuvers and
how to properly and safely apply an Automated External Defibrillator (AED).
You will have hands-on practice with a CPR manikin and the Heartstart OnSite
Defibrillator, the AED to be placed at each Club.
<p>
<strong>Successful completion requires:</strong> Demonstration of all Skills and Written Test
Score of 84% or better
<p>
<strong>Certification Card:</strong> American Heart Association Heartsaver AED (Adult CPR +
AED), valid for 2 years.
</span>	<br><br>

<!--begin nyc schedule-->

<table cellpadding="0" cellspacing="1" border="0" bgcolor="#666633" width="100%"><tr><td>
<table bgcolor="#F6F6D5" width="100%" border="0" cellpadding="12">
<tr>
<td>
<span class="copy">
<strong>Local training dates in the <?=$citystr?> region:
</span>	<p>	   

	<table width="100%">
<? while ($row = mysql_fetch_array( $result ) ) { ?>

			<tr>
			<td valign="top"><span class="copy"><li><strong><?=date( "D. F d, Y", strtotime( $row["session1date"] ) )?> </strong><br>&nbsp;&nbsp;&nbsp;&nbsp;<? if( $row["session1starttime"] ) { ?> <?=$row["session1starttime"]?> to <?=$row["session1endtime"]?> <? } ?><? if( $row["recertification"] ) { ?><br>&nbsp;&nbsp;&nbsp;&nbsp;<strong><em>Recertification</em></strong><? } ?> </li></span></td>
			<td valign="top"><span class="copy"><strong><?=$row["region"]?></strong></span></td>	
			<td valign="top"><span class="copy">$<?=$row["price"]?></span></td>
			<td valign="top" align="right"><span class="copy"><a href="<?=$row["mollyguard"]?>" target="outside"><strong>Register</strong></a></span></td>				
		<tr><td colspan="4">&nbsp;</td></tr>
		<? } ?>
	</table>

</td></tr></table>
</td></tr></table>

<!--end nyc schedule-->


		<br><br><br><br><br>
		<!--end center content-->
		
		<hr width="457" align="left">
		<img src="../images/ribbon.gif" align="left"><br><span class="footer"><strong>Copyright &copy; 2003-2004 Emergency Skills Inc.</strong><br>
<a href="../index.shtml"><span class="footer">Home</span></a>  |  <a href="../about.shtml"><span class="footer">About Us</span></a>  |  <a href="../news.shtml"><span class="footer">ESI News</span></a>  |  <a href="../contact.shtml"><span class="footer">Contact Us</span></a>
		</span>

		
		<!--end footer-->
		
		</span>
		</td>
		<td valign="top" width="15"><img src="../images/dotclear.gif" width="10"></td>
	</tr>
</table>
<br><br>
</div>

</body>
</html>
