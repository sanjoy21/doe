<?php
// --- 1. Immediate Redirect (As per the original script) ---
// This redirect happens regardless of the content below it.
header( "Location: login.php" );
exit;

// The following code is technically unreachable due to the immediate 'exit' above, 
// but it is converted for PHP 8.2 compatibility for completeness, 
// assuming the redirect might be temporarily commented out later.

$nologinrequired = true;
include "mysql.php";
?>
<?php include "ssi/top.php"; ?>
 <span class="copy">
 <table cellpadding="0" cellspacing="2" border="0"><tr><td valign='top'>
<table cellpadding="4" cellspacing="0" border="0" valign='top'>
 <tr>
<td valign="middle" align="center" class='copy' colspan='2' >
<Br><span class="copy"><a href='individual_registration1.php'><b>Individual Registration<b></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>
 </td>
 </tr>
 <tr>
 <td valign="middle" align="center" class='copy' colspan='2' >
<Br><span class="copy"><a href='login.php'><b>Schedule a Class At Your School<b></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>
 </td>
 </tr>
 <tr>
 <td valign="middle" align="center" class='copy' colspan='2' >
<Br><span class="copy"><a href='login.php'><b>View School Information<b></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>
</td>
 </tr>
<tr>
<td valign="middle" align="center" class='copy' colspan='2' >
<Br><span class="copy"><a href='login.php'><b>ESI Instructors And Staff<b></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>
 </td>
</tr>
<tr>
 <td valign="middle" align="center" class='copy' colspan='2' >
<Br><span class="copy"><a href='general.php'><b>General Information<b></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br>
 </td>
 </tr>
 </table></td>
</tr></table></div>
 <br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
 
<?php include "ssi/footer.php"; ?>