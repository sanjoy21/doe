<?php
require_once "mysql.php"; 

// Initialize variables
$ids = array();

// If no ID provided, try to get from user's company
if( !$id && isset($thisusersrow["companyid"]) && $thisusersrow["companyid"] ) {
    $id = $thisusersrow["companyid"];
}

$mainrow = array();
if( $id > 0 ) {
    $mainrow = getCompanyRow( $id );
    $ids[] = $id;
}

// Get other schools in campus if applicable
if( !isset($mainrow["iscorp"]) || !$mainrow["iscorp"] ) {
    if( isset($mainrow["campusid"]) && $mainrow["campusid"] ) {
        $others = getSchoolsInCampus( $mainrow["campusid"], $mainrow["id"] );
        if( is_array($others) ) {
            foreach( $others as $o ) {
                if( isset($o['id']) ) {
                    $ids[] = $o['id'];
                }
            }
        }
    }
}

// Get AED rows
$aed_rows = array();
if( !empty($ids) ) {
$aed_rows = db_query_rows("select * from aed_esi a where a.clientid in ( " . join( ",", $ids ) . " ) and a.deleted=0 and a.aedstolen = 0 and a.aedmissing = 0 order by serial");
}
?>
<?php if( !$nostyles ) { ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<form method='post'>
<html>
<head>
<title>Emergency Skills Inc. -- Optimize Your Response Time</title>

<META NAME="Keywords" CONTENT="CPR, CPR Classes, AED, AED training, AED Sales, Defib Sales, Defibrillator, Defibrillators, Defibrillation, Defib, Automatic External Defibrillator, Automatic External Defibrillator, Emergency Skills, Emergency Services, New York, New York City, NYC, NY, emergency, emergencies, urgent, 911, help, cardiac arrest, heart attack, stroke, public defibrillation, public access defibrillator, defib vendor, defib sales, defib training, Cardio Pulmonary Recussitation, Recussitate, Heimlich, Heimlich manuever, choking, pocket mask, ventilation, rescue breathing, manikin, manakin">

<META NAME="Description" CONTENT="EMERGENCY SKILLS, Inc., a corporate safety training company, provides AED sales and CPR training the New York City metro area.">

<link rel="stylesheet" href="https://<?php echo SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN; ?>/css/style.css">
<style type="text/css">

body,td,th {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 11px;
}
.style3 {font-size: 10px}

</style>
</head>
<body>
<?php } ?>
<center>
<table cellpadding="0" cellspacing="0" border="0" width="460">
    <tr>
        <td valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <center><img src='images/lightning.jpg'></center>
<?php 
$row = $mainrow;
?>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
    <tr bgcolor="#e1e1f6">
        <td valign="top" width="120"><span class="small"><strong><?php echo getSchoolStr( "School", isset($row["iscorp"]) ? $row["iscorp"] : 0 ); ?> Name:</strong></span></td>
        <td valign="top" bgcolor='#ffffff' width="330"><span class="small"><?php echo htmlspecialchars($row["companyname"]); ?></span></td>
    </tr>
<?php if( !isset($row["iscorp"]) || !$row["iscorp"] ) { ?>
    <tr bgcolor="#e1e1f6">
        <td valign="top"><span class="small"><strong>School Number:</strong></span></td>
        <td bgcolor='#ffffff' valign="top"><span class="small"><?php echo htmlspecialchars($row["schoolcode"]); ?></span></td>
    </tr>
<?php } ?>
    <tr bgcolor="#e1e1f6">
        <td valign="top"><span class="small"><strong>Address:</strong></span></td>
        <td bgcolor='#ffffff' valign="top"><span class="small"><?php echo htmlspecialchars($row["address"]); ?><br>
        <?php echo htmlspecialchars($row["city"]); ?> <?php echo htmlspecialchars($row["state"]); ?>, <?php echo htmlspecialchars($row["zip"]); ?></span></td>
    </tr>
</table>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="450" bgcolor="#999999">
    <tr bgcolor="#e1e1f6">
        <td valign="top"><span class="small"><strong>Specific Location</strong></span></td>
    </tr>
<?php 
if( is_array($aed_rows) ) {
    foreach( $aed_rows as $a ) {
        // Skip PSAL locations and missing/out of service AEDs
        $location = isset($a["location"]) ? strtolower($a["location"]) : '';
        $is_psal = strpos($location, "psal") !== false;
        $is_missing = isset($a["aedmissing"]) && $a["aedmissing"];
        $is_out_of_service = isset($a["outofservice"]) && $a["outofservice"];
        
        if( $is_psal || $is_missing || $is_out_of_service ) {
            continue;
        }
        
        $display_location = isset($a["location"]) ? htmlspecialchars($a["location"]) : '';
        $display_floor = isset($a["floor"]) ? htmlspecialchars($a["floor"]) : '';
        $separator = ($display_location && $display_floor) ? ", " : "";
?>
    <tr bgcolor="#ffffff">
        <td valign="top"><span class="copy"><?php echo $display_location . $separator . $display_floor; ?></span></td>
    </tr>
<?php 
    }
}
?>
</table>

</td></tr></table>
</td></tr></table>
</center>
</body>
</html>
<p class='breakhere'></p>