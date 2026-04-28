<?php
// Assumed external functions: db_query_first
include "mysql.php"; 

// Initialize external variables safely
$xls = $xls ?? false;

$handle = fopen("/tmp/serialtest.csv", "r");

// Check if the file opened successfully
if ($handle === false) {
    // In a real application, you'd output an error message here.
    echo "Could not open CSV file /tmp/serialtest.csv.";
    exit;
}

// --- Output Headers for Excel Download ---
if ($xls) {
    header("Content-Disposition: attachment; filename=serials.xls");
    header("Content-Type: application/vnd.ms-excel");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
}

// --- Start Table Output ---
echo "<table><tr><th>School</th><th>School Code</th><th>Serial</th><th>Stolen?</th><th>Missing</th><th>Out of Service</th></tr>";

// --- Process CSV File Row by Row ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    $serial = trim($data[0] ?? '');
    
    // Skip empty serial numbers
    if (empty($serial)) {
        continue;
    }
    
    // 1. Query AED details
    $res = db_query_first("SELECT clientid, aedid, outofservice, aedmissing, aedstolen FROM aed_esi WHERE serial = '{$serial}'");
    
    // Check if an AED match was found
    if ($res && ($res['clientid'] ?? null)) {
        $client_id = (int)$res['clientid'];
        $aed_id = (int)($res['aedid'] ?? 0);

        // 2. Query Company details
        $crow = db_query_first("SELECT companyname, schoolcode FROM company_esi WHERE id = {$client_id}");
        
        $company_name = htmlspecialchars($crow['companyname'] ?? 'N/A');
        $school_code = htmlspecialchars($crow['schoolcode'] ?? 'N/A');
        
        // Prepare status strings (Y or empty)
        $stolen_flag = ($res['aedstolen'] ?? 0) ? "Y" : "";
        $missing_flag = ($res['aedmissing'] ?? 0) ? "Y" : "";
        $outofservice_flag = ($res['outofservice'] ?? 0) ? "Y" : "";

        $stolenstr = "<td>{$stolen_flag}</td>";
        $stolenstr .= "<td>{$missing_flag}</td>";
        $stolenstr .= "<td>{$outofservice_flag}</td>";
        
        // 3. Output results
        if ($xls) {
            // Output for Excel
            echo "<tr><td>{$company_name}</td><td>{$school_code}</td><td>{$serial}</td>{$stolenstr}</tr>";
        } else {
            // Output for HTML with links
            $company_link = "viewcompany.php?id={$client_id}";
            $serial_link = "viewserial.php?aedid={$aed_id}";
            
            echo "<tr><td><a href='{$company_link}'>{$company_name}</a></td><td>{$school_code}</td><td><a href='{$serial_link}'>{$serial}</a></td>{$stolenstr}</tr>";
        }
    } else {
        // Output for no match
        echo "<tr><td>no match for " . htmlspecialchars($serial) . "</td><td></td><td></td><td></td><td></td><td></td></tr>";
    }
}

// Close the file handle
fclose($handle);
?>
</table>