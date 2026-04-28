<?php
function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

include "mysql.php"; 

$crow = getCompanyRow($id);

// --- 1. Determine Scope (Single Client or Entire Campus) ---
$allschools = [];
$campus_id = $crow['campusid'];

if ($campus_id) {
    // Client is part of a campus
    $allschools = getSchoolsInCampus($campus_id);
} else {
    // Client is standalone
    $allschools = [$crow];
}

// Build list of client IDs for the SQL IN clause
$cstr = "-1"; // Start with an impossible ID for safety
foreach ($allschools as $arow) {
    $client_id = $arow['id'];
    if ($client_id > 0) {
        $cstr .= ", " . $client_id;
    }
}

// --- 2. Fetch All Active AEDs in Scope ---
$sql = "SELECT a.*, c.companyname FROM aed_esi a, company_esi c 
        WHERE a.deleted = 0 
        AND a.clientid = c.id 
        AND c.deleted = 0 
        AND a.clientid IN ({$cstr})";

$result = db_query_rows($sql);

// --- 3. CSV Generation Setup ---
$filename = "aedsforschool_" . time() . ".csv";

// Set headers to force download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// --- 4. Write Header Row ---
$header = [
    "Location",
    "AED Serial Number", 
    "Model/Type",
    "AED Location/Floor",
    "Building Number",
    "Missing?",
    "Pads A Exp. Date",
    "Pads B Exp. Date",
    "Pediatric Pads Exp. Date",
    "Medical Direction Invoice Date",
    "Municipal Filing Exp. Date",
    "SC/Drill Date",
    "Reason",
    "Comments",
    "Record Type" // New column to identify record type
];

fputcsv($output, $header);

// --- 5. Write Data Rows ---
foreach ($result as $row) {
    $serial = db_escape($row['serial']);
    
    // Fetch Drills and Service Calls for the current AED
    $drills = db_query_rows("SELECT d.* FROM drill d, aed_to_drill atd WHERE atd.drillid = d.drillid AND atd.serial = '{$serial}'");
    $servicecalls = db_query_rows("SELECT d.* FROM servicecall d, aed_to_servicecall atd WHERE atd.servicecallid = d.servicecallid AND atd.serial = '{$serial}'");
    
    // --- Primary AED Row ---
    $location_floor = $row['location'] . (!empty($row['floor']) ? "/ " . $row['floor'] : "");
    
    $aedData = [
        $row['companyname'],
        $row['serial'],
        $row['model'],
        $location_floor,
        $row['buildingno'],
        $row['aedmissing'] ? "Yes" : "No",
        $row['padaexpiration'],
        $row['padbexpiration'],
        $row['pediatricpads'],
        $row['medicalinvoicedate'],
        $row['filingexpirationdate'],
        "", // SC/Drill Date (empty for main AED row)
        "", // Reason (empty for main AED row)
        "", // Comments (empty for main AED row)
        "AED" // Record type identifier
    ];
    
    // Escape formulas to prevent CSV injection
    foreach($aedData as &$value) {
        if($value !== null && $value !== '') {
            $firstChar = substr($value, 0, 1);
            if(in_array($firstChar, array('=', '+', '-', '@'))) {
                $value = "'" . $value;
            }
        }
    }
    
    fputcsv($output, $aedData);
    
    // --- Drill History Sub-rows ---
    if (is_array($drills)) {
        foreach ($drills as $drow) {
            $drill_date = $drow['drilldate'] != "0000-00-00" ? $drow['drilldate'] : "";
            
            $drillData = [
                "", // Location (blank for sub-rows)
                "", // AED Serial Number (blank for sub-rows)
                "", // Model/Type (blank for sub-rows)
                "Drill", // Label in AED Location/Floor column
                "", // Building Number (blank for sub-rows)
                "", // Missing? (blank for sub-rows)
                "", // Pads A Exp. Date (blank for sub-rows)
                "", // Pads B Exp. Date (blank for sub-rows)
                "", // Pediatric Pads Exp. Date (blank for sub-rows)
                "", // Medical Direction Invoice Date (blank for sub-rows)
                "", // Municipal Filing Exp. Date (blank for sub-rows)
                $drill_date, // SC/Drill Date
                "", // Reason (empty for drills)
                $drow['comments'], // Comments
                "Drill" // Record type identifier
            ];
            
            // Escape formulas
            foreach($drillData as &$value) {
                if($value !== null && $value !== '') {
                    $firstChar = substr($value, 0, 1);
                    if(in_array($firstChar, array('=', '+', '-', '@'))) {
                        $value = "'" . $value;
                    }
                }
            }
            
            fputcsv($output, $drillData);
        }
    }
    
    // --- Service Call History Sub-rows ---
    if (is_array($servicecalls)) {
        foreach ($servicecalls as $drow) {
            $sc_date = $drow['servicecalldate'] != "0000-00-00" ? $drow['servicecalldate'] : "";
            
            $scData = [
                "", // Location (blank for sub-rows)
                "", // AED Serial Number (blank for sub-rows)
                "", // Model/Type (blank for sub-rows)
                "Service Call", // Label in AED Location/Floor column
                "", // Building Number (blank for sub-rows)
                "", // Missing? (blank for sub-rows)
                "", // Pads A Exp. Date (blank for sub-rows)
                "", // Pads B Exp. Date (blank for sub-rows)
                "", // Pediatric Pads Exp. Date (blank for sub-rows)
                "", // Medical Direction Invoice Date (blank for sub-rows)
                "", // Municipal Filing Exp. Date (blank for sub-rows)
                $sc_date, // SC/Drill Date
                $drow['reason'], // Reason
                $drow['comments'], // Comments
                "Service Call" // Record type identifier
            ];
            
            // Escape formulas
            foreach($scData as &$value) {
                if($value !== null && $value !== '') {
                    $firstChar = substr($value, 0, 1);
                    if(in_array($firstChar, array('=', '+', '-', '@'))) {
                        $value = "'" . $value;
                    }
                }
            }
            
            fputcsv($output, $scData);
        }
    }
    
    // Add an empty row for separation after each AED's records
    fputcsv($output, array_fill(0, count($header), ""));
}

// --- 6. Close and Send ---
fclose($output);
exit();

// Note: No HTML output after this point since headers were sent
?>