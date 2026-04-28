<?php
require_once('mysql.php');

$typedispl = "";
$iscorp = 1; // All reports seem corporate-focused


// --- 2. Determine Report Type Display Name and Title ---
switch ($type) {
    case "last10corp":
        $typedispl = "Last Training > 10 Mo. Ago";
        break;
    case "notrainedcorp60":
        $typedispl = "Cards Exp. in 60 Days";
        break;
    case "notrainedcorp":
        $typedispl = "No Trained Responders";
        break;
    default:
        // Default case: Municipal Filing Expiring
        // Ensure $fromdt is a valid timestamp for date formatting
        $safe_from_dt = is_numeric($fromdt) ? (int)$fromdt : time();
        $typedispl = "Municipal Filing Expiring -- " . date("F Y", $safe_from_dt);
        break;
}

$title = getSessionTypeDisplay($iscorp) . " " . $typedispl;

// --- 3. Handle CSV Export Headers ---
if ($csv) {
    // Sanitize filename parts
    $fn = preg_replace('/[^a-zA-Z0-9_-]/', '_', $typedispl);
    $filename = $fn . '_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write CSV headers
    fputcsv($output, ["#", "Expiration Date", "Company Name", "Company ID"]);
}
?>
<?php if (!$csv) { ?>
<?php include "ssi/top.php"; ?>

         <span class="page-head"><?= htmlspecialchars($title) ?></span><br><br><br clear="all">
         <!----------begin FR2 Adult Pads box------------->
<A href='<?= htmlspecialchars($_SERVER["REQUEST_URI"]) ?>&csv=1'>Export to CSV</a>
<?php } ?>

<?php if (!$csv) { ?>
         <table class="table2">
           <tr>
             <th class="left">Expiration Date</th> 
             <th class="left">Name</th> 
           </tr>
<?php } ?>

<?php
// --- 4. Query Database based on Report Type ---
$rows = [];
$safe_from_date = is_numeric($fromdt) ? date("Y-m-d", (int)$fromdt) : null;
$safe_to_date = is_numeric($todt) ? date("Y-m-d", (int)$todt) : null;

if (empty($type)) {
    // Municipal Filing Expiring
    if ($safe_from_date && $safe_to_date) {
        $rows = db_query_rows("
            SELECT * FROM company_esi 
            WHERE (filingexpirationdate >= '{$safe_from_date}' AND filingexpirationdate < '{$safe_to_date}') 
            AND deleted = 0 AND iscorp = 1 AND excludereporting = 0 AND excludetraining = 0 
            ORDER BY filingexpirationdate
        ");
    }
} elseif ($type === "last10corp") {
    // Last Training > 10 Mo. Ago
    if ($safe_from_date) {
        // Query logic seems designed to find companies without training after $fromdt (10 months ago)
        $rows = db_query_rows("
            SELECT * FROM company_esi 
            WHERE id NOT IN ( 
                SELECT DISTINCT(clientid) 
                FROM responders_esi r, responder_training_dates rtd 
                WHERE r.responderid = rtd.responderid AND rtd.trainingdate > '{$safe_from_date}' 
            ) 
            AND deleted = 0 AND iscorp = 1 AND excludereporting = 0 
            AND emailtype LIKE '%Training%' AND showlasttraining = 1
        ");
    }
} elseif ($type === "notrainedcorp60") {
    // Cards Exp. in 60 Days
    if ($safe_from_date) {
        $rows = db_query_rows("
            SELECT * FROM company_esi 
            WHERE id NOT IN ( 
                SELECT DISTINCT(clientid) 
                FROM responders_esi r, responder_training_dates rtd 
                WHERE r.responderid = rtd.responderid AND rtd.trainingdate > '{$safe_from_date}' 
            ) 
            AND deleted = 0 AND iscorp = 1 AND excludereporting = 0 
            AND emailtype LIKE '%Training%' AND showcardsexp = 1
        ");
    }
} elseif ($type === "notrainedcorp") {
    // No Trained Responders
    if ($safe_from_date) {
        $rows = db_query_rows("
            SELECT * FROM company_esi 
            WHERE id NOT IN ( 
                SELECT DISTINCT(clientid) 
                FROM responders_esi r, responder_training_dates rtd 
                WHERE r.responderid = rtd.responderid AND rtd.trainingdate > '{$safe_from_date}' 
            ) 
            AND deleted = 0 AND iscorp = 1 AND excludereporting = 0 
            AND emailtype LIKE '%Training%' AND shownotrained = 1
        ");
    }
}

// --- 5. Process and Display Results ---
$i = 0;

foreach ((array)$rows as $row) {
    $dtstr = "";
    
    // Default expiration date (Municipal Filing)
    if (!empty($row['filingexpirationdate'])) {
        $dtstr = date("m/d/Y", strtotime($row['filingexpirationdate']));
    }

    $company_id_safe = $row['id'];
    
    // Specific date calculation logic for corporate training reports
    if ($type === "last10corp" || $type === "notrainedcorp" || $type === "notrainedcorp60") {
        
        $max_startdate = db_query_first_cell("SELECT MAX(startdate) FROM class WHERE companyid = {$company_id_safe} AND deleted = 0");
        $dt = strtotime($max_startdate);

        if ($type === "last10corp") {
            $dtstr = ($dt > 0) ? date("m/d/Y", $dt) : "N/A";
            // Skip if last10corp and no training date found
            if ($dtstr === "N/A") {
                continue;
            }
        } else {
            // For notrainedcorp/notrainedcorp60, calculate card expiration date (2 years after training)
            if ($dt > 0 && $max_startdate !== '0000-00-00') {
                $dtstr = date("m/d/Y", mktime(0, 0, 0, date("m", $dt), date("d", $dt), date("Y", $dt) + 2));
            } else {
                $dtstr = "N/A";
            }
        }
    }
    
    // Final check for uninitialized/null dates
    if ($dtstr === "12/31/1969" || empty($dtstr)) {
        $dtstr = "N/A";
    }

    $i++;
    $company_name_safe = $row['companyname'];

    if ($csv) {
        // Export to CSV
        fputcsv($output, [
            $i,
            $dtstr,
            $company_name_safe,
            $company_id_safe
        ]);
    } else {
        // Display HTML table
?>
           <tr>
             <td class="left"><?= $i ?>. <?= htmlspecialchars($dtstr) ?></td>
             <td class="left"><a href="http://<?= htmlspecialchars(getUrlPrefix($session_iscorp)) ?>.<?php echo URL_WITHOUT_SUBDOMAIN;?>/viewcompany.php?id=<?= $company_id_safe ?>"><?= htmlspecialchars($company_name_safe) ?></a></td>
           </tr>
<?php 
    }
}

// Close CSV output stream
if ($csv) {
    fclose($output);
    exit;
}
?>

<?php if (!$csv) { ?>
         </table>
         
         <!----------end FR2 Adult Pads box------------->  

<?php include "ssi/footer.php"; ?>
</body>
</html>
<?php } ?>