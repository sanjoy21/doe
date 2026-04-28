<?php
require_once('mysql.php');

// Set expiration type display
$typedispl = "FR2 Adult Pads";
if ($type == "fr2ped") {
    $typedispl = "FR2 Pediatric Pads";
}
if ($type == "FRX") {
    $typedispl = "FRX Pads";
}

// Ensure $iscorp is properly set
if (!$iscorp) {
    $iscorp = 0;
}

$title = getSessionTypeDisplay($iscorp) . " " . $typedispl . " Expiring -- " . date("F Y", $fromdt);

// Display HTML or export based on request
if (!$xls && !$csv) {
    include "ssi/top.php";
?>
    <span class="page-head"><?= htmlspecialchars($title) ?></span><br><br><br clear="all">
    <!----------begin FR2 Adult Pads box------------->
    <a href='<?= htmlspecialchars(($_SERVER['REQUEST_URI']) . '&csv=1') ?>'>Export to CSV</a>
<?php 
} elseif ($csv) {
    // Set headers for CSV download
    $filename = "aeds_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write CSV headers
    fputcsv($output, [
        "Expiration Date",
        "Serial",
        "Company",
        "Instructor",
        "Contact Name",
        "Contact Phone",
        "Contact Email"
    ]);
}
// Only display HTML table if not exporting to CSV
if (!$csv) {
?>
    <table class="table2">
        <tr>
            <th class="left">Expiration Date</th>
            <th class="left">Serial</th>
            <th class="left">Company</th>
            <th class="left">Instructor</th>
            <th class="left">Contact Name</th>
            <th class="left">Contact Phone</th>
            <th class="left">Contact Email</th>
        </tr>
<?php
}

// Build SQL query based on parameters
$fields = ["padaexpiration", "padbexpiration"];
$obstr = " CASE WHEN (padaexpiration >= '" . date("Y-m-d", $fromdt) . "' AND padaexpiration < '" . date("Y-m-d", $todt) . "') THEN padaexpiration ELSE padbexpiration END, serial ";

$model = "%fr2%";
if ($type == "fr2ped") {
    $fields = ["pediatricpads"];
    $obstr = " pediatricpads, serial ";
}

if ($type == "frx") {
    $model = "FRX";
}

if (!$type) {
    $model = "%";
}

if ($type == "ped") {
    $fields = ["pediatricpads"];
    $obstr = " pediatricpads, serial ";
    $model = "%";
}

$dtstr = " 1 = 0 ";
foreach ($fields as $f) {
    $dtstr .= " OR ($f >= '" . date("Y-m-d", $fromdt) . "' AND $f < '" . date("Y-m-d", $todt) . "') ";
}

$sord = $iscorp ? "" : " AND showsondrillreports = 1 ";
$dontcount = " AND id <> 11345";

// Execute query with proper escaping
$query = "SELECT * FROM aed_esi WHERE model LIKE '$model' AND ($dtstr) AND aedmissing = 0 AND aedstolen = 0 AND deleted = 0 AND clientid IN (SELECT id FROM company_esi WHERE iscorp = $iscorp AND deleted = 0 $sord $dontcount) ORDER BY $obstr";
$rows = db_query_rows($query);

foreach ($rows as $r) {
    // Extract data with null safety
    $padaExpiration = $r['padaexpiration'];
    $padbExpiration = $r['padbexpiration'];
    $pediatricPads = $r['pediatricpads'];
    $serial = $r['serial'];
    $clientId = $r['clientid'];
    $aedId = $r['aedid'];
    
    // Format expiration date
    if ($type == "fr2ped") {
        $expirationDate = "Ped Pads: " . date("m/d/Y", strtotime($pediatricPads));
    } else {
        $expirationDate = "Pad A: " . date("m/d/Y", strtotime($padaExpiration)) . (($csv) ? "; " : "<br>") . "Pad B: " . date("m/d/Y", strtotime($padbExpiration));
    }
    
    // Get company zip and trainers
    $zip = db_query_first_cell("SELECT zip FROM company_esi WHERE id = " . (int)$clientId);
    $responderarr = getTrainersForZip($zip);
    
    $trainername = [];
    foreach ($responderarr as $tr) {
        $trainername[] = $tr['name'];
    }
    $trainername = implode(", ", $trainername);
    
    // Get company details
    $comrow = getCompanyRow($clientId);
    
    if ($csv) {
        // Export to CSV
        fputcsv($output, [
            $expirationDate,
            $serial,
            getCompanyName($clientId),
            $trainername,
            $comrow['contactname'],
            $comrow['contactphone'],
            $comrow['contactemail']
        ]);
    } else {
        // Display HTML table
?>
        <tr>
            <td class="left"><?= $expirationDate ?></td>
            <td class="left"><a href="editaed.php?aedid=<?= $aedId ?>"><?= htmlspecialchars($serial) ?></a></td>
            <td class="left"><a href="viewcompany.php?id=<?= $clientId ?>"><?= htmlspecialchars(getCompanyName($clientId)) ?></a></td>
            <td class="left"><?= htmlspecialchars($trainername) ?></td>
            <td class="left"><?= htmlspecialchars($comrow['contactname']) ?></td>
            <td class="left"><?= htmlspecialchars($comrow['contactphone']) ?></td>
            <td class="left"><?= htmlspecialchars($comrow['contactemail']) ?></td>
        </tr>
<?php
    }
}

// Close CSV output or HTML table
if ($csv) {
    fclose($output);
    exit;
} elseif (!$xls && !$csv) {
?>
    </table>
    <!----------end FR2 Adult Pads box------------->
    <?php include "ssi/footer.php"; ?>
    </body>
    </html>
<?php
} elseif ($xls) {
?>
    </table>
<?php
}