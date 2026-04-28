<?php
include "mysql.php";

// ------------------------------------------------------------------------------------------------
// 1. Variable Initialization and Sanitization
// ------------------------------------------------------------------------------------------------
$data = "responders";
$table = "responders_esi";
$extra = "CONCAT(firstname, ' ', lastname) AS fullname, title, ";

$extrawhere = "";
$orderby = "";

// ------------------------------------------------------------------------------------------------
// 2. Query Construction
// ------------------------------------------------------------------------------------------------
if ($exportedonly) {
    $extrawhere .= " AND responder_training_dates.exported = 0 ";
}

if ($ob) {
    $orderby = "ORDER BY " . $ob;
}

$extrawhere .= " AND company_esi.iscorp = " . $session_iscorp;
$cl = $classid ? " AND classid = " . $classid : "";

$sql = "SELECT 
    classid, 
    responder_training_dates.program AS tprogram, 
    responder_training_dates.id AS tid, 
    responder_training_dates.trainingdate AS ttrainingdate, 
    {$extra} 
    responders_esi.* FROM 
    responders_esi, 
    responder_training_dates, 
    company_esi 
WHERE 
    company_esi.id = responders_esi.clientid 
    AND responders_esi.deleted = 0 
    AND responders_esi.responderid = responder_training_dates.responderid 
    AND classid > 0 
    {$cl} 
    {$extrawhere} 
    {$orderby}";

$results = db_query_rows($sql);

// ------------------------------------------------------------------------------------------------
// 3. CSV Export Logic (Replaces deprecated Spreadsheet/Excel/Writer)
// ------------------------------------------------------------------------------------------------
if ($xls) {
    $filename = "responder_export_" . date('Ymd_His') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Define CSV Headers
    $headers = [
        "", "Class Number", "Last Name", "Responder Number", "Name", "Program Type",
        "Program Date", "Program Time", "Issue Date", "Expiration Date", 
        "Training Site", "Lead Instructor Name", "AHA ID", "Class Location", 
        "Company", "Code", "Address", "City", "State", "Zip", 
        "Phone Number", "Title", "Responder Email", "Responder Address", 
        "Responder Apt./Floor", "Responder City", "Responder State", 
        "Responder Zip", "Responder Phone"
    ];
    
    fputcsv($output, $headers);
    
    foreach ($results as $row) {
        $crow = $row["classid"] ? getClassRow($row["classid"]) : [];
        $comrow = $crow["companyid"] ? getCompanyRow($crow["companyid"]) : [];
        $mycomrow = $row["clientid"] ? getCompanyRow($row["clientid"]) : [];
        
        $program = $crow["code"] ? $class_names[$crow["code"]] : $row["tprogram"] ;
        
        $trainer = "";
        $ahaid = "";
        $trow = [];

        if ($crow) {
            $trainers = getTrainers($crow["id"]);
            if (!empty($trainers)) {
                $trow = reset($trainers); // Get the first trainer
                $trainer = getFullname($trow["trainerid"]);
                // Safe access for trainer AHA ID
                $ahaid = db_query_first_cell("SELECT ahaid FROM user WHERE id = '" . $trow["trainerid"] . "'");
            }
        } else {
            $trainer = $row["instructor"];
        }

        // Determine Training Site
        $trainingsite = $comrow['companyname'] ?: $row['trainingsite'];
        
        $td = strtotime($row["ttrainingdate"]);
        $td2 = mktime(0, 0, 0, date("m", $td), date("d", $td), date("Y", $td) + 2); // +2 years expiration
        
        // Determine Class Location
        if ($comrow["iscorp"]) {
            $class_location = $comrow['companyname'];
        } else {
            $class_location = "#" . $crow['id'] . " @ " . $comrow["schoolcode"];
        }

        // CSV Row Data Mapping
        $csv_row = [
            '', // Placeholder for column 1
            $crow['id'], 
            $trow['last_name'], // Trainer last name
            "http://".SUB_DOE."." .URL_WITHOUT_SUBDOMAIN. "/edituser.php?id=" . $trow['id'], // Trainer/Responder Link
            $row['fullname'], 
            $program, 
            date("m/d/y", $td), 
            date("H:i", $td), 
            date("M Y", $td), // Issue Date
            date("M Y", $td2), // Expiration Date
            "Emergency Skills, Inc.", // Training Site (Hardcoded)
            $trainer, // Lead Instructor Name
            $ahaid, // AHA ID
            $class_location, // Class Location
            $mycomrow['companyname'], // Client Company
            $mycomrow['schoolcode'], // Client Code
            $mycomrow['address'], 
            $mycomrow['city'], 
            $mycomrow['state'], 
            $mycomrow['zip'], 
            $mycomrow['schoolphone'], 
            $row['title'], 
            $trow['userid'], // Trainer Email
            $trow['address1'], // Trainer Address
            $trow['address2'], // Trainer Apt./Floor
            $trow['city'], // Trainer City
            $trow['state'], // Trainer State
            $trow['zip'], // Trainer Zip
            $trow['phone'], // Trainer Phone
        ];
        
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    exit;
}

// ------------------------------------------------------------------------------------------------
// 4. HTML Display Logic
// ------------------------------------------------------------------------------------------------
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Report</title>
</head>

<body bgcolor="#ffffff">

<table cellpadding="3" cellspacing="0" border="1" width="100%">
<tr>
        <th></th>
        <th>Name</th>
        <th>Program Type</th>
        <th>Program Date</th>
        <th>Program Time</th>
        <th>Issue Date</th>
        <th>Expiration Date</th>
        <th>Training Site</th>
        <th>Lead Instructor Name</th>
        <th>AHA ID</th>
        <th>Class Location</th>
        <th>Company</th>
        <th>Code</th>
        <th>Address</th>
        <th>City</th>
        <th>State</th>
        <th>Zip</th>
        <th>Phone Number</th>
        <th>TDID</th>
        <th>Title</th>
</tr>

<?php
$rows = [];
$cnt = 0;
// Loop through results and buffer contents for sorting
foreach ($results as $row) {
    $cnt++;
    
    $crow = $row["classid"] ? getClassRow($row["classid"]) : [];
    $comrow = $crow["companyid"] ? getCompanyRow($crow["companyid"]) : [];
    $mycomrow = $row["clientid"] ? getCompanyRow($row["clientid"]) : [];
    
    $program = $crow["code"] ? $class_names[$crow["code"]] : $row["tprogram"];
    
    $trainer = "";
    $ahaid = "";
    $trow = [];

    if ($crow) {
        $trainers = getTrainers($crow["id"]);
        if (!empty($trainers)) {
            $trow = reset($trainers);
            $trainer = getFullname($trow["trainerid"]);
            $ahaid = db_query_first_cell("SELECT ahaid FROM user WHERE id = '" . ($trow["trainerid"] ?? 0) . "'");
        }
    } else {
        $trainer = $row["instructor"];
    }

    $trainingsite = $comrow['companyname'] ?: $row['trainingsite'];
    
    $td = strtotime($row["ttrainingdate"]);
    $td2 = mktime(0, 0, 0, date("m", $td), date("d", $td), date("Y", $td) + 2);
    
    ob_start();
?>
    <tr>
        <td></td>
        <td><?=htmlspecialchars($row["fullname"])?></td>
        <td><?=htmlspecialchars($program)?></td>
        <td><?=date("m/d/y", $td)?></td>
        <td><?=date("H:i", $td)?></td>
        <td><?=date("M Y", $td)?></td>
        <td><?=date("M Y", $td2)?></td>
        <td>Emergency Skills, Inc.</td>
        <td><?=htmlspecialchars($trainer)?></td>
        <td><?=htmlspecialchars($ahaid)?></td>
        <td>
            <?=htmlspecialchars($comrow["companyname"])?>
            <?php if (!($session_iscorp ?? false)) { ?>
                , <?=htmlspecialchars($comrow["schoolcode"])?>
            <?php } ?>
        </td>
        <td><?=htmlspecialchars($mycomrow["companyname"])?></td>
        <td><?=htmlspecialchars($mycomrow["schoolcode"])?></td>
        <td><?=htmlspecialchars($mycomrow["address"])?></td>
        <td><?=htmlspecialchars($mycomrow["city"])?></td>
        <td><?=htmlspecialchars($mycomrow["state"])?></td>
        <td><?=htmlspecialchars($mycomrow["zip"])?></td>
        <td><?=htmlspecialchars($mycomrow["schoolphone"])?></td>
        <td><?=htmlspecialchars($row["tid"])?></td>
        <td><?=htmlspecialchars($row["title"])?></td>
        <td><?=date("m/d/y", $td)?></td>
    </tr>
<?php 
    $contents = ob_get_contents();
    ob_end_clean();
    
    // Key used for sorting the rows before display
    $key = str_pad($program, 40) . "_" . date("Y-m-d", $td) . "_" . str_pad($mycomrow['companyname'], 40) . "_" . $crow['id'] . "_" . $cnt;

    $rows[$key] = $contents;
} 
// Sort rows by the constructed key
ksort($rows);
// Output sorted rows
foreach ($rows as $r) {
    echo $r;
}
?>
</table>