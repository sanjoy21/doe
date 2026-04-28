<?php
include "mysql.php";

// --- PHP 8.2 Initialization Block ---
// Initialize variables from Request to prevent "Undefined Variable" errors
$moveaeds = $_POST['moveaeds'] ?? 0;
$update = $_POST['update'] ?? 0;
$delete = $_POST['delete'] ?? 0;
$newschoolid = $_POST['newschoolid'] ?? 0;
$serials = $_POST['serials'] ?? []; // Array expected
$newinstallationdate = $_POST['newinstallationdate'] ?? '';
$newinstallationtime = $_POST['newinstallationtime'] ?? '';
$nextnewinstallationdate = $_POST['nextnewinstallationdate'] ?? '';
$newinstallationid = $_REQUEST['newinstallationid'] ?? 0;
$inspector = $_POST['inspector'] ?? '';
$completed = $_POST['completed'] ?? 0;
$newinstall = $_POST['newinstall'] ?? 0;
$actionneeded = $_POST['actionneeded'] ?? 0;
$invoiced = $_POST['invoiced'] ?? 0;
$invoiceno = $_POST['invoiceno'] ?? '';
$batteryrequest = $_POST['batteryrequest'] ?? '';
$callnumber = $_POST['callnumber'] ?? '';
$comments = $_POST['comments'] ?? '';
$reason = $_POST['reason'] ?? '';
$id = $_REQUEST['id'] ?? 0;
$sherrycomments = $_POST['sherrycomments'] ?? '';
$otherschools = $_POST['otherschools'] ?? []; // Array expected
$notifysherry = $_POST['notifysherry'] ?? 0;
$notifydoe = $_POST['notifydoe'] ?? 0;
$notifyemily = $_POST['notifyemily'] ?? 0;
$redirect = $_REQUEST['redirect'] ?? '';
$session_userid = $_SESSION['userid'] ?? ''; // Assuming session variable
$session_iscorp = $_SESSION['iscorp'] ?? 0;  // Assuming session variable
$specialadmin = $specialadmin ?? false; // Likely defined in mysql.php or included auth
$readonly = $readonly ?? false;
// ------------------------------------

if ($moveaeds) {
    if ($newschoolid) {
        if (is_array($serials)) {
            foreach ($serials as $s) {
                // Fix: Quoted keys and string interpolation
                $ar = db_query_first("select aedid, clientid from aed_esi where serial = '$s'");
                if ($ar) {
                    $schoolid = $ar['clientid'];
                    $s_id = $ar['aedid']; // Renamed variable to avoid conflict with loop var
                    db_query("insert into oldaedschools ( aedid, clientid, movedate, movedby ) values ( '$s_id', '$schoolid', Now(), '$session_userid') ");
                    db_query("update aed_esi set clientid = $newschoolid where aedid = $s_id");
                }
            }
        }
    }
}

if ($update) {
    $newinstallationdate = fixdate($newinstallationdate);

    $thecid = 0;
    $oldscrow = [];

    if ($newinstallationid) {
        $oldscrow = db_query_first("select * from newinstallation where newinstallationid = '$newinstallationid'");
        $thecid = $oldscrow['companyid'];
        db_query("update newinstallation set newinstallationdate = '$newinstallationdate', newinstallationtime = '$newinstallationtime', nextnewinstallationdate = '$nextnewinstallationdate', inspector = '$inspector', completed = '$completed', newinstall = '$newinstall', actionneeded = '$actionneeded', invoiced = '$invoiced', invoiceno = '$invoiceno', batteryrequest = '" . trim($batteryrequest) . "', callnumber = '$callnumber', comments = '$comments', reason = '$reason' where newinstallationid = $newinstallationid ");
    } else {
        $thecid = $id;
        $newinstallationid = db_query_insert_id("insert into newinstallation ( companyid, newinstallationdate, newinstallationtime, nextnewinstallationdate, comments, reason, callnumber, invoiced, invoiceno, completed, newinstall, batteryrequest ) values ('$id','$newinstallationdate','$newinstallationtime','$nextnewinstallationdate','$comments','$reason','$callnumber','$invoiced','$invoiceno','$completed','$newinstall','$batteryrequest') ");
    }

    if ($session_userid == "sarahg@emergencyskills.com") {
        db_query("update newinstallation set sherrycomments = '$sherrycomments' where newinstallationid = $newinstallationid");
    }


    db_query("delete from aed_to_newinstallation where newinstallationid = '$newinstallationid'");

    // $o is undefined here in original code, assuming empty or logic flaw in legacy script
    $drill_company_id = $oldscrow['companyid'] ?? 0;
    $drill_o = $o ?? 0;
    db_query("insert into drill_to_companyid ( drillid, companyid ) values ( '$drill_company_id', '$drill_o' )");

    if (is_array($serials)) {
        foreach ($serials as $s) {
            db_query("insert into aed_to_newinstallation (newinstallationid, serial) values ( $newinstallationid, '$s' ) ");
        }
    }

    db_query("delete from newinstallation_to_companyid where newinstallationid = '$newinstallationid'");
    db_query("insert into newinstallation_to_companyid ( newinstallationid, companyid ) values ( '$newinstallationid', '$thecid' )");

    if (is_array($otherschools) && count($otherschools)) {
        foreach ($otherschools as $o) {
            db_query("insert into newinstallation_to_companyid ( newinstallationid, companyid ) values ( '$newinstallationid', '$o' )");
        }
    }

    if ($notifysherry) {
        // Assuming URL_WITHOUT_SUBDOMAIN is a constant defined in mysql.php
        sendMail("sarahg@emergencyskills.com", "Action Needed", "New Installation $newinstallationid needs action.\n https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/editnewinstallation.php?newinstallationid=$newinstallationid\n", "info@emergencyskills.com");
        if ($session_iscorp) {
            sendMail("michael@emergencyskills.com", "Action Needed", "New Installation $newinstallationid needs action.\n https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/editnewinstallation.php?newinstallationid=$newinstallationid\n", "info@emergencyskills.com");
        }
    }


    if ($notifydoe) {
        $newinstallation_row = db_query_first("select * from newinstallation where newinstallationid=$newinstallationid");
        $crow = getCompanyRow($id);

        $initial = "The following school has requested:\n" . $newinstallation_row["batteryrequest"] . "\n";
        $battreq = 1;

        db_query("update newinstallation set lastnotified = now() where newinstallationid = $newinstallationid");
        // Fix: Array interpolation in double quotes
        mail("hthomps@schools.nyc.gov, cmcgee3@schools.nyc.gov", "AED/Accessory Request", "$initial  \n {$newinstallation_row['newinstallationdate']} \n {$crow['companyname']} \n {$crow['schoolcode']}\n {$crow['address']} {$crow['city']}, {$crow['zip']}\n Principal Name: {$crow['principalname']} \n Principal Email: {$crow['principalemail']} \n School Phone: {$crow['schoolphone']} \n\nEmergency Skills, Inc.", "From:sarahg@emergencyskills.com\nCc:sarahg@emergencyskills.com");
    }

    if ($notifyemily) {
        $newinstallation_row = db_query_first("select * from newinstallation where newinstallationid=$newinstallationid");
        $crow = getCompanyRow($id);

        $initial = "The following company has requested:\n" . $newinstallation_row["batteryrequest"] . "\n";
        $battreq = 1;

        db_query("update newinstallation set lastnotified = now() where newinstallationid = $newinstallationid");
        mail("michael@emergencyskills.com", "AED/Accessory Request", "$initial  \n {$newinstallation_row['newinstallationdate']} \n {$crow['companyname']}\n {$crow['address']} {$crow['city']}, {$crow['zip']} \n \n\nEmergency Skills, Inc.", "From:info@emergencyskills.com\nCc:info@emergencyskills.com");
    }



    Header("location: $redirect ");
    exit;
}



if ($delete) {
    db_query("delete from newinstallation where newinstallationid = $newinstallationid ");
    Header("location: $redirect ");
    exit;
}


//get info for the form
$newinstallation_row = array();
if ($newinstallationid) {
    $newinstallation_row = db_query_first("select * from newinstallation where newinstallationid = $newinstallationid");
    if ($newinstallation_row) {
        $id = $newinstallation_row["companyid"];
    }
}

// Provide defaults for row to prevent warnings in HTML
if (!$newinstallation_row) {
    $newinstallation_row = array(
        'newinstallationdate' => '',
        'newinstallationtime' => '',
        'callnumber' => '',
        'newinstallationid' => '',
        'inspector' => '',
        'reason' => '',
        'batteryrequest' => '',
        'companyid' => '',
        'newinstall' => 0,
        'completed' => 0,
        'actionneeded' => 0,
        'lastnotified' => '',
        'invoiced' => 0,
        'invoiceno' => '',
        'comments' => '',
        'sherrycomments' => ''
    );
}
$company_row = getCompanyRow($id);
?>
<?php
$noleftnav = 1;
$overridecname = "newschoolid";

include "ssi/top.php";
include "getschooldropdown.php";

?>
</head>

<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>

<script language="JavaScript">
    function validRequired(formField, fieldLabel) {
        var result = true;

        if (formField.value == "") {
            alert('Please enter a value for the "' + fieldLabel + '" field.');
            formField.focus();
            result = false;
        }

        return result;
    }

    function allDigits(str) {
        return inValidCharSet(str, "0123456789");
    }

    function inValidCharSet(str, charset) {
        var result = true;

        for (var i = 0; i < str.length; i++)
            if (charset.indexOf(str.substr(i, 1)) < 0) {
                result = false;
                break;
            }

        return result;
    }

    function isValidShortDate(formField, fieldLabel, required) {
        if (required && (formField.value.length > 7)) {
            alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel + '" field.');
            formField.focus();
            return false;
        }
        var result = true;
        var formValue = formField.value;

        if (required && !validRequired(formField, fieldLabel))
            result = false;

        if (result && (formField.value.length > 0)) {
            var elems = formValue.split("/");

            result = (elems.length == 2); // should be two components
            var expired = false;

            if (result) {
                var month = parseInt(elems[0], 10);
                var year = parseInt(elems[1], 10);

                if (elems[1].length == 2)
                    year += 2000;

                var now = new Date();

                var nowMonth = now.getMonth() + 1;
                var nowYear = now.getFullYear();



                result = allDigits(elems[0]) && (month > 0) && (month < 13) &&
                    allDigits(elems[1]) && ((elems[1].length == 2) || (elems[1].length == 4));
            }

            if (!result) {
                alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel + '" field.');
                formField.focus();
            }
        }
        return result;
    }
</script>

<?php
if (!$redirect)
    $redirect = "/viewcompany.php?id=$id";
?>

<form onsubmit="return validateUSPersonalInfo(this)" method="post">
    <input type="hidden" name="redirect" value="<?= $redirect ?>">
    <input type="hidden" name="update" value="true">
    <input type="hidden" name="newinstallationid" value="<?= $newinstallationid ?>">
    <input type="hidden" name="id" value="<?= $id ?>">
    <?php if ($specialadmin) { ?>
        <table cellpadding="5" cellspacing="1" border="0" width="100%">
            <tr>
                <td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="schools.php">&laquo; Back to Admin Main</a></strong></span></td>
            </tr>
        </table>
    <?php } ?>
    <strong>THIS NEW INSTALLATION IS FOR:</strong><br>
    <?php echo  "<a href='viewcompany.php?id=" . $id . "'>" . $company_row['companyname'] . "</a><br>" . $company_row['address'] . "<br>" . $company_row['floor'] . "<br>" . $company_row['city'] . ", " . $company_row['state'] . " " . $company_row['zip']; ?>
    <br><br>
    <table cellpadding="5" cellspacing="1" border="0" width="100%">
        <tr>
            <td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><Strong>New Installation Information</strong></span></td>
        </tr>


        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>New Installation Date*:</strong> <input type="text" size="12" VALUE="<?= $newinstallation_row['newinstallationdate'] ?>" maxlength="50" name="newinstallationdate" style="font-size: 10px;  font-family: verdana;"> <strong>Time:</strong> <input type="text" size="12" VALUE="<?= $newinstallation_row['newinstallationtime'] ?>" maxlength="50" name="newinstallationtime" style="font-size: 10px;  font-family: verdana;"></span>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Call Number:</strong><br><input type="text" size="30" VALUE="<?= $newinstallation_row['callnumber'] ? $newinstallation_row['callnumber'] : $newinstallation_row['newinstallationid'] ?>" maxlength="50" name="callnumber" style="font-size: 10px;  font-family: verdana;"></span>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>ESI Staff/Inspector:</strong><br><input type="text" size="50" VALUE="<?= $newinstallation_row['inspector'] ?>" name="inspector" style="font-size: 10px;  font-family: verdana;"></span>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Reason:</strong><br><textarea name="reason" row="5" cols="50" style="font-size: 10px;  font-family: verdana;"><?= $newinstallation_row['reason'] ?></textarea></span>
            </td>
        </tr>
        <?php
$myaeds = getAedsForNewinstallation($newinstallationid);
        //print_r( $myaeds );
        $aed_rows = getAedRows($id, false, !empty($company_row["iscorp"]) ? "" : ($company_row['campusid'] ?? ''));
        ?>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>AED:</strong><br><select style="font-size: 10px;  font-family: verdana;" name="serials[]" multiple class=copy>
                        <option value=''>Please Choose</option>
                        <?php
                        $already = array();
                        if (is_array($myaeds)) {
                            foreach ($myaeds as $ser => $throwaway) {
                                $already[$ser] = 1;
                                printOption($ser, $ser, $ser);
                            }
                        }

                        // don't auto select any of these
                        if (is_array($aed_rows)) {
                            foreach ($aed_rows as $a) {
                                if (!empty($already[$a['serial']]))
                                    continue;
                                printOption($a['serial'], $a['serial']);
                            }
                        }

                        if (is_array($myaeds)) {
                            foreach ($myaeds as $s) {
                                $found = false;
                                if (is_array($aed_rows)) {
                                    foreach ($aed_rows as $a) {
                                        if ($a['serial'] == $s)
                                            $found = true;
                                    }
                                }
                                if (!$found)
                                    printOption($s, $s, $myaeds[$s]);
                            }
                        }

                        ?>
                    </select></span>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF" class='copy'>
                <b>AED/Accessory Request:</b> <input type='checkbox' <?= $newinstallation_row["batteryrequest"] ? "CHECKED" : "" ?> name='batteryreq' value='1'> <input type='text' size='45' name='batteryrequest' value="<?= $newinstallation_row['batteryrequest'] ?>" style="font-size: 10px;  font-family: verdana;">&nbsp;&nbsp;&nbsp;
            </td>
        </tr>
        <?php
        $scho = getSchoolsInCampus($company_row["campusid"] ?? '', $company_row["id"] ?? 0);
        if (is_array($scho) && count($scho)) {
        ?>
            <tr>
                <td valign="top" bgcolor="#E2DFDF">
                    <span class="copy"><strong>Other participating <?= getSchoolStr("Schools") ?>:</strong><br>
                        <?php
                        $otherschools = db_query_array("select companyid from newinstallation_to_companyid where newinstallationid = '$newinstallationid'", "companyid",  "companyid");
                        foreach ($scho as $s) {
                            if ($s['id'] != $newinstallation_row['companyid']) {
                                $already = !empty($otherschools[$s['id']]) ? "CHECKED" : "";
                                echo ("<input type='checkbox' name='otherschools[]' value='{$s['id']}' $already > <a href='viewcompany.php?id={$s['id']}'>{$s['companyname']}</a><br>");
                            }
                        }
                        ?>
                    </span>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td class=copy bgcolor="#E2DFDF">
                <strong>New Install:</strong> <input type='checkbox' class='copy' name='newinstall' value="1" <?= $newinstallation_row["newinstall"] ? "checked" : "" ?>><br>
                <strong>Completed:</strong> <input type='checkbox' class='copy' name='completed' value="1" <?= $newinstallation_row["completed"] ? "checked" : "" ?>><br>
                <strong>Action Needed:</strong> <input type='checkbox' class='copy' name='actionneeded' value="1" <?= $newinstallation_row["actionneeded"] ? "checked" : "" ?>> <strong>Notify Sarah:</strong> <input type='checkbox' name='notifysherry' value='1'><br>
                <?php if (empty($company_row["iscorp"])) { ?>
                    <strong>Notify DOE:</strong>
                    <nobr><input type='checkbox' name='notifydoe' value='1'> (Last Notified: <?= $newinstallation_row["lastnotified"] ? $newinstallation_row["lastnotified"] : "N/A" ?>)<br>
                    <?php } else { ?>
                        <strong>Notify Emily:</strong>
                        <nobr><input type='checkbox' name='notifyemily' value='1'> (Last Notified: <?= $newinstallation_row["lastnotified"] ? $newinstallation_row["lastnotified"] : "N/A" ?>)<br>
                        <?php } ?>
                        <strong>Invoiced:</strong> <input type='checkbox' class='copy' name='invoiced' value="1" <?= $newinstallation_row["invoiced"] ? "checked" : "" ?>><br>


            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Invoice No:</strong><br><input type="text" size="10" VALUE="<?= $newinstallation_row['invoiceno'] ? $newinstallation_row['invoiceno'] : $newinstallation_row['invoiceno'] ?>" maxlength="10" name="invoiceno" style="font-size: 10px;  font-family: verdana;"></span>
            </td>
        </tr>

        <tr>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy"><strong>Comments:</strong><br><textarea rows="5" cols='50' name="comments" style="font-size: 10px;  font-family: verdana;"><?= $newinstallation_row['comments'] ?></textarea></span>
            </td>
        </tr>

        <?php if ($session_userid == "sarahg@emergencyskills.com") { ?>
            <tr>
                <td valign="top" bgcolor="#E2DFDF">
                    <span class="copy"><strong>Comments For Sarah:</strong><br><textarea rows="5" cols='50' name="sherrycomments" style="font-size: 10px;  font-family: verdana;"><?= $newinstallation_row['sherrycomments'] ?></textarea></span>
                </td>
            </tr>
        <?php } ?>


        <tr>
            <td valign="top" bgcolor="#FFFFFF" colspan="2">
                <br>
                <?php if (!$readonly) { ?>
                    <div align="center">
                        <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php if ($newinstallationid) { ?>
                            <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                        <?php } ?>
                    </div>
                <?php } else if ($newinstallationid && !$newinstallation_row["newinstallationdate"]) { ?>
                    <div align="center">
                        <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                    </div>
                <?php } ?>
                <?php if ($specialadmin) {
                ?><br><br>
                    <div align="center">
                        Set New <?= getSchoolStr("School") ?>:
                        <select id=borough name="borough" onChange="changeBorough();" style="font-size: 10px;  font-family: verdana;">
                            <option value=""></option>
                            <?php if ($session_iscorp) { ?>
                                <option value="other">Other</option>
                            <?php  } ?>

                            <option value="Bronx">The Bronx</option>
                            <option value="Brooklyn">Brooklyn</option>
                            <option value="Manhattan">Manhattan</option>
                            <option value="Queens">Queens</option>
                            <option value="Staten Island">Staten Island</option>
                        </select>

                        <span class='copy'><?= getSchoolStr("School") ?> Name: </span> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='changeBorough()'> <input type='button' value='Search' class=copy onClick='changeBorough()'>
                        <span id='school_select'>

                        </span>
                        <input type='submit' name='moveaeds' value='Move Selected AEDs to This School'>

                    <?php } ?>
            </td>
        </tr>
    </table>
    <br><br>
    <br><br>
    <?php include "ssi/footer.php"; ?>
    </span>
    </td>
    <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
    </tr>
    </table>
    <br><br>
    </div>
</form>
</body>

</html>