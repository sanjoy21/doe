<?php
// Set flag to bypass login requirement
$nologinrequired = true;
include "mysql.php";
// Assumed external functions: db_query_rows, db_query_first_cell

// --- 1. Fetch Buildings (unused in original script, but kept for context) ---
$buildings = db_query_rows("SELECT * FROM buildings");

// --- 2. Define Time Range (Last Month) ---
$dts = [];
// Calculate the start (1st of last month) and end (1st of this month) date strings
$dts[] = [
    date("Y-m-01", strtotime("last month")), 
    date("Y-m-01", strtotime("this month"))
];

// --- 3. File Setup ---
$date_start = $dts[0][0];
$filename = "historicaldata/monthlyreport" . htmlspecialchars($date_start) . ".csv";
$filename2 = "historicaldata/historical.csv";

// Open the files for writing (w for new monthly file, a+ for appending to historical file)
$h = fopen($filename, "w");
$h2 = fopen($filename2, "a+");

// Prepare header row array
$arr = [];
$arr[] = "Month";
$arr[] = "# Schools";
$arr[] = "# Classes";
$arr[] = "# Responders";
$arr[] = "# Drills";

// Write header to the new monthly file
if ($h) {
    fputcsv($h, $arr);
}

// --- 4. Data Extraction & Output ---
foreach ($dts as $dtarr) {
    // dtarr[0] is the start date (inclusive), dtarr[1] is the end date (exclusive)
    $start_date = $dtarr[0];
    $end_date = $dtarr[1];
    
    // Safety: Escape dates for SQL queries
    $start_date_safe = addslashes($start_date);
    $end_date_safe = addslashes($end_date);
    
    // Query 1: Number of Responders trained in the period
    $sql_responders = "SELECT COUNT(*) FROM responders_esi r, responder_training_dates rtd, company_esi c 
                       WHERE c.id = r.clientid 
                       AND r.responderid = rtd.responderid 
                       AND rtd.trainingdate >= '{$start_date_safe}' 
                       AND rtd.trainingdate < '{$end_date_safe}' 
                       AND iscorp = 0";
    $numresponders = db_query_first_cell($sql_responders);
    
    // Query 2: Number of Drills conducted in the period
    $sql_drills = "SELECT COUNT(*) FROM drill r, company_esi c 
                   WHERE c.id = r.companyid 
                   AND r.drilldate >= '{$start_date_safe}' 
                   AND r.drilldate < '{$end_date_safe}' 
                   AND iscorp = 0";
    $numdrills = db_query_first_cell($sql_drills);

    // Query 3: Number of Classes held in the period
    $sql_classes = "SELECT COUNT(*) FROM class r, company_esi c 
                    WHERE c.id = r.companyid 
                    AND r.startdate >= '{$start_date_safe}' 
                    AND r.startdate < '{$end_date_safe}' 
                    AND iscorp = 0 
                    AND r.deleted = 0";
    $numclasses = db_query_first_cell($sql_classes);
    
    // Query 4: Cumulative number of Schools/Companies active up to the start of the period
    $sql_schools = "SELECT COUNT(*) FROM company_esi c 
                    WHERE `date` <= '{$start_date_safe}' 
                    AND iscorp = 0";
    $numschools = db_query_first_cell($sql_schools);
    
    // Prepare data row array
    $arr = [];
    $arr[] = $start_date . " - " . $end_date;
    $arr[] = $numschools;
    $arr[] = $numclasses;
    $arr[] = $numresponders;
    $arr[] = $numdrills;
    
    // Write data to both files
    if ($h) {
        fputcsv($h, $arr);
    }
    if ($h2) {
        fputcsv($h2, $arr);
    }
}

// --- 5. Close Files ---
if ($h) {
    fclose($h);
}
// Note: File $h2 is not explicitly closed in the original, 
// but it is good practice to close it.
if ($h2) {
    fclose($h2);
}

?>