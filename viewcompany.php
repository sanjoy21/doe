<?php
include "mysql.php";

$showaedlink = true;

// Polyfill for mysqli_real_escape_string if not defined
if (!function_exists('mysqli_real_escape_string')) {
    function mysqli_real_escape_string($str)
    {
        return addslashes((string)$str);
    }
}

//echo( getcurrentusercompany() );
if ($id == 1866) {
    if (getcurrentusercompany() > 0 && getcurrentusercompany() != 1866) {
        header("Location: /");
        exit;
    }
}

if ($sendall) {
    $responder_rows = getCurrentResponders($id);
    if (is_array($responder_rows)) {
        foreach ($responder_rows as $r) {
            require_once('services.php');
            $msg .= ("sent {$r['responderid']}, {$r['lastname']}, {$r['pmsid']}<br>");
            $lastname = $r["lastname"];
            $pmsid = $r["pmsid"];
            $pmsidvalidated = validateEmployee($pmsid, $lastname);
            $extpms = "pmsidvalidated = '$pmsidvalidated', lastpmsvalidated = now()";
            db_query("update responders_esi set $extpms where responderid = {$r['responderid']}");
            if (1 == 0)
                updateResponder($r);
        }
    }
    $err = "<font color='red'>Re-sent all current responders: <br>$msg</font>";
}

if ($delunused && $id) {
    $drows = db_query_array("select d.drillid from drill d, drill_to_companyid dtc where d.drillid = dtc.drillid and dtc.companyid = $id and ( isdone = 0 or isdone is null )", "drillid", "drillid");

    if (!is_array($drows)) $drows = [];
    $drows[] = -1;
    db_query("delete from drill where drillid in (" . implode(", ", $drows) . " )");
    db_query("delete from drill_to_companyid where drillid in (" . implode(", ", $drows) . " )");
    $drows = db_query_array("select d.servicecallid from servicecall d, servicecall_to_companyid dtc where d.servicecallid = dtc.servicecallid and dtc.companyid = $id and ( isdone = 0 or isdone is null ) and servicecalldate is null", "servicecallid", "servicecallid");
    // print_r( $drows );
    // exit;
    if (!is_array($drows)) $drows = [];
    $drows[] = -1;
    db_query("delete from servicecall where servicecallid in (" . implode(", ", $drows) . " )");
    db_query("delete from servicecall_to_companyid where servicecallid in (" . implode(", ", $drows) . " )");
    //    db_query( "delete from servicecall where companyid = $id and isdone = 0 and servicecalldate is null " );

    $err = "<font color='red'>Deleted.</font>";
}

if ($markreceived) {
    foreach ($markreceived as $did => $throwaway) {

        if (!empty($ddate[$did])) {
            $d = date("Y-m-d", strtotime($ddate[$did]));
            db_query("update drill set drilldate = '$d', received = 1 where drillid = $did");
        } else
            db_query("update drill set received = 1 where drillid = $did");
        db_query("delete from drill_to_companyid where drillid = '$did'");
        $thecid = $id;
        $campusid = db_query_first_cell("select campusid from company_esi where id = $thecid");
        if ($campusid > 0)
            $arr = db_query_array("select id from company_esi where campusid = $campusid", "id", "id");
        else
            $arr = array();
        $arr[] = $thecid;

        foreach ($arr as $thecid)
            db_query("insert into drill_to_companyid ( drillid, companyid, showed ) values ( '$did', '$thecid', 1 )");
    }
}
if ($uploadmf) {
    if (isset($_FILES["mf"]["tmp_name"]) && is_uploaded_file($_FILES["mf"]["tmp_name"])) {
        move_uploaded_file($_FILES["mf"]["tmp_name"], "mfs/$id.pdf");
    }
    header("Location: viewcompany.php?id=$id");
    exit;
}

if ($updatebuildingassessment) {
    db_query("update company_esi set buildingassessment = '$ba', buildingassessmentdate = now() where id = $id");
    $cid = db_query_first_cell("select campusid from company_esi where id = $id");
    if ($cid > 0)
        db_query("update company_esi set buildingassessment = '$ba', buildingassessmentdate = now() where campusid = $cid");
    header("Location: viewcompany.php?id=$id");
    exit;
}


if ($markcompleted) {
    //    echo( "update phonecalls set completed = 1, completeddate = now(), completednote='$completednote' where id = $markcompleted" );
    db_query("update phonecalls set completed = 1, completeddate = now(), completednote='$completednote' where id = $markcompleted");

    header("Location: viewcompany.php?id=$id");
    exit;
}
if ($markscompleted) {
    db_query("update supplyrequests set completed = 1, datereplied = now(), completednote='$scompletednote' where id = $markscompleted");
    header("Location: viewcompany.php?id=$id");
    exit;
}
if ($delsr) {
    db_query("delete from supplyrequests where id = $delsr");
    header("Location: viewcompany.php?id=$id");
    exit;
}
//        $cur = getCurrentResponders( $id, mktime( 0,0,0, 10, 13, 2007 ) );
//      echo( "aaaa:'" . count( $cur ) . "'" );

// $val = db_query_rows( "select campusid, companyname, officeid, related_company, id from company_esi where iscorp = 1" );
// foreach( $val as $vrow )
// {
//      $campus = db_query_first_cell("Select name from campus where id= $vrow[campusid]" );
//     db_query( "update company_esi set companyname = concat( '".mysqli_real_escape_string( $campus )."', ' - ', officeid ) where id = $vrow[id] " );
// }

if ($addnewcall) {
    db_query("insert into phonecalls ( companyid, description, calldate ) values ( $id, '$newcall', '" . date("Y-m-d H:i:s", strtotime($newcalldate)) . "' )");
    sendMail("sarahg@emergencyskills.com", "new phone call: " . getCompanyName($id), "A new call for " . getCompanyName($id) . " happened at : $newcalldate\n\n $newcall\n\n user: $session_userid\n IP: " . $_SERVER["REMOTE_ADDR"], "info@emergencyskills.com");
}

if ($addnewsupply) {
    db_query("insert into supplyrequests( companyid, descr, datesent, username ) values ( '$id', '$newsupply', now(), '$session_userid' )");
    $crow = getCompanyRow($id);
    if (empty($crow["iscorp"])) {
        sendMail("sarahg@emergencyskills.com, hthomps@schools.nyc.gov, cmcgee3@schools.nyc.gov", getCompanyName($id), "Notes to " . getSchoolStr("the DOE", $crow["iscorp"]) . ": " . $crow["companyname"] . "\n" . getCompanyAddress($id) . "\n{$crow['schoolphone']}\n happened at : " . date("Y-m-d H:i") . "\n\n $newsupply", "info@emergencyskills.com");
    }
}

?>
<?php include "ssi/top.php"; ?>
<script language='javascript'>
    function toggleDiv(element) {
        var href = document.getElementById(element + "href");
        if (document.getElementById(element).style.display == 'none') {
            document.getElementById(element).style.display = 'block';
            href.innerHTML = "v";
        } else if (document.getElementById(element).style.display == 'block') {
            document.getElementById(element).style.display = 'none';
            href.innerHTML = ">";
        }
    }

    function toggleDivOther(element) {
        if (document.getElementById(element).style.display == 'none') {
            document.getElementById(element).style.display = 'block';
        } else if (document.getElementById(element).style.display == 'block') {
            document.getElementById(element).style.display = 'none';
        }
    }
</script>
<form method='post' enctype='multipart/form-data'>

    <br>

    <?php
    $row = getCompanyRow($id);
    // Fix: Avoid warnings for undefined array keys

    // if (!$row) {
    //     $row = [
    //         'id' => 0, 'companyname' => '', 'address' => '', 'floor' => '', 'city' => '', 'state' => '', 'zip' => '', 
    //         'related_company' => 0, 'iscorp' => 0, 'inspectionfrequency' => 0, 'bic' => 0, 'retired' => 0, 'deleted' => 0,
    //         'displayname' => '', 'accountmanager' => '', 'campusid' => 0, 'region' => '', 'borough' => '', 'schoolcode' => '',
    //         'iscoolingcenter' => 0, 'isala' => 0, 'buildingno' => '', 'isheadquarter' => 0, 'emailtype' => '', 'locationcode' => '',
    //         'cfn' => '', 'date' => '', 'clientrequests' => '', 'summer' => 0, 'buildingassessment' => '', 'buildingassessmentdate' => '',
    //         'contactname' => '', 'contacttitle' => '', 'contactphone' => '', 'contactphoneExtension' => '', 'contactcell' => '', 'contactemail' => '',
    //         'contact2name' => '', 'contact2title' => '', 'contact2phone' => '', 'contact2phoneExtension' => '', 'contact2cell' => '', 'contact2email' => '',
    //         'contact3name' => '', 'contact3title' => '', 'contact3phone' => '', 'contact3phoneExtension' => '', 'contact3cell' => '', 'contact3email' => '',
    //         'psalprincipalname' => '', 'psalprincipalphone' => '', 'psalprincipalemail' => '', 'annualremindersent' => '', 'companynotes' => '', 'esinotes' => '', 'parkinginfo' => '',
    //         'principalname' => '', 'principalemail' => '', 'schoolphone' => '', 'programexpirationdate' => '', 'programna' => 0,
    //         'filingexpirationdate' => '', 'municipalna' => 0, 'typeofentity' => '', 'nycentity' => '', 'ambulance' => '', 'county' => '', 'directorname' => '',
    //         'mdaddress' => '', 'mdphone' => '', 'mdfax' => '', 'medicalinvoicedate' => '', 'mdina' => 0
    //     ];
    // }

    $responderarr = getTrainersForZip($row["zip"]);
    $responder = array();
    if (is_array($responderarr)) {
        foreach ($responderarr as $r) {
            $responder[] = $r["name"];
        }
    }
    $f = db_query_rows("Select concat( first_name, ' ', last_name ) as name from user where extraschools like '%$id%'");
    if (is_array($f)) {
        foreach ($f as $namearr) {
            $responder[] = $namearr["name"];
        }
    }


    $responder = join(", ", $responder);
    ?>

    <table cellpadding="8" cellspacing="1" border="0" width="100%" class="table3">
        <tr>
            <td valign="top" bgcolor="#5a179e" colspan="2">
                <strong>
                    <font color='white'><?= getSchoolStr("School", $row["iscorp"]) ?> Information</font>
                </strong>&nbsp;&nbsp;
                <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <?php if (empty($thisusersrow["healthdirector"])) { ?>
                        <tr>
                            <td><span class="white"><strong>
                                        <blink><input type='button' name='' class='copy' value='Click Here To Request <?= getSchoolStr("School", $row["iscorp"]) ?>/<?= getSchoolStr("Campus", $row["iscorp"]) ?> Information Update' onClick='javascript:document.location.href="schoolplanupdate.php?id=<?= $id ?>"'></blink></span></td>
                            <td valign="top" bgcolor="#5a179e" align="right">
                                <?php if (!$readonly && $specialadmin) { ?> <a href="editcompany.php?id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']) ?>"><span class="white">[<?= $readonly && !$specialadmin ? "View" : "Edit" ?> Location]</span></a><br>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>

        <tr>
            <?php
            // $related_company=$row['related_company']?$row['related_company']:$row['id'];
            // $branch_rows=db_query_rows("select address,city,id from company_esi where (companyname like '".mysqli_real_escape_string( $row['companyname'] )."' or related_company='$id' or related_company='$related_company' or id='$id' or id='$related_company') and deleted=0");

            if (1 == 0) { // count($branch_rows)>1&& 
            ?>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">
                    <span class="copy">
                        <strong>Select Another Location:</strong><br>
                        <select name="id" style="font-size: 10px;  font-family: verdana;">
                            <option value=""></option>
                            <?php foreach ($branch_rows as $branch) { ?>
                                <option value="<?= $branch['id']; ?>"><?= $branch['address'] . ',' . $branch['city']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </span>&nbsp;
                    <input type="submit" value="Go!">
                <?php
            }
                ?>
                </td>
        </tr>

        <tr>
            <td valign="top" colspan='2' bgcolor="#E2DFDF" width="230"><span class="copy">
                    <a href='#aeds'>AEDs</a> ||
                    <?php if (empty($thisusersrow["healthdirector"])) { ?>

                        <?php if (!$row["iscorp"]) { ?> <a href='#drills'>Drills</a> || <?php } ?>
                        <a href='#scs'>Service Calls</a> ||
                        <?php if (!$row["iscorp"]) { ?> <a href='#areqs'>AED/Accessory Requests</a> || <?php } ?>
                        <?php if (!$row["iscorp"]) { ?> <a href='#faresps'>First Aid Responders</a> || <?php } ?>
                        <a href='#resps'>Responders</a> ||
                        <?php if (!$row["iscorp"]) { ?>
                            <a href='#supplies'>Notes to <?= getSchoolStr("the DOE", $row["iscorp"]) ?></a> ||
                        <?php } ?>
                        <a href='#users'>Users</a>
                    <?php } ?>
            </td>
        </tr>
        <tr>
            <td valign="top" bgcolor="#E2DFDF" width="230"><span class="copy">
                    <?= $err ?>
                    <?php
                    if (isOverallAdmin() || $thisusersrow["usertype"] == "trainer") {
                        $currresponders = getCurrentResponders($id);
                        if (!count($currresponders)) {
                    ?>
                            <font color='red' id="notrained">No Trained Responders!</font><br>
                        <?php }
                    }
                    if ($row["iscorp"]) {
                        $aedsexpired = db_query_first_cell("select count(*) from aed_esi where clientid = $id and aedstolen = 0 and aedmissing = 0 and deleted = 0 and outofwarranty = 0 and outofservice = 0 and ( padaexpiration < now() or ( padbexpiration < now() and padbexpiration > '2000-01-01' ) ) ");
                        if ($aedsexpired) {
                        ?>
                            <font color='red' id="padsexpired">Pads Expired!</font><br>
                        <?php
                        }
                    }
                    if ($row["inspectionfrequency"] > 0) {
                        // Check if instypes exists
                        
                        ?><font color='red'>Inspection Frequency: <?= $instypes[$row["inspectionfrequency"]] ?></font><br>
                    <?php
                    }
                    ?>
                    <?php if ($row["bic"]) { ?><font color='red'>BIC</font><br><?php } ?>
                    <?php if ($row["retired"]) { ?><font color='red'>RETIRED!</font><Br><?php } else if ($row["deleted"]) { ?><font color='red'>DELETED!</font><br><?php } ?>
                    <?php
                    // Assuming bannedzips is defined in included files
                    if (isset($bannedzips) && is_array($bannedzips) && in_array($row["zip"], $bannedzips)) { ?><font color='red'>RESTRICTED ZIP CODE</font><Br><?php } ?>
                    <?php
                    // Assuming bannedschoolids is defined
                    if (!empty($bannedschoolids[$row["id"]])) { ?><font color='red'>RESTRICTED SCHOOL ID - <font color='<?= $bannedschoolids[$row["id"]] ?>'><?= $bannedschoolids[$row["id"]] ?></font>
                        </font><Br><?php } ?>
                    <strong>Location:</strong><br>
                    <br>
                    <?= $row['companyname']; ?><br>
                    <a target='_blank' href='http://maps.google.com?q=<?= "{$row['address']}, {$row['city']} {$row['state']} {$row['zip']}" ?>'>
                        <?= $row['address']; ?>&nbsp;
                        <?= $row['floor']; ?><br>
                        <?= $row['city']; ?>,&nbsp;<?= $row['state']; ?>&nbsp;<?= $row['zip']; ?></a><br>
                    <?= $row["displayname"] ? "<br><b>Display Name:</b> {$row['displayname']} <Br>" : "" ?>
                    <?= $row["accountmanager"] ? "<b>Account Manager:</b> <a href='mailto:" . getEmail($row["accountmanager"]) . "'>" . getUserName($row["accountmanager"]) . "</a><Br>" : "" ?>
                    <?php if ($row['campusid']) { ?>
                        <?php $campus = db_query_first_cell("Select name from campus where id= {$row['campusid']}"); ?>
                        <?= getSchoolStr("Campus") ?>: <?= $campus ?><br>
                    <?php } ?>
                    <?php if ($row['region']) { ?>
                        Region: <?= $row['region'] ?> -- <?= $row['borough']; ?><br>

                        <?php if ($row["region"] == "Aging") {
                        ?>
                            <Br>Company Code:<?= $row["schoolcode"] ?><br>
                        <?php
                        }
                        ?>
                        Cooling Center?: <?= $row['iscoolingcenter'] ? "Yes" : "No" ?><br>
                        ALA: <?= $row['isala'] ? "Yes" : "No" ?><br>
                    <?php } ?>
                    <?php if ($row['buildingno']) { ?>
                        Building #: <?= $row['buildingno']; ?><br>
                    <?php } ?>
                    <br>
                    <?php if (isOverallAdmin() && $row["iscorp"]) { ?>
                        <strong><?= $row["isheadquarter"] ? "Headquarters" : "Branch" ?></strong><Br>
                        <strong>Type:</strong> <?= $row['emailtype']; ?><br>
                    <?php } ?>
                    <?php if (file_exists("schoolimages/{$id}.jpg")) { ?>
                        <a href="schoolimages/<?= $id ?>.jpg" target=_blank><img src="schoolimages/<?= $id ?>.jpg" height=30></a><br><br>
                    <?php } ?>
                    <?php if (!$row["iscorp"]) { ?>
                        <strong>Location Code:</strong><?= $row['locationcode']; ?><br>
                        <?php if ($row["locationcode"]) { ?>
                            <strong>Buildings:</strong>
                            <?php
                            $bdarr = db_query_array("select buildingcode from location_to_building where locationcode = '{$row['locationcode']}'", "buildingcode", "buildingcode");
                            echo (join(", ", $bdarr)  . "<Br>");
                            ?>
                        <?php } ?>
                        <strong>CFN:</strong><?= $row['cfn']; ?><br>
                    <?php } ?>
                    <?php if (isOverallAdmin()) { ?>
                        <?php if (!$row["iscorp"]) { ?>
                            <strong>ESI Representative:</strong> <?= $responder ?><br>
                        <?php } else { ?>
                        <!-- getUrlPrefix($row["iscorp"]) Changed to 0 to stay at same subdomain (doe/doe8) -- Sanjoy Dey  -->
                            <a id="regLink" href='https://<?= getUrlPrefix(0) ?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/individual_registration2.php?region=<?= $row["region"] ?>&setcorp=<?=$session_iscorp?>'>
    Link to Individual Registration
</a>&nbsp;


<button type="button" onclick="copyLink()">Copy Link</button><br><br>

<script>
function copyLink() {
    // Get the href attribute from the link
    const linkElement = document.getElementById('regLink');
    const url = linkElement.href;

    // Use the Clipboard API to copy the text
    navigator.clipboard.writeText(url).then(() => {
        alert("Link copied to clipboard!");
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>
                        <?php } ?>
                    <?php } ?>
                    <strong>Date Added:</strong> <?= $row["date"] > "0000-00-00" ? $row["date"] : "N/A" ?><br>
                    <?php if ($row["iscorp"]) {

                        $res = db_query_first_cell("select group_concat( distinct( model ) ) from aed_esi where clientid = '{$row['id']}' and deleted = 0 ");
                        echo ("<strong>AED Model(s):</strong> $res<br>");

                        echo ("<br><br><strong>Client Requests:</strong> " . "<font color='red'><strong>" . nl2br($row["clientrequests"]) . "</strong></font><br>");
                    ?>
                    <?php } ?>
                    <?php if (!$row["iscorp"]) { ?>
                        <strong>Summer School?</strong> <?= $row["summer"] ? "Yes" : "No" ?><br>
                        <?php if (isOverallAdmin() || $currentusertype == 'trainer') { ?>
                            <strong>Building Assessment:</strong> <input type='text' name='ba' value=""> <input type='submit' name='updatebuildingassessment' value='Update'> <?php if ($row["buildingassessmentdate"]) echo ("<Br><b>Last Updated:</b> " . date("m/d/Y", strtotime($row["buildingassessmentdate"])) . " - " . $row["buildingassessment"]); ?>
                        <?php } ?>
                        <?php
                        $retired = db_query_rows("select * from company_esi where mergedinto = '$id'");
                        if (is_array($retired) && count($retired)) {
                        ?>
                            <br><br>
                            Retired Schools: <br>
                        <?php
                            foreach ($retired as $rrow) {
                                echo ("<a href='viewcompany.php?id={$rrow['id']}'>{$rrow['companyname']}<br>");
                            }
                        }
                        ?>
                    <?php } ?>

            </td>
            <td valign="top" bgcolor="#E2DFDF">
                <span class="copy">
                    <strong>Contact:</strong><br>
                    <?= $row['contactname']; ?> <?php if (isOverallAdmin()) {
                                                    echo (", " . $row['contacttitle']);
                                                } ?> <br>
                    <?= $row['contactphone']; ?> <?php if ($row['contactphoneExtension']) print "Ext."; ?><?= $row['contactphoneExtension']; ?><br>
                    <?php if ($row['contactcell']) print "Cell: $row[contactcell]<br>"; ?>
                    <a href="mailto:<?= $row['contactemail']; ?>"><strong><?= $row['contactemail']; ?></strong></a>
                </span><br><br>
                <?php if ($row["iscorp"] || $row['contact2name']) { ?>
                    <?= $row['contact2name']; ?> <?php if (isOverallAdmin()) {
                                                    echo (", " . $row['contact2title']);
                                                } ?> <br>
                    <?= $row['contact2phone']; ?> <?php if ($row['contact2phoneExtension']) print "Ext."; ?><?= $row['contact2phoneExtension']; ?><br>
                    <?php if ($row['contact2cell']) print "Cell: $row[contact2cell]<br>"; ?>
                    <a href="mailto:<?= $row['contact2email']; ?>"><strong><?= $row['contact2email']; ?></strong></a>
                    </span><br><br>
                    <?= $row['contact3name']; ?> <?php if (isOverallAdmin() && $row['contact3title']) {
                                                    echo (", " . $row['contact3title']);
                                                } ?> <br>
                    <?= $row['contact3phone']; ?> <?php if ($row['contact3phoneExtension']) print "Ext."; ?><?= $row['contact3phoneExtension']; ?><br>
                    <?php if ($row['contact3cell']) print "Cell: $row[contact3cell]<br>"; ?>
                    <a href="mailto:<?= $row['contact3email']; ?>"><strong><?= $row['contact3email']; ?></strong></a>
                    </span><br><br>
                <?php } ?>
                <?php if (!$row["iscorp"]) { ?>
                    <span class="copy">
                        <strong>PSAL Principal:</strong><br>
                        <?= $row['psalprincipalname']; ?><br>
                        <?= $row['psalprincipalphone']; ?>
                        <a href="mailto:<?= $row['psalprincipalemail']; ?>"><strong><?= $row['psalprincipalemail']; ?></strong></a>
                    </span><br><br>
                <?php } ?>

                <?php
                if ($row["iscorp"]) {
                    if ($row["iscorp"] == 4) {
                        $next = db_query_rows("select id, startdate, accepted from class where companyid = $id and startdate > now() and accepted = 1 and deleted = 0 order by startdate");
                        $pending = db_query_rows("select id, startdate, accepted from class where companyid = $id and startdate > now() and accepted = 0 and deleted = 0 order by startdate", "id");
                        if ($next) {
                            foreach ($next as $n)
                                $pending[] = $n;
                        }
                    } else {
                        $next = db_query_first("select id, startdate, accepted from class where companyid = $id and startdate > now() and accepted = 1 and deleted = 0 order by startdate");
                        $pending = db_query_rows("select id, startdate, accepted from class where companyid = $id and startdate > now() and accepted = 0 and deleted = 0 order by startdate", "id");
                        if ($next)
                            $pending[] = $next;
                    }

                    $mostrecentarr = db_query_array("select id, startdate from class where companyid = $id and startdate < now() and accepted = 1 and deleted = 0 order by startdate desc limit 20", "id", "startdate");
                    $cnt = 0;
                    if (is_array($mostrecentarr)) {
                        foreach ($mostrecentarr as $mostrecent => $sd) {
                            if (file_exists("classcards/$mostrecent.pdf")) {
                                $cnt++;
                            }
                        }
                    }

                    echo ("<a href='#' onClick=\"javascript:toggleDiv( 'histdiv' );return false\" class='copy'> View Cards ($cnt)</a><span id='histdivhref'>></span></nobr> <div id='histdiv' style='display:none'>");
                    if (is_array($mostrecentarr)) {
                        foreach ($mostrecentarr as $mostrecent => $sd) {
                            if (file_exists("classcards/$mostrecent.pdf")) {
                                echo (" <A href='classcards/$mostrecent.pdf'>View Cards from " . getFormattedDateWTime($sd) . "</a><Br>");
                            }
                        }
                    }
                    echo ("</div><br><br>");
                } else {
                    $pending = db_query_rows("select id, startdate, accepted from class where companyid = $id and startdate > now() and deleted = 0 order by accepted desc, startdate", "id");
                }

                if ($session_userid == "sarahg@emergencyskills.com") {
                    $ltdt = db_query_first_cell("select max( datesent ) from automatedemails where emailkey like '%_company{$id}%' ");
                ?>
                    <Br><span class='copy'>Last Training Reminder Sent: <br>
                        <?= $ltdt ?>
                    </span><br>
                <?php } ?>
                <span class='copy'>Next Scheduled Training Date: <br></span>
                <?php
                if (is_array($pending)) {
                    foreach ($pending as $next) {
                        $nextclass = $next["id"];
                        $nextdate = $next["startdate"] ? date("m/d/y H:i", strtotime($next["startdate"])) : "";
                ?>
                        <?= "<a href='class_detail.php?id=$nextclass'>$nextdate</a> " . (!$next["accepted"] ? "(pending)" : "") . "<br>" ?>

                <?php }
                } ?>
                <br>
                <?php if (isOverallAdmin()) { ?>
                    <?php if ($row["iscorp"]) { ?>
                        <span class='copy'><A href='sendannualcorpemail.php?id=<?= $id ?>' target=_blank>Send Annual Training Reminder</a> <?php if ($row["annualremindersent"]) echo ("Last sent: {$row['annualremindersent']}"); ?></span><br>
                    <?php } ?>
                    <span class='copy'><A href='schedule_class.php?av_x=1&quicksched=1&companyid=<?= $id ?>&doit=1'><b>Quick</b> Schedule Class</a></span><br>
                <?php } ?>
                <?php  // Assumes $noreportsorcalendar array exists
                $session_ut = $_SESSION['usertype'];
                if ($session_ut != "trainer" && isset($noreportsorcalendar) && is_array($noreportsorcalendar) && !in_array($session_id, $noreportsorcalendar) && (empty($thisusersrow["healthdirector"]) || $id == ($thisusersrow["singleschoolid"]))) { ?>
                    <span class='copy'><A href='schedule_class.php?companyid=<?= $id ?>&doit=1'>Schedule Class</a></span><br>
                    <span class='copy'><A href='classhistory.php?id=<?= $id ?>'>Class History</a></span><br>

                <?php } ?>
                <?php if (isOverallAdmin() || $session_userid == "michele.adler@morganstanley.com") {
                    $recertnotes = db_query_first_cell("select count(*) from recertnotes where companyid = {$row['id']} and completed = 0 ");
                ?>
                    <a href='editrecertnotes.php?id=<?= $id ?>'>Recertification Notes</a> (<?= $recertnotes ?>)
                    <br><a href='writedoereport.php?companyid=<?= $id ?>'>RESMCO Report</a>
                    <br><a href='zerotrainedresponderemail.php?companyid=<?= $id ?>'>Zero Trained Responder Email</a> <?= (isset($sent) && $sent) ? "<font color='red'>Sent.</font>" : "" ?>
                <?php } ?>
            </td>
        </tr>

        <tr>
            <td valign="top" bgcolor="#E2DFDF" colspan="2">
                <span class="copy">
                    <strong>General <?= getSchoolStr("School", $row["iscorp"]) ?> Notes:</strong>
                    <br>
                    <?= nl2br(stripslashes($row['companynotes'])); ?><br>

                    <?php if (isOverallAdmin()) { ?>
                        <A href='aedreport.php?id=<?= $id ?>'>AED Report</a>
                        <br>
                        <br><b>ESI Notes:</b> <?= nl2br($row["esinotes"]) ?><br><br>
                    <?php } ?>

                    <strong>Parking Information:</strong>
                    <br><br>
                    <?= $row['parkinginfo']; ?>
                </span>
                <table border=0>
                    <tr>
                        <td valign='top'>
                            <?php if (!$row["iscorp"]) { ?>
                                <br><br><span class="copy"><b>DOE Supplied Data: </b><br>
                                    <table>
                                        <tr>
                                            <td><span class='copy'>Principal Name:</td>
                                            <td span class='copy'><?= $row["principalname"] ?></td>
                                        </tr>
                                        <tr>
                                            <td><span class='copy'>Principal Email:</td>
                                            <td span class='copy'><A href='mailto:<?= $row["principalemail"] ?>'><?= $row["principalemail"] ?></a></td>
                                        </tr>
                                        <tr>
                                            <td><span class='copy'>School Code:</td>
                                            <td span class='copy'><?= $row["schoolcode"] ?></td>
                                        </tr>
                                        <tr>
                                            <td><span class='copy'>School Phone:</td>
                                            <td span class='copy'><?= $row["schoolphone"] ?></td>
                                        </tr>
                                    </table>
                                </span>
                                <br><br>
                                <?php
                                if ($row["borough"] == "Staten Island" && (($thisusersrow["companyid"]) == $id || isOverallAdmin())) {

                                    $tmplink = "http://" . SUB_DOE . "." . URL_WITHOUT_SUBDOMAIN . "/monthlyaedchecklist.php?encryptedid=" . ($id * 1440) . "_";
                                    echo ("<b><A href='$tmplink'><font color='red'>Monthly AED Checklist</font></a></b>");
                                }
                                ?>
                            <?php }
                            $campusname = getCampusName($row["campusid"]);
                            
                            ?>
                        </td>
                        <td>&nbsp;&nbsp;</td>
                        <?php
                        // Assuming AGING is a constant
                        if ($row["iscorp"] != (defined('AGING') ? AGING : 'AGING')) { ?>
                            <td valign='top' class='copy'><b><?= getSchoolStr("Campus", $row["iscorp"]) ?>:</b> <a href='editcampus.php?backto=<?= $row["id"] ?>&campusid=<?= $row["campusid"] ?>'><?= $cname ?></a> <?php if (isOverallAdmin()) { ?><a target=_blank href='mailmerge.php?campusid=<?= $row["campusid"] ?>'>Mail Merge</a><?php } ?><Br><br>
                                <b>Other <?= getSchoolStr("Schools", $row["iscorp"]) ?> In This <?= getSchoolStr("Campus", $row["iscorp"]) ?>:</b> <Br>

                                <?php
                                //              echo( $visi );
                                //echo( $row[campusid] );
                                $scho = getSchoolsInCampus($row["campusid"], $row["id"]);
                                // echo( "<a href='#' onClick=\"javascript:toggleDiv( 'histdiv' );return false\"> View (".count( $scho).")</a><span id='histdivhref'>></span></nobr> <div id='histdiv' style='display:none'>" );
                                if (is_array($scho))
                                    foreach ($scho as $s) {
                                        $currresponders = getCurrentResponders($s["id"]);
                                        $count_resp = is_array($currresponders) ? count($currresponders) : 0;
                                        $numtr = "($count_resp trained responder" . ($count_resp == 1 ? "" : "s") . ")";
                                        $otherclasses = db_query_array("select id, startdate from class where companyid = '{$s['id']}' and startdate > now() and accepted = 1", "id", "startdate");
                                        $cl = "";
                                        if (is_array($otherclasses) && count($otherclasses)) {
                                            foreach ($otherclasses as $o => $odate) {
                                                $cl .= $cl ? ", " : "(";
                                                if (isOverallAdmin())
                                                    $cl .= "<a href='class_detail.php?id=$o'>$odate</a>";
                                                else
                                                    $cl .= "$odate";
                                            }
                                            if ($cl)
                                                $cl .= ")";
                                        }
                                        if (!$count_resp)
                                            $numtr = "<font color='red'>$numtr</font>";
                                        if (isOverallAdmin() || strtolower($session_userid) == "cmcgee3@schools.nyc.gov" || strtolower($session_userid) == "hthomps@schools.nyc.gov") {
                                            if ($row["iscorp"])
                                                echo ("<a href='viewcompany.php?id={$s['id']}'>{$s['companyname']} - {$s['address']}</a> <b>$numtr</b> $cl<br>");
                                            else
                                                echo ("<a href='viewcompany.php?id={$s['id']}'>{$s['companyname']}</a> <b>$numtr</b> $cl<br>");
                                        } else {
                                            if ($row["iscorp"])
                                                echo ("{$s['companyname']} - {$s['address']} <b>$numtr</b> $cl<br>");
                                            else
                                                echo ("{$s['companyname']}</a> <b>$numtr $cl<br>");
                                        }
                                    }
                                //echo("</div>" );
                                ?>
                            </td>
                    </tr>
                </table>
            </td>
        <?php } ?>
        </tr><br>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>
        <?php if (empty($thisusersrow["healthdirector"])) { ?>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <span class="white"><strong>Cardiac Emergency Response Plan</strong></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">
                    <span class="white"><strong><a href="#" onClick="MyWindow=window.open('response_plan<?= $row["iscorp"] ? "_corp" : "" ?>.php?id=<?= $row['id'] ?>','MyWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500'); return false;">Print Cardiac Emergency Response Plan</a></strong></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">
                    <span class="white"><a href='viewcompany.php?id=<?= $id ?>&sendall=1'>Re-send all current responders</a></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
            </tr>
        <?php } ?>
        <?php if ($row["iscorp"]) { ?>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <span class="white"><strong>Medical Direction</strong></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">

                    <span><strong>Medical Director Name:</strong> <?= $row["directorname"] ?></span><br>
                    <span><strong>MD Address:</strong> <?= $row["mdaddress"] ?></span><br>
                    <span><strong>MD Phone:</strong> <?= $row["mdphone"] ?></span><br>
                    <span><strong>MD Fax:</strong> <?= $row["mdfax"] ?></span><br>
                    <span><strong>Medical Direction Invoice Date:</strong> <?= $row["medicalinvoicedate"] ?> <?= $row["mdina"] ? "N/A" : "" ?></span><br>
            </tr>
            <tr>
                <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <span class="white"><strong>Program Management</strong></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">
                    <span><strong>Expiration Date:</strong> <?= getFormattedDate($row["programexpirationdate"]) ?> <?= $row["programna"] ? "N/A" : "" ?></span><br>
            </tr>
            <tr>
                <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <span class="white"><strong>Municipal Filing</strong></span>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2">
                    <span><strong>Filing Expiration Date:</strong> <?= getFormattedDate($row["filingexpirationdate"]) ?> <?= $row['municipalna'] ? "N/A" : "" ?></span><br>
                    <span><strong>Type of Entity:</strong> <?= $row["typeofentity"] ?></span><br>
                    <span><strong>NYC Local Law Entity:</strong> <?= $row["nycentity"] ?></span><br>
                    <span><strong>Ambulance:</strong> <?= $row["ambulance"] ?></span><br>
                    <span><strong>County:</strong> <?= $row["county"] ?></span><br>

                    <?php if (isOverallAdmin()) { ?> <br>
                        <span class="white"><strong><input type='file' name='mf' value=''> <input type='submit' name='uploadmf' value='Upload New'></span><?php } ?>
                    <?php if (file_exists("mfs/$id.pdf")) { ?><br>
                        <A href='mfs/<?= $id ?>.pdf'>View Current</a>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
            </tr>

        <?php } ?>
        <tr>
            <td valign="top" bgcolor="#5a179e" colspan="2">
                <a name='aeds'></a>
                <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td><span class="white"><strong>AED Serial Numbers</strong></span></td>
                        <td valign="top" bgcolor="#5a179e" align="right"><?php if (!$readonly && $specialadmin) { ?>
                                <a href='monthlyaedchecklist.php?id=<?= $row["id"] ?>'><span class='white'>[mc]</span></a> <a href='monthlyaedchecklistdata.php?id=<?= $row["id"] ?>'><span class='white'>[mc data]</span></a>
                                <a href="editaed.php?id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']); ?>">
                                    <span class="white">[Add an AED]</span></a> <?php } ?> <?php if ((!$readonly && $specialadmin) || 1) { ?><a href='editpsals.php?id=<?= $id ?>'><span class='white'>[Track PSAL/SSAL AEDs]</span></a><?php } ?><?php if ((!$readonly && $specialadmin)) { ?><a href='principalaeds.php?id=<?= $id * 1234 ?>'><span class='white'>[Principal Email]</span></a><?php } ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>


        <?php include "viewaeds.php"; ?>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>
        <?php if (isOverallAdmin() || strtolower($session_userid) == "cmcgee3@schools.nyc.gov" || strtolower($session_userid) == "hthomps@schools.nyc.gov") {  ?>
            <tr bgcolor="#5a179e">
                <td colspan='2'><span class="white"><strong>STOLEN AED Serial Numbers</strong></span></td>
            </tr>

            <?php
            $onlystolen = 1;
            include "viewaeds.php"; ?>
        <?php } ?>
        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>
        <?php if (!$row["iscorp"]) {

            $hasopen = db_query_first_cell("select drill.drillid from drill left join drill_to_companyid dtc on drill.drillid = dtc.drillid where ( dtc.companyid = '" . $id . "' or drill.companyid ='" . $id . "') and completed = 0");

        ?>
            <script language='javascript'>
                function checkNewDrill() {
                    <?php if ($hasopen) { ?>
                        if (confirm("Are you sure you want to request a new drill worksheet? There is already an open one."))
                        <?php } ?>
                        document.location.href = "drillinspection.php?companyid=<?= $id ?>&newdrill=true";
                }
            </script>
            <?php if (empty($thisusersrow["healthdirector"])) { ?>
                <tr>
                    <td valign="top" bgcolor="#5a179e" colspan="2">
                        <a name='drills'></a>
                        <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td><span class="white"><strong>Drills</strong> <?php if ($currentusertype == "trainer" || $specialadmin) { ?> <input type='button' class='copy' name='' value='Request New Drill Worksheet' onClick='javascript:checkNewDrill()'>
                                        <?php } ?></td>
                                <td valign="top" bgcolor="#5a179e" align="right"><?php if (!$readonly && $specialadmin) { ?><a href="editdrill.php?id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']); ?>">
                                            <span class="white">[Add a Drill]</span></a><br>
                                        <?php if ($session_userid == "sarahg@emergencyskills.com" || $session_userid == "rebekah@emergencyskills.com" || $session_userid == "noah@emergencyskills.com") { ?>

                                            <a onClick='return confirm( "Are you sure you want to delete ALL unused drills and service calls for this <?= getSchoolStr("school", $row["iscorp"]) ?>?" )' href='viewcompany.php?id=<?= $row['id'] ?>&delunused=1'><span class="white">[Delete Unused Drills and Service Calls]</span></a>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>



                <tr>
                    <td colspan="2" bgcolor="#E2DFDF">
                        <?php
                        $extsearch = "";
                        if ($thisusersrow["companyid"] > 0)
                            $extsearch = " and completed = 1";
                        $aed_rows = db_query_rows("select drill.* from drill left join drill_to_companyid dtc on drill.drillid = dtc.drillid where ( dtc.companyid = '" . $id . "' or drill.companyid ='" . $id . "') $extsearch group by drill.drillid order by drilldate desc ");
                        ?>
                        <table width="95%" border="0">
                            <tr>
                                <td>
                                    <?php
                                    foreach ($aed_rows as $aed) {
                                    ?>
                                        <span class="copy">
                                            <strong>
                                                <a href="editdrill.php?drillid=<?= $aed['drillid']; ?>&id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']) ?>"><strong>D<?= $aed['drillid'] ?> <?= $aed["companyid"] != $id ? "c" : "" ?>: <?= $aed['drilldate'] ? $aed['drilldate'] : "N/A"; ?></strong></a>
                                                <?php
                                                if (!$aed["completed"]) {
                                                    echo ("<a href='drillinspection.php?companyid=$id&drillid=" . $aed["drillid"] . "'>(worksheet)</a> ");
                                                    // if( isOverallAdmin() && !$aed["received"] ) {
                                                    //     echo( "<table><tr><td>Date:</td><td> ".printdates2( "ddate[$aed[drillid]]", date( "Y-m-d" ), true )."</td>" );
                                                    //                        echo( "<td><input type='submit' name='markreceived[$aed[drillid]]' value='Mark Received'> </td>" );
                                                    //                    echo( "</tr></table>" );
                                                    //                    }

                                                }
                                                $status = "";
                                                if ($aed["isdone"])
                                                    $status =      "(Done)";
                                                if ($aed["shipped"])
                                                    $status =      "(Shipped)";
                                                if ($aed["received"])
                                                    $status =      "(Received)";
                                                if ($aed["completed"] == 1)
                                                    $status =  "";
                                                echo ($status);
                                                ?>
                                                <br>
                                        </span>
                                        </strong>
                                    <?php
                                    }
                                    //exit;
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
        <?php if (empty($thisusersrow["healthdirector"])) { ?>
            <tr>
                <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <a name='scs'></a>
                    <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><span class="white">
                                    <nobr><strong>Service Calls</strong> <?php if ($currentusertype == "trainer" || $specialadmin) { ?> <input type='button' class='copy' name='' value='Request New Service Call Worksheet' onClick='javascript:document.location.href="servicecallsheet.php?companyid=<?= $id ?>&newservicecall=true"'>

                                            <input type='button' class='copy' name='' value='Request New Installation Worksheet' onClick='javascript:document.location.href="servicecallsheet.php?companyid=<?= $id ?>&newinstall=true"'>

                                        <?php } ?>
                                    </nobr><?php if (!$readonly && $specialadmin) { ?><a href="editservicecall.php?id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']); ?>">
                                            <span class="white">[Add a Service Call]</span></a><?php } ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>



            <tr>
                <td colspan="2" bgcolor="#E2DFDF">
                    <?php
                    $extsearch = "";
                    $aed_rows = db_query_rows("select * from servicecall where companyid = '" . $row['id'] . "' and fromdrill = 0 $extsearch order by servicecalldate desc");
                    ?>
                    <table width="95%" border="0">
                        <tr>
                            <td>
                                <table>
                                    <?php
                                    foreach ($aed_rows as $arow) {
                                        $myaeds = getAedsForServiceCall($arow["servicecallid"]);

                                    ?>
                                        <tr>
                                            <td>
                                                <span class="copy">
                                                    <strong>
                                                        <a href="editservicecall.php?servicecallid=<?= $arow['servicecallid']; ?>&id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']) ?>"><strong><?= !empty($arow["newinstall"]) ? "NI" : "S" ?><?= $arow['servicecallid'] ?>: <?= $arow['servicecalldate'] ? $arow['servicecalldate'] : "N/A"; ?></strong></a>
                                                        <?php if (!$arow["completed"]) {
                                                            echo ("(Requested but not completed) <a href='servicecallsheet.php?companyid=$id&servicecallid=" . $arow["servicecallid"] . "'>(worksheet)</a>");
                                                        } ?>

                                                        <br>
                                                </span>
                                                </strong>
                                            </td>
                                            <td class=copy><?= join(", ", $myaeds) ?></td>
                                        </tr>
                                    <?php
                                    }
                                    //exit;
                                    ?>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>


        <?php } ?>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>


        <?php if (!$row["iscorp"]) { ?>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <a name='areqs'></a>
                    <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><span class="white"><strong>AED/Accessory Requests</strong></td>
                            <td valign="top" bgcolor="#5a179e" align="right"><?php if ((!$readonly && $specialadmin) || $session_userid == "hthomps@schools.nyc.gov" || $session_id == "3349" || strtolower($session_userid) == "cmcgee3@schools.nyc.gov") { ?><a href="editaccessoryrequest.php?id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']); ?>">
                                        <span class="white">[Add an AED/Accessory Request]</span></a><?php } ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>



            <tr>
                <td colspan="2" bgcolor="#E2DFDF">
                    <?php
                    if ($thisusersrow["companyid"] > 0)
                        $extsearch = " and completed = 1";
                    $aed_rows = db_query_rows("select accessoryrequests.* from accessoryrequests where companyid = $id $extsearch order by requestdate desc ");
                    ?>
                    <table width="95%" border="0">
                        <tr>
                            <td>
                                <?php
                                foreach ($aed_rows as $aed) {
                                ?>
                                    <span class="copy">
                                        <strong>
                                            <a href="editaccessoryrequest.php?accessoryrequestid=<?= $aed['id']; ?>&id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']) ?>"><strong>AR<?= $aed['id'] ?> <?= $aed['requestdate'] ? $aed['requestdate'] : "N/A"; ?></strong> - <?= $aed['completed'] ? "" : "OPEN"; ?></a>
                                            <br>
                                    </span>
                                    </strong>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
            </tr>
        <?php } ?>



        <tr>
            <td valign="top" bgcolor="#5a179e" colspan="2">
                <a name='resps'></a>
                <?php

                // Assuming donthide is initialized properly or checking isset
                if (isset($donthide) && $donthide) {
                    $tmpresponder_rows = getResponders($id);
                } else {
                    $tmpresponder_rows = getCurrentResponders($id);
                }

                $responder_rows = array();
                // let's remove people who have never been trained
                foreach ($tmpresponder_rows as $r) {
                    $mostcurrent = db_query_first("Select responder_training_dates.*, class.code from responder_training_dates left join class on classid = class.id where responderid = {$r['responderid']} order by trainingdate desc");
                    $anyclasses = db_query_first_cell("Select count(*) from responder_to_class where responderid = {$r['responderid']} ");
                    // 
                    if (!$anyclasses && empty($mostcurrent["trainingdate"]))
                        continue;

                    $responder_rows[] = $r;
                }

                ?>
                <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td><span class="white"><strong>Trained Responders (<span id='respcount'></span>)</strong> </span></td>
                        <td valign="top" bgcolor="#5a179e" align="right">
                            <?php if (!$readonly && $specialadmin) { ?>
                                <a href="editresponder.php?id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?id=' . $row['id']); ?>"><span class="white">[Add Responder]</span></a>
                            <?php } ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>



        <tr>
            <td colspan="2" bgcolor="#E2DFDF">
                <?php $firstaid = false; ?>
                <?php $pagename = "viewcompany.php"; ?>
                <?php include "viewresponders.php"; ?>

            </td>
        </tr>


        <?php if (!$session_iscorp) { ?>

            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <a name='faresps'></a>

                    <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><span class="white"><strong>First Aid Responders (<span id='farcount'></span>)</strong> </span></td>
                            <td valign="top" bgcolor="#5a179e" align="right">
                                <?php if (!$readonly && $specialadmin) { ?>
                                    <a href="editresponder.php?id=<?= $row['id']; ?>&redirect=<?= urlencode($_SERVER['PHP_SELF'] . '?id=' . $row['id']); ?>"><span class="white">[Add Responder]</span></a>
                                <?php } ?>

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>



            <tr>
                <td colspan="2" bgcolor="#E2DFDF">
                    <?php $firstaid = true; ?>
                    <?php $pagename = "viewcompany.php"; ?>
                    <?php include "viewresponders.php"; ?>
                    <?php $firstaid = false; ?>

                </td>
            </tr>

        <?php } // end first aid responders
        ?>

        <?php if (!$row["iscorp"] && (isOverallAdmin() || $session_userid == "hthomps@schools.nyc.gov" || $session_id == "3349" || strtolower($session_userid) == "cmcgee3@schools.nyc.gov" || $currentusertype == "trainer")) { ?>

            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <a name='supplies'></a>
                    <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><span class="white"><strong>Notes to the DOE</strong></td>
                            <td valign="top" bgcolor="#5a179e" align="right">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2" bgcolor="#E2DFDF">
                    <?php $pagename = "viewcompany.php"; ?>
                    <?php include "viewsupplyrequests.php"; ?>

                </td>
            </tr>
        <?php } ?>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>
        <?php if (empty($thisusersrow["healthdirector"])) { ?>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <a name='users'></a>
                    <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><span class="white"><strong>Signed Up Users</strong></span></td>
                            <td valign="top" bgcolor="#5a179e" align="right">
                                <?php
                                $extra = " and companyid = $id";

                                $trainers = db_query_rows("select user.* from user where usertype = 'principal' $extra order by last_name, first_name");
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            <tr>
                <td colspan="2" bgcolor="#E2DFDF">
                    <table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
                        <tr bgcolor="#e1e1f6">
                            <th class='copy'>Name</th>
                            <?php 
                            // if (strtolower($session_userid) == "sarahg@emergencyskills.com") { 
                            ?>
                            <!--<th class='copy'>Password</th>-->
                            <?php 
                            // } 
                            ?><th class='copy'>Login</th>
                            <th class='copy'><?= getSchoolStr("School") ?></th>
                        </tr>
                        <?php
                        foreach ($trainers as $t) {
                            echo ("<tr bgcolor='#ffffff'><td class='copy' valign='top'>" . $t["first_name"] . " " . $t["last_name"] . "</a></td>" . 
                            // (strtolower($session_userid) == "sarahg@emergencyskills.com" ? "<td valign='top' class='copy'><a href='edituser.php?id={$t['id']}'>{$t['password']}</a></td>" : "") .
                            "<td valign='top' class='copy'><a href='edituser.php?id={$t['id']}'>" . $t["userid"] . "</a></td><td class='copy'> ");
                            echo (getCompanyName($t["companyid"])  . "</td>");
                            echo ("</tr>");
                        }
                        ?>
                    </table>
                    <p>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>
        <?php if (!$row["iscorp"] && empty($thisusersrow["healthdirector"])) {  ?>
            <tr>
                <td valign="top" bgcolor="#5a179e" colspan="2">
                    <table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><span class="white"><strong>Possible <?= getSchoolStr("Schools", $row["iscorp"]) ?> in the Same Building</strong></span></td>
                            <td valign="top" bgcolor="#5a179e" align="right">
                                <?php
                                $schools = db_query_rows("select * from company_esi where borough = '{$row['borough']}' and address like '" . mysql_escape_string($row["address"]) . "' and city like '{$row['city']}' and id <> '$id' and deleted = 0 order by companyname");
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            <tr>
                <td colspan="2" bgcolor="#E2DFDF">
                    <table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
                        <tr bgcolor="#e1e1f6">
                            <th class='copy'>Name</th>
                            <th class='copy'>Address</th>
                        </tr>
                        <?php
                        foreach ($schools as $t) {
                            $address =  $t["address"] . ", " . $t["city"] . ", " . $t["zip"] . ", " . $t["borough"];
                            echo ("<tr bgcolor='#ffffff'><td class='copy' valign='top'><a href='viewcompany.php?id={$t['id']}'>" . $t["companyname"] . "</a></td><td valign='top' class='copy'>" . $address . "</td>");
                            echo ("</tr>");
                        }
                        ?>
                    </table>
                    <p>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>
        <?php if (empty($thisusersrow["healthdirector"])) { ?>
            <tr>
                <td valign="top" bgcolor="#E2DFDF" colspan="2"><span class='white'><strong>
                            <?php if (!$row["iscorp"]) { ?>
                                <a href="reportaeds.php?id=<?= $row['id']; ?>&company=<?= urlencode($row['companyname']); ?>&order=order by serial">AEDs by Serial Number</a>
                                <a href="reportaeds.php?id=<?= $row['id']; ?>&company=<?= urlencode($row['companyname']); ?>&order=order by serial&xls=true">(xls)</a><br>
                                <a href="reportrespondersgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>">All Responders in Group</a>
                                <a href="reportrespondersgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>&xls=true">(xls)</a><br>
                            <?php } else { ?>
                                <a href="reportaedsgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>&order=order by serial">AEDs by Serial Number</a>
                                <a href="reportaedsgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>&order=order by serial&xls=true">(xls)</a><br>

                                <a href="reportrespondersgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>">All Responders in Group</a>
                                <a href="reportrespondersgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>&xls=true">(xls)</a><br>
                                <a href="reportscgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>">All Service Calls For Group</a>
                                <a href="reportscgroup.php?id=<?= $row['id']; ?>&cid=<?= $row['campusid']; ?>&company=<?= urlencode($row['companyname']); ?>&xls=true">(xls)</a><br>
                            <?php } ?>
                </td>
            </tr>
        <?php } ?>

        <tr>
            <td valign="top" bgcolor="#ffffff" colspan="2"><img src="images/dotclear.gif" height="3"></td>
        </tr>
    </table>
    <br><br>
    <?php include "ssi/footer.php"; ?>
    </span>
    </td>
    <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
    </tr>
    </table>
    <br><br>
</form>
</body>

</html>