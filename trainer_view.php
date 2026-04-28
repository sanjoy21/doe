<?php
$nologinrequired = true;
require_once('mysql.php');
$myaeds = array();

if (isset($tid) && $tid && !isset($specialadmin) && $tid != (isset($session_id) ? $session_id : 0) && !isTCFaculty()) {
    Header("Location: login.php");
    exit;
}

if (!isset($tid) || !$tid) {
    $tid = isset($session_id) ? $session_id : 0;
}

if ($tid) {
    $row = getUserRow($tid);
    if (!$row || (isset($row["usertype"]) && $row["usertype"] != "trainer")) {
        $redirectURL = isset($row["redirectURL"]) ? $row["redirectURL"] : "login.php";
        Header("Location: $redirectURL");
        exit;
    }
    $boroughs = db_query_array("select borough from trainer_to_borough where trainerid = '" . intval($tid) . "'", "borough", "borough");
}

if (isset($saveannual) && $saveannual) {
    $y = date("Y");
    db_query("delete from annualtests where trainerid = " . intval($tid) . " and thedate like  '$y%'");

    if (isset($annuals) && is_array($annuals)) {
        foreach ($annuals as $type => $arr) {
            if (isset($arr["exists"]) && $arr["exists"] && isset($arr["date"]) && $arr["date"]) {
                db_query("insert into annualtests ( trainerid, type, thedate ) values ( '" . intval($tid) . "', '" . addslashes($type) . "', '" . addslashes($arr["date"]) . "' ) ");
            }
        }
    }
}
?>
<?php
$specialtnav = 1;
include "ssi/top.php"; ?>
<!--start center content-->
<strong><span class="title">VIEW PROFILE</span></strong>
<?php if (isSpecialAdmin()) { ?> <a href='trainer_profile.php?tid=<?php echo intval($tid); ?>'>Edit</a><?php } ?>
<BR>
<hr>
<strong>Contact Information:</strong><BR><BR>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
    <!--row 1-->
    <tr>
        <td valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width='400'>
                <tr>
                    <td valign="top"><span class="copy">Salutation:</span><br>
                        <?php echo isset($row["salutation"]) ? htmlspecialchars($row["salutation"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="top"><span class="copy">First Name:</span><br>
                        <?php echo isset($row["first_name"]) ? htmlspecialchars($row["first_name"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="top"><span class="copy">MI:</span><br>
                        <?php echo isset($row["mi"]) ? htmlspecialchars($row["mi"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="top"><span class="copy">Last Name:</span><br>
                        <?php echo isset($row["last_name"]) ? htmlspecialchars($row["last_name"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="top"><span class="copy">Preferred Pronouns:</span><br>
                        <?php echo isset($row["preferredpronouns"]) ? htmlspecialchars($row["preferredpronouns"]) : ''; ?>
                    </td>
                </tr>
            </table>
            <Br>
        </td>
    </tr>
    <!--end row 1-->

    <!--row 2-->
    <tr>
        <td valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width='300'>
                <tr>
                    <td valign="middle"><span class="copy">Street Address 1:<br>
                            <?php echo isset($row["address1"]) ? htmlspecialchars($row["address1"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="middle"><span class="copy">Street Address 2:<br>
                            <?php echo isset($row["address2"]) ? htmlspecialchars($row["address2"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="middle"><span class="copy">AHA ID:<br> <?php echo isset($row["ahaid"]) ? htmlspecialchars($row["ahaid"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="middle"><span class="copy">ASHI ID:<br> <?php echo isset($row["ashiid"]) ? htmlspecialchars($row["ashiid"]) : ''; ?>
                    </td>
                </tr>
            </table><br>

            <table cellpadding="0" cellspacing="0" border="0" width='300'>
                <tr>
                    <td valign="middle"><span class="copy">City:<br>
                            <?php echo isset($row["city"]) ? htmlspecialchars($row["city"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="middle"><span class="copy">State:<br>
                            <?php echo isset($row["state"]) ? htmlspecialchars($row["state"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="middle"><span class="copy">Zip:<br>
                            <?php echo isset($row["zip"]) ? htmlspecialchars($row["zip"]) : ''; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!--end row 2-->

    <!--row 3-->
    <tr>
        <td valign="top"><br>
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td valign="top"><span class="copy">Phone Number:</span><br>
                        <?php echo isset($row["phone"]) ? htmlspecialchars($row["phone"]) : ''; ?>
                    </td>
                    <td valign="top"><span class="copy">Ext:</span><br>
                        <?php echo isset($row["phone_ext"]) ? htmlspecialchars($row["phone_ext"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="1" bgcolor="#999999"><img src="images/dotclear.gif" height="10" width="1" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="top"><span class="copy">Cell Number:</span><br>
                        <?php echo isset($row["cell"]) ? htmlspecialchars($row["cell"]) : ''; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!--end row 3-->

    <!--row 4-->
    <tr>
        <td valign="top"><Br>
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td valign="top"><span class="copy">Other Phone <em>(if any)</em>:</span><br>
                        <?php echo isset($row["otherphone"]) ? htmlspecialchars($row["otherphone"]) : ''; ?><br>
                        <span class="copy">Ext:</span><br>
                        <?php echo isset($row["otherphoneext"]) ? htmlspecialchars($row["otherphoneext"]) : ''; ?>
                    </td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                    <td valign="top"><span class="copy">Email Address:</span><br>
                        <span class='copy'><?php echo isset($row["userid"]) ? htmlspecialchars($row["userid"]) : ''; ?></span>
                        <?php if (isset($specialadmin) && $specialadmin || isTCFaculty()) { ?>
                            <A href='mailto:<?php echo isset($row["userid"]) ? htmlspecialchars($row["userid"]) : ''; ?>'>Email</a>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>

                <?php if (isset($specialadmin) && $specialadmin) {
                    $year = date("Y");
                    $annuals_result = db_query_rows("select * from annualtests where trainerid = " . intval($tid) . " and thedate like '{$year}%'", "type");
                    $annuals = $annuals_result ?: array();
                ?>
                    <tr>
                        <td class='copy'>
                            AHA ID: <?php echo isset($row["ahaid"]) ? htmlspecialchars($row["ahaid"]) : ''; ?><br>
                            <?php if (isset($row["paused"]) && $row["paused"]) { ?><font color='red'>PAUSED</font><br><?php } ?>
                            View <?php echo getSchoolStr("Schools"); ?>: <?php echo isset($row["viewschools"]) && $row["viewschools"] ? "Y" : "N"; ?><br>
                            National: <?php echo isset($row["national"]) && $row["national"] ? "Y" : "N"; ?><br>
                            Is ASHI?: <?php echo isset($row["ashi"]) && $row["ashi"] ? "Y" : "N"; ?><br>
                            BLS?: <?php echo isset($row["bls"]) && $row["bls"] ? "Y" : "N"; ?><br>
                            Fingerprinted?: <?php echo isset($row["fingerprinted"]) && $row["fingerprinted"] ? "Y" : "N"; ?><br>
                            First Aid?: <?php echo isset($row["firstaid"]) && $row["firstaid"] ? "Y" : "N"; ?><br>
                            Alive FA?: <?php echo isset($row["alivefa"]) && $row["alivefa"] ? "Y" : "N"; ?><br>
                            Corporate?: <?php echo isset($row["corporate"]) && $row["corporate"] ? "Y" : "N"; ?><br>
                            2020 Update?: <?php echo isset($row["2020update"]) && $row["2020update"] ? "Y" : "N"; ?><br>
                            Has Car?: <?php echo isset($row["hascar"]) && $row["hascar"] ? "Y" : "N"; ?><br>
                            TC Faculty?: <?php echo isset($row["tcfaculty"]) && $row["tcfaculty"] ? "Y" : "N"; ?><br>
                            Assigned TC Faculty?: <?php echo isset($row["assignedtcfacultyid"]) && $row["assignedtcfacultyid"] ? getFullname($row["assignedtcfacultyid"]) : "None"; ?>
                            <?php if (isset($row["tcfaculty"]) && $row["tcfaculty"]) { ?>
                                <br>Trainers Assigned?: <?php echo db_query_first_cell("select group_concat( concat( first_name, ' ', last_name  ), ' ' ) from user where assignedtcfacultyid = " . intval($tid) . " order by last_name, first_name"); ?>
                            <?php } ?>
                            <br>Stage: <?php echo isset($row["instructorstage"]) ? htmlspecialchars($row["instructorstage"]) : ''; ?>
                        </td>
                        <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                        <td width="6"><img src="images/dotclear.gif" height="10" width="6" alt=""></td>
                        <td colspan='5' class='copy' valign='top' style="width:400px"> Visible Zips: (comma separated)
                            <?php
                            $zips = isset($row) ? getZips($row) : '';
                            if ($zips) {
                                $zips = str_replace(",1", ", 1", $zips);
                                $zips = str_replace(",2", ", 2", $zips);
                                echo htmlspecialchars($zips);
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </td>
    </tr>

    <?php if (isOverallAdmin()) { ?>
        <tr>
            <td>
                <table>
                    <tr>
                        <td valign='top'>Notes:</td>
                        <td><?php echo isset($row["notes"]) ? nl2br(htmlspecialchars($row["notes"])) : ''; ?></td>
                    </tr>
                    <?php if (isset($zips) && $zips) { ?>
                        <tr>
                            <td colspan='2'><a href='allschools.php?overruserid=<?php echo isset($row["id"]) ? intval($row["id"]) : 0; ?>&nodrills=true&go=true&onscreen=true'>View <?php echo getSchoolStr("Schools"); ?> Needing Drills</a> <a href='allschools.php?overruserid=<?php echo isset($row["id"]) ? intval($row["id"]) : 0; ?>&nodrills=true&go=true'>(xls)</a></td>
                        </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>
    <?php } ?>
    <!--end row 4-->

    <tr>
        <td valign="top">
            <hr>
        </td>
    </tr>

    <!--row 5-->
    <tr>
        <td valign="top">
            <table cellpadding="0" cellspacing="6" border="0">
                <tr>
                    <td valign="top" colspan="2"><span class="copy">Boroughs:</span><br>
                        <table cellpadding="5" cellspacing="0" border="0">
                            <tr>
                                <td valign="middle"><span class="copy">
                                        <?php echo isset($boroughs["Bronx"]) && $boroughs["Bronx"] ? "Bronx<br>" : ""; ?>
                                        <?php echo isset($boroughs["Brooklyn"]) && $boroughs["Brooklyn"] ? "Brooklyn<br>" : ""; ?>
                                        <?php echo isset($boroughs["Manhattan"]) && $boroughs["Manhattan"] ? "Manhattan<br>" : ""; ?>
                                </td>
                                <td>&nbsp;&nbsp;&nbsp;</td>
                                <td valign="top"><span class="copy">
                                        <?php echo isset($boroughs["Queens"]) && $boroughs["Queens"] ? "Queens<br>" : ""; ?>
                                        <?php echo isset($boroughs["Staten Island"]) && $boroughs["Staten Island"] ? "Staten Island<br>" : ""; ?>
                                        <?php echo isset($boroughs["New Jersey"]) && $boroughs["New Jersey"] ? "New Jersey<br>" : ""; ?>
                                    </span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!--end row 5-->

    <?php if (isSpecialAdmin() || isTCFaculty()) { ?>
        <tr>
            <td valign="top">
                <hr>
                <strong>Upcoming Classes</strong><Br><br>
                <table cellpadding=2 cellspacing=0 border=1 width='550'>
                    <tr>
                        <th>School/Company</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Conf?</th>
                        <th>Spot Check?</th>
                        <th>Hours This Class</th>
                        <th>Hours This Week</th>
                        <th>Class ID</th>
                    </tr>
                    <?php
                    $classes = db_query_rows("select tcfacultyid, tcfacultyconfirmeddate, class.* from class where (tcfacultyid = '" . intval($tid) . "' or class.id in ( select classid from trainer_to_class where trainerid = '" . intval($tid) . "' ) ) and  startdate > '" . date("Y-m-d") . "'  and canceldate is null and deleted = 0 order by startdate");
                    if (is_array($classes)) {
                        foreach ($classes as $crow) {
                            $thisweek = hoursThisWeek($tid, $crow, true);
                            $comrow = getCompanyRow(isset($crow["companyid"]) ? $crow["companyid"] : 0);
                            $thisclass = hoursInClass($crow, isset($comrow["iscorp"]) ? $comrow["iscorp"] : 0);
                            echo ("<tr>");
                            echo ("<td><a href='viewcompany.php?id=" . (isset($comrow["id"]) ? $comrow["id"] : 0) . "'>" . (isset($comrow["companyname"]) ? htmlspecialchars($comrow["companyname"]) : '') . "</a></td>");
                            echo ("<td>" . (isset($crow["code"]) ? htmlspecialchars($crow["code"]) : '') . "</td>");
                            echo ("<td><nobr>" . (isset($crow["startdate"]) ? getFormattedDateWTime($crow["startdate"]) : '') . "</nobr></td>");

                            $conf = "N";
                            $tc = "";
                            if (isset($crow['tcfacultyid']) && $crow['tcfacultyid'] == $tid) {
                                $conf = isset($crow["tcfacultyconfirmeddate"]) && $crow["tcfacultyconfirmeddate"] ? "Y" : "N";
                                $tc = "(TC)";
                            } else {
                                $conf = db_query_first_cell("select trainerconfirmeddate from trainer_to_class where classid = " . intval($crow["id"]) . " and trainerid = '" . intval($tid) . "'") ? "Y" : "N";
                            }
                            echo ("<td><nobr>" . $conf . " $tc</nobr></td>");
                            echo ("<td><nobr>" . (isset($crow["spotcheck"]) && $crow["spotcheck"] ? "Y" : "N") . "</nobr></td>");
                            echo ("<td><nobr>" . $thisclass . "</nobr></td>");
                            echo ("<td><nobr>" . $thisweek . "</nobr></td>");
                            $pval = isset($comrow["schoolcode"]) ? $comrow["schoolcode"] : '';
                            echo ("<td><a onMouseover=\"popup('" . addslashes($pval) . "', 'white')\" onMouseout=\"kill()\" href='class_detail.php?id=" . (isset($crow["id"]) ? $crow["id"] : 0) . "'>" . (isset($crow["id"]) ? $crow["id"] : '') . "</a></td></tr>");
                        }
                    }
                    ?>
                </table>
                <br><Br>
            </td>
        </tr>
    <?php } ?>

    <tr>
        <td valign="top">
            <hr>
        </td>
    </tr>

    <?php if (isset($specialadmin) && $specialadmin || isTCFaculty()) { ?>
        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3" alt=""></td>
        </tr>

        <?php
        $year = date("Y");
        $annuals_result = db_query_rows("select * from annualtests where trainerid = " . intval($tid) . " and thedate like '{$year}%'", "type");
        $annuals = $annuals_result ?: array();
        ?>
        <tr>
            <td class='copy' colspan='4'>
                <form method='post'><b>Annual Proficiency Check</b><Br><br>
                    <nobr> <input type='checkbox' name='annuals[cognitive][exists]' value='1' <?php echo isset($annuals["cognitive"]["thedate"]) && $annuals["cognitive"]["thedate"] ? "CHECKED" : ""; ?>> Cognitive: <?php echo printdates2("annuals[cognitive][date]", isset($annuals["cognitive"]["thedate"]) ? $annuals["cognitive"]["thedate"] : ''); ?></nobr> <br>
                    <nobr><input type='checkbox' name='annuals[psychomotor][exists]' value='1' <?php echo isset($annuals["psychomotor"]["thedate"]) && $annuals["psychomotor"]["thedate"] ? "CHECKED" : ""; ?>> Psychomotor: <?php echo printdates2("annuals[psychomotor][date]", isset($annuals["psychomotor"]["thedate"]) ? $annuals["psychomotor"]["thedate"] : ''); ?></nobr> <br>
                    <input type='submit' name='saveannual' value='Save'>
                </form>
                <br><br>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <hr>
            </td>
        </tr>

        <tr>
            <td valign="top" colspan="2">
                <a name='mon'></a>
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td><span><strong>Instructor Monitoring</strong> <?php if (isset($specialadmin) && $specialadmin || isTCFaculty()) { ?> <input type='button' class='copy' name='' value='Request New Monitoring Worksheet' onClick='javascript:document.location.href="editmonitoring.php?id=<?php echo intval($tid); ?>&newservicecall=true"'> <input type='button' class='copy' name='' value='Training History' onClick='javascript:document.location.href="training_history.php?id=<?php echo intval($tid); ?>"'><?php } ?></td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <?php
                $extsearch = "";
                $aed_rows = db_query_rows("select * from monitoring where trainerid = '" . (isset($row['id']) ? intval($row['id']) : 0) . "' $extsearch order by monitoringdate");
                ?>
                <div align="center">
                    <table width="95%" border="0">
                        <tr>
                            <td>
                                <Table>
                                    <?php
                                    if (is_array($aed_rows)) {
                                        foreach ($aed_rows as $arow) {
                                    ?>
                                            <tr>
                                                <td>
                                                    <span class="copy">
                                                        <strong>
                                                            <a href="editmonitoring.php?monitoringid=<?php echo isset($arow['monitoringid']) ? intval($arow['monitoringid']) : 0; ?>&id=<?php echo isset($row['id']) ? intval($row['id']) : 0; ?>&redirect=<?php echo urlencode((isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '') . '?' . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '')); ?>"><strong>M<?php echo isset($arow['monitoringid']) ? $arow['monitoringid'] : ''; ?>: <?php echo isset($arow['monitoringdate']) && $arow['monitoringdate'] ? $arow['monitoringdate'] : "N/A"; ?></strong></a><br>
                                                        </strong>
                                                    </span>
                                                </td>
                                                <td class=copy><?php echo is_array($myaeds) ? join(", ", $myaeds) : ''; ?></td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                </span>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <hr>
            </td>
        </tr>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3" alt=""></td>
        </tr>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3" alt=""></td>
        </tr>

        <tr>
            <td valign="top" colspan="2">
                <a name='mon'></a>
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td><span><strong>Drill Monitoring</strong> <?php if (isset($specialadmin) && $specialadmin) { ?> <input type='button' class='copy' name='' value='Request New Drill Monitoring Worksheet' onClick='javascript:document.location.href="editdrillmonitoring.php?id=<?php echo intval($tid); ?>&newservicecall=true"'><?php } ?></td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <?php
                $aed_rows = db_query_rows("select * from drillmonitoring where trainerid = '" . (isset($row['id']) ? intval($row['id']) : 0) . "' $extsearch order by drillmonitoringdate");
                ?>
                <div align="center">
                    <table width="95%" border="0">
                        <tr>
                            <td>
                                <Table>
                                    <?php
                                    if (is_array($aed_rows)) {
                                        foreach ($aed_rows as $arow) {
                                    ?>
                                            <tr>
                                                <td>
                                                    <span class="copy">
                                                        <strong>
                                                            <a href="editdrillmonitoring.php?drillmonitoringid=<?php echo isset($arow['drillmonitoringid']) ? intval($arow['drillmonitoringid']) : 0; ?>&id=<?php echo isset($row['id']) ? intval($row['id']) : 0; ?>&redirect=<?php echo urlencode((isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '') . '?' . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '')); ?>"><strong>DM<?php echo isset($arow['drillmonitoringid']) ? $arow['drillmonitoringid'] : ''; ?>: <?php echo isset($arow['drillmonitoringdate']) && $arow['drillmonitoringdate'] ? $arow['drillmonitoringdate'] : "N/A"; ?></strong></a><br>
                                                        </strong>
                                                    </span>
                                                </td>
                                                <td class=copy><?php echo is_array($myaeds) ? join(", ", $myaeds) : ''; ?></td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                </span>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <hr>
            </td>
        </tr>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3" alt=""></td>
        </tr>

        <tr>
            <td class='copy'><a href='expdatehistory.php?trainerid=<?php echo intval($tid); ?>'>View History</a>
        <tr>
            <td class='copy'><b>AHA CPR Instructor</b><br>
                Expiration Date: <?php echo getCurrentTrainerExp("aha", "expdate", $tid); ?><br>
                Type: <?php echo getCurrentTrainerExp("aha", "type", $tid); ?><br>
                Site: <?php echo getCurrentTrainerExp("aha", "site", $tid); ?><br>
            </td>
        </tr>

        <tr>
            <td class='copy'><b>CPR Provider</b><br>
                Expiration Date: <?php echo getCurrentTrainerExp("cpr", "expdate", $tid); ?><br>
                Type: <?php echo getCurrentTrainerExp("cpr", "type", $tid); ?><br>
                Site: <?php echo getCurrentTrainerExp("cpr", "site", $tid); ?><br>
            </td>
        </tr>

        <tr>
            <td class='copy'><b>Initial Certification</b><br>
                Date: <?php echo getCurrentTrainerExp("coreinst", "expdate", $tid); ?><br>
            </td>
        </tr>

        <tr>
            <td class='copy'><b>Last Renewal Date</b><br>
                Date: <?php echo isset($row["lastrenewaldate"]) ? htmlspecialchars($row["lastrenewaldate"]) : ''; ?><br>
            </td>
        </tr>

        <tr>
            <td class='copy'><b>TC Affiliation</b><br>
                <?php echo getCurrentTrainerExp("tc", "type", $tid); ?><br>
            </td>
        </tr>

        <tr>
            <td class='copy'><b>Other Credentials</b><br>
                Expiration Date: <?php echo getCurrentTrainerExp("other", "expdate", $tid); ?><br>
                Type: <?php echo getCurrentTrainerExp("other", "type", $tid); ?><br>
                Site: <?php echo getCurrentTrainerExp("other", "site", $tid); ?><br>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <hr>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <span class="copy"><strong>Password:</strong><br><br>
            </td>
        </tr>

        <!--password row-->
        <tr>
            <td valign="top">
                <table cellpadding="0" cellspacing="6" border="0">
                    <tr>
                        <td valign="top" align="right"><span class="copy">Password:</span></td>
                        <td><?php
                        // echo isset($row["password"]) ? htmlspecialchars($row["password"]) : ''; 
                        ?>
                    </tr>
                    <tr>
                        <td align="right"><span class="copy">Inactive?:</span></td>
                        <td><?php echo isset($row["inactive"]) && $row["inactive"] ? "Yes" : "No"; ?>
                    </tr>
                </table>
            </td>
        </tr>
        <!--end password row-->
    <?php } ?>

    <tr>
        <td valign="top">
            <hr>
        </td>
    </tr>

    <tr>
        <td valign="top">
            <strong><span class='copy'>Availability</span></strong> <?php if (isSpecialAdmin()) { ?> <a href='trainer_availability.php?theid=<?php echo intval($tid); ?>'>Edit Availability</a><?php } ?>
            <?php
            $theid = $tid;
            $avail = db_query_rows("select * from trainer_availability where trainerid = " . intval($theid) . " order by startdate", "weekday");
            ?>
            <table cellpadding="0" cellspacing="0" border="0" width="476">
                <tr>
                    <td valign="top">
                        <p><span class='copy'>
                                <table>
                                    <tr>
                                        <td colspan='3'>Available on: </td>
                                    </tr>
                                    <?php for ($i = 0; $i < 7; $i++) {
                                        $curravail = isset($avail[$i]) ? $avail[$i] : array();
                                    ?>
                                        <tr>
                                            <td class='copy'><?php echo getDayDisplay($i); ?>:</td>
                                            <td class='copy'><?php echo isset($avail[$i]) && $avail[$i] ? "Yes" : "No"; ?>
                                            <td class='copy'>
                                                <?php if (isset($avail[$i]) && $avail[$i]) { ?>
                                                    &nbsp;&nbsp;&nbsp; From:
                                                    <?php echo date("g a", mktime((isset($curravail["starttime"]) ? $curravail["starttime"] : 0), 0)); ?>
                                                    To:
                                                    <?php echo date("g a", mktime((isset($curravail["endtime"]) ? $curravail["endtime"] : 0), 0)); ?>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td colspan='4' class='copy'>
                                            <table>
                                                <tr>
                                                    <td>Not available on: </td>
                                                </tr>
                                                <?php
                                                $mynotavail = db_query_array("select notavail, enddate from trainer_notavail where trainerid = '" . intval($theid) . "'", "notavail", "enddate");
                                                if (is_array($mynotavail)) {
                                                    foreach ($mynotavail as $n => $end) { ?>
                                                        <tr>
                                                            <td class='copy'><?php echo htmlspecialchars($n); ?> <?php echo $n != $end ? " - " . htmlspecialchars($end) : ""; ?></td>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </table>
                            </span>
                    </td>
                    <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
                </tr>
            </table>
            <?php if (isSpecialAdmin()) { ?> <a href='trainer_profile.php?tid=<?php echo intval($tid); ?>'>Edit</a><?php } ?>
        </td>
    </tr>
</table>

<br><br><br><br>
<!--end center content-->
<?php include "ssi/footer.php"; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
<?php include "popupjs.php"; ?>
</body>

</html>