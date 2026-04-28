<?php
require_once('mysql.php');

// --- Access Control ---
if (getcurrentusercompany() > 0 && ($thisusersrow["companyid"]) != PARKSMAINCOMPANY) {
    header("location: login.php");
    exit;
}

// --- Admin Actions (Merge/Delete) ---
if ($mergechecked) {
    foreach ($tomerge as $t) {
        $t_safe = (int)$t;
        // Move responder to the holding company (ID 2810)
        db_query("UPDATE responders_esi SET clientid = 2810 WHERE responderid = {$t_safe}");
    }
}

if ($deletechecked) {
    foreach ($tomerge as $t) {
        $t_safe = $t;
        // Soft delete responder
        db_query("UPDATE responders_esi SET deleted = 1, deletiondate = NOW() WHERE responderid = {$t_safe}");
    }
}

// --- Search Logic ---
$result = [];
$err = '';
if ($go || ($deletechecked && $fieldvalue) || $mergechecked) {
    $where = "";

    // Field Name/Value Search
    if ($fieldname && $fieldvalue) {
        $fieldvalue_safe = addslashes($fieldvalue);
        $fieldname_safe = addslashes($fieldname);

        if ($fieldname == "title" && strtolower($fieldvalue) == "nurse") {
            // Special handling for 'Nurse' title
            $where .= " AND ({$fieldname_safe} LIKE '%{$fieldvalue_safe}%' OR {$fieldname_safe} = 'RN')";
        } else {
            if ($searchexact) {
                $where .= " AND ({$fieldname_safe} = '{$fieldvalue_safe}'";
                if ($fieldname == "lastname") $where .= " OR maidenname = '{$fieldvalue_safe}'";
                $where .= " ) ";
            } else {
                $where .= " AND ({$fieldname_safe} LIKE '%{$fieldvalue_safe}%'";
                if ($fieldname == "lastname") $where .= " OR maidenname LIKE '%{$fieldvalue_safe}%'";
                $where .= " ) ";
            }
        }
    }

    // Borough Filter
    if ($borough) {
        $borough_safe = addslashes($borough);
        $where .= " AND borough = '{$borough_safe}'";
    }

    // Region Filter (Parks Users)
    if (isParksUser()) {
        $where .= " AND region = '" . addslashes($thisusersrow['visibleregion']) . "'";
    }

    // Region Filter (Manual Input)
    if ($region) {
        $region_safe = addslashes($region);
        $where .= " AND region = '{$region_safe}'";
    }

    // Company ID Filter
    if ($companyid) {
        $companyid_safe = $companyid;
        $where .= " AND clientid = '{$companyid_safe}'";
    }

    // Training Date Range Filter
    if ($datefrom || $dateto) {
        $where .= " AND r.responderid IN ( SELECT responderid FROM responder_training_dates WHERE 1=1 ";
        if ($datefrom) {
            $datefrom_safe = addslashes($datefrom);
            $where .= " AND trainingdate >= '{$datefrom_safe}' ";
        }
        if ($dateto) {
            $dateto_safe = addslashes($dateto);
            $where .= " AND trainingdate <= '{$dateto_safe} 23:59:59' ";
        }
        $where .= " )";
    }

    // Parks Main Company Filter
    if (($thisusersrow["companyid"]) == PARKSMAINCOMPANY) {
        $where .= " AND c.campusid = " . PARKSCAMPUS;
    }

    // Set default order
    if (!$order) {
        $order = "ORDER BY lastname, firstname";
    }

    // Final Query Construction
    $sql = "SELECT r.*, c.borough, c.companyname 
            FROM responders_esi r, company_esi c 
            WHERE iscorp = '{$session_iscorp}' 
            AND c.id = clientid 
            AND r.deleted = 0 
            {$where} {$order}";

    // Execute query only if search criteria were provided
    if ($where) {
        $result = db_query_rows($sql);
    } else {
        $err = "You need to choose a search criteria: Field/Value, Borough, Company ID, etc.";
    }
}

// --- Excel Export ---
if (!$err && $xls) {
    include "respondersxls.php"; // Assumed script handles $result data
    exit;
}

// --- HTML Output ---
?>
<?php include "ssi/top.php"; ?>
<form name='myform' method='post'><input type='hidden' name='order' value='order by lastname, firstname'>
    <strong><span class="title">RESPONDERS</span></strong>
    <p>
    <table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3">
        <tr height="23" bgcolor="#e1e1f6">
            <td valign="top"><span class="copy"><strong>Responders:</strong></span></td>
        </tr>
        <tr bgcolor="#ffffff">
            <td valign="bottom"><span class="copy">
                    <font color='red'><?php echo htmlspecialchars($err); ?></font><br>
                    <?php if ($specialadmin) { ?>
                        <a class='copy' href='individual_registration1.php'>Sign up an individual</a><br>
                        <a class='copy' href='possiblemerges.php'>Possible Merges</a><br>
                    <?php } ?>
                    <table cellpadding="3" cellspacing="0" border="0">
                        <tr>
                            <td valign="middle"><span class="copy">View Responders </span></td>
                            <td valign="middle"><span class="copy">By <select class=copy name='fieldname'>
                                        <option value=''></option>
                                        <option <?php echo $fieldname == "lastname" ? "SELECTED" : ""; ?> value='lastname'>Last Name</option>
                                        <option <?php echo $fieldname == "responderid" ? "SELECTED" : ""; ?> value='responderid'>TR ID</option>
                                        <option <?php echo $fieldname == "firstname" ? "SELECTED" : ""; ?> value='firstname'>First Name</option>
                                        <option <?php echo $fieldname == "title" ? "SELECTED" : ""; ?> value='title'>Job Title</option>
                                        <?php if (!$session_iscorp) { ?>
                                            <option <?php echo $fieldname == "pmsid" ? "SELECTED" : ""; ?> value='pmsid'><?php echo htmlspecialchars(getSchoolStr("PMS ID")); ?></option>
                                        <?php } ?>
                                    </select>: <input size='10' name='fieldvalue' class=copy value="<?php echo htmlspecialchars(stripslashes($fieldvalue)); ?>"></span>
                                <select name='searchexact'>
                                    <option value='0'>Fuzzy</option>
                                    <option <?php echo $searchexact ? "SELECTED" : ""; ?> value='1'>Exact</option>
                                </select>
                                XLS: <input type='checkbox' name='xls' value=1 <?php echo $xls ? 'checked' : ''; ?>>
                            </td>
                            <td valign="middle"><input type='submit' class=copy name='go' value='Go'></td>
                        </tr>
                        <tr>
                            <td class='copy'>Region:</td>
                            <td><input type='text' size='4' name='region' value="<?php echo htmlspecialchars($region); ?>"></td>
                        </tr>
                        <tr>
                            <td class='copy'>Training Dates: </td>
                            <td><?php echo printdates2("datefrom", $datefrom); ?> and <?php echo printdates2("dateto", $dateto); ?></td>
                        </tr>
                        <?php if (!$session_iscorp) { ?>
                            <tr>
                                <td class='copy'>School: </td>
                                <td colspan='3'>
                                    <select id="borough" name="borough" onChange="changeBorough();" style="font-size: 10px; font-family: verdana;">
                                        <option value=""></option>
                                        <option value="Bronx" <?php echo $borough == 'Bronx' ? 'SELECTED' : ''; ?>>The Bronx</option>
                                        <option value="Brooklyn" <?php echo $borough == 'Brooklyn' ? 'SELECTED' : ''; ?>>Brooklyn</option>
                                        <option value="Manhattan" <?php echo $borough == 'Manhattan' ? 'SELECTED' : ''; ?>>Manhattan</option>
                                        <option value="Queens" <?php echo $borough == 'Queens' ? 'SELECTED' : ''; ?>>Queens</option>
                                        <option value="Staten Island" <?php echo $borough == 'Staten Island' ? 'SELECTED' : ''; ?>>Staten Island</option>
                                    </select>
                                    <?php include "getschooldropdown.php"; // Assumed to handle dropdown logic 
                                    ?>

                                    <span class='copy'>School Name: </span> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='changeBorough()'> <input type='button' value='Search' class=copy onClick='changeBorough()'>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td colspan='3' valign="top">
                                <div id='school_select'>
                                </div>

                            </td>

                        </tr>
                    </table>
                    <?php if (!empty($result)) { ?>
                        <table>
                            <tr><?php if ($specialadmin ?? false) { ?>
                                    <th>ID</th><?php } ?><th>Name</th>
                                <th><?php echo htmlspecialchars(getSchoolStr("School")); ?></th>
                                <th>Borough</th>
                                <th>Exp Date</th>
                                <th>Next Scheduled Date</th>
                                <?php if ($fieldname == "title") { ?><th>Title</th><?php } ?>
                            </tr>
                            <?php
                            foreach ($result as $row) {
                                $responder_id = $row["responderid"];
                                $client_id = $row["clientid"];
                                $company_name = htmlspecialchars($row["companyname"]);
                                $borough_name = htmlspecialchars($row["borough"]);
                                $full_name = htmlspecialchars(($row["firstname"]) . " " . $row["lastname"]);

                                $exp = getResponderExpDate($responder_id); // Assumed external function
                                if ($exp && $exp != "1969-12-31 00:00:00") {
                                    $exp_display = getFormattedDate($exp . " + 2 years"); // Assumed external function
                                } else {
                                    $exp_display = "";
                                }

                                $nextdate = db_query_first_cell("SELECT GROUP_CONCAT(startdate, ' ') FROM responder_to_class, class WHERE responderid = '{$responder_id}' AND class.id = classid AND startdate > NOW() AND deleted = 0 ORDER BY startdate");
                            ?>
                                <tr>
                                    <?php if ($specialadmin) { ?>
                                        <td class='copy'><input type='checkbox' name='tomerge[<?php echo $responder_id; ?>]' value='<?php echo $responder_id; ?>'></td>
                                    <?php } ?>
                                    <td class='copy'><a href='viewresponder.php?responderid=<?php echo $responder_id; ?>'><?php echo $full_name; ?></a></td>
                                    <td class='copy'><a href='viewcompany.php?id=<?php echo $client_id; ?>'><?php echo $company_name; ?></a></td>
                                    <td class='copy'><?php echo $borough_name; ?></td>
                                    <td><?php echo $exp_display; ?></td>
                                    <td><?php echo htmlspecialchars($nextdate); ?></td>

                                    <?php if ($fieldname == "title") { ?>
                                        <td><?php echo htmlspecialchars($row[$fieldname]); ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                            <?php if ($specialadmin) { ?>
                                <tr>
                                    <td colspan='7'>
                                        <input class=copy type='submit' name='deletechecked' value='Delete Checked' onclick="return confirm('Are you sure you want to delete the selected responders?');">
                                        <input class=copy type='submit' name='mergechecked' value='Move Checked To Holding School' onclick="return confirm('Are you sure you want to move the selected responders to the holding school (ID 2810)?');">
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } ?>

            </td>
        </tr>
    </table>
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