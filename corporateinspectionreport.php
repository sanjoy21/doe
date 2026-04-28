<?php

require_once('mysql.php');

$visi = "";

if (!empty($thisusersrow["visiblezips"])) {
    $visi = " AND c.zip IN ( " . getZips($thisusersrow) . " ) ";
}

// Main query (This part is still vulnerable if getZips is flawed, but matches original structure)
$sql = "SELECT c.* FROM company_esi c WHERE c.deleted = 0 AND inspectionfrequency > 0 {$visi}";

// Define report column headers
$tmparr = [
    "Company Name",
    "Address",
    "City",
    "Zip",
    "Contact Name",
    "Contact Phone",
    "Contact email",
    "Number of AEDs",
    "AED Type",
    "Inspection Frequency",
    "Last Inspection"
];

// --- 2. CSV Export Logic ---
if (isset($xls)) {
    // Generate CSV instead of Excel
    $filename = "corporate_report_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write Headers
    fputcsv($output, $tmparr);
    
    // Fetch all companies
    $companies = db_query_rows($sql);
    
    foreach ($companies as $r) {
        // SECURITY FIX: Sanitize the ID to ensure it is a safe integer before concatenation
        $company_id = intval($r['id']);
        
        // SECURE SUB-QUERIES: ID is now safe ($company_id)
        $numaeds = db_query_first_cell( "SELECT COUNT(*) FROM aed_esi WHERE clientid = {$company_id} AND deleted = 0" );
        
        // GROUP_CONCAT may be risky; ensuring the output is not misinterpreted later is crucial.
        $types = db_query_first_cell( "SELECT GROUP_CONCAT( model ) FROM aed_esi WHERE clientid = {$company_id} AND deleted = 0 GROUP BY clientid" );
        
        $lastins = db_query_first_cell( 
            "SELECT servicecalldate 
             FROM servicecall s, servicecall_to_companyid sc 
             WHERE sc.companyid = {$company_id} 
             AND s.servicecallid = sc.servicecallid 
             AND completed = 1 
             ORDER BY servicecalldate DESC 
             LIMIT 1" 
        );
        
        // Prepare data row
        $rowData = [
            $r['companyname'] ?? '',
            $r['address'] ?? '',
            $r['city'] ?? '',
            $r['zip'] ?? '',
            $r['contactname'] ?? '',
            $r['contactphone'] ?? '',
            $r['contactemail'] ?? '',
            $numaeds ?? 0,
            $types ?? '',
            $instypes[$r['inspectionfrequency']] ?? '',
            $lastins ?? ''
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
    exit();
}
// --- 3. HTML Display Logic ---
else {
    echo "<table cellspacing='0' cellpadding='2' border='1'>";
    
    // Write Headers
    echo "<tr>";
    foreach ($tmparr as $o) { 
        echo "<th>" . htmlspecialchars($o) . "</th>";
    }
    echo "</tr>";

    // Fetch all companies
    $companies = db_query_rows($sql);
    
    foreach ($companies as $r) {
        
        echo "<tr>";
        
        // SECURITY FIX: Sanitize the ID to ensure it is a safe integer before concatenation
        $company_id = intval($r['id']);
        
        // SECURE SUB-QUERIES: ID is now safe ($company_id)
        $numaeds = db_query_first_cell( "SELECT COUNT(*) FROM aed_esi WHERE clientid = {$company_id} AND deleted = 0" );
        $types = db_query_first_cell( "SELECT GROUP_CONCAT( model ) FROM aed_esi WHERE clientid = {$company_id} AND deleted = 0 GROUP BY clientid" );
        $lastins = db_query_first_cell( 
            "SELECT servicecalldate 
             FROM servicecall s, servicecall_to_companyid sc 
             WHERE sc.companyid = {$company_id} 
             AND s.servicecallid = sc.servicecallid 
             AND completed = 1 
             ORDER BY servicecalldate DESC 
             LIMIT 1" 
        );

        // Write Data to HTML Table (using htmlspecialchars for output)
        echo "<td><a target='_blank' href='viewcompany.php?id=" . urlencode($r['id']) . "'>" . htmlspecialchars($r['companyname']) . "</a></td>";
        echo "<td>" . htmlspecialchars($r['address']) . "</td>";
        echo "<td>" . htmlspecialchars($r['city']) . "</td>";
        echo "<td>" . htmlspecialchars($r['zip']) . "</td>";
        echo "<td>" . htmlspecialchars($r['contactname']) . "</td>";
        echo "<td>" . htmlspecialchars($r['contactphone']) . "</td>";
        echo "<td>" . htmlspecialchars($r['contactemail']) . "</td>";
        echo "<td>" . htmlspecialchars($numaeds) . "</td>";
        echo "<td>" . htmlspecialchars($types) . "</td>";
        echo "<td>" . htmlspecialchars($instypes[$r['inspectionfrequency']]) . "</td>";
        echo "<td>" . htmlspecialchars($lastins) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

?>