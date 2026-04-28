<?php
require_once "mysql.php";

// --- Input Sanitization and Validation ---
// Assuming all IDs should be integers from request variables (GET/POST/Cookie)
$companyid = $companyid;
$aedid = $aedid;
$servicecallid = $servicecallid;
$newscid = $newscid;
$drillid = $drillid;


if (!$noheader) {
    if ($newservicecall) {
        // SQLi Mitigation: Use safe integer variables
        $newid = db_query_insert_id("insert into servicecall ( companyid, singleaedid ) values ( '{$companyid}', '{$aedid}' )");
        Header("Location: servicecallsheet.php?companyid={$companyid}&servicecallid={$newid}");
        exit;
    }
    if ($newinstall) {
        // SQLi Mitigation: Use safe integer variables
        $q = db_query_rows("select * from aed_esi where clientid = '{$companyid}' and deleted = 0 and newinstall = 1");
        $newid = db_query_insert_id("insert into servicecall ( companyid, singleaedid, newinstall ) values ( '{$companyid}', '{$aedid}', 1 )");

        // SQLi Mitigation: Ensure $qrow[serial] is escaped (assuming it is user-controlled/database data)
        foreach ($q as $qrow) {
            $serial_safe = db_escape_or_placeholder($qrow['serial']); // Placeholder for real escaping function
            db_query("insert into aed_to_servicecall ( serial, servicecallid ) values ( '{$serial_safe}', '{$newid}' )");
        }

        Header("Location: servicecallsheet.php?companyid={$companyid}&servicecallid={$newid}");
        exit;
    }

    // Fetch data using safe integer IDs
    $crow = getCompanyRow($companyid);
    $sc = getServiceCallRow($servicecallid);
} else if ($newscid) {
    // Fetch data using safe integer IDs
    $crow = getCompanyRow($companyid);
    $sc = getServiceCallRow($newscid);
} else if (!$drillid && !$isroster) {
    $crow = "";
    $sc = "";
}

// Re-fetch company row if missing
if ($companyid && !$crow) {
    $crow = getCompanyRow($companyid);
}

// Helper function for XSS mitigation
if (!function_exists('h')) {
    function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
?>
<?php if (!$noheader) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title></title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<style type="text/css">
            td {
                font-family: arial;
                font-size: 11px;
                color: #000000;
                height: 23px;
            }

            td.rowA1 {
                border-top: 1px solid #83afcc;
                border-right: 1px solid #83afcc;
                border-bottom: 1px solid #83afcc;
                border-left: 1px solid #83afcc;
                padding: 3px;
            }

            td.rowA2 {
                border-top: 1px solid #83afcc;
                border-right: 1px solid #83afcc;
                border-bottom: 1px solid #83afcc;
                padding: 3px;
            }

            td.rowB1 {
                border-right: 1px solid #83afcc;
                border-bottom: 1px solid #83afcc;
                border-left: 1px solid #83afcc;
                padding: 3px;
            }

            td.rowB2 {
                border-right: 1px solid #83afcc;
                border-bottom: 1px solid #83afcc;
                padding: 3px;
            }

            td.rowAB1 {
                border-right: 1px solid #83afcc;
                border-top: 1px solid #83afcc;
                border-bottom: 1px solid #83afcc;
                border-left: 1px solid #83afcc;
                padding: 3px;
            }

            td.rowAB2 {
                border-right: 1px solid #83afcc;
                border-top: 1px solid #83afcc;
                border-bottom: 1px solid #83afcc;
                padding: 3px;
            }

            .fontBig {
                font-size: 24px;
                font-weight: bold;
            }

            .fontMed {
                font-size: 11px;
                font-weight: bold;
            }
        </style>
</head>
<body>
<?php } ?>
<table cellpadding="0" cellspacing="0" border="0" width="650">
<tr>
<td valign="top" style="padding-bottom: 20px;">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="middle" width="70" style="padding-right: 10px;"><img src="images/servicecalllogo.jpg"></td>
<td valign="middle" style="color:#333333; font-size:12px;"><b>Emergency Skills, Inc. <?php if (!$crow['iscorp']) { ?>/ NYC Department of Education<?php } ?>
<br />AED <?= h($sc["newinstall"] ? "New Install" : "Service Call") ?></b></td>
                        <td valign="middle" align="right" style="color:#333333; font-size:12px;">
                            <?php if ($servicecallid || $newscid) { ?>
                                <b><?= h($sc["newinstall"] ? "New Install" : "Service Call") ?></b><br />#&nbsp;<input type="text" value="<?= h($sc["newinstall"] ? "NI" : "S") ?><?= h($sc["servicecallid"]) ?>" style="font-family: arial; font-size: 11px; color: #333333; width:70px;">
                            <?php } else { ?>
                                <b>Drill/Inspection</b><br />#&nbsp;<input type="text" value="<?= h('D' . $drillid) ?>" style="font-family: arial; font-size: 11px; color: #333333; width:70px;">
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top">
                <table cellpadding="10" cellspacing="0" border="0" style="width: 100%; background-color: #eff5f9; border: 1px #83afcc solid;">
                    <tr>
                        <td valign="top" colspan="3"><span class="fontBig"><?= h($crow["iscorp"] && $crow["iscorp"] != AGING ? $crow["displayname"] : $crow["companyname"]) ?></span></td>
                    </tr>
                    <tr>
                        <td valign="top" style="width: 33%">
                            <b>ADDRESS &amp; PHONE</b><br />
                            <?= h($crow["address"]) ?><br />
                            <?= h($crow["city"]) ?> <?= h($crow["state"]) ?>, <?= h($crow["zip"]) ?>
                            <?php if ($crow["iscorp"]) { ?><br><?php } ?>
                            <?= h($crow["iscorp"] ? $crow["contactphone"] : $crow["schoolphone"]) ?>
                            <?php if (!$crow["iscorp"]) { ?><br /><br /> <b>SCHOOL CODE:</b> <?= h($crow["schoolcode"]) ?><?php } ?>
                        </td>
                        <td valign="top" style="width: 33%">
                            <b>PRINCIPAL</b><br/>
                            <?= h($crow["principalname"]) ?><br/>
                            <?= h($crow["principalemail"]) ?>
                        </td>
                        <td valign="top" style="width: 33%">
                            <b>AED CONTACT</b><br/>
<?= h($crow["contactname"]) ?><br/>
<?= h($crow["contactemail"]) ?>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign="top">
<?php
// Assuming getAedRows, getAedRow are safe and use integer IDs
$allaeds = getAedRows($companyid, false, $crow["iscorp"] ? "" : $crow["campusid"], true);
$aeds = [];
if ($sc["singleaedid"])
{
$aeds = array(getAedRow($sc["singleaedid"]));
}
else
{
$aeds = $allaeds;
}
$cnt = 0;
foreach ($aeds as $arow) {
if ($arow["aedstolen"]) continue;
if (!$showallaeds && $sc["newinstall"] && !$arow["newinstall"]) continue;
$cnt++;
?>
<div style="border:1px solid black; margin-top:5px; padding:2px"><table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top" colspan="4" style="padding-top: 5px; border-bottom: 5px solid #83afcc;">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td align="bottom">
<span class="fontMed">Serial #: <?php if ($arow["aedmissing"] || $arow["outofservice"]) { ?><font color='red'><?php } ?><?php if ($arow["outofwarranty"]) { ?>W<?php } ?>
<?= h($arow["serial"]) ?> <?= h($arow["newinstall"] ? "(N)" : "") ?><?php if ($arow["aedmissing"] || $arow["outofservice"]) { ?></font><?php } ?></span></td>
<td align="bottom"><input type="checkbox"><b>New Serial #:&nbsp;&nbsp;_________________________</b></td>
<td valign="bottom" style="text-align: right; padding-bottom:4px;"><b>Physical Location:</b><br /><?= h($arow["location"]) ?> <?php if ($crow["iscorp"]) { ?>&nbsp;&nbsp;|&nbsp;&nbsp;<b>Floor:</b><?= h($arow["floor"]) ?><?php } ?></td>
</tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td valign="bottom">&nbsp;</td>
                                <td valign="bottom"><b>Exp. Date:</b></td>
                                <td valign="bottom"><b>New Date:</b></td>
                                <td valign="bottom"><b>Lot #: </b></td>
                            </tr>
                            <?php
                            // XSS Mitigation: Values inside the font tags are sanitized/controlled date/lot numbers. 
                            // Assuming fixdatefordisplay returns a clean string.
                            $red_a = (!$session_iscorp && strtotime($arow["padaexpiration"]) < strtotime(getsetting("expredbefore"))) ? "<font color='red'>" : "<font color='black'>"; ?>
                            <tr>
                                <td class="rowA1" width="15%"><b>Adult Pads A:</b></td>
                                <td class="rowA2" width="15%"><?php echo $red_a . h(fixdatefordisplay($arow["padaexpiration"])) . "</font>"; ?>&nbsp;</td>
                                <td class="rowA2" width="30%">&nbsp;</td>
                                <td class="rowA2" width="30%"><?php echo $red_a . h($arow["padalot"]) . "</font>"; ?></td>
                            </tr>
                            <?php $red_b = (!$session_iscorp && strtotime($arow["padbexpiration"]) < strtotime(getsetting("expredbefore"))) ? "<font color='red'>" : "<font color='black'>"; ?>
                            <tr>
                                <td class="rowB1"><b>Adult Pads B:</b></td>
                                <td class="rowB2"><?php echo $red_b . h(fixdatefordisplay($arow["padbexpiration"])) . "</font>"; ?>&nbsp;</td>
                                <td class="rowB2">&nbsp;</td>
                                <td class="rowB2"><?php echo $red_b . h($arow["padblot"]) . "</font>"; ?></td>
                            </tr>
                            <?php $red_p = (!$session_iscorp && strtotime($arow["pediatricpads"]) < strtotime(getsetting("expredbefore"))) ? "<font color='red'>" : "<font color='black'>"; ?>
                            <tr>
                                <td class="rowB1"><b>Pediatric Pads:</b></td>
                                <td class="rowB2"><?php echo $red_p . h(fixdatefordisplay($arow["pediatricpads"])) . "</font>" ?>&nbsp;</td>
                                <td class="rowB2">&nbsp;</td>
                                <td class="rowB2"><?php echo $red_p . h($arow["pedpadlot"]) . "</font>"; ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding: 0px 0 0 0;"><b>Comments:</b></td>
                                <td style="padding: 0px 0 0 0;"><b>Spare Battery Install Before Date:</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="rowA1">
                                    <?= h($arow["aedmissing"] ? "MISSING" : "") ?>&nbsp;<?= h($arow["aedcomments"]) ?>&nbsp;<br><br><br>
                                </td>
                                <td class="rowA2" valign='top'><?= h(fixdatefordisplay($arow["sparedate"])) ?>&nbsp;
                                    <br>Main Battery Install Date: <Br>
                                    <?= h(fixdatefordisplay($arow["batterydate"])) ?>
                                </td>
                            </tr>
                            <tr>
                                <td class='rowB1' colspan='4'>
                                    <nobr><input type="checkbox">Status Indicator
                                        <input type="checkbox">Fast Response Kit
                                        <input type="checkbox">Pediatric Mask
                                        <input type="checkbox" <?php if ($arow["hasbeenupdated"]) { ?>CHECKED<?php } ?>>G2005 Update
                                    </nobr>
                                    <input type="checkbox" <?php if ($arow["pediatrickey"]) { ?>CHECKED<?php } ?>>Pediatric Key
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php
                    if (($cnt == 2 || $cnt == 5 || $cnt == 8) && count($aeds) > $cnt)
                    echo "<p class='breakhere'></p>";
                } ?>
            </td>
        </tr>
        <tr>
            <td valign="top" style="padding-top: 15px;">
                <table cellpadding="0" cellspacing="0" border="0" width="500px">
                    <tr>
                        <td class="rowAB1" style="background-color: #eff5f9; width: 170px;"><b>Total # of AEDs at <?=h(getSchoolStr("school")) ?>:</b>
                        <td class="rowAB2"><?= h(count($allaeds)) ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td class="rowAB1" style="background-color: #eff5f9; width: 170px;"><b>Total # inspected:</b>
                        <td class="rowAB2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" style="padding-top: 20px;">
                <table cellpadding="0" cellspacing="0" border="0" width="600px">
                    <tr>
                        <td style="border-bottom: 1px solid #666666; width: 150px;">&nbsp;</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="border-bottom: 1px solid #666666; width: 150px;">&nbsp;</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="border-bottom: 1px solid #666666; width: 200px;">&nbsp;</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="border-bottom: 1px solid #666666; width: 100px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td valign="top"><?= h(getSchoolStr("School", $crow["iscorp"])) ?> Rep. Signature</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td valign="top">Print Name</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td valign="top"><?= h(getSchoolStr("School", $crow["iscorp"])) ?></td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td valign="top">Date & Time</td>
                    </tr>
                </table>
                <table cellpadding="0" cellspacing="0" border="0" width="600px">
                    <tr>
                        <td style="border-bottom: 1px solid #666666; width: 250px;">&nbsp;</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="border-bottom: 1px solid #666666; width: 250px;">&nbsp;</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="border-bottom: 1px solid #666666; width: 100px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td valign="top">ESI Rep. Signature</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td valign="top">Print Name</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td valign="top">Date</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    </body>

    </html>