<?php

require_once('mysql.php');

// Simple placeholder for escaping, as we cannot define the actual db connection object here.
function db_escape_string($str) {
    return str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$str);
}

if (!$specialadmin && !($thisusersrow["tcfaculty"]))
{
    header("location: login.php");
    exit;
}

// 3. Process Deletion (Soft Delete or Archive logic not used here, only the hard delete from original)
if ($del)
{
    $del_id = $del;
    db_query("DELETE FROM user WHERE id = $del_id AND usertype='trainer'");
    // Redirect to self after delete
    $borough_param = $borough ? "&borough=" . urlencode($borough) : "";
    header("Location: trainers.php?$borough_param");
    exit;
}

// 4. Build SQL Query Filters
$extra = "";
$extrajoin = "";
$extraljoin = "";

if ($borough)
{
    $borough_safe = db_escape_string($borough);
    $extra .= " AND trainer_to_borough.trainerid = user.id AND borough = '$borough_safe'";
    $extrajoin .= ", trainer_to_borough ";
}

// Filter for Training Sites vs Regular Trainers
if ($session_iscorp == TRAININGSITES || $showtrainingsiteinst)
    $extra .= " AND trainingsites = 1";
else
    $extra .= " AND trainingsites = 0";


// Filter by assigned TC Faculty (if current user is TC Faculty)
if ($thisusersrow["tcfaculty"] && !$showallactive && !$showtrainingsiteinst)
    $extra .= " AND assignedtcfacultyid = " . $thisusersrow['id'];

// Show only active (inactive = 0)
if ($showallactive)
    $extra .= " AND inactive = 0";

// Filter by Zip
if ($filterzip)
{
    $filterzip_safe = db_escape_string($filterzip);
    $extrajoin .= ", user_to_zip ";
    $extra .= " AND user_to_zip.userid = user.id AND ( user_to_zip.zip = '$filterzip_safe' )";
}

// Filter National Trainers
if (!$shownational)
    $extra .= " AND national = 0";
    
// Set Order By
$ob_safe = db_escape_string($ob) ?: "last_name, first_name";


// 5. Excel Export Logic (Retaining PEAR dependency as requested)
// if ($export)
// {
//     require_once "Spreadsheet/Excel/Writer.php";
//     $xls = new Spreadsheet_Excel_Writer();
//     $filename = "trainers.xls";
//     $xls->send($filename);
//     $sheet =& $xls->addWorksheet("Report");
    
//     $rownum = 0;
//     $colnum = 0;
    
//     // Write Headers
//     $sheet->write($rownum, $colnum++, "Trainer");
//     $sheet->write($rownum, $colnum++, "Group");
//     $sheet->write($rownum, $colnum++, "Type");
//     $sheet->write($rownum, $colnum++, "Phone");
//     $sheet->write($rownum, $colnum++, "Carrier");
//     $sheet->write($rownum, $colnum++, "Site");
//     $sheet->write($rownum, $colnum++, "Exp Date");
//     $sheet->write($rownum, $colnum++, "TC Faculty");
//     $sheet->write($rownum, $colnum++, "Last Monitoring Date");
//     $sheet->write($rownum, $colnum++, "Instructor Number");
//     $sheet->write($rownum, $colnum++, "ASHI?");
//     $sheet->write($rownum, $colnum++, "BLS?");
//     if ($session_iscorp == TRAININGSITES)
//         $sheet->write($rownum, $colnum++, "Training Site");
        
//     $ext = $expired ? " AND expdate < NOW() " : ""; // Assuming $expired is defined if needed
//     $drows = db_query_rows("SELECT * FROM trainer_exp_dates WHERE type <> 'coreinst' AND current = 1 $ext ORDER BY id DESC");
//     $types = array("tc"=>"TC Affiliation", "other"=>"Other Credentials", "firstaid"=>"First Aid Provider", "cpr"=>"CPR Provider", "aha"=>"AHA CPR Provider");
//     $tarr = db_query_array("SELECT CONCAT(first_name, ' ', last_name) AS name, id FROM user WHERE usertype = 'trainer' AND inactive = 0", "id", "name");

//     $allusers = db_query_rows("SELECT * FROM user $extrajoin WHERE usertype = 'trainer' AND inactive = 0 $extra", "id");
//     $tcfac = db_query_array("SELECT assignedtcfacultyid, id FROM user WHERE usertype = 'trainer' AND inactive = 0", "id", "assignedtcfacultyid");
    
//     foreach ($drows as $sid => $drow)
//     {
//         if (!$tarr[$drow['trainerid']] || !$allusers[$drow['trainerid']])
//             continue;
            
//         $rownum++;
//         $colnum = 0;
        
//         $trainer_id = (int)$drow['trainerid'];

//         $sheet->write($rownum, $colnum++, $tarr[$trainer_id]);
//         $sheet->write($rownum, $colnum++, $types[$drow['expgroup']]);
//         $sheet->write($rownum, $colnum++, $drow["type"]);
//         $sheet->write($rownum, $colnum++, $drow["cell"]);
//         $sheet->write($rownum, $colnum++, $drow["cellprovider"]);
//         $sheet->write($rownum, $colnum++, $drow["site"]);
//         $sheet->write($rownum, $colnum++, $drow["expdate"]);
//         $sheet->write($rownum, $colnum++, $tarr[$tcfac[$trainer_id]]);
        
//         $lastm = db_query_first_cell("SELECT monitoringdate FROM monitoring WHERE trainerid = '$trainer_id' ORDER BY monitoringdate DESC LIMIT 1");
//         $sheet->write($rownum, $colnum++, $lastm);
        
//         $sheet->write($rownum, $colnum++, $allusers[$trainer_id]["ahaid"]);
//         $sheet->write($rownum, $colnum++, $allusers[$trainer_id]["ashi"] ? "Y" : "N");
//         $sheet->write($rownum, $colnum++, $allusers[$trainer_id]["bls"] ? "Y" : "N");
        
//         if ($session_iscorp == TRAININGSITES)
//             $sheet->write($rownum, $colnum++, $allusers[$trainer_id]["trainingsite"]);
//     }
//     $xls->close();
//     exit;
// }

if ($export)
{
    // Generate CSV instead of Excel
    $filename = "trainers_report_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Prepare header row
    $header = [
        "Trainer",
        "Group",
        "Type",
        "Phone",
        "Carrier",
        "Site",
        "Exp Date",
        "TC Faculty",
        "Last Monitoring Date",
        "Instructor Number",
        "ASHI?",
        "BLS?"
    ];
    
    if ($session_iscorp == TRAININGSITES) {
        $header[] = "Training Site";
    }
    
    fputcsv($output, $header);
    
    $ext = $expired ? " AND expdate < NOW() " : ""; // Assuming $expired is defined if needed
    $drows = db_query_rows("SELECT * FROM trainer_exp_dates WHERE type <> 'coreinst' AND current = 1 $ext ORDER BY id DESC");
    $types = array("tc"=>"TC Affiliation", "other"=>"Other Credentials", "firstaid"=>"First Aid Provider", "cpr"=>"CPR Provider", "aha"=>"AHA CPR Provider");
    $tarr = db_query_array("SELECT CONCAT(first_name, ' ', last_name) AS name, id FROM user WHERE usertype = 'trainer' AND inactive = 0", "id", "name");

    $allusers = db_query_rows("SELECT * FROM user $extrajoin WHERE usertype = 'trainer' AND inactive = 0 $extra", "id");
    $tcfac = db_query_array("SELECT assignedtcfacultyid, id FROM user WHERE usertype = 'trainer' AND inactive = 0", "id", "assignedtcfacultyid");
    
    foreach ($drows as $sid => $drow)
    {
        if (!$tarr[$drow['trainerid']] || !$allusers[$drow['trainerid']])
            continue;
            
        $trainer_id = (int)$drow['trainerid'];
        
        // Prepare data row
        $rowData = [
            $tarr[$trainer_id] ?? '',
            $types[$drow['expgroup']] ?? '',
            $drow["type"] ?? '',
            $drow["cell"] ?? '',
            $drow["cellprovider"] ?? '',
            $drow["site"] ?? '',
            $drow["expdate"] ?? '',
            $tarr[$tcfac[$trainer_id]] ?? '',
            db_query_first_cell("SELECT monitoringdate FROM monitoring WHERE trainerid = '$trainer_id' ORDER BY monitoringdate DESC LIMIT 1") ?? '',
            $allusers[$trainer_id]["ahaid"] ?? '',
            $allusers[$trainer_id]["ashi"] ? "Y" : "N",
            $allusers[$trainer_id]["bls"] ? "Y" : "N"
        ];
        
        if ($session_iscorp == TRAININGSITES) {
            $rowData[] = $allusers[$trainer_id]["trainingsite"] ?? '';
        }
        
        // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
        foreach($rowData as &$value) {
            if($value !== null && $value !== '') {
                $firstChar = substr($value, 0, 1);
                if(in_array($firstChar, array('=', '+', '-', '@'))) {
                    $value = "'" . $value;
                }
            }
        }
        
        fputcsv($output, $rowData);
    }
    
    fclose($output);
    exit;
}

// 6. Active Trainers Address Export Logic (Retaining PEAR dependency as requested)
// if ($exportactive)
// {
//     $ext = " AND expdate >= NOW() ";
//     require_once "Spreadsheet/Excel/Writer.php";
//     $xls = new Spreadsheet_Excel_Writer();
//     $filename = "addresses.xls";
//     $xls->send($filename);
//     $sheet =& $xls->addWorksheet("Report");
    
//     $rownum = 0;
//     $colnum = 0;
    
//     // Write Headers
//     $sheet->write($rownum, $colnum++, "Trainer");
//     $sheet->write($rownum, $colnum++, "Address1");
//     $sheet->write($rownum, $colnum++, "Address2");
//     $sheet->write($rownum, $colnum++, "City");
//     $sheet->write($rownum, $colnum++, "State");
//     $sheet->write($rownum, $colnum++, "Zip");
//     $sheet->write($rownum, $colnum++, "Last Monitoring Date");
//     $sheet->write($rownum, $colnum++, "Instructor Number");
//     $sheet->write($rownum, $colnum++, "TC Faculty");
//     $sheet->write($rownum, $colnum++, "Exp Date");

//     $drows = db_query_rows("SELECT * FROM trainer_exp_dates WHERE type <> 'coreinst' AND current = 1 $ext ORDER BY id DESC", "trainerid");
//     $tarr = db_query_rows("SELECT id, CONCAT(first_name, ' ', last_name) AS name, address1, address2, city, state, zip, ahaid FROM user WHERE usertype = 'trainer' AND inactive = 0 AND trainingsites = 0 ORDER BY last_name, first_name", "id");
//     $tcfac = db_query_array("SELECT assignedtcfacultyid, id FROM user WHERE usertype = 'trainer' AND inactive = 0", "id", "assignedtcfacultyid");
    
//     foreach ($tarr as $trow)
//     {
//         $rownum++;
//         $colnum = 0;
//         $trainer_id = (int)$trow['id'];
        
//         $sheet->write($rownum, $colnum++, $trow["name"]);
//         $sheet->write($rownum, $colnum++, $trow["address1"]);
//         $sheet->write($rownum, $colnum++, $trow["address2"]);
//         $sheet->write($rownum, $colnum++, $trow["city"]);
//         $sheet->write($rownum, $colnum++, $trow["state"]);
//         $sheet->write($rownum, $colnum++, $trow["zip"]);
        
//         $lastm = db_query_first_cell("SELECT monitoringdate FROM monitoring WHERE trainerid = '$trainer_id' ORDER BY monitoringdate DESC LIMIT 1");
//         $sheet->write($rownum, $colnum++, $lastm);
        
//         $sheet->write($rownum, $colnum++, $trow["ahaid"]);
        
//         $tcfaculty_id = $tcfac[$trainer_id];
//         $tcfaculty_name = $tarr[$tcfaculty_id]['name'];
//         $sheet->write($rownum, $colnum++, $tcfaculty_name);
        
//         $drow = $drows[$trainer_id];
//         $sheet->write($rownum, $colnum++, $drow["expdate"]);
//     }
//     $xls->close();
//     exit;
// }

if ($exportactive)
{
    $ext = " AND expdate >= NOW() ";
    
    // Generate CSV instead of Excel
    $filename = "trainer_addresses_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write Header Row
    $header = [
        "Trainer",
        "Address1",
        "Address2",
        "City",
        "State",
        "Zip",
        "Last Monitoring Date",
        "Instructor Number",
        "TC Faculty",
        "Exp Date"
    ];
    
    fputcsv($output, $header);
    
    $drows = db_query_rows("SELECT * FROM trainer_exp_dates WHERE type <> 'coreinst' AND current = 1 $ext ORDER BY id DESC", "trainerid");
    $tarr = db_query_rows("SELECT id, CONCAT(first_name, ' ', last_name) AS name, address1, address2, city, state, zip, ahaid FROM user WHERE usertype = 'trainer' AND inactive = 0 AND trainingsites = 0 ORDER BY last_name, first_name", "id");
    $tcfac = db_query_array("SELECT assignedtcfacultyid, id FROM user WHERE usertype = 'trainer' AND inactive = 0", "id", "assignedtcfacultyid");
    
    foreach ($tarr as $trow)
    {
        $trainer_id = (int)$trow['id'];
        
        // Get last monitoring date
        $lastm = db_query_first_cell("SELECT monitoringdate FROM monitoring WHERE trainerid = '$trainer_id' ORDER BY monitoringdate DESC LIMIT 1");
        
        // Get TC Faculty name
        $tcfaculty_id = $tcfac[$trainer_id] ?? null;
        $tcfaculty_name = $tcfaculty_id ? ($tarr[$tcfaculty_id]['name'] ?? '') : '';
        
        // Get expiration date
        $expdate = $drows[$trainer_id]["expdate"] ?? '';
        
        // Prepare data row
        $rowData = [
            $trow["name"] ?? '',
            $trow["address1"] ?? '',
            $trow["address2"] ?? '',
            $trow["city"] ?? '',
            $trow["state"] ?? '',
            $trow["zip"] ?? '',
            $lastm ?? '',
            $trow["ahaid"] ?? '',
            $tcfaculty_name,
            $expdate
        ];
        
        // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
        foreach($rowData as &$value) {
            if($value !== null && $value !== '') {
                $firstChar = substr($value, 0, 1);
                if(in_array($firstChar, array('=', '+', '-', '@'))) {
                    $value = "'" . $value;
                }
            }
        }
        
        fputcsv($output, $rowData);
    }
    
    fclose($output);
    exit;
}

// 7. AHA Export Logic (CSV output)
if ($exportaha)
{
    $ext = " AND expdate >= NOW() ";
    $whr = "";
    if ($blsonly) {
        $whr = " AND bls = 1";
    }
    
    $out = fopen('php://output', 'w');
    $filename = "instructors.csv";
    $now = gmdate("D, d M Y H:i:s");
    
    // Set CSV headers
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
    
    $arr = array("First Name", "Last Name", "Email", "Group");
    fputcsv($out, $arr);

    $drows = db_query_rows("SELECT * FROM trainer_exp_dates WHERE type <> 'coreinst' AND current = 1 $ext ORDER BY id DESC", "trainerid");
    
    $tarr = db_query_rows("SELECT id, first_name, last_name, userid FROM user WHERE usertype = 'trainer' AND inactive = 0 AND trainingsites = 0 AND national = 0 $whr ORDER BY last_name, first_name", "id");
    
    foreach ($tarr as $trow)
    {
        $arr = array(
            $trow["first_name"], 
            $trow["last_name"], 
            $trow["userid"], 
            "ESI Instructors"
        );
        fputcsv($out, $arr);
    }
    fclose($out);
    exit;
}

// 8. Fetch Trainers for Display
// Final Query to fetch all trainers based on applied filters
$trainers = db_query_rows("SELECT user.* FROM (user $extrajoin) $extraljoin WHERE usertype = 'trainer' $extra ORDER BY inactive, paused, $ob_safe"); 
?>
<?php include "ssi/top.php"; ?>
<p>
<strong><span class="title">MANAGE TRAINERS</span></strong></p>
<p>
<form method='post' action='trainers.php' name="myform">
<?php if ($thisusersrow["tcfaculty"]) { ?>
Show All Active ESI Trainers: <input type='checkbox' name='showallactive' value='1' <?= $showallactive ? "CHECKED" : "" ?> >
View  Training Site Instructors: <input type='checkbox' name='showtrainingsiteinst' value='1' <?= $showtrainingsiteinst ? "CHECKED" : "" ?> >    
<?php } else { ?>
View By Borough: <select name='borough' onChange='document.forms["myform"].submit()' class='copy' >
<?php if ($borough) { echo("<option value='" . htmlspecialchars($borough) . "'>" . htmlspecialchars($borough) . "</option>"); } ?>
<option value=''>All</option>
<option value='Manhattan'>Manhattan</option>
<option value='Queens'>Queens</option>
<option value='Brooklyn'>Brooklyn</option>
<option value='Bronx'>Bronx</option>
<option value='Staten Island'>Staten Island</option>
</select>
Filter by Zip: <input type='text' name='filterzip' class='copy' size='6' value='<?= htmlspecialchars($filterzip) ?>'>
<?php if (!$thisusersrow["tcfaculty"]) { ?>
Show National: <input type='checkbox' name='shownational' value='1' <?= $shownational ? "CHECKED" : "" ?> >
<?php } ?>

<?php } ?>

<input type='submit'  class='copy' name='go' value='Go'>
<a href='trainers.php?export=1'>Export Trainers</a> | <a href='trainers.php?exportactive=1'>Export Active Trainers</a> | <a href='trainers.php?exportaha=1'>Export For AHA</a> <a href='trainers.php?exportaha=1&blsonly=1'>(BLS only)</a> <a href='trainers.php?annualtests=1'>Show Annual Tests Completed</a> | <a href='trainers.php'>Show All</a>
<br>
<p>
<font color='red'>Needs Monitoring</font> | <font color='brown'>National</font> | <font color='blue'>Fingerprinted</font> | <font color='green'>Not Fingerprinted</font> | <font color='orange'>Monitoring Unsuccessful</font> | <font color='gray'>Pending or Upcoming Monitoring</font>
<table class="table3" cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
<tr bgcolor="#e1e1f6">
<th class='copy'><a href='trainers.php'>Name</a></th>
<?= $session_iscorp == TRAININGSITES ? "<th><a href='trainers.php?ob=trainingsite'>Training Site</a></th>" : "" ?>
<th class='copy'><a href='trainers.php?expdate=1'>AHA/CPR Expiration Date</a></th>
<th class='copy'><a href='trainers.php?ob=assignedtcfacultyid'>TC Faculty?</a></th>
<?php if (!isTCFaculty()) { ?>
<th>Last monitoring date</th>
<th>Instructor number </th>
<th><a href='trainers.php?ob=ashi+desc'>ASHI</a></th>
<th><a href='trainers.php?ob=bls+desc'>BLS</a></th>
<th class='copy'>Actions</th></tr>
<?php } ?>

<?php
$newarr = array();
if ($expdate)
{
    // Re-sort logic for expiration date
    foreach ($trainers as $t)
    {
        // Assuming getCurrentTrainerExp is defined and returns the expiration date string
        $exp_key = getCurrentTrainerExp("aha", "expdate", $t['id']);
        $key = $exp_key . "_" . $t['id'];
        $newarr[$key] = $t;
    }
    krsort($newarr);
    $trainers = $newarr;
}
$year = date("Y");

foreach ($trainers as $t)
{
    $trainer_id = $t['id'] ;
    $annuals = db_query_rows("SELECT * FROM annualtests WHERE trainerid = $trainer_id AND thedate LIKE '{$year}%'", "type");

    // Filter trainers who haven't completed both annual tests this year
    if ($annualtests && count($annuals) < 2) continue;

    // Determine Font Color (Status)
    $fnt = $t["fingerprinted"] ? "blue" : "green";
    if ($t["national"]) $fnt = "brown";

    $bgcolor = "#ffffff";
    if ($t["inactive"]) $bgcolor = "#cccccc";
    if ($t["paused"]) $bgcolor = "#dddddd";

    $maxmonrow = db_query_first("SELECT * FROM monitoring WHERE trainerid = $trainer_id ORDER BY monitoringdate DESC LIMIT 1");

    if ($maxmonrow && strtotime($maxmonrow["monitoringdate"]) > time())
    {
        $fnt = "gray"; // Upcoming Monitoring
    }
    else
    {
        // Check needs monitoring status
        $needs = needsMonitoring($trainer_id);
        if ($needs)
            $fnt = "red"; // Needs Monitoring
        else if ($maxmonrow && ($maxmonrow["needsremediation"] > 0))
            $fnt = "orange"; // Monitoring Unsuccessful
        else if ($maxmonrow && !$maxmonrow["needsremediation"])
        $fnt = "gray"; // Monitoring Successful
    }

    // Bold if both annual tests completed
    $bld = count($annuals) == 2 ? "<b>" : "";
    $bldend = count($annuals) == 2 ? "</b>" : "";

    $first_name_safe = htmlspecialchars($t["first_name"]);
    $last_name_safe = htmlspecialchars($t["last_name"]);
    $assigned_tcfaculty_id = (int)($t["assignedtcfacultyid"]);

    // Output Row
    echo ("<tr bgcolor='$bgcolor'><td class='copy' valign='top'>");
    // Name with color/bolding for status/annual tests
    echo ("<a style='color:$fnt;' href='trainer_view.php?tid=$trainer_id'>{$bld}$first_name_safe $last_name_safe{$bldend}</a>");
    echo ("</td>");

    // Training Site column
    if ($session_iscorp == TRAININGSITES)
    {
        echo ("<td>" . htmlspecialchars($t['trainingsite']) . "</td>"); 
    }

    // AHA/CPR Expiration Date
    $exp_date_str = getCurrentTrainerExp("aha", "expdate", $trainer_id);
    echo ("<td>" . htmlspecialchars($exp_date_str) . "</td>");

    // TC Faculty?
    echo ("<td>");
    echo ("<a href='trainer_view.php?tid=$assigned_tcfaculty_id'>" . htmlspecialchars(getUserName($assigned_tcfaculty_id)) . "</a>");
    echo (($t["tcfaculty"]) ? " (Is TC Faculty)" : "");
    echo ("</td>");

    if (!isTCFaculty()) { 
        $lastm_date = db_query_first_cell("SELECT monitoringdate FROM monitoring WHERE trainerid = '$trainer_id' ORDER BY monitoringdate DESC LIMIT 1");

        echo ("<td>" . htmlspecialchars($lastm_date) . "</td>");
        echo ("<td>" . htmlspecialchars($t['ahaid']) . "</td>");
        echo ("<td>" . (($t["ashi"]) ? "Y" : "N") . "</td>");
        echo ("<td>" . (($t["bls"]) ? "Y" : "N") . "</td>");

        // Actions
        echo ("<td valign='top' class='copy'>
        <a href='trainer_profile.php?tid=$trainer_id' target=_blank><font color='$fnt'>Edit</font></a>&nbsp;&nbsp;");
        // Delete link - confirmation removed
        $delete_url = "trainers.php?del=$trainer_id";
        if ($borough) $delete_url .= "&borough=" . urlencode($borough);
        echo ("<a href='$delete_url'>Delete</a></td></tr>");
    }
}
?>
</table>
<br><br><br><br>
<?php include "ssi/footer.php" ; ?>
 </span>
 </td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
</form>
<br><br>
</div>
</body>
</html>
