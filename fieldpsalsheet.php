<?php
require_once('mysql.php');

// Initialize variables
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$theid = $id;

// If no ID provided, try to get from user's company
if( !$theid && isset($thisusersrow['companyid']) && $thisusersrow['companyid'] ) {
    $theid = $thisusersrow['companyid'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0055)http://doe.emergencyskills.com/roster_print.php?id=7345 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<style type="text/css">
html {
margin: 0;
}
body {
margin: 0;
}

.dotTop {border: 1pt dashed #666666;
}

</style>
<link rel="stylesheet" href="http://doe.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">
<style type="text/css">

P.breakhere {page-break-before: always}

body {
margin-top: 0px;
margin-bottom: 0px;
margin-left: 0px;
margin-right: 0px;
}
.style2 {
font-family: arial;
font-size: 13px;
}
.style3 {
font-family: arial;
font-size: 14px;
color: #0066cc;
}

</style>
</head>

<body>

<table cellpadding="7" cellspacing="0" border="1" style="width:100%;">
    <tbody>
        <tr style="background-color: #ededed; height: 28px;">
            <td valign="middle" class="style3"><strong>Serial Number</strong></td>
            <td valign="middle" class="style3"><strong>Coach</strong></td>
            <td valign="middle" class="style3"><strong>Email</strong></td>
            <td valign="middle" class="style3"><strong>Cell Phone</strong></td>
            <td valign="middle" class="style3"><strong>Reference<br>ID Number</strong></td>
            <td valign="middle" class="style3"><div align="center"><strong>Current CPR<br>Certification</strong></div></td>
            <td valign="middle" class="style3"><div align="center"><strong>Signed Agreement<br>Received</strong></div></td>
        </tr>
<?php
// Get AED rows
$aed_rows = array();
if( $theid > 0 ) {
    $aed_rows = db_query_rows("select * from aed_esi where clientid=$theid and deleted=0 and location in ( 'PSAL', 'SSAL' ) order by serial");
}

// Get responders (though not used in this template)
$responder_rows = array();
if( $id > 0 ) {
    $responder_rows = getResponders( $id );
}

if( is_array($aed_rows) ) {
    foreach( $aed_rows as $arow ) {
        // Initialize variables for this row
        $serial = isset($arow['serial']) ? htmlspecialchars($arow['serial']) : '';
        $cname = isset($arow['psalassignedto']) ? htmlspecialchars($arow['psalassignedto']) : '';
        $cemail = isset($arow['psalassignedemail']) ? htmlspecialchars($arow['psalassignedemail']) : '';
        $cphone = isset($arow['psalassignedphone']) ? htmlspecialchars($arow['psalassignedphone']) : '';
        $cid = '';
?>
        <tr>
            <td valign="middle" class="copy"><?php echo $serial; ?>&nbsp;</td>
            <td valign="middle" class="copy"><?php echo $cname; ?>&nbsp;</td>
            <td valign="middle" class="copy"><?php echo $cemail; ?>&nbsp;</td>
            <td valign="middle" class="copy"><?php echo $cphone; ?>&nbsp;</td>
            <td valign="middle" class="copy"><?php echo $cid; ?>&nbsp;</td>
            <td valign="middle" class="copy"><div align="center"><input type="checkbox"></div></td>
            <td valign="middle" class="copy"><div align="center"><input type="checkbox"></div></td>
        </tr>
<?php 
    }
}
?>
    </tbody>
</table>

</body>
</html>