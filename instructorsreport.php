<?php
// PHP 8.2 conversion for Trainer Report Generation

// Assuming 'mysql.php' handles the database connection using mysqli or PDO 
// and defines the required functions (db_query_rows, db_query_first_cell, getCurrentTrainerExp, getFormattedDate).
require_once('mysql.php');

// --- 1. Fetch Data ---
// Safely query for all active trainers, ordered by name.
// Assuming db_query_rows handles errors internally, otherwise die() should be updated.
$rids = db_query_rows("SELECT * FROM user WHERE usertype = 'trainer' AND inactive = 0 ORDER BY last_name, first_name");

// --- 2. Setup CSV Download Headers ---
$filename = "instructors_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Open the output stream for writing CSV data
$output = fopen('php://output', 'w');

// --- 3. Define Header Row ---
$header = array(
    "Last Name",
    "First Name",
    "Cell Phone",
    "Email",
    "Address",
    "City",
    "State",
    "Zip",
    "AHA ID",
    "AHA CPR INSTRUCTOR Expiration Date",
    "Type (AHA)",
    "CPR PROVIDER Expiration Date",
    "Type (CPR)",
    "2020 Update",
    "First Aid",
    "Alive FA",
    "Corporate",
    "TC Faculty",
    "Training Site",
    "Last Renewal Date",
    "Next Monitoring Date",
    "Monitoring Quarter",
    "Boroughs"
);

// Write the header row to the CSV
fputcsv($output, $header);

// --- 4. Process and Write Data Rows ---
foreach ($rids as $trow) {
    
    // Check for the next monitoring date
    // Assumes $trow['id'] is available and safe to use in the query
    $nextmon = db_query_first_cell("SELECT MAX(nextmonitoringdate) FROM monitoring WHERE trainerid = " . (int)$trow['id']);
    
    // Check for boroughs
    $isremote = db_query_first_cell("SELECT GROUP_CONCAT(borough SEPARATOR ', ') FROM trainer_to_borough WHERE trainerid = " . (int)$trow['id']);

    // Build the data row array
    $data_row = array(
        $trow["last_name"],
        $trow["first_name"],
        $trow["cell"],
        $trow["userid"], // Assuming userid holds the email
        $trow["address1"],
        $trow["city"],
        $trow["state"],
        $trow["zip"],
        $trow["ahaid"],
        getCurrentTrainerExp("aha", "expdate", $trow['id']),
        getCurrentTrainerExp("aha", "type", $trow['id']),
        getCurrentTrainerExp("cpr", "expdate", $trow['id']),
        getCurrentTrainerExp("cpr", "type", $trow['id']),
        (isset($trow["2020update"]) && $trow["2020update"]) ? "Yes" : "No",
        (isset($trow["firstaid"]) && $trow["firstaid"]) ? "Yes" : "No",
        (isset($trow["alivefa"]) && $trow["alivefa"]) ? "Yes" : "No",
        (isset($trow["iscorp"]) && $trow["iscorp"]) ? "Yes" : "No",
        (isset($trow["tcfaculty"]) && $trow["tcfaculty"]) ? "Yes" : "No",
        $trow["trainingsite"],
        $trow["lastrenewaldate"],
        getFormattedDate($nextmon),
        ($trow["monitoringquarter"]) ? $trow["monitoringquarter"] : "Not Set",
        $isremote,
    );
    
    // Write the data row to the CSV
    fputcsv($output, $data_row);
}

// --- 5. Finalize Output ---
fclose($output);
exit; // Terminate script execution after outputting the file

?>