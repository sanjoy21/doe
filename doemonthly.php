<?php
include "mysql.php";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="report_responders.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// --- 1. Fetch School Data ---
// Only fetch non-corporate, active, non-retired schools
$sql = "SELECT 
            id, companyname, schoolcode, campusid, borough, address, city, zip, principalname, principalemail 
        FROM 
            company_esi c 
        WHERE 
            iscorp = '0' 
            AND c.deleted = 0 
            AND c.donotinclude = 0 
            AND retired = 0 
        ORDER BY 
            companyname";

global $link;
$result = mysqli_query($link, $sql) or die(mysqli_error($link) . $sql);

// --- 2. Prepare Header Row ---
$headers = array();

$headers[] = "ATS System Code (DBN)";
$headers[] = "School Name";
$headers[] = "School Address";
$headers[] = "Principal's Name";
$headers[] = "Principal's Email";

// Define the date ranges for the 10 monthly compliance columns
// Format: Month/Year (display) => [Start Date, End Date (exclusive)]
$compliance_periods = [
    "09/17" => ["09/01/2015", "10/01/2017"],
    "10/17" => ["10/01/2015", "11/01/2017"],
    "11/17" => ["11/01/2015", "12/01/2017"],
    "12/17" => ["12/01/2015", "01/01/2018"],
    "01/18" => ["01/01/2016", "02/01/2018"],
    "02/18" => ["02/01/2016", "03/01/2018"],
    "03/18" => ["03/01/2016", "04/01/2018"],
    "04/18" => ["04/01/2016", "05/01/2018"],
    "05/18" => ["05/01/2016", "06/01/2018"],
    "06/18" => ["06/01/2016", "07/01/2018"],
];

foreach ($compliance_periods as $label => $dates) {
    $headers[] = "AED/CPR Status (Compliant/Non-Compliant) {$label}";
    $headers[] = "Number of Responders";
}

// Write header row
fputcsv($output, $headers);

// --- 3. Process Data Rows ---
while ($row = mysqli_fetch_assoc($result)) {
    $row_data = array();
    
    // School Static Data
    $row_data[] = getDBN($row["schoolcode"] ?? '');
    $row_data[] = $row["companyname"] ?? '';
    
    // Build address with null safety
    $address_parts = array();
    if (!empty($row["address"])) $address_parts[] = $row["address"];
    if (!empty($row["city"])) $address_parts[] = $row["city"];
    if (!empty($row["borough"])) $address_parts[] = $row["borough"];
    if (!empty($row["zip"])) $address_parts[] = $row["zip"];
    $row_data[] = implode(", ", $address_parts);
    
    $row_data[] = $row["principalname"] ?? '';
    $row_data[] = $row["principalemail"] ?? '';

    // Monthly Compliance Data
    foreach ($compliance_periods as $dates) {
        list($dt1, $dt2) = $dates;
        $numr = getBetweenDates($row, $dt1, $dt2);
        
        $status = $numr > 0 ? "Compliant" : "Non-Compliant";
        
        $row_data[] = $status;
        $row_data[] = $numr;
    }
    
    fputcsv($output, $row_data);
}

fclose($output);
exit;

// --- 4. Helper Function for Data Retrieval ---

/**
 * Counts the number of distinct, non-deleted responders associated with a company (clientid)
 * who received training between the two specified dates (inclusive start, exclusive end).
 *
 * @param array $row The company row array containing the 'id' (clientid).
 * @param string $dt1 The start date of the period (e.g., "09/01/2015").
 * @param string $dt2 The end date of the period (e.g., "10/01/2017").
 * @return int The count of distinct responders.
 */
function getBetweenDates($row, $dt1, $dt2)
{
    // Convert human-readable dates to MySQL format
    $mydt = date("Y-m-d", strtotime($dt1));
    $mydt2 = date("Y-m-d", strtotime($dt2));

    // Sanitize dates and the company ID for security, although dates from a constant array 
    // and $row[id] (assumed int) are usually safe here.
    global $link;
    $esc_mydt = mysqli_real_escape_string($link, $mydt);
    $esc_mydt2 = mysqli_real_escape_string($link, $mydt2);
    $clientid = intval($row['id'] ?? 0);
    
    $sql = "SELECT 
                COUNT(DISTINCT rt.responderid) 
            FROM 
                responders_esi r, 
                responder_training_dates rt 
            WHERE 
                r.responderid = rt.responderid 
                AND (rt.trainingdate >= '{$esc_mydt}' AND rt.trainingdate < '{$esc_mydt2}') 
                AND r.deleted = 0 
                AND clientid = {$clientid}";
                
    $numr = db_query_first_cell($sql);
    
    return intval($numr ?? 0);
}

?>