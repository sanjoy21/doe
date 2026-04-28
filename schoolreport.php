<?php

$rep = $rep ?? [];
$counter = $counter ?? [];
$trainerzips = $trainerzips ?? [];
$bannedzips = $bannedzips ?? [];
$bannedschoolids = $bannedschoolids ?? [];

?>
<table>
<tr>
<?php if ($session_iscorp) { ?><td></td><?php } ?>
<td>School Code</td>
<td>School Name</td>
<?php if (!$session_iscorp) { ?><td>Restricted?</td><?php } ?>
<?php if (!$minimal) { ?>
<td>Address</td>
<td>Floor</td>
<td>City</td>
<td>State</td>
<?php } ?>
<td>Zip</td>
<td>Rep Name</td>
<?php if (!$minimal) { ?>
<td>Contact Email</td>
<td>Principal Email</td>
<td>CFN</td>
<?php } ?>
<?php if (!$minimal) { ?>
<td>Phone</td>
<?php } ?>
<?php if ($deleted) { ?>
<td>Deleted</td>
<td>Deleted Date</td>
<td>Retired</td>
<td>Retired Date</td>
<?php } ?>
<td>AED Contact</td>
<td>AED Contact Phone</td>
<td>Principal name</td>
<td>Principal Email</td>
<td>Borough</td>
<td>Region</td>
<?php if (!$minimal && !$deleted) { ?>
<td>Last Drill Date</td>
<td>Last Service Call Date</td>
<?php } ?>

<td>List of responders - Exp Dates</td >
<td>General School Notes</td>
<td>Campus</td>
<td>ID</td>
<td>Gets Drills?</td>
<td>School Created Date</td>
<td>AED Serial(s)</td>
<td>AED Type(s)</td>
<td>Expired Pads</td>
<td>Last Recert Note</td>
</tr>
<?php
$i = 0;
foreach ($rep as $class) {
    $id = $class['id'];
    if ($id === null) continue; // Skip if no ID is available
    
    $num = $counter[$id];

    // --- Drill/No Drill Filtering Logic ---
    if ($nodrills || $withnodrills) {
        if ($nodrills && $num) {
            continue; // Skip schools that have drills if $nodrills is true
        } elseif ($nodrills && $class['campusid']) {
            // Logic to check other schools in the same campus
            $otherschoolsinbuilding = getSchoolsInCampus($class['campusid'], $class['id']);
            $has_drill_in_campus = false;
            foreach ($otherschoolsinbuilding as $frow) {
                if ($counter[$frow['id']]) {
                    $has_drill_in_campus = true;
                    break;
                }
            }
            if ($has_drill_in_campus) {
                 continue; // Skip if another school in campus has a drill
            }
        }
        if ($withnodrills && $num > 1) {
            continue; // Skip if more than 1 drill exists and $withnodrills is true
        }
    }
    
    // --- Data Fetching ---
    $zip = $class['zip'];
    $repname = $zip ? $trainerzips[$zip] : '';
    $drillrow = [];
    $servicecallrow = [];

    // Fetch last drill and service call rows
    $company_id_safe = is_numeric($id) ? $id : addslashes($id);
    if (!$minimal && !$deleted) {
        $drillrow = db_query_first("SELECT * FROM drill WHERE companyid = '{$company_id_safe}' OR otherschools LIKE '%," . addslashes($id) . ",%' ORDER BY drilldate DESC");
        $servicecallrow = db_query_first("SELECT * FROM servicecall WHERE companyid = '{$company_id_safe}' OR otherschools LIKE '%," . addslashes($id) . ",%' ORDER BY servicecalldate DESC");
    }

    // Prepare Responders List
    $resparr = getNonExpiredResponders($id);
    $resp = "";
    foreach ($resparr as $rrow) {
        $responder_id = $rrow['responderid'];
        $tmpexpdate = getFormattedDate(getResponderExpDatePlus($responder_id));
        if ($resp) {
            $resp .= "<br>";
        }
        $resp .= htmlspecialchars($rrow["firstname"]) . " " . htmlspecialchars($rrow["lastname"]) . " - " . htmlspecialchars($tmpexpdate);
    }
    
    // Increment row counter
    $i++;
?>
    <tr>
        <?php if ($session_iscorp) { ?><td></td><?php } ?>
<?php if ($onscreen) { ?>
        <td><?php echo $i; ?>. <a target="_blank" href='viewcompany.php?id=<?php echo htmlspecialchars($id); ?>'><?php echo htmlspecialchars($class['schoolcode']); ?></a></td>
        <td><a target="_blank" href='viewcompany.php?id=<?php echo htmlspecialchars($id); ?>'><?php echo htmlspecialchars($class['companyname']); ?></a></td>
<?php } else { 
    $url_prefix = getUrlPrefix($session_iscorp);
    $view_link = "http://" . htmlspecialchars($url_prefix) . "." . URL_WITHOUT_SUBDOMAIN . "/viewcompany.php?id=" . urlencode($id);
?>
        <td><a href='<?php echo $view_link; ?>'><?php echo htmlspecialchars($class['schoolcode']); ?></a></td>
        <td><?php echo htmlspecialchars($class['companyname']); ?></td>
<?php } ?>

        <?php if (!$session_iscorp) { 
            $is_restricted_zip = in_array($zip, $bannedzips);
            $restricted_school_id_note = $bannedschoolids[$id];
        ?>
        <td><?php if ($is_restricted_zip) { ?>RESTRICTED ZIP CODE<?php } ?>
<?php if ($restricted_school_id_note) { ?> RESTRICTED SCHOOL ID - <?php echo htmlspecialchars($restricted_school_id_note); ?><?php } ?>
</td>
<?php } ?>
<?php if (!$minimal) { ?>
        <td><a href='<?php echo $view_link; ?>'><?php echo htmlspecialchars($class['address']); ?></a></td>
        <td><?php echo htmlspecialchars($class['floor']); ?></td>
        <td><?php echo htmlspecialchars($class['city']); ?></td>
        <td><?php echo htmlspecialchars($class['state']); ?></td>
            <?php } ?>
        <td><?php echo htmlspecialchars($class['zip']); ?></td>
    <td><?php echo htmlspecialchars($repname); ?></td>
<?php if (!$minimal) { ?>
             <td><?php echo htmlspecialchars($class['contactemail']); ?></td>
             <td><?php echo htmlspecialchars($class['principalemail']); ?></td>
        <td><?php echo htmlspecialchars($class['cfn']); ?></td>
                         <?php } ?>
<?php if (!$minimal) { ?>
        <td><?php echo htmlspecialchars($class['contactphone']); ?></td>
<?php } ?>
<?php if ($deleted) { ?>
<td><?php echo ($class['deleted']) ? "Yes" : "No"; ?></td>
<td><?php echo htmlspecialchars($class['deletiondate']); ?></td>
<td><?php echo ($class['retired']) ? "Yes" : "No"; ?></td>
<td><?php echo htmlspecialchars($class['retiredate']); ?></td>
                         <?php } ?>
<td><?php echo htmlspecialchars($class['contactname']); ?></td>
<td><?php echo htmlspecialchars($class['contactphone']); ?></td>
<td><?php echo htmlspecialchars($class['principalname']); ?></td>
<td><?php echo htmlspecialchars($class['principalemail']); ?></td>
<td><?php echo htmlspecialchars($class['borough']); ?></td>
<td><?php echo htmlspecialchars($class['region']); ?></td>

<?php if (!$minimal && !$deleted) { 
    $drill_date = $drillrow['drilldate'];
    $service_date = $servicecallrow['servicecalldate'];
?>
<td><?php echo (($drill_date && $drill_date != "0000-00-00") ? htmlspecialchars($drill_date) : ""); ?></td>
<td><?php echo (($service_date && $service_date != "0000-00-00") ? htmlspecialchars($service_date) : ""); ?></td>
<?php } ?>

<td><?php echo $resp; ?></td>
<td><?php echo htmlspecialchars($class['companynotes']); ?></td>
<td><?php echo htmlspecialchars($class['campus']); ?></td>
<td><?php echo htmlspecialchars($class['id']); ?></td>
<td><?php echo ($class['showsondrillreports']) ? "Y" : "N"; ?></td>
<td><?php echo htmlspecialchars($class['date']); ?></td>
<td><?php echo htmlspecialchars(getAEDSerials($class['id'])); ?></td>
<td><?php echo htmlspecialchars(getAEDTypes($class['id'])); ?></td>
<td><?php echo htmlspecialchars(getExpiredAEDDates($class['id'])); ?></td>
<td><?php echo htmlspecialchars(getLastRecertNote($class['id'])); ?></td>
</tr>
<?php
}
?>
</table>