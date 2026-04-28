<?php
require_once('mysql.php');

if (getcurrentusercompany() > 0) {
    Header("location: login.php");
    exit;
}
$trainerid = [] ?? null;
if (isset($go) && $go) {
    if (isset($fromdate) && $fromdate)
        $extra .= " and startdate >= '$fromdate' ";
    if (isset($todate) && $todate) {
        $special = date("Y-m-d 23:59:59", strtotime($todate));
        $extra .= " and startdate <= '$special' ";
    }

    if (isset($pendingonly) && $pendingonly) {
        $extra .= " and class.accepted = 0";
    }
    if (isset($nocanceled) && $nocanceled) {
        $extra .= " and class.canceldate is null";
    }
    if (isset($type) && $type == "trainer") {
        $extra .= " and company_esi.deleted = 0";
    } else {
        if (!isset($showboth) || !$showboth) {
            if (isset($session_iscorp)) {
                $extra .= " and company_esi.iscorp = '$session_iscorp'";
            }
        }
    }

    if (isset($doh) && $doh) {
        $extra .= " and company_esi.campusid = 3235";
    }
    if (isset($national) && $national) {
        $extra .= " and isnational = 1";
    }
    if (isset($zip) && $zip) {
        $extra .= " and zip = '$zip'";
    }
    if (isset($region) && $region) {
        $extra .= " and region = '$region'";
    }
    if (isset($schoolid) && $schoolid) {
        $extra .= " and companyid = $schoolid";
    }
    if (isset($trainerid) && $trainerid) {
        $tstr = implode(", ", $trainerid);
        $extra .= " and trainer_to_class.trainerid in ( $tstr )";
    }
    if (isset($code) && is_array($code) && count($code)) {
        $cstr = "";
        foreach ($code as $c) {
            if ($cstr) $cstr .= ", ";
            $cstr .= "'$c'";
        }
        $extra .= " and code in ( $cstr )";
    }
    if (isset($campusid) && $campusid) {
        $extra .= " and campusid = '$campusid'";
    }
    if (isset($conferenceroom) && $conferenceroom) {
        $extra .= " and isconferenceroom = '1'";
    }
    if (isset($equipreturned) && (strval($equipreturned) == "1" || strval($equipreturned) == "0")) {
        $extra .= " and equipreturned = '$equipreturned'";
    }

    $extra .= " and company_esi.iscorp <> 2 ";
    $extra .= " and company_esi.companyname not like 'Sample%' ";

    if (isset($type) && $type == "class") $cd = "";
    else $cd = "";
    $sql = ("Select trainer_to_class.trainerconfirmeddate, trainer_to_class.trainerid as tid, class.* from (class, company_esi) left join trainer_to_class on classid = class.id where company_esi.id = class.companyid $cd  $extra order by startdate");


    $rep = db_query_rows($sql);
    if (!isset($onscreen) || !$onscreen) {
        Header("Content-type: application/vnd.ms-excel");
        header("Content-Transfer-Encoding: binary");
        $user_agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
        $filename = time() . "classes.xls";
        if ((is_integer(strpos($user_agent, "msie"))) && (is_integer(strpos($user_agent, "win")))) {
            header("Content-Disposition: filename=" . basename($filename) . ";");
        } else {
            header("Content-Disposition: attachment; filename=" . basename($filename) . ";");
        }
    }
    if (isset($type) && $type == "class")
        include "classreportxls.php";
    else
        include "trainerreportxls.php";

    exit;
}
?>

<?php include "ssi/top.php"; ?>
<!--start center content-->
<strong><span class="title">REPORTS</span></strong>
<p>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
    <form method='get'>
        <table>
            <tr>
                <td class='copy'>
                    <?= getSchoolStr("School") ?>: <input type='text' class='copy' name='substr' value='<?= $substr ?>'> <input type='checkbox' name='showboth' value='1'> Show Corp *and* DOE *and* Aging? <input type='submit' name='searchschool' class=copy value='Search'></td>
            </tr>
            <tr>
                <td class='copy'>Exclude Canceled Classes? <input type='checkbox' name='nocanceled' value='1'><br>
                    Pending Classes Only? <input type='checkbox' name='pendingonly' value='1'><br>
                    <?php if (isset($session_iscorp) && $session_iscorp) { ?>
                        DOH Only? <input type='checkbox' name='doh' value='1'>
                        National Only? <input type='checkbox' name='national' value='1'>
                    <?php } ?>
                </td>
            </tr>
            <?php if (isset($substr) && $substr) { ?>
                <tr>
                    <td class='copy'>
                        <select name='schoolid' class='copy'>
                            <?php
                            $whr = " and ( companyname like '%$substr%' or schoolno like '%$substr%'  or schoolcode like '%$substr%' or zip = '$substr' ) ";
                            $rows = db_query_rows("select *, concat( schoolcode, companyname ) as longname from company_esi where iscorp = '$session_iscorp' and deleted=0 $whr order by longname");
                            foreach ($rows as $row) {
                            ?> <option value="<?= $row["id"] ?>"><?= $row["schoolcode"] ?> (<?= $row["companyname"] ?>)</option><?php
                                                                                            }
                                                                                                ?>
                        </select>
                    </td>
                </tr>
            <?php } ?>
            <?php if (isOverallAdmin()) { ?>
                <tr>
                    <td class='copy'>Trainer: <select multiple size='3' name='trainerid[]' class='copy'>
                            <?php $trainers = db_query_array("select id, concat( last_name, ', ', first_name ) as name from user where usertype = 'trainer' and inactive = 0  order by last_name", "id", "name");
                            foreach ($trainers as $tid => $tname) {
                                echo ("<option value='$tid'>$tname</option>");
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='copy'><?= getSchoolStr("Campus") ?> ID: <select name='campusid' class='copy'>
                            <option value=''></option>
                            <?php $campuses = db_query_array("select id, name from campus where iscorp = '$session_iscorp'  order by name", "id", "name");
                            foreach ($campuses as $tid => $tname) {
                                echo ("<option value='$tid'>$tname</option>");
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td class='copy'>Class Type: <select multiple size='5' class='copy' name='code[]'>
                        <?php foreach ($class_names as $code => $name) { ?>
                            <option value="<?= $code ?>"><?= $name ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class='copy'>
                    From (YYYY-MM-DD)<?= printdates2("fromdate", $fromdate) ?> To <?= printdates2("todate", $todate) ?>
            <tr>
                <td class='copy'>
                    Zip <input type='text' name='zip' value='<?= $zip ?>' size=10>
                </td>
            </tr>
            <tr>
                <td class='copy'>
                    Region <input type='text' name='region' value='<?= $region ?>' size=10>
                </td>
            </tr>
            <?php if (isOverallAdmin()) { ?>
                <tr>
                    <td class='copy'>Equipment Returned? <select name='equipreturned'>
                            <option value=''>Either</option>
                            <option value='1'>Yes</option>
                            <option value='0'>No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='copy'>Conference Room Only? <input type=checkbox name='conferenceroom' value='1'>
                    </td>
                </tr>
                <tr>
                    <td class='copy'>Type: <select class='copy' name='type'>
                            <option value='class'>Class Report</option>
                            <option <?php echo isset($type) && $type == "trainer" ? "SELECTED" : "" ?> value='trainer'>Trainer Report</option>
                        </select>
                    <?php } else { ?>
                        <input type='hidden' name='type' value='class'>
                    <?php } ?>
                    <input type='submit' name='go' class=copy value='Get Report'>
                    <input type='checkbox' name='onscreen' value='1'> HTML Version
                    </td>
                </tr>
        </table>
        <!--end center content-->

        <?php include "ssi/footer.php"; ?>

        <!--end footer-->
        </span>
        </td>
        <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
        </tr>
</table>
<br><br>
</div>
</body>

</html>