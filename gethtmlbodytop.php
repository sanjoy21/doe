<?php 
// Assuming functions.php is included elsewhere or getUrlPrefix is globally available.

// Safely retrieve assumed variable
$comrow = $comrow ?? ['iscorp' => 0]; // Default to non-corporate if not set
$iscorp_status = (int)($comrow['iscorp'] ?? 0); 

// Assuming getUrlPrefix() exists and is safe
$url_prefix = htmlspecialchars(getUrlPrefix( $iscorp_status ));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
 <title>Emergency Skills, Inc.</title>
 <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
</head>
<body>

<table cellpadding="0" cellspacing="0" border="0" style="width: 750px; border: 1px #666666 solid;">
<tr>
<td valign="top">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top" colspan="3"><img src="https://<?php echo $url_prefix; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Header.jpg" alt="Emergency Skills Header"></td>
 </tr>
 <tr>
 <td valign="top"><img src="https://<?php echo $url_prefix; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Header2.jpg" alt="Emergency Skills Header Graphics"></td>
 <td valign="top"><a href='http://www.emergencyskills.com'><img src="https://<?php echo $url_prefix; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Home-Link.jpg" border="0" alt="Home Link"></a></td>
 <td valign="top"><a href='https://emergencyskills.com/index.php/contact/'><img src="https://<?php echo $url_prefix; ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/email/Emergency-Skills-Contact-Link.jpg" border="0" alt="Contact Link"></a></td>
</tr>
 <tr>
 <td valign="top" colspan="3">

 <table cellpadding="0" cellspacing="0" border="0">
 <tr>
 <td valign="top" style="padding: 15px; width: 470px;">
 <span style="font-family: arial; font-size: 13px; color: #333333; line-height: 17px;">
