<?php
include "mysql.php";
function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// Initialize variables to avoid undefined variable warnings
$where = $where ?? "";
$order = $order ?? "";
$xls = $xls ?? null;
$cid = $cid ?? null;
$id = $id ?? null;
$companyname = $companyname ?? "";
$filename = $filename ?? "report_servicecalls.xls";

if (isset($cid) && $cid) {
    $resultArray = db_query_array("SELECT id FROM company_esi WHERE deleted = 0 AND campusid = '" . db_escape($cid) . "'", "id", "id");
    $possids = !empty($resultArray) ? implode(", ", $resultArray) : "0";
} else {
    $possids = isset($id) ? (int)$id : "0";
}

$sql = "SELECT r.*, region, companyname, schoolcode, iscorp FROM servicecall r, company_esi c WHERE c.id IN ($possids) AND c.deleted = 0 AND c.id = r.companyid $where $order";
$result = db_query_rows($sql);

$otherfields = array("servicecallid", "servicecalldate", "reason", "comments", "serial", "inspector", "completed");

if (isset($xls) && $xls) {
    // CSV Download (replacing Excel)
    $csv_filename = "report_servicecalls_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $csv_filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Prepare header row
    $header = ["Company Name"];
    foreach ($otherfields as $o) {
        $header[] = $o;
    }
    
    fputcsv($output, $header);
    
    foreach ($result as $row) {
        // Get serial numbers for this service call
        $sers = db_query_first_cell("SELECT GROUP_CONCAT(', ', serial) FROM aed_to_servicecall WHERE servicecallid = " . (int)($row['servicecallid'] ?? 0));
        $row['serial'] = $sers;
        
        // Get company info
        $s = getCompanyRow($row['companyid'] ?? 0);
        $companyInfo = ($s['companyname'] ?? '') . " " . ($s['address'] ?? '');
        
        // Prepare data row
        $rowData = [$companyInfo];
        foreach ($otherfields as $o) {
            $value = $row[$o] ?? '';
            
            // Format completed field
            if ($o === 'completed') {
                $value = !empty($value) ? "Y" : "N";
            }
            
            $rowData[] = $value;
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
?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<html>
<head>
    <title>Service Call Report</title>
    <link rel='stylesheet' href='../css/style.css'>
</head>
<body bgcolor='#ffffff'>
<table cellpadding="3" cellspacing="0" border="1" width="100%">
<tr><td colspan='8'><?php echo htmlspecialchars($companyname); ?></td></tr>
    <tr>
        <td valign="top"><span class="copy"><strong>Company Name</strong></span></td>
        <?php
        foreach ($otherfields as $o) {
            echo '<td valign="top"><span class="copy"><strong>' . htmlspecialchars($o) . '</strong></span></td>';
        }
        ?>
    </tr>
    <?php
    foreach ($result as $row) {
        $sers = db_query_first_cell("SELECT GROUP_CONCAT(', ', serial) FROM aed_to_servicecall WHERE servicecallid = " . (int)($row['servicecallid'] ?? 0));
        $row['serial'] = $sers;

        $s = getCompanyRow($row['companyid'] ?? 0);
        $companyDisplay = htmlspecialchars(($s['companyname'] ?? '') . " - " . ($s['address'] ?? ''));
        ?>
        <tr>
            <td valign="top"><span class="copy"><?php echo $companyDisplay; ?></span></td>
            <td valign="top"><span class="copy">
                <a href="editservicecall.php?servicecallid=<?php echo (int)($row['servicecallid'] ?? 0); ?>">
                    <?php echo (int)($row['servicecallid'] ?? 0); ?>
                </a>
            </span></td>
            <td valign="top"><span class="copy"><?php echo htmlspecialchars($row['servicecalldate'] ?? ''); ?></span></td>
            <td valign="top"><span class="copy"><?php echo htmlspecialchars($row['reason'] ?? ''); ?></span></td>
            <td valign="top"><span class="copy"><?php echo htmlspecialchars($row['comments'] ?? ''); ?></span></td>
            <td valign="top"><span class="copy"><?php echo htmlspecialchars($row['serial'] ?? ''); ?></span></td>
            <td valign="top"><span class="copy"><?php echo htmlspecialchars($row['inspector'] ?? ''); ?></span></td>
            <td valign="top"><span class="copy"><?php echo !empty($row['completed']) ? "Y" : "N"; ?></span></td>
        </tr>
        <?php
    }
    ?>
</table>
</body>
</html>