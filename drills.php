<?php

// 465-3637
require_once('mysql.php');

// --- 1. Initialize Variables and Defaults ---
// Use null coalescing and default values
$showdone = $_POST['showdone'] ?? 0;
$showshipped = $_POST['showshipped'] ?? 0;
$hideunused = $_POST['hideunused'] ?? 0;
$showreceived = $_POST['showreceived'] ?? 0;
$instructorname = $_POST['instructorname'] ?? '';
$lname = $_POST['lname'] ?? '';
$actionneeded = $_POST['actionneeded'] ?? 0;
$year = $_POST['year'] ?? null;
$printinv = $_POST['printinv'] ?? null;
$update = $_POST['update'] ?? null;
$invoiceno = $_POST['invoiceno'] ?? [];
$hiddens = $_POST['hiddens'] ?? [];
$dd = $_POST['dd'] ?? [];
$tr = $_POST['tr'] ?? [];
$sh = $_POST['sh'] ?? [];
$done = $_POST['done'] ?? [];
$rec = $_POST['rec'] ?? [];

// Default filter settings if no form data submitted
if (empty($_POST)) {
    $showdone = 1;
    $showshipped = 1;
}

// Assumed variables from session/user data:
// $thisusersrow, $specialadmin, $session_id, $visi
// Assumed external functions: db_query, db_query_first_cell, db_escape, 
// getVisibleZipsString, getDistrictString, getCompanyRow, getUserName, printdates2

if ($printinv) {
    include "instructorinvoice.php";
    exit;
}

$isspecialtrainer = ($thisusersrow["usertype"] === "trainer") && ($thisusersrow["viewschools"] ?? false);

// --- 2. Database Update Logic ---
if ($update) {
    
    // Update Invoicing (for non-trainers)
    if (($thisusersrow["usertype"] ?? null) !== "trainer") {
        foreach ($invoiceno as $id => $val) {
            $id = (int)$id;
            $val = db_escape($val); // Sanitize input
            
            if (!empty($val)) {
                db_query("UPDATE drill SET invoiced = 1, invoiceno = '{$val}' WHERE drillid = {$id}");
            } else {
                db_query("UPDATE drill SET invoiced = 0, invoiceno = '' WHERE drillid = {$id}");
            }
        }
    }

    // Update Drill Statuses
    foreach ($hiddens as $h) {
        $h = (int)$h;
        $done_status = (int)($done[$h] ?? 0);
        $shipped_status = (int)($sh[$h] ?? 0);
        $tracking_no = db_escape($tr[$h] ?? ''); // Sanitize
        $received_status = (int)($rec[$h] ?? 0);
        $done_date_raw = $dd[$h] ?? '';
        
        $ddsave = "NULL";
        $drilldate_update = "";
        
        if (!empty($done_date_raw) && $done_date_raw !== "0000-00-00") {
            // Attempt to format date securely
            $timestamp = strtotime($done_date_raw);
            if ($timestamp !== false) {
                $formatted_date = date("Y-m-d", $timestamp);
                $ddsave = "'{$formatted_date}'";
                $drilldate_update = ", drilldate = '{$formatted_date}'";
            }
        }

        if ($isspecialtrainer) {
            db_query("UPDATE drill SET trackingno = '{$tracking_no}', shipped = {$shipped_status}, isdone = {$done_status}, donedate = {$ddsave} {$drilldate_update} WHERE drillid = {$h}");
        } else {
            db_query("UPDATE drill SET received = {$received_status}, trackingno = '{$tracking_no}', shipped = {$shipped_status}, isdone = {$done_status}, donedate = {$ddsave} {$drilldate_update} WHERE drillid = {$h}");
        }

        // Logging updates
        if ($shipped_status) {
            $sd = db_query_first_cell("SELECT shippeddate FROM drill WHERE drillid = {$h}");
            if (empty($sd)) {
                db_query("UPDATE drill SET shippeddate = NOW() WHERE drillid = {$h}");
            }
        }
        
        if ($done_status) {
            $db = db_query_first_cell("SELECT doneby FROM drill WHERE drillid = {$h}");
            if (empty($db)) {
                $session_id_safe = (int)($session_id ?? 0); // Assuming $session_id is available and integer
                if ($session_id_safe > 0) {
                    db_query("UPDATE drill SET doneby = {$session_id_safe} WHERE drillid = {$h}");
                }
            }
        }
    }
}

// --- 3. Access Control and Redirection ---
if (!($specialadmin ?? false) && !$isspecialtrainer) {
    header("Location: login.php");
    exit;
}

// --- 4. Query Construction ---
$extra = "";
$extrajoin = "";

if (!empty($lname)) {
    $drill_id_part = str_ireplace("D", "", $lname);
    $extra .= " AND drillid = '" . db_escape($drill_id_part) . "'";
}

if ($actionneeded) {
    $extra .= " AND actionneeded = '1'";
}

// Default year logic
if (empty($year)) {
    $current_year = date("Y");
    if (date("m") < 9) {
        $current_year--;
    }
    $year = $current_year;
}

// School year filter logic
if (!empty($year)) {
    $year = (int)$year;
    $next_year = $year + 1;
    $extra .= " AND ( 
        ( drilldate > '{$year}-09-01' AND drilldate < '{$next_year}-09-01' ) 
        OR drilldate IS NULL 
        OR drilldate = '0000-00-00' 
        OR drilldate = '1969-12-31' 
    )";
}

// Visibility filters
if (!empty($visi)) {
    $extrajoin = ", company_esi c";
    $extra .= " AND drill.companyid = c.id " . (getVisibleZipsString("c") ?? '');
} elseif (!empty($thisusersrow["visibleregion"] ?? null)) {
    $extrajoin = ", company_esi c";
    $region_safe = (int)($thisusersrow["visibleregion"]);
    $extra .= " AND drill.companyid = c.id AND c.region = {$region_safe} ";
} elseif (!empty($thisusersrow["districts"] ?? null)) {
    $extrajoin = ", company_esi c";
    $extra .= getDistrictString($thisusersrow["districts"], "c.");
}

if (!empty($instructorname)) {
    $instructorname_safe = db_escape($instructorname);
    $extra .= " AND drill.inspector LIKE '%{$instructorname_safe}%'";
}

// Fetch Drills
$sql_trainers = "SELECT drill.* FROM drill {$extrajoin} WHERE 1 {$extra} ORDER BY drillid";
$trainers = db_query_rows($sql_trainers);
?>
<?php include "ssi/top.php"; ?>    
<p>
       
         <strong><span class="title">MANAGE DRILLS</span></strong>
       
</p>
       <form method='post'>
      <?php if (!$isspecialtrainer) { ?>
             <input type='checkbox' name='hideunused' value='1' <?= $hideunused ? "CHECKED" : "" ?>> Hide Unused? <br>
             <input type='checkbox' name='showdone' value='1' <?= $showdone ? "CHECKED" : "" ?>> Show Done? <br>
             <input type='checkbox' name='showshipped' value='1' <?= $showshipped ? "CHECKED" : "" ?>> Show Shipped<br>
             <input type='checkbox' name='showreceived' value='1' <?= $showreceived ? "CHECKED" : "" ?>> Show Received? 
<br>ESI Drill/Instructor:          <input type='text' name='instructorname' value="<?= htmlspecialchars($instructorname) ?>"> <br>

          <br><input type='submit' name='search' value='Refresh'><br><br>
             <?php } ?>
       <span class='copy'>Search (school year): 
<select name='year'>
<option value=''></option>
<?php for ($i = 2006; $i <= date("Y"); $i++) { ?>
<option value='<?= $i ?>' <?= $year == $i ? "SELECTED" : "" ?>><?= $i ?> - <?= $i + 1 ?></option>
<?php } ?>
</select> 
<input type='text' name='lname' class='copy' value="<?= htmlspecialchars($lname) ?>"> 
<input class='copy' type='submit' name='search' value='Search'> 
<input type='checkbox' name='actionneeded' value='1' <?= $actionneeded ? "CHECKED" : "" ?>> Action Needed Only?<br><br>
      <?php if ($isspecialtrainer) { ?>
             <?= printdates2('fromdate', $_POST['fromdate'] ?? null) ?> to   <?= printdates2('todate', $_POST['todate'] ?? null) ?> <input type='submit' value='Print Invoice' name='printinv'><Br><Br>
             <?php } ?>
      <table cellpadding=2 cellspacing=0 border=1><tr><td><b>Key:</b></td>
      <td bgcolor='#F5F3B0'>&nbsp;Done&nbsp;</td>
<td bgcolor='#ccffcc'>&nbsp;Shipped&nbsp;</td>
<?php   if (($thisusersrow["usertype"] ?? null) !== "trainer") {
?>
<td bgcolor='#C29EE8'>&nbsp;Received&nbsp;</td>
      <?php } ?>
      </tr></table>
<input type='submit' name='update' value='Update'><br>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
         <tr bgcolor="#e1e1f6"><th class='copy'>Drill Number</th><th class='copy'>Drill Date</th><th class='copy'>Zip</th>
<?php   if (($thisusersrow["usertype"] ?? null) !== "trainer") {
?>
      <th class='copy'>School Name</th>
      <th class='copy'>Last Drill Date</th>
      <th class='copy'>Invoice #</th>
<?php } ?>
<th class='copy'>Done</th><th class='copy'>Shipped</th><th class='copy'>Tracking No</th>
<?php if (!$isspecialtrainer) { ?>

             <th class='copy'>Received</th>
             <th class='copy'>Trainer</th>
            <?php } ?>
            </tr>
<?php 
if (is_array($trainers)) {
    foreach ($trainers as $t) {
        $drillid = (int)($t['drillid'] ?? 0);
        
        // --- Filtering (Replicated in PHP) ---
        if (($thisusersrow["usertype"] ?? null) === "trainer" && ($t["received"] ?? 0)) {
            continue;
        }
        if (!$isspecialtrainer && !$showreceived && ($t["received"] ?? 0)) {
            continue;
        }
        if (!$isspecialtrainer && !$showdone && ($t["isdone"] ?? 0)) {
            continue;
        }
        if (!$isspecialtrainer && !$showshipped && ($t["shipped"] ?? 0)) {
            continue;
        }
        if (!$isspecialtrainer && $hideunused && !($t["shipped"] ?? 0) && !($t["received"] ?? 0) && !($t["isdone"] ?? 0)) {
            continue;
        }
        
        $crow = getCompanyRow($t['companyid'] ?? 0);
        
        // --- Row Display and Coloring ---
        echo "<input type=hidden name='hiddens[]' value='{$drillid}'>";
        $bgcolor = "#FFFFFF";
        if (($crow["deleted"] ?? 0)) $bgcolor = "#FFccccc";
        if (($t["isdone"] ?? 0)) $bgcolor = "#F5F3B0";
        if (($t["shipped"] ?? 0)) $bgcolor = "#ccffcc";
        if (($t["received"] ?? 0)) $bgcolor = "#C29EE8";
        
        $company_id_safe = (int)($t['companyid'] ?? 0);

        if ($isspecialtrainer) {
            echo "<tr bgcolor='{$bgcolor}'><td class='copy' valign='top'><a href='viewcompany.php?id={$company_id_safe}'>{$drillid}</a>";
            if (!empty($t['appid'])) {
                echo "<br><a href='appdrill.php?id={$t['appid']}'>Worksheet</a>";
            }
            echo "</td><td class='copy'>{$t['drilldate']}</td> ";
        } else {
            echo "<tr bgcolor='{$bgcolor}'><td class='copy' valign='top'><a href='editdrill.php?drillid={$drillid}'>D{$drillid}</a>";
            if (!empty($t['appid'])) {
                echo "<br><a href='appdrill.php?id={$t['appid']}'>Worksheet</a>";
            }
            
            echo "</td><td class='copy'>{$t['drilldate']}</td> ";

            echo "<td class='copy'><a href='viewcompany.php?id={$company_id_safe}'>" . htmlspecialchars($crow["zip"] ?? '') . "</a></td>";
        }
        
        // Non-trainer specific columns
        if (($thisusersrow["usertype"] ?? null) !== "trainer") {
            $last = db_query_first_cell("SELECT drilldate FROM drill WHERE companyid = {$company_id_safe} AND ( ''= '{$t['drilldate']}' OR drilldate < '{$t['drilldate']}' ) ORDER BY drilldate DESC LIMIT 1");
            echo "<td class='copy'>" . htmlspecialchars($crow['companyname'] ?? '') . "</td>";
            echo "<td class='copy'>{$last}</td>";

            if (!empty($t['invoiceno'])) {
                echo "<td class='copy'>" . htmlspecialchars($t['invoiceno']) . "</td>";
            } else {
                echo "<td class='copy'><input class='smallinput' type='text' size='7' name='invoiceno[{$drillid}]' value=''></td>";
            }
        } 
        
        // Done Status
        echo "<td><nobr><input type=checkbox onClick='fillDoneDate({$drillid}, this.checked)' name='done[{$drillid}]' value='1' " . (($t['isdone'] ?? 0) ? "checked" : "") . ">
<input type='text' name='dd[{$drillid}]' size='10' class='smallinput' value='" . htmlspecialchars($t['donedate'] ?? '') . "' id='dd{$drillid}'></nobr>
</td>";
        
        // Shipped Status
        echo "<td><input type=checkbox name='sh[{$drillid}]' onClick='checkTracking({$drillid}, this.checked)' value='1' " . (($t['shipped'] ?? 0) ? "checked" : "") . "></td>";
        
        // Tracking Number
        echo "<td><input class='smallinput' type=text id='tr{$drillid}' size='15' name='tr[{$drillid}]' value='" . htmlspecialchars($t['trackingno'] ?? '') . "'></td>";
        
        // Received and Trainer (Non-trainer view)
        if (!$isspecialtrainer) {
            echo "<td><input type=checkbox name='rec[{$drillid}]' value='1' " . (($t['received'] ?? 0) ? "checked" : "") . "></td>";
            echo "<td>" . (getUserName($t['doneby'] ?? 0) ?? '') . "</td>";
        }

        echo "</tr>";
    }
}
?>
</table><p>
<input type='submit' name='update' value='Update'><br><br><br>
       <script language='javascript'>
       function checkTracking( id, ischecked )
{
       if( ischecked )
        {
       p = prompt( "What is the tracking number?" );
        document.getElementById( "tr" + id ).value = p;
        }
}
       function fillDoneDate( id, ischecked )
{
       ele = document.getElementById( "dd" + id );
       
        if( ischecked && ele.value == "" )
        {
        ele.value = "<?= date("Y-m-d") ?>";
        }
}
</script>
 <?php include "ssi/footer.php"; ?>
       
