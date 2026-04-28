<? include "mysql.php" ;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR,AED,AED training,AED Sales,Defibrillator,Automated External Defibrillator,Emergency Skills,New York,New York City,cardiac arrest,heart attack, public access defibrillation,defib sales,defib training,Cardio Pulmonary Resuscitation, pocket mask,Defensive Driving,Road Rage">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

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
a:link { color: blue; text-decoration: none }
a:active { color: blue; text-decoration: none }
a:visited { color: blue; text-decoration: none }
a:hover { color: blue; text-decoration: none }
</STYLE> 

<link rel="stylesheet" href="css/style.css">

</head>

<body bgcolor="#000066" marginwidth="0" marginheight="0" link="blue" visited="blue">

<br>
<div align="center">
<table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" width="700">
	<tr>
		<td colspan="5" valign="top"><img src="images/topbanner.jpg" width="700"></td>
	</tr>
	<tr>
		<td colspan="5" valign="top" background="images/topnav_background.jpg" width="700" height="24"><div align="right">
			
						<table cellpadding="0" cellspacing="0" border="0">
				<tr>	
					<td><img src="images/dotclear.gif" height="5"></td>
				</tr>
				<tr>
					<td>
						<span class="topnav"><strong><a href="index.shtml"><span class="topnav">Home</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="about.shtml"><span class="topnav">About Us</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="news.shtml"><span class="topnav">ESI News</span></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="contact.shtml"><span class="topnav">Contact Us</span></a>&nbsp;&nbsp;&nbsp;
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
			<table cellpadding="0" cellspacing="0" border="0" width="204" background="images/swoosh.gif">
				<tr height="240">
					<td width="202" valign="top">
						<table cellpadding="0" cellspacing="0" border="0" width="204">
				<tr>
					<td valign="top"><A HREF="training_programs.shtml" onMouseover="ChangeImage ('training_programs','images/lncol_trainingprog_on.gif')" onMouseout="ChangeImage ('training_programs','images/lncol_trainingprog_off.gif')"><IMG SRC="images/lncol_trainingprog_off.gif" NAME="training_programs" BORDER=0></A></td>
				</tr>
				<tr>
					<td valign="top"><A HREF="training_schedule.shtml" onMouseover="ChangeImage ('training_schedule','images/lncol_trainingsched_on.gif')" onMouseout="ChangeImage ('training_schedule','images/lncol_trainingsched_off.gif')"><IMG SRC="images/lncol_trainingsched_off.gif" NAME="training_schedule" BORDER=0></A></td>
				</tr>	
				<tr>
					<td valign="top"><A HREF="defensive_driving.shtml" onMouseover="ChangeImage ('defensive_driving','images/lncol_defdriving_on.gif')" onMouseout="ChangeImage ('defensive_driving','images/lncol_defdriving_off.gif')"><IMG SRC="images/lncol_defdriving_off.gif" NAME="defensive_driving" BORDER=0></A></td>
				</tr>
				<tr>
					<td valign="top"><A HREF="heartstart.shtml" onMouseover="ChangeImage ('heartstart','images/lncol_heartstart_on.gif')" onMouseout="ChangeImage ('heartstart','images/lncol_heartstart_off.gif')"><IMG SRC="images/lncol_heartstart_off.gif" NAME="heartstart" BORDER=0></A></td>
				</tr>			
				<tr>
					<td valign="top"><A HREF="shop.shtml" onMouseover="ChangeImage ('shop','images/lncol_shop_on.gif')" onMouseout="ChangeImage ('shop','images/lncol_shop_off.gif')"><IMG SRC="images/lncol_shop_off.gif" NAME="shop" BORDER=0></A></td>
				</tr>	
				
				<tr>
					<td valign="top"><br><IMG SRC="images/lncol_corpsales.gif" NAME="Buy AEDs" BORDER=0 hspace="8"></A></td>
						</tr>	
					</table>				
				 </td>
				</tr>
			</table>
			<br>
			<table cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td valign="top"><a href="defensive_driving.shtml"><img src="images/stoplight.gif" hspace="10" border="0"></a></td>
              </tr>
            </table>
		<br><br><br><br>
		<!--end collapse nav-->
		
		
		</td>
		<td valign="top" width="5"><img src="images/dotclear.gif" width="10"></td>
		<td valign="top" width="476"><br>

		<span class="copy">
		
		<!--begin center content-->
		
		<span class="title"><strong>Training Schedule for Individuals &amp; General Public</strong></span><p>
		
<strong><a href="contact.shtml">[Corporate or group training click here]</a></strong><br><br>

The following is a schedule of upcoming courses for individuals who are not part of a corporation or group.  <p>

<strong>Course Location:</strong> SLC Conference Center, 305 7th Avenue, Suite 1100<br><br>
<strong>Pre-registration is required. Payment in advance is required.</strong> Cash is due one week before class.
We accept MasterCard, VISA, and American Express, Discover, Money orders or Cash.


<p>


<table cellpadding="0" cellspacing="1" border="0" bgcolor="#000066"><tr><td><table bgcolor="#F7F7FD" width="100%" border="0" cellpadding="12">
<tr>
<td>
<?
$result = mysql_query( "select * from schedule where type ='reg' and classtype = 'CPR for the Healthcare Provider' order by session1date" );
if( mysql_num_rows( $result ) > 0 ) { 
?>
<span class="copy">
<strong><span class="title">CPR for the Healthcare Provider</span></strong><br><br>
<strong>Content:</strong>&nbsp;&nbsp;	1 &amp; 2-rescuer CPR, Rescue Breathing, Choking, Bag-valve mask for the Adult, Child and Infant and basic Automated External Defibrillator (AED) <br>
<strong>Who:</strong>&nbsp;&nbsp;	Nurses, Nursing Students, Physicians, Lifeguards, &amp; Therapists<br><br>
</span>			   

	<table width="100%">
<?	while ($row = mysql_fetch_array( $result ) ) { ?>		
		<tr>
			<td valign="top"><span class="copy"><li><?=date( "D. F d, Y", strtotime( $row["session1date"] ) )?> </li><? if( $row["recertification"] ) { ?><br>&nbsp;&nbsp;&nbsp;&nbsp;<strong><em>Recertification</em></strong><? } ?></span></td>
			<td valign="top"><span class="copy"><? if( $row["session1starttime"] ) { ?> <?=$row["session1starttime"]?> to <?=$row["session1endtime"]?> <? } ?> </span></td>	
			<td valign="top"><span class="copy">$<?=$row["price"]?></span></td>
			<td valign="top"><span class="copy"><a href="<?=$row["mollyguard"]?>" target="outside"><strong>Register</strong></a></span></td>				
		</tr>
<? } ?>		
	</table>
	<p>
<span class="copy">	
Upon successful completion, you will receive the American Heart Association CPR for the Healthcare Provider completion card valid for 2 years.
<br><br>
<strong><span class="footer"><em>Successful completion requires return demonstration of all skills and a score of 84% or better on a multiple-choice exam.</em></span></strong>
</span>
<br><hr><br>
<? } ?>

<?
$result = mysql_query( "select * from schedule where type ='reg' and classtype = 'HeartSaver (adult) CPR' order by session1date" );
if( mysql_num_rows( $result ) > 0 ) { 
?>
<span class="copy">
<strong><span class="title">HeartSaver (adult) CPR</span></strong><br><br>
<strong>Content:</strong>&nbsp;&nbsp;	1-rescuer CPR, Rescue Breathing, Choking, and Pocket Mask for the Adult <br>
<strong>Who:</strong>&nbsp;&nbsp;	Fitness professionals, security guards, or people with a personal interest<br><br>
</span>
	<table width="100%">		
				
	<?
	while ($row = mysql_fetch_array( $result ) ) { ?>		
		<tr>
			<td valign="top"><span class="copy"><li><?=date( "D. F d, Y", strtotime( $row["session1date"] ) )?> </li><? if( $row["recertification"] ) { ?><br>&nbsp;&nbsp;&nbsp;&nbsp;<strong><em>Recertification</em></strong><? } ?></span></td>
			<td valign="top"><span class="copy"><? if( $row["session1starttime"] ) { ?> <?=$row["session1starttime"]?> to <?=$row["session1endtime"]?> <? } ?> </span></td>	
			<td valign="top"><span class="copy"><?=$row["price"]?></span></td>
			<td valign="top"><span class="copy"><a href="<?=$row["mollyguard"]?>" target="outside"><strong>Register</strong></a></span></td>				
		</tr>
<? } ?>		

	</table>
	<p>
<span class="copy">
Upon successful completion, you will receive the American Heart Association HeartSaver CPR completion card valid for 2 years.
<br><br>
<strong><span class="footer"><em>Successful completion requires return demonstration of all skills and a score of 84% or better on a multiple-choice exam.</em></span></strong>
</span>


<br><hr><br>
<? } ?>

<?
$result = mysql_query( "select * from schedule where type ='reg' and classtype = 'HeartSaver AED (with adult CPR)' order by session1date" );
if( mysql_num_rows( $result ) > 0 ) { 
?>

<span class="copy">
<strong><span class="title">HeartSaver AED (with adult CPR)</span></strong><br><br>
<strong>Content:</strong>&nbsp;&nbsp;	1-rescuer CPR, Rescue Breathing, Choking, Pocket Mask &amp AED<br>
<strong>Who:</strong>&nbsp;&nbsp;	Corporate Employees with an AED<br><br>
</span>
	<table width="100%">
	
				
	<?
	while ($row = mysql_fetch_array( $result ) ) { ?>		
		<tr>
			<td valign="top"><span class="copy"><li><?=date( "D. F d, Y", strtotime( $row["session1date"] ) )?> </li><? if( $row["recertification"] ) { ?><br>&nbsp;&nbsp;&nbsp;&nbsp;<strong><em>Recertification</em></strong><? } ?></span></td>
			<td valign="top"><span class="copy"><? if( $row["session1starttime"] ) { ?> <?=$row["session1starttime"]?> to <?=$row["session1endtime"]?> <? } ?> </span></td>	
			<td valign="top"><span class="copy"><?=$row["price"]?></span></td>
			<td valign="top"><span class="copy"><a href="<?=$row["mollyguard"]?>" target="outside"><strong>Register</strong></a></span></td>				
		</tr>
<? } ?>		

	</table>
	<p>
<span class="copy">
Upon successful completion, you will receive the American Heart Association Heartsaver AED completion card valid for 2 years.
<br><br>
<strong><span class="footer"><em>Successful completion requires return demonstration of all skills and a score of 84% or better on a multiple-choice exam.</em></span></strong>
</span>

<? } ?>

<!--

<br><hr><br>

<span class="copy">
<strong><span class="title">BLS Instructor Program (2-day course)</span></strong><br><br>
Become qualified to <em>teach</em> American Heart Association CPR & AED Training Programs<br>
<strong>Fee:</strong> $396.00 per person<br>
<strong>Pre-requisite:</strong> Current certification in CPR for the Healthcare Provider with a written test score of 90% or better and mastery level  in CPR skills
<br><br>
</span>
	<table width="100%">
		<tr>
			<td valign="top" width="220"><span class="copy">

<li>Thursday, Jan 8, 2004 <br> 
	&nbsp;&nbsp;&nbsp;&nbsp;Thursday, Jan 15, 2004</li>

</span></td>
			<td valign="top" width="180"><span class="copy">9:00am to 5:00pm<br>9:00am to 5:00pm</span></td>
			<td valign="top" width="60"><span class="copy">$396</span></td>
			<td valign="top" width="60"><span class="copy"><a href="http://www.mollyguard.com/event/8605740" target="outside"><strong>Register</strong></a></span></td>					
		</tr>	
		
	</table>
	<p>
<span class="copy">The instructor program is one course held over two days.  <strong>In order to complete the program you must attend both days.</strong><br><br>
Upon successful completion, you will receive the American Heart Association 
BLS Instructor completion card valid for 2 years.

<br><br>
<strong><span class="footer"><em>Successful completion requires return demonstration of all skills and a score of 84% or better on a multiple-choice exam.</em></span></strong>
</span>-->
</td>
</tr>
</table>
	</td></tr></table>
		<br><br>
		<!--end center content-->
		
		<hr width="457" align="left">
		<img src="images/ribbon.gif" align="left"><br><span class="footer"><strong>Copyright &copy; 2003-2004 Emergency Skills Inc.</strong><br>
<a href="index_new.shtml"><span class="footer">Home</span></a>  |  <a href="about.shtml"><span class="footer">About Us</span></a>  |  <a href="news.shtml"><span class="footer">ESI News</span></a>  |  <a href="contact.shtml"><span class="footer">Contact Us</span></a>
		</span>
		
		</span>
		</td>
		<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
	</tr>
</table>
</div>

</body>
</html>
