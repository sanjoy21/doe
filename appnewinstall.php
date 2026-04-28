<?php
require_once "mysql.php";

// --- PHP 8.2 COMPATIBILITY INITIALIZATION ---
// Initialize variables from Request/Post to prevent "Undefined Variable" warnings
$id = $_REQUEST['id'] ?? 0;
$savedata = $_POST['savedata'] ?? 0;
$acceptschool = $_POST['acceptschool'] ?? 0;
$acceptservicecall = $_POST['acceptservicecall'] ?? 0;
$noheader = $noheader ?? false; // Assumes this might be set by an include or earlier script
$nosave = $nosave ?? false;
$drillrow = $drillrow ?? ['id' => 0]; // Default array structure
$drillid = $drillid ?? 0;
$allaeds = $allaeds ?? []; // Initialize to avoid count() warning if undefined


$appuploadrow = getAppUploadRow($id);
$values = getAppUploadValues($id);

// Fix: Quoted array key and used curly braces for string interpolation
$uploader = db_query_first("select * from user where userid = '{$appuploadrow['uploader']}'");

$companyid = $values["id"];
$crow = getCompanyRow($companyid);
$serialrows = getAppServiceCallRows($id);

// Fix: Elvis operator for cleaner fallback logic
$servicecallid = $values["serviceid"] ?: $values["servideid"];

if (!empty($values["servicecallid"])) {
    $servicecallid = $values["servicecallid"];
}

$sc = [];
if ($servicecallid) {
    $sc = getServiceCallRow($servicecallid);
} else {
    print_r($values);
}

if ($savedata) {
    // Fix: String interpolation syntax
    db_query("update servicecall set appid = $id where servicecallid = $servicecallid");

    if ($acceptschool) {
        $uplvalues = array();
        $uplvalues["address"]       = $values["address"];
        $uplvalues["city"]          = $values["city"];
        $uplvalues["state"]         = $values["state"];
        $uplvalues["zip"]           = $values["zip"];
        $uplvalues["schoolphone"]   = $values["phone"];
        $uplvalues["principalname"] = $values["principal"];
        $uplvalues["principalemail"] = $values["principalemail"];
        $uplvalues["contactname"]   = $values["contact"];
        $uplvalues["contactemail"]  = $values["contactemail"];
        $uplvalues["contactphone"]  = $values["contactphone"];

        foreach ($uplvalues as $colname => $value) {
            // Sanitizing value inside loop before query
            $safeValue = mysqli_real_escape_string($link,$value);
            db_query("update company_esi set $colname = '$safeValue' where id = $companyid");
        }
    }

    $serialkeys = array();
    $serialkeys["adultpadA_newdate"] = "padaexpiration";
    $serialkeys["adultpadB_newdate"] = "padbexpiration";
    $serialkeys["pediatric_newdate"] = "pediatricpads";
    $serialkeys["has_frx_pediatric_key"] = "pediatrickey";
    $serialkeys["spare_battery_new_date"] = "sparedate";
    $serialkeys["main_battery_new_date"] = "batterydate";
    $serialkeys["comments"] = "aedcomments";
    $serialkeys["serial_number"] = "serial";

    if ($acceptservicecall) {
        $uplvalues = array();
        // Fix: Ensure we don't pass null to strtotime
        $dateInUpload = $appuploadrow["dateinupload"] ?? 'now';
        $uplvalues["servicecalldate"] = date("Y-m-d", strtotime($dateInUpload));
        $uplvalues["servicecalltime"] = date("H:i:s", strtotime($dateInUpload));
        $uplvalues["inspector"] = $uploader["first_name"] . " " . $uploader["last_name"];

        foreach ($uplvalues as $colname => $value) {
            $safeValue = mysqli_real_escape_string($link,$value);
            db_query("update servicecall set $colname = '$safeValue' where servicecallid = $servicecallid");
        }

        db_query("delete from aed_to_servicecall where servicecallid = '$servicecallid'");

        // Fix: Ensure $aeds is defined. Assuming it comes from the generic 'mysql.php' scope or previous logic. 
        // Based on code below, it seems $aeds matches $serialrows logic? 
        // I will re-fetch it here based on context if it wasn't defined globally, 
        // but assuming it exists as per your original script:
        $aeds_save_loop = getAppServiceCallRows($id); // Re-fetching to be safe

        foreach ($aeds_save_loop as $tmparow) {
            $aedvalues = getAppServiceCallDetailRows($tmparow['id']);

            // Fix: String interpolation
            $origarow = db_query_first("select * from aed_esi where aedid = '{$aedvalues['aedid']}' and deleted = 0");

            $s = !empty($aedvalues['serial_number']) ? $aedvalues['serial_number'] : $origarow["serial"];
            db_query("insert into aed_to_servicecall ( servicecallid, serial ) values ( '$servicecallid', '$s' )");

            $pada = $aedvalues["adultpadA_newdate"];

            if (!empty($aedvalues["installed_new_battery"])) {
                db_query("insert into aed_new_battery_dates ( aedid, dateadded, servicecallid ) values ( '{$aedvalues['aedid']}', '{$uplvalues['servicecalldate']}','$servicecallid' )");
            }

            $uplvalues_inner = array();
            foreach ($serialkeys as $key => $tablename) {
                if (!empty($aedvalues[$key])) {
                    $val = $aedvalues[$key];
                    if ($val == "yes") $val = 1;
                    if ($val == "no") $val = 0;
                    $uplvalues_inner[$tablename] = $val;
                }
            }
            foreach ($uplvalues_inner as $column => $value) {
                $safeValue = mysqli_real_escape_string($link,$value);
                db_query("update aed_esi set $column = '$safeValue' where aedid = '{$origarow['aedid']}'");
            }
        }
    }

    $err = "<font color='red'>Data saved. <a href='editservicecall.php?servicecallid=$servicecallid'>Click here</a> to view.</font><br><br>";
}
?>
<?php if (!$noheader) { ?>
    
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>

    <head>
        <title><?= date("Y-m-d", strtotime($appuploadrow["dateinupload"] ?? 'now')) ?>-SC<?= $servicecallid ?> (<?= $uploader["first_name"] . " " . $uploader["last_name"] ?>)</title>
        <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
        <form method='post'>

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

                /* Fixed CSS break below */
                .fontMed {
                    font-size: 18px;
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
                        <td valign="middle" style="color:#333333; font-size:12px;"><b>Emergency Skills, Inc. <?php if (empty($crow['iscorp'])) { ?>/ NYC Department of Education<?php } ?>
                                <br />AED <?= !empty($sc["newinstall"]) ? "New Install" : "Service Call" ?></b></td>
                        <td valign="middle" align="right" style="color:#333333; font-size:12px;">
                            <?php if (!empty($drillrow["id"])) {    ?>
                                <b>Drill/Inspection</b><br />#&nbsp;D<?= $drillid ?>
                            <?php } else { ?>
                                <b><?= !empty($sc["newinstall"]) ? "New Install" : "Service Call" ?></b><br />#&nbsp;<?= !empty($sc["newinstall"]) ? "NI" : "S" ?><?= $sc["servicecallid"] ?? '' ?> <br> <?php if (!$nosave) { ?><a href='editservicecall.php?servicecallid=<?= $servicecallid ?>' target=_blank>View</a><?php } ?>
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
                        <td valign="top" colspan="3"><span class="fontBig"><?= !empty($crow['iscorp']) ? $crow['displayname'] : $crow['companyname'] ?></span></td>
                    </tr>
                    <tr>
                        <td valign="top" style="width: 33%">
                            <b>ADDRESS &AMP; PHONE</b><br />
                            <?= $values["address"] ?> <?= getDifferent("address") ?><br />
                            <?= $values["city"] ?><?= getDifferent("city") ?> <?= $values["state"] ?><?= getDifferent("state") ?>, <?= $values["zip"] ?><?= getDifferent("zip") ?>
                            <?= $values["phone"] ?><?= getDifferent("phone", "schoolphone") ?>
                            <?php if (empty($crow['iscorp'])) {  ?><br /><br /> <b>SCHOOL CODE:</b> <?= $crow['schoolcode'] ?? '' ?><?php } ?>
                                <?php if (!$nosave) { ?>
                                    <br><input type='checkbox' name='acceptschool' value=1> Accept School Data?
                                <?php } ?>
                        </td>
                        <?php if (empty($crow["iscorp"])) { ?>

                            <td valign="top" style="width: 33%">
                                <b>PRINCIPAL</b><br />
                                <?= $values["principal"] ?><?= getDifferent("principal", "principalname") ?><br />
                                <?= $values["principalemail"] ?><?= getDifferent("principalemail") ?>
                            </td>
                        <?php } ?>
                        <td valign="top" style="width: 33%">
                            <b>AED CONTACT</b><br />
                            <?= $values["contact"] ?><?= getDifferent("contact", "contactname") ?><br />
                            <?= $values["contactemail"] ?><?= getDifferent("contactemail") ?><br>
                            <?= $values["contactphone"] ?><?= getDifferent("contactphone") ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top">

                <?php
                $aeds = getAppServiceCallRows($id);
                $cnt = 0;

                foreach ($aeds as $tmparow) {
                    $aedvalues = getAppServiceCallDetailRows($tmparow['id']);
                    // Fix: String interpolation
                    $origarow = db_query_first("select * from aed_esi where aedid = '{$aedvalues['aedid']}' and deleted = 0");

                    $cnt++;
                ?>
                    <div style="border: 1px solid black; margin-top: 5px; padding: 2px">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td valign="top" colspan="4" style="padding-top: 5px; border-bottom: 5px solid #83afcc;">
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td align="bottom"><span class="fontMed">Serial #: <?= $origarow["serial"] ?> <?= !empty($origarow["newinstall"]) ? "(N)" : "" ?></span></td>
                                            <td align="bottom"><input type="checkbox" <?= ($aedvalues['serial_number'] != $origarow["serial"]) ? "CHECKED" : "" ?>><b>New Serial #:&nbsp;&nbsp;<?= ($aedvalues['serial_number'] != $origarow["serial"]) ? $aedvalues['serial_number'] : "" ?></b></td>
                                            <td valign="bottom" style="text-align: right; padding-bottom:4px;"><b>Physical Location:</b><br /><?= $aedvalues['physicallocation'] ?> </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td valign="bottom">&nbsp;</td>
                                <td valign="bottom"><b>Exp. Date:</b></td>
                                <td valign="bottom"><b>New Date:</b></td>
                                <td valign="bottom"><b>Lot #: </b></td>
                                <td valign="bottom"><b>Spare Battery Install Before Date:</b></td>
                            </tr> <?php $red = ""; ?>
                            <tr>
                                <td class="rowA1" width='25%'><b>Adult Pads A:</b></td>
                                <td class="rowA2" width='15%'><?= $red . fixdatefordisplay($aedvalues["adultpadA_expirationdate"]) . "</font>" ?>&nbsp;</td>
                                <td class="rowA2" width='15%'>
                                    <font color='green'><b><?= fixdatefordisplay($aedvalues["adultpadA_newdate"]) ?></b></font>&nbsp;
                                </td>
                                <?php $red = (!empty($aedvalues["adultpadA_lot"]) && ($aedvalues["adultpadA_lot"] != $origarow["padalot"])) ? "<font color=green><b>" : "<font color='black'>";                                                                                                                                                                       ?>
                                <td class="rowA2" width='20%'><?= $red . (!empty($aedvalues["adultpadA_lot"]) ? $aedvalues["adultpadA_lot"] : $origarow["padalot"]) ?></font></b></td>
                                <?php $red = (!empty($aedvalues["spare_battery_new_date"]) && ($aedvalues["spare_battery_new_date"] != $origarow["sparedate"])) ? "<font color=red><b>" : "<font color='black'>";                                                                                                                                                                       ?>

                                <?php $redmain = (!empty($aedvalues["main_battery_new_date"]) && ($aedvalues["main_battery_new_date"] != $origarow["batterydate"])) ? "<font color=red><b>" : "<font color='black'>";                                                                                                                                                                       ?>

                                <td class="rowA2" rowspan='3' width='25%' valign='top'><?= $red ?><?= fixdatefordisplay(!empty($aedvalues['spare_battery_new_date']) ? $aedvalues["spare_battery_new_date"] : $origarow["sparedate"]) ?></font>&nbsp;<Br>
                                    Main: <?= $redmain ?><?= fixdatefordisplay(!empty($aedvalues['main_battery_new_date']) ? $aedvalues["main_battery_new_date"] : $origarow["batterydate"]) ?></font>&nbsp;<Br> </td>
                            </tr>
                            <tr> <?php $red = ""; ?>
                                <td class="rowB1"><b>Adult Pads B:</b></td>
                                <td class="rowB2"><?= $red . fixdatefordisplay($aedvalues["adultpadB_expirationdate"]) . "</font>" ?>&nbsp;</td>
                                <td class="rowB2">
                                    <font color='green'><b><?= $red . fixdatefordisplay($aedvalues["adultpadB_newdate"]) . "</font></b>" ?>&nbsp;
                                </td>
                                <?php $red = (!empty($aedvalues["adultpadB_lot"]) && ($aedvalues["adultpadB_lot"] != $origarow["padblot"])) ? "<font color=green><b>" : "<font color='black'>";                                                                                                    ?>
                                <td class="rowB2"><?= $red . (!empty($aedvalues['adultpadB_lot']) ? $aedvalues["adultpadB_lot"] : $origarow["padblot"]) ?></font>
                                </td>
                            </tr> <?php $red = ""; ?>
                            <tr>
                                <td class="rowB1"><b>Pediatric Pads:</b></td>
                                <td class="rowB2"><?= $red . fixdatefordisplay($aedvalues["pediatric_expirationdate"]) . "</font>" ?>&nbsp;</td>
                                <td class="rowB2">
                                    <font color='green'><b><?= fixdatefordisplay($aedvalues["pediatric_newdate"]) ?></b></font>&nbsp;
                                </td>
                                <?php $red = (!empty($aedvalues["pediatric_lot"]) && ($aedvalues["pediatric_lot"] != $origarow["pedpadlot"])) ? "<font color=green><b>" : "<font color='black'>";                                                                                                    ?>
                                <td class="rowB2"><?= $red . (!empty($aedvalues['pediatric_lot']) ? $aedvalues["pediatric_lot"] : $origarow["pedpadlot"]) . "</font></b>" ?></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="padding: 0px 0 0 0;"><b>Comments:</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="rowA1">
                                    <font color='blue'><?= $aedvalues["comments"] ?></font>&nbsp;<br><br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class='rowB1' colspan='4'>
                                    <nobr>
                                        <input <?= ($aedvalues["status_indicator"] == "yes") ? "CHECKED" : "" ?> type="checkbox">Status Indicator
                                        <input <?= ($aedvalues["unit_unavailable"] == "yes") ? "CHECKED" : "" ?> type="checkbox">Unit Unavailable
                                        <input <?= ($aedvalues["error_with_unit"] == "yes") ? "CHECKED" : "" ?> type="checkbox">There is an ERROR with this unit
                                        <font color='red'><?= $aedvalues["error_info"] ?></font>
                                        <br><br>
                                        <?= ($aedvalues["has_frx_pediatric_key"] == "<font color='red'>") ? "CHECKED" : "" ?> <input <?= ($aedvalues["has_frx_pediatric_key"] == "yes") ? "CHECKED" : "" ?> type="checkbox">Pediatric Key</font>
                                        <?= ($aedvalues["request_doe_send_pediatric_key"] == "<font color='red'>") ? "CHECKED" : "" ?><input <?= ($aedvalues["request_doe_send_pediatric_key"] == "yes") ? "CHECKED" : "" ?> type="checkbox">Request DOE Send Pediatric Key</font>
                                        <?= ($aedvalues["installed_new_battery"] == "<font color='red'>") ? "CHECKED" : "" ?><input <?= ($aedvalues["installed_new_battery"] == "yes") ? "CHECKED" : "" ?> type="checkbox">Installed New Battery</font>
                                        <?= ($aedvalues["psal_aed_out_with_coach"] ?? '' == "<font color='red'>") ? "CHECKED" : "" ?><input <?= ($aedvalues["PSAL_AED_out_with_coach"] ?? '' == "yes") ? "CHECKED" : "" ?> type="checkbox">PSAL Out with Coach</font>
                                        <?= ($aedvalues["fastresponsekit"] ?? '' == "<font color='red'>") ? "CHECKED" : "" ?><input <?= ($aedvalues["fastresponsekit"] ?? '' == "yes") ? "CHECKED" : "" ?> type="checkbox">Fast Response Kit</font><br>
                                        <?= ($aedvalues["pediatricmaskstatus"] ?? '' == "<font color='red'>") ? "CHECKED" : "" ?><input <?= ($aedvalues["pediatricmaskstatus"] ?? '' == "yes") ? "CHECKED" : "" ?> type="checkbox">Pediatric Mask</font>
                                        <?= ($aedvalues["request_doe_send_spare_battery"] == "<font color='red'>") ? "CHECKED" : "" ?><input <?= ($aedvalues["request_doe_send_spare_battery"] == "yes") ? "CHECKED" : "" ?> type="checkbox">Request DOE Send Spare Battery</font>
                                        <?= ($aedvalues["installed_new_battery"] == "<font color='red'>") ? "CHECKED" : "" ?><input <?= ($aedvalues["installed_new_battery"] == "yes") ? "CHECKED" : "" ?> type="checkbox">Installed New Battery</font>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php
                    if (($cnt == 2 || $cnt == 5 || $cnt == 8) && count($aeds) > $cnt)
                        echo "        <p class='breakhere'></p>";
                } ?>

            </td>
        </tr>
        <tr>
            <td valign="top" style="padding-top: 15px;">
                <table cellpadding="0" cellspacing="0" border="0" width="500px">
                    <tr>
                        <td class="rowAB1" style="background-color: #eff5f9; width: 170px;"><b>Total # of AEDs at <?= getSchoolStr("school") ?> :</b>
                        <td class="rowAB2"><?= count($allaeds) ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td class="rowAB1" style="background-color: #eff5f9; width: 170px;"><b>Total # inspected:</b>
                        <td class="rowAB2"><?= count($allaeds) ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php //echo( "values:" ); print_r( $values );
        ?>

        <tr>
            <td valign="top" style="padding-top: 20px;">
                <table cellpadding="0" cellspacing="0" border="1" width="600" bgcolor="#999999">

                    <tr bgcolor="#ffffff">
                        <td valign="top">School Rep Name:</td>
                        <td width="200" colspan='6' valign="top"><?= $appuploadrow["name"] ?>&nbsp;</td>
                    </tr>
                    <tr bgcolor="#ffffff">
                        <td valign="top">School Rep Signature:</td>
                        <td width="200" colspan='6' valign="top"><img src='signatures/<?= $values["media_file"] ?>' style="width:450px">&nbsp;</td>
                    </tr>
                    <tr bgcolor="#ffffff">
                        <td valign="top">ESI Rep Name:</td>
                        <td width="200" colspan='6' valign="top"><?= $appuploadrow["esi_repname"] ?>&nbsp;</td>
                    </tr>
                    <tr bgcolor="#ffffff">
                        <td valign="top">ESI Rep Signature:</td>
                        <td width="200" colspan='6' valign="top"><img src='signatures/<?= $values["media_file_esr"] ?>' style="width:450px">&nbsp;</td>
                    </tr>

                </table>
            </td>
        </tr>
        <?php if (!$nosave) { ?>
            <tr>
                <td><br><input type='checkbox' name='acceptservicecall' value=1> Accept Service Call Data?
                    <br><input type='submit' name='savedata' value='Save Data'>
                </td>
            </tr>
        <?php } ?>
    </table>

    </body>

    </html>