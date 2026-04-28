<?php 

// Placeholder for db_escape_string function for safety
if (!function_exists('db_escape_string')) {
    function db_escape_string($str) {
        // In a real application, this should use the active database connection's escape function.
        return str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$str);
    }
}

require "mysql.php";

if (!empty($toaccept) && is_array($toaccept))
{
    foreach ($toaccept as $tmpid => $accept)
    {
        $tmpid_safe = (int)$tmpid;
        $accept_safe = (int)$accept;

        if ($tmpid_safe > 0) {
            $therow = db_query_first("SELECT * FROM requesttotrain WHERE id = $tmpid_safe");

            if (!$therow) continue; 

            if ($accept_safe == 1)
            {
                // Accept: adds the trainer to class and sends the confirm email
                sendTrainerConfirmEmail($therow['trainerid'], getClassRow($therow['classid']));
            }
            else if ($accept_safe == -1)
            {
                // Deny: Sends a 'Not Booked' email
                $subject = "Not Booked";
                $crow = getClassRow($therow['classid']);
                $company = getCompanyRow($crow['companyid']);
                
                // Assuming $allclass_names is available globally
                $class_names = $allclass_names[$company['iscorp']];
                
                $newbod = "Thank you, this class has already been booked. Your name will stay on file if the status of this class changes.
Date: " . (date("m/d/Y h:i a", strtotime($crow['startdate']))) . " " . getEndDateStr($crow['enddate']) . "
Class: " . htmlspecialchars($class_names[$crow['code']] ?? 'N/A') . "
" . getSchoolStr("School", $company['iscorp']) . ": " . htmlspecialchars($company['companyname']) . " (" . htmlspecialchars($company['schoolcode']) . ")
";
                sendMail(getEmail($therow['trainerid']), $subject, $newbod, "info@emergencyskills.com");
            }
            // Update request status
            db_query("UPDATE requesttotrain SET done = $accept_safe WHERE id = $tmpid_safe");
        }
    }
    $err = "<font color='red'>Trainers assigned/updated.</font>";
}

// 3. Process 'cc' (Remove Class From List)
if ($cc)
{
    $cc_safe = (int)$cc;
    db_query("UPDATE class SET lasttrainerreqdate = NULL WHERE id = $cc_safe");
}

// 4. Set up sorting
$orderby_safe = db_escape_string($orderby);
if ($orderby_safe == "alpha") {
    $obstr = "company_esi.companyname, class.id";
} else if ($orderby_safe == "date") {
    $obstr = "startdate";
} else {
    $obstr = "company_esi.companyname, class.id";
    $orderby = "alpha";
}

$alreadytrain = array();
$trainerarr = array();

include "ssi/top.php";
?>
<h3>Open Trainer Requests</h3>
<?= $err ?>
<!-- <form method='post' action='trainerrequests.php'> -->

<?php

if ($orderby_safe == "date")
    {
        echo "<form method='post' action='trainerrequests.php?orderby=date'>";
    }
    else{
        echo "<form method='post' action='trainerrequests.php'>";
    }
    ?>

<!-- Sorting Links -->
<?php if ($orderby == "alpha") { ?>
    <a href='trainerrequests.php?orderby=date<?= $noempty ? '&noempty=1' : '' ?>'>Order By Date</a>
<?php } else { ?>
    <a href='trainerrequests.php?orderby=alpha<?= $noempty ? '&noempty=1' : '' ?>'>Order By Company Name</a>
<?php } ?>
||

<!-- Empty Class Toggle -->
<?php if ($noempty) { ?>
    <a href='trainerrequests.php?orderby=<?= htmlspecialchars($orderby) ?>'>Show Classes with no Requests</a>
<?php } else { ?>
    <a href='trainerrequests.php?orderby=<?= htmlspecialchars($orderby) ?>&noempty=1'>Hide Classes with no Requests</a>
<?php } ?>

<input type='submit' name='assignthesetrainers' value='Assign These Trainers' class='button-primary'><br><br>

<table border=1 cellpadding=4 cellspacing=0 class="table3">
<tr><th>Class</td><th># </td><th>Requested Trainers</td></tr>
<?php

$twentyfour = time() - 24 * 60 * 60; // Timestamp for 24 hours ago

// 5. Query Classes based on requests status
$cids1 = []; 
if (!$noempty) {
    $cids1_sql = "
        SELECT companyname, class.id, lasttrainerreqdate, startdate 
        FROM class, company_esi 
        WHERE class.companyid = company_esi.id 
        AND class.deleted = 0 
        AND lasttrainerreqdate IS NOT NULL 
        AND startdate > NOW() 
        AND class.id NOT IN (SELECT DISTINCT(classid) FROM trainer_to_class) 
        AND class.id NOT IN (SELECT DISTINCT(classid) FROM requesttotrain WHERE done = 0) 
        ORDER BY $obstr
    ";
    // Assuming db_query_rows returns an associative array keyed by the second argument
    $cids1 = db_query_rows($cids1_sql, "id");
}

$cids2_sql = "
    SELECT companyname, classid, startdate, lasttrainerreqdate 
    FROM class, requesttotrain, company_esi 
    WHERE class.companyid = company_esi.id 
    AND class.id = classid 
    AND done = 0 
    ORDER BY $obstr
";
$cids2 = db_query_rows($cids2_sql, "classid");


// 6. Merge and Sort Class IDs, grouping by 24-hour status
$notlast24 = array();
$last24 = array();

foreach (array($cids1, $cids2) as $tmpcids)
{
    foreach ($tmpcids as $cid => $reqrow)
    {
        // Use a unique key for ksort based on sort order
        $key = ($orderby == "date") ? ($reqrow["startdate"] . "_" . $cid) : ($reqrow["companyname"] . "_" . $cid);
        $reqdate = $reqrow["lasttrainerreqdate"];
        
        if (!$reqdate || strtotime($reqdate) < $twentyfour)
        {
            $notlast24[$key] = $cid;
        }
        else
        {
            $last24[$key] = $cid;
        }
    }
}
ksort($notlast24);
ksort($last24);

// Combine, putting 'Other' (older) first, then 'Last 24 Hours' (newer)
$cids = array_merge($notlast24, $last24);

$lastwas24 = -1; // -1: initial, 0: in 'Other' group, 1: in '24 Hours' group

foreach ($cids as $cid)
{
    $crow = getClassRow($cid);
    $comrow = getCompanyRow($crow['companyid']);

    // Fetch requested trainers for this class
    $people = db_query_rows("SELECT id, trainerid, previouslydenied FROM requesttotrain WHERE classid = " . $cid . " AND done = 0 ORDER BY requestdate", "id");

    // Get already assigned trainers
    $alreadyt = getTrainers($cid);
    $numalready = count($alreadyt);
    $arr = [];

    // Include TCFaculty if set
    if ($crow['tcfacultyid']) {
        $alreadyt[] = db_query_first("SELECT * FROM user WHERE id = " . $crow['tcfacultyid']);
    }

    foreach ($alreadyt as $tmprow)
    {
        if ($tmprow) {
            $arr[] = htmlspecialchars($tmprow['first_name'] . " " . $tmprow['last_name']);
        }
    }
    $alreadytstr = join(", ", $arr);
    if ($alreadytstr) {
        $alreadytstr = "<br><span style='font-weight: bold; color: #555;'>Already Assigned:</span> " . $alreadytstr;
    }

    // Separator logic based on 24-hour request date
    $is_last_24 = $crow['lasttrainerreqdate'] && strtotime($crow['lasttrainerreqdate']) > $twentyfour;

    if ($lastwas24 < 1 && $is_last_24) {
        // Transition to 'Last 24 Hours' section
        echo( "<tr><td colspan='3'><b>Request Sent Within the Last 24 Hours</b></td></tr>" );
        $lastwas24 = 1;
    } else if ($lastwas24 == -1 && !$is_last_24) {
        // Start of 'Other' (older) section
        echo( "<tr><td colspan='3'><b>Other</b></td></tr>" );
        $lastwas24 = 0;
    }

    // Display class status
    $pen = "";
    if (!$crow['accepted']) {
        $pen = "<b><font color='red' style='font-size:16px'>Pending</font></b><br>";
    }

    $can = $crow["canceldate"] ? "<font color='red'>Cancelled</font>" : "";
    $company_name_safe = getCompanyNameWithColorString($comrow, true);
    $class_name_safe = htmlspecialchars($allclass_names[$comrow['iscorp']][$crow['code']]);

    // Start Class Row
    echo ("<tr><td valign='top' style='background-color: #ffffff;'>");

    // Class Link & Name
    echo ("<a target=_blank href='class_detail.php?id=$cid'>$cid</a> - $class_name_safe $can<br>");
    echo $pen;

    // Company Link
    echo ("<a target=_blank href='viewcompany.php?id=" . htmlspecialchars($comrow['id']) . "'>" . $company_name_safe . "</a><br>");

    // Requested Trainer (from system)
    if ($crow["trainerreq"]) {
        echo ("<i><font color='red'>Requested: " . htmlspecialchars(getUserName($crow['trainerreq'])) . "</font></i><br>");
    }

    // Location
    if ($crow["remote"]) { 
        echo ("<b>Remote Class</b><br>");
    } else if ($crow["training_location"]) { 
        $address = getTrainingAddress($crow);
        echo ("<a target='_blank' href='http://maps.google.com?q=" . urlencode($address) . "'>" . nl2br(htmlspecialchars($address)) . "</a><br>");
    } else {
        $address_map = urlencode($comrow['address'] . ", " . $comrow['city'] . " " . $comrow['state'] . " " . $comrow['zip']);
        echo ("<a target='_blank' href='http://maps.google.com?q=$address_map'>
" . htmlspecialchars($comrow['address']) . "<br>
" . htmlspecialchars($comrow['city'] . " " . $comrow['zip']) . " <br>
    </a>
");
    }

    // Class Info
    $hours = hoursInClass($crow, $comrow['iscorp']);
    $start_date_str = getFormattedDate($crow['startdate']);
    $start_datetime_str = getFormattedDateWTime($crow['startdate'], true);
    $day_of_week = date('l', strtotime($start_date_str));

    echo ("
Class Date: $day_of_week $start_datetime_str<br>
Borough: " . htmlspecialchars($comrow['borough']) . "<br>");

    if ($crow['blendedlearning']) {
        echo ("Blended: Yes<br>");
    }

    if (!$crow['accepted']) {
        echo ("Pending Notes: " . htmlspecialchars($crow['pendingnotes']) . "<br>");
    }

    echo ("
Sent: " . ($crow['lasttrainerreqdate'] ? getFormattedDateWTime($crow['lasttrainerreqdate'], true) : "Never") . "<br>
$alreadytstr<br>
<font color='green'>Total Hours: $hours</font>"
    );

    // Remove Class Link (Confirmation removed for environment constraint)
    if (!count($people)) {
        echo ("<br><a href='trainerrequests.php?orderby=" . htmlspecialchars($orderby) . "&cc=$cid' style='color: blue; text-decoration: underline;' 
        title='Clicking this will remove the class from the Open Requests list immediately.'>Remove Class From List</a>");
    }
    echo ("</td>");

    // # Trainers Needed
    $trainers_needed = (int)$crow['numtrainers'] - $numalready;
    echo ("<td valign='top' >$trainers_needed</td>");

    // Requested Trainers List
    echo ("<td valign='top'><table>");

    $showaccept = max(0, $trainers_needed); // Max number of slots remaining

    foreach ($people as $tmpid => $peoplerow)
    {
        $pid = $peoplerow["trainerid"];

        // Caching trainer details
        if (!isset($trainerarr[$pid])) {
            $trow = getUserRow($pid);
            $trainerarr[$pid] = $trow;
            // Fetch number of upcoming classes (alreadytrain)
            $comingup = db_query_first_cell("SELECT COUNT(*) FROM trainer_to_class ttc, class WHERE ttc.trainerid = " . $pid . " AND classid = class.id AND class.startdate > NOW() AND class.canceldate IS NULL");
            $alreadytrain[$pid] = $comingup;
        } else {
            $comingup = $alreadytrain[$pid];
            $trow = $trainerarr[$pid];
        }

        if (!$trow || !$trow['id']) { continue; } // Skip if user row is not found or invalid

        // Check availability/denials
        $avon = availableOn($crow['startdate'], $pid, $cid);
        $ab = ($avon == 2 || $avon == 3) ? "<font color='red'>(AB)</font>" : ""; // AB: Already Booked/Unavailable
        if ($crow['tcfacultyid'] == $trow['id']) $ab = "<font color='red'>(AB)</font>"; 

        $col = needsMonitoring($trow['id']); // Assumed to return an HTML style string
        $thisweek = hoursThisWeek($trow['id'], $crow);
        $stage = $trow['instructorstage'] ?? '';
        if ($stage == "Completed") $stage_display = "C"; else $stage_display = htmlspecialchars($stage);

        if ($peoplerow["previouslydenied"]) $ab .= " (previously denied)";

        // Output trainer request row
        echo ("<tr><td style='border: none;'><nobr>");
        echo ("<a $col href='trainer_view.php?tid=" . htmlspecialchars($trow['id']) . "'>" . htmlspecialchars($trow['first_name'] . " " . $trow['last_name']) . "</a> - ($stage_display) $ab");
        echo ("</nobr></td>");

        echo ("<td style='border: none;'><nobr>C ($comingup) <font color='green'>H ($thisweek)</font></nobr></td>");

        // Action radio buttons
        $tmpid_html = htmlspecialchars($tmpid);
        echo ("<td style='border: none;'><nobr>");
        if ($showaccept > 0) {
            echo ("<input class='c{$tmpid_html}' type='radio' name='toaccept[{$tmpid_html}]' value='1'> Accept");
        }
        echo (" <input class='c{$tmpid_html}' type='radio' name='toaccept[{$tmpid_html}]' value='-1'> Deny <input class='c{$tmpid_html}' type='radio' name='toaccept[{$tmpid_html}]' value='-2'> Ignore</nobr> 
        <a href='#' onClick='clearRadio(" . htmlspecialchars($tmpid) . "); return false'>Clear</a></td>");
        echo ("</tr>");
    }
    echo ("</table></td></tr>");
}
?>
</table>
<br><input type='submit' name='assignthesetrainers' value='Assign These Trainers' class='button-primary'>
</form>
<?php include "ssi/footer.php" ; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="../images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
<!-- JavaScript for radio button clearing -->
<script>
function clearRadio( id )
{
    // Use modern selector for class names
    const radios = document.querySelectorAll('.c' + id);
    radios.forEach(radio => {
        radio.checked = false;
    });
}
</script>
<?php include "popupjs.php" ;?>
</body>
</html>