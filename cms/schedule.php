<?
include "mysql.php" ;

if( $delid )
{
	mysql_query( "delete from schedule where id = '$delid' ");
}


$result = mysql_query("select * from schedule where type = '$session_schedule' order by classtype, session1date, session1starttimeampm" ) or die( mysql_error() );

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<SCRIPT LANGUAGE="JavaScript">
<!---------- JavaScript begins...
function ChangeImage (ImageName,FileName) 
{
	document[ImageName].src = FileName;
}
// JavaScript ends ---------->
</SCRIPT>	


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
<link rel="stylesheet" href="css/style.css">
</head>




<body bgcolor="#000066" marginwidth="0" marginheight="0" link="blue" visited="blue">

<br>
<div align="center">
<table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" width="700">
	<tr>
		<td colspan="4" valign="top"><img src="images/topbanner.jpg" width="700" height="84"></td>
	</tr>
	<tr>
		<td colspan="4" valign="top" background="images/topnav_background.jpg" width="700" height="24">
        <div align="right">		
        <? include "../ssi/topnav.php"; ?>
		</div>
		</td>
	</tr>
	<tr>
		<td valign="top">		
		<? include"ssi/leftnav.php"; ?>
		</td>
		<td valign="top" width="5"><img src="images/dotclear.gif" width="10"></td>
		<td valign="top" width="476"><br><span class="copy">
		
		<!--begin center content-->
		
		
		<table cellpadding="5" cellspacing="1" border="0" width="465">
			<tr>
				<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="http://doe.emergencyskills.com/adminmain.php">&laquo; Back to Admin Main</a></strong></span></td>				
			</tr>
			<!--<tr>
				<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><div align="right">* indicates required fields</div></span></td>				
			</tr>-->
		</table>
		<p>
		
		<strong>You are editing the
<? if( $session_schedule == "reg" ){
echo( "Emergency Skills Regular Class" );
}
else if( $session_schedule == "dd" )
{
echo( "Emergency Skills Defensive Driving" );
}
else
{
$result2 = mysql_query( "select name from cities where shortcut = '$session_schedule'" );
$row = mysql_fetch_array( $result2 );
echo( "TSI ". $row["name"] );
}
?>
 Schedule.</strong><p>
		
		The current schedule is below, please use the buttons to make edits.<p>
		
					<table border="0" cellpadding="3" width="465"><tr><td colspan="5" align="right"> <a href="schedule.php"><img border=0 src="images/refresh.gif"></a><a href='#' onclick='javascript:window.open( "addnew.php" )'><img border=0 src="images/new.jpg"></a>&nbsp;<a href='http://doe.emergencyskills.com/login.php'><img border=0 src="images/logoff.jpg"></a></td></tr></table>			
						
						<table border="0" cellpadding="0" cellspacing="1" width="465" bgcolor="#1C0792"><tr><td colspan="5">
						<table border="1" cellpadding="3" cellspacing="0" width="465" bgcolor="#ffffff">
							<tr><td colspan="5" bgcolor="#1C0792"><span class="white"><strong>Schedule</strong></span></td></tr>
<?
$prevtype = "";
while( $row = mysql_fetch_array( $result ) )
{
if( $prevtype != $row["classtype"] )
{
?>
							<tr><td colspan="5" bgcolor="#E6E4E4"><span class="copy"><strong><?=$row["classtype"]?></strong></span></td></tr>
<?
$prevtype = $row["classtype"];
} ?>
							<tr>
								<td valign="top" width="113">
									<div align="center"><span class="copy">
									<?=date( "l", strtotime( $row["session1date"] ) )?><br>
									<?=date( "M. d, Y", strtotime( $row["session1date"] ) )?>
<? if( $row["session2date"] ) { ?>
<br>									<div align="center"><span class="copy">   
									<?=date( "l", strtotime( $row["session2date"] ) )?><br>
									<?=date( "M. d, Y", strtotime( $row["session2date"] ) )?>
									</span></div>									

<? } ?>
<? if( $row["recertification"] ) { ?>					<strong><em>Recertification</em></strong><br> <? } ?>
<? if( $row["attendboth"] ) { ?>					<strong><em>Must attend both sessions</em></strong><br> <? } ?>
									</span></div>									
								</td>							
								<td valign="top" width="113">
									<div align="center"><span class="copy"><? if( $row["region"] ){ ?><?=$row["region"]?><br><? } ?>
									<?=$row["session1starttime"]?>-<?=$row["session1endtime"] ?>
<? if( $row["session2starttime"] && $row["session2starttime"]  != ":" ) { ?> <br> 
<?=$row["session2starttime"]?>-<?=$row["session2endtime"] ?>
<? } ?>

									</span></div>									
								</td>
								<td valign="top" width="58">
									<div align="center"><span class="copy">
									$<?=$row["price"]?>
									</span></div>							
								</td>
								<td valign="top" width="78">
									<div align="center"><span class="copy"><strong>
								<a href="<?=$row["mollyguard"]?>">Register</a>
									</strong></span></div>									
								</td>
								<td width="103">
								<div align="center">
								<a href='#' onclick='javascript:window.open( "addnew.php?id=<?=$row["id"]?>" )'><img border=0 src="images/edit.jpg"></a><br>
								<a href='schedule.php?delid=<?=$row["id"]?>'><img border=0 src="images/delete.jpg"></a>
								</div>
								</td>
							</tr>

<? 
} ?>							
						</table>
						</td></tr></table>
				

<br><br>

</td></tr></table>	
		<br>
        <br>

		
		</span>
		</td>
		<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
	</tr>
</table>
<br><br>
</div>

</body>
</html>
