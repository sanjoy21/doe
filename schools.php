<?php
include "mysql.php";

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if(isset($visi) && $visi) 
    $search = true;

if(isset($thisusersrow["iscorp"]) && $thisusersrow["iscorp"])
    $session_iscorp = $thisusersrow["iscorp"];

// Initialize session_iscorp if not set
if(!isset($session_iscorp)) {
    $session_iscorp = 0;
}

if(isset($printchecked) && $printchecked)
{
    $comid = isset($all4) && is_array($all4) ? implode(",", array_keys($all4)) : '';
    Header("Location: drillinspection.php?companyid=" . urlencode($comid) . "&aedsigntoo=true&all4=1");
    exit;
}

if(isset($printcheckedsc) && $printcheckedsc)
{
    $comid = isset($sconly) && is_array($sconly) ? implode(",", array_keys($sconly)) : '';
    Header("Location: printmultiplescs.php?comids=" . urlencode($comid));
    exit;
}

if((isset($search) && $search) || (isset($showsheets) && $showsheets)) { 
    $whr = "";
    
    if(isset($substr) && $substr)
    {   
        $exttmp = "";
        $bc = db_query_array("select locationcode from location_to_building where buildingcode = '" . db_escape_string($substr) . "'", "locationcode", "locationcode"); 
        if($bc && is_array($bc))
        {
            foreach($bc as $b)
            {
                $exttmp .= " or locationcode = '" . db_escape_string($b) . "'";
            }
        }
        
        if(preg_match("#[0-9]{5}#", $substr))
        {
            $exttmp .= " or zip = '" . db_escape_string($substr) . "'";
        }
        
        if(preg_match("#[0-9]{2}[a-zA-z]{1}[0-9]{3}#", $substr))
        {
            $exttmp .= " or replace(schoolcode, '-', '') = '" . db_escape_string($substr) . "'";
        }
        
        $whr = " and (companyname like '%" . db_escape_string($substr) . "%' or address like '%" . db_escape_string($substr) . "%' or schoolno like '%" . db_escape_string($substr) . "%' or schoolcode like '%" . db_escape_string($substr) . "%' or locationcode like '%" . db_escape_string($substr) . "%' or region = '" . db_escape_string($substr) . "' $exttmp)";
    }
    
    if(isset($substrcon) && $substrcon)
        $whr .= " and (contact2name like '%" . db_escape_string($substrcon) . "%' or contact3name like '%" . db_escape_string($substrcon) . "%' or principalname like '%" . db_escape_string($substrcon) . "%' or psalprincipalname like '%" . db_escape_string($substrcon) . "%' or contactname like '%" . db_escape_string($substrcon) . "%' or directorname like '%" . db_escape_string($substrcon) . "%') ";
    
    if(isset($searchzip) && $searchzip)
        $whr .= " and (zip = '" . db_escape_string($searchzip) . "') ";
    
    if(isset($searchborough) && $searchborough)
        $whr .= " and (borough = '" . db_escape_string($searchborough) . "') ";
    
    if(isset($searchregion) && $searchregion)
        $whr .= " and (region = '" . db_escape_string($searchregion) . "') ";

    if(isset($withoutfrom) && $withoutfrom)
    {
        $withoutto_sql = isset($withoutto) ? db_escape_string($withoutto) : '';
        $whr .= " and id not in (select dtc.companyid from drill_to_companyid dtc, drill d where drilldate >= '" . db_escape_string($withoutfrom) . "' and drilldate <= '" . $withoutto_sql . "' and dtc.drillid = d.drillid)";
    }

    if(isset($thisusersrow["usertype"]) && $thisusersrow["usertype"] == "trainer")
        $whr .= " and showsondrillreports = 1 ";

    if(isset($retired) && $retired)
        $whr .= " and retired = 1";
    else
        $whr .= " and deleted = 0";
    
    if(isset($nodrills) && $nodrills)
        $whr .= " and showsondrillreports = 0";
    
    $order_by = isset($showsheets) && $showsheets ? "address, longname" : "longname";
    $rows = db_query_rows("select *, concat(schoolcode, companyname) as longname from company_esi where iscorp = '" . $session_iscorp . "' $whr $visi order by " . $order_by);

    if(isset($xls) && $xls)
{
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="schools.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Prepare headers
    $headers = array(
        "school name",
        "schoolcode",
        "address",
        "city",
        "zip",
        "phone",
        "contact",
        "region",
        "contact email",
        "principal email"
    );
    
    if(!$session_iscorp)
    {
        $headers[] = "# trained responders";
    }
    
    if(isset($withoutfrom) && $withoutfrom)
    {
        $headers[] = "trainer";
    }
    
    // Write headers
    fputcsv($output, $headers);
    
    if(isset($rows) && is_array($rows))
    {
        foreach($rows as $r)
        {
            // Prepare contact info
            $contact_parts = array();
            if (!empty($r["contactname"])) $contact_parts[] = $r["contactname"];
            if (!empty($r["contactphone"])) $contact_parts[] = $r["contactphone"];
            if (!empty($r["contactemail"])) $contact_parts[] = $r["contactemail"];
            $contactInfo = implode(", ", $contact_parts);
            
            // Start row data
            $row_data = array(
                $r["companyname"] ?? '',
                $r["schoolcode"] ?? '',
                $r["address"] ?? '',
                $r["city"] ?? '',
                $r["zip"] ?? '',
                $r["schoolphone"] ?? '',
                $contactInfo,
                $r["region"] ?? '',
                $r["contactemail"] ?? '',
                $r["principalemail"] ?? ''
            );
            
            if(!$session_iscorp)
            {
                $numresp = isset($r["id"]) ? getNumResponders($r["id"], true) : 0;
                $row_data[] = $numresp;
            }
            
            if(isset($withoutfrom) && $withoutfrom)
            {
                $tzip = !empty($r["zip"]) ? getTrainersForZip($r["zip"]) : array();
                $trainerNames = '';
                if (is_array($tzip) && !empty($tzip)) {
                    $trainerNames = implode(", ", array_keys($tzip));
                }
                $row_data[] = $trainerNames;
            }
            
            // Write row to CSV
            fputcsv($output, $row_data);
        }
    }
    
    fclose($output);
    exit;
}
}
?>

<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">MANAGE <?php echo strtoupper(getSchoolStr("Schools")); ?></span></strong>

<p>
<table cellpadding="3" cellspacing="0" border="0" width="100%" class="table3">
<tr><td><span class="copy"><strong>View <?php echo getSchoolStr("School"); ?>:</strong></td></tr>
<tr><td nowrap>
        <form method='get'>
<span class='copy'>        
Search: <input type='text' name='substr' size=12 value="<?php echo htmlspecialchars(isset($substr) ? $substr : ''); ?>">

<?php if(isset($session_userid) && ($session_userid == "hthomps@schools.nyc.gov" || strtolower($session_userid) == "cmcgee3@schools.nyc.gov")) { ?>
&nbsp;&nbsp;&nbsp;<input type='checkbox' name='retired' value='1' <?php echo isset($retired) && $retired ? "checked" : ""; ?>> Include Retired?
<?php } ?>
    <?php if(isOverallAdmin()) { ?>
Contacts: <input type='text' name='substrcon' size=12 value="<?php echo htmlspecialchars(isset($substrcon) ? $substrcon : ''); ?>">
                                <?php } ?>
    <?php if(isOverallAdmin() || (isset($thisusersrow["usertype"]) && $thisusersrow["usertype"]=="trainer")) { ?>
Zip Code: <input type='text' name='searchzip' size=7 value="<?php echo htmlspecialchars(isset($searchzip) ? $searchzip : ''); ?>">
               <?php } ?>

        <input type='submit' name='search' value='Search'> 
<?php if(isOverallAdmin()){ ?>
        <input type='submit' name='showsheets' value='Show Sheets Links'>
<br>Without Drills Between <?php echo printdates2("withoutfrom", isset($withoutfrom) ? $withoutfrom : ''); ?> and <?php echo printdates2("withoutto", isset($withoutto) ? $withoutto : ''); ?><br>
<?php } ?>

<br><a href='schools.php?search=true'>View All</a> <a href='schools.php?xls=true&search=true'>xls</a>
<?php if(!isset($noreportsorcalendar) || !in_array(isset($session_id) ? $session_id : 0, $noreportsorcalendar)) { ?>
    <?php if(isOverallAdmin()) { ?>
<br>Borough: <select name="searchborough" id='searchborough' style="font-size: 10px; font-family: verdana;">
                                        <option value=""></option>
<?php if($session_iscorp) { ?>
<option value='other'>Any</option>
<?php } else { ?>
                                        <option <?php echo isset($searchborough) && $searchborough=="Bronx" ? "SELECTED" : ""; ?> value="Bronx">The Bronx</option>
<option <?php echo isset($searchborough) && $searchborough=="Brooklyn" ? "SELECTED" : ""; ?> value="Brooklyn">Brooklyn</option>
                                        <option <?php echo isset($searchborough) && $searchborough=="Manhattan" ? "SELECTED" : ""; ?> value="Manhattan">Manhattan</option>
                                        <option <?php echo isset($searchborough) && $searchborough=="Queens" ? "SELECTED" : ""; ?> value="Queens">Queens</option>
                                        <option <?php echo isset($searchborough) && $searchborough=="Staten Island" ? "SELECTED" : ""; ?> value="Staten Island">Staten Island</option>
<?php } ?>
                    </select>

<input type='checkbox' name='xls' value='1' <?php echo isset($xls) && $xls ? "checked" : ""; ?>>XLS? 
&nbsp;&nbsp;&nbsp;<input type='checkbox' name='retired' value='1' <?php echo isset($retired) && $retired ? "checked" : ""; ?>>Retired?
    <?php if(isOverallAdmin()){ ?> &nbsp;&nbsp;&nbsp;<input type='checkbox' name='nodrills' value='1' <?php echo isset($nodrills) && $nodrills ? "checked" : ""; ?>> Not on drill reports<?php } ?>

Region: <input type='text' size='3' name='searchregion' value='<?php echo htmlspecialchars(isset($searchregion) ? $searchregion : ''); ?>'>
<?php } ?>
<?php } ?>
<br></span></form>
        <?php if(isset($showsheets) && $showsheets) { ?>
<script language='javascript'>
function checkNewDrill(hasopen, hasopensc, id, allthree)
{
    var goon = true; 
    if(hasopen) { 
        if(confirm("Are you sure you want to request a new drill worksheet? There is already an open one."))
            goon = true;
        else
            goon = false;
    }

    if(hasopensc && goon && allthree) { 
        if(confirm("Are you sure you want to request a new service call sheet? There is already an open one."))
            goon = true;
        else
            goon = false;
    }

    if(goon) 
        document.location.href="drillinspection.php?companyid=" + id + "&newdrill=true&aedsigntoo=" + allthree;
}

function checkNewSC(hasopen, id, allthree)
{
    var goon = true; 
    if(hasopen) { 
        if(confirm("Are you sure you want to request a new service call sheet? There is already an open one."))
            goon = true;
        else
            goon = false;
    }
    if(goon) 
        document.location.href="servicecallsheet.php?companyid=" + id + "&newservicecall=true";
}
</script>
                    <span class='copy'><strong>Search Results (<?php echo isset($rows) && is_array($rows) ? count($rows) : 0; ?>)</strong></span>
<form  method='post'>
            <table cellpadding=2 border=1 cellspacing=0>
<tr>            <th class='copy'><?php echo getSchoolStr("School"); ?> Code</th>
            <th class='copy'>Name</th>
            <th class='copy'>Shows On <Br>Drill<br>Reports?</th>
            <th class='copy'>Service Call Sheet</th>
            <th class='copy'>Drill Worksheet</th>
            <th class='copy'>Cardiac Emergency Plan</th>
            <th class='copy'>AED Sign</th>
            <th class='copy'>All 4</th>
            <th class='copy'>SC Only</th>
 <?php 
 if(isset($rows) && is_array($rows)) {
     foreach($rows as $row) { 
         $hasopen = isset($row['id']) ? db_query_first_cell("select drill.drillid from drill left join drill_to_companyid dtc on drill.drillid = dtc.drillid where (dtc.companyid = '" . intval($row['id']) . "' or drill.companyid ='" . intval($row['id']) . "') and completed = 0") : 0;
         $hasopensc = isset($row['id']) ? db_query_first_cell("select servicecall.servicecallid from servicecall left join servicecall_to_companyid dtc on servicecall.servicecallid = dtc.servicecallid where (dtc.companyid = '" . intval($row['id']) . "') and completed = 0") : 0;
?>
<tr><td class='copy'><a href='viewcompany.php?id=<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>'><?php echo htmlspecialchars(isset($row['schoolcode']) ? $row['schoolcode'] : ''); ?></a> <a href='editcompany.php?id=<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>'>Edit</a></td>
<td class='copy'><a href='viewcompany.php?id=<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>'><?php echo htmlspecialchars(isset($row['companyname']) ? $row['companyname'] : ''); ?></a><br>
<?php echo htmlspecialchars(isset($row["address"]) ? $row["address"] : ''); ?>, <?php echo htmlspecialchars(isset($row["city"]) ? $row["city"] : ''); ?>, <?php echo htmlspecialchars(isset($row["zip"]) ? $row["zip"] : ''); ?>
</td>
<td class='copy'><?php echo isset($row["showsondrillreports"]) && $row["showsondrillreports"] ? "Yes" : "No"; ?></td>
<td class='copy'><a href='#' onClick='javascript:checkNewSC(<?php echo $hasopensc ? "true" : "false"; ?>, <?php echo isset($row['id']) ? $row['id'] : 0; ?>, true)'>Service Call Sheet (<?php echo $hasopensc ? htmlspecialchars($hasopensc) : '0'; ?>)</a></td>
<td class='copy'><a href='#' onClick='javascript:checkNewDrill(<?php echo $hasopen ? "true" : "false"; ?>, <?php echo $hasopensc ? "true" : "false"; ?>, <?php echo isset($row['id']) ? $row['id'] : 0; ?>, false)'>Drill Worksheet (<?php echo $hasopen ? htmlspecialchars($hasopen) : '0'; ?>)</a></td>
<td class='copy'><a href="#" onClick="MyWindow=window.open('response_plan<?php echo isset($row["iscorp"]) && $row["iscorp"] ? "_corp" : ""; ?>.php?id=<?php echo isset($row['id']) ? $row['id'] : ''; ?>','MyWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500'); return false;">Cardiac Emergency Plan</a></td>
<td class='copy'><a href='printaedsign.php?id=<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>'>AED Sign</a></td>
<td class='copy'><a href='#' onClick='javascript:checkNewDrill(<?php echo $hasopen ? "true" : "false"; ?>, <?php echo $hasopensc ? "true" : "false"; ?>, <?php echo isset($row['id']) ? $row['id'] : 0; ?>, true)'>All 4</a>
<input type='checkbox' name='all4[<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>]' value='1'>
</td>
<td class='copy'><input type='checkbox' name='sconly[<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>]' value='1'>
</td>
</tr>
<?php 
     }
}
?>
</table>
<input type='submit' name='printchecked' value='Print All 4 for Checked'>
<input type='submit' name='printcheckedsc' value='Print SC For Checked'>
</form>
</td>
</tr>
                    <?php } ?>
        <?php if(isset($search) && $search) { ?>
                    <span class='copy'><strong>Search Results (<?php echo isset($rows) && is_array($rows) ? count($rows) : 0; ?>)</strong></span>
<form action="viewcompany.php">
            <table cellpadding=2 border=1 cellspacing=0>
<tr>            <th class='copy'><?php echo getSchoolStr("School"); ?> Code</th>
<?php if($session_iscorp) { ?>
            <th class='copy'>HQ?</th>
<?php } ?>
            <th class='copy'>Name</th>
                 <?php if(!isset($thisusersrow["usertype"]) || $thisusersrow["usertype"] != "trainer") { ?>
            <th class='copy'>Address</th>
            <th class='copy'>Region</th>
            <th class='copy'>Next Training In Building</th>
<?php if(!$session_iscorp) { ?>
            <th class='copy'>Num Trained Responders</th>
                           <?php } ?>
<?php if(isset($withoutfrom) && $withoutfrom) { ?>
            <th class='copy'>Trainer</th>
                           <?php } ?>
   
<?php } ?>
                 <?php if(isset($thisusersrow["usertype"]) && $thisusersrow["usertype"] == "trainer") { ?>
            <th class='copy'>Expiring Pads</th>
<?php }?>
 <?php 
 if(isset($rows) && is_array($rows)) {
     foreach($rows as $row) { 
?>
<tr><td class='copy'><a href='viewcompany.php?id=<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>'><?php echo htmlspecialchars(isset($row['schoolcode']) ? $row['schoolcode'] : ''); ?></a>

                            <?php if(isOverallAdmin()) { ?>
                            <a href='editcompany.php?id=<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>'>Edit</a>
<?php } ?>
</td>
<?php if($session_iscorp) { ?>
<td class='copy'><?php echo isset($row['isheadquarter']) && $row['isheadquarter'] ? "Y" : "N"; ?></td>
<?php } ?>
<td class='copy'><a href='viewcompany.php?id=<?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : ''); ?>'><?php echo htmlspecialchars(isset($row['companyname']) ? $row['companyname'] : ''); ?></a></td>
                 <?php if(!isset($thisusersrow["usertype"]) || $thisusersrow["usertype"] != "trainer") { ?>
<td class='copy'><?php echo htmlspecialchars(isset($row["address"]) ? $row["address"] : ''); ?></td>
<td class='copy'><?php echo htmlspecialchars(isset($row["region"]) ? $row["region"] : ''); ?></td>
<?php 
                $next = isset($row['id']) ? db_query_first("select id, startdate, accepted from class where companyid = " . intval($row['id']) . " and startdate > now() and accepted = 1 and deleted = 0 order by startdate") : array();
                $nstr = "";
                if($next && isset($next['startdate']))
                {
                    $nstr = htmlspecialchars($next['startdate']) . " <a href='class_detail.php?id=" . htmlspecialchars(isset($next['id']) ? $next['id'] : '') . "'>#" . htmlspecialchars(isset($next['id']) ? $next['id'] : '') . "</a>";
                }
                ?>
                            <td class='copy'><?php echo $nstr; ?></td>
                                                                   <?php if(!$session_iscorp) { ?>
                            <td class='copy'><?php echo isset($row['id']) ? count(getNonExpiredResponders($row['id'])) : 0; ?></td>
                            <?php } ?>
<?php } ?>
                 <?php if(isset($thisusersrow["usertype"]) && $thisusersrow["usertype"] == "trainer") { ?>
                                                                   <td class='copy'>
                                                                   <?php
                            $earliest = 99999999999;
                            if(isset($row['id']))
                            {
                                $aedrows = getAeds($row['id']);
                                $sixmoago = mktime(0, 0, 0, date("m") + 6);
                                
                                if(isset($aedrows) && is_array($aedrows))
                                {
                                    foreach($aedrows as $tarow)
                                    {
                                        $arow = isset($tarow['aedid']) ? getAedRow($tarow['aedid']) : array();
                                        
                                        if(isset($arow['aedstolen']) && $arow['aedstolen']) 
                                            continue;
                                        
                                        if(isset($arow['padaexpiration']) && $arow['padaexpiration'])
                                        {
                                            $exp = strtotime($arow['padaexpiration']);
                                            if($exp && $exp < $sixmoago && $exp < $earliest)
                                            {
                                                $earliest = $exp;
                                            }
                                        }
                                        
                                        if(isset($arow['padbexpiration']) && $arow['padbexpiration'])
                                        {
                                            $exp = strtotime($arow['padbexpiration']);
                                            if($exp && $exp < $sixmoago && $exp < $earliest)
                                            {
                                                $earliest = $exp;
                                            }
                                        }
                                        
                                        if(isset($arow['pediatricpads']) && $arow['pediatricpads'])
                                        {
                                            $exp = strtotime($arow['pediatricpads']);
                                            if($exp && $exp < $sixmoago && $exp < $earliest)
                                            {
                                                $earliest = $exp;
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                                <?php echo $earliest && $earliest < 99999999999 ? date("m/d/Y", $earliest) : "&nbsp;"; ?>
                                                                   
</td>
                 <?php } ?>
<?php if(isset($withoutfrom) && $withoutfrom && isset($row["zip"]))
{
    $tzip = getTrainersForZip($row["zip"]);
    $trainerNames = isset($tzip) && is_array($tzip) ? implode(", ", array_keys($tzip)) : '';
    echo "<td>" . htmlspecialchars($trainerNames) . "</td>";
}
?>
</tr>                                               
<?php 
     }
 }
 ?>
</table>
</form>
</td>
</tr>
<?php } ?>
<?php if(isset($specialadmin) && $specialadmin) { ?>
<tr><td><span class="copy"><a href="editcompany.php">Add New <?php echo getSchoolStr("School"); ?></a></span></td>
</tr>
<tr><td><span class='copy'>
<a href="allnoaeds.php?noaeds=true"><?php echo getSchoolStr("Schools"); ?> with No AEDs</a> <a href="allnoaeds.php?noaeds=true&xls=true">(xls)</a><br><br>
<!--<a href="allnobuildingnos.php?noaeds=true"><?php echo getSchoolStr("Schools"); ?> with No Building Nos</a> <a href="allnobuildingnos.php?noaeds=true&xls=true">(xls)</a><br><br>-->
<!--<a href="locnoaeds.php?noaeds=true">Locations with No AEDs</a> <a href="locnoaeds.php?noaeds=true&xls=true">(xls)</a><br><br>-->
<!--<a href="locnoaeds.php?type=r&noaeds=true">Locations with No Responders</a> <a href="locnoaeds.php?type=r&noaeds=true&xls=true">(xls)</a>-->
<!--<br><br><a href="schoolswithwrongbuildingcodes.php">Schools with 0 or > 1 Buildings, or no Location Code</a> <a href="schoolswithwrongbuildingcodes.php?xls=true">(xls)</a>
<br><br><a href="schoolswithwrongbuildingcodes.php?nobuildings=1">Schools with 0 buildings, or no Location Code</a> <a href="schoolswithwrongbuildingcodes.php?nobuildings=1&xls=true">(xls)</a>-->
                 </span>
            </td>
</tr>
                 <?php } else if(isset($session_id) && $session_id == "845") { ?>
<tr><td>
<a href="allexpired.php"><?php echo getSchoolStr("Schools"); ?> with No Current Trained Responders</a> <a href="allexpired.php?xls=true">(xls)</a>
            <br><br>
<a href="allexpired.php?minnum=5"><?php echo getSchoolStr("Schools"); ?> with Fewer than Six Trained Responders</a> <a href="allexpired.php?minnum=5&xls=true">(xls)</a>
            <br><br>
</td></tr>
<?php } ?>
</table>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
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