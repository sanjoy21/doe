<?php
$nologinrequired = true;
require_once "mysql.php";

// Safely retrieve the external variable $id (the class ID)
$id = $_REQUEST['id'] ?? ($id ?? null);
$db_link = $GLOBALS['link'] ?? $link; 

// Safety: Ensure $id is an integer for the SQL query
$safe_id = (int)$id;

// --- Update the database with the host confirmation date ---
db_query( "UPDATE class SET hostconfirmdate = NOW() WHERE id = {$safe_id}" );

$web_root = $GLOBALS['WEB_ROOT'] ?? WEB_ROOT ?? '';
?>

<!DOCTYPE html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<meta name="keywords" content="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<meta name="description" content="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<!-- <script language="JavaScript">
function ChangeImage (ImageName,FileName) {
document[ImageName].src = FileName;
}
</script> -->


<!-- <script language="JavaScript">
function openWindow1() {  popupWin = window.open('downloads/brochure.shtml', 'figure1', 'scrollbars,resizable,width=700,height=550')
}
</script> -->

<!-- <style type="text/css">
BODY {margin:0}
</style> -->


<style type="text/css">
a:link { color: #330099; text-decoration: none }
a:active { color: #330099; text-decoration: none }
a:visited { color: #330099; text-decoration: none }
a:hover { color: #330099; text-decoration: none }
</style> 

<link rel="stylesheet" href="<?php echo htmlspecialchars($web_root); ?>/css/style.css">

</head>

<body bgcolor="#5a179e" marginwidth="0" marginheight="0" link="blue" visited="blue">

<br>
<div align="center">
<table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" width="700">
<tr>
<td colspan="4" valign="top"><img src="<?php echo htmlspecialchars($web_root); ?>/images/topbanner.jpg" width="700"></td>
</tr>
<tr>
<td colspan="4" valign="top" background="/images/topnav_background.jpg" width="700" height="24"><div align="right">

<?php include "ssi/topnav.php";?>

</div>
</td>
</tr>
<tr>
<td valign="top">
<?php include "ssi/leftnav2.php" ;?>

</td>
<td valign="top" width="5"><img src="<?php echo htmlspecialchars($web_root); ?>/images/dotclear.gif" width="10"></td>
<td valign="top" width="476"><br>

Thanks! Your class has been confirmed.

<br><br><br><br><br><br><br><br>
 <br><br><br><br><br><br><br><br>
<?php include "ssi/footer.php";?>
</span>
</td>
<td valign="top" width="15"><img src="<?php echo htmlspecialchars($web_root); ?>/images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>

</body>
</html>