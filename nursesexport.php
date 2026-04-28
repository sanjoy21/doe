<?php 
// Include the database connection file and utility functions
include "mysql.php";
// Assumed external functions: escMe(), db_query_first_cell()

// --- Set headers for Excel file download ---
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=nurses2.xls");
header("Pragma: no-cache");
header("Expires: 0"); 

$filepath = "/tmp/nurses.csv";
$handle = fopen($filepath, "r");

// Check if the file was opened successfully
if ($handle === FALSE) {
    echo "Could not open CSV file: " . htmlspecialchars($filepath);
    exit;
}

echo "<table>";
$i = 0;

// Set the header row for the Excel output
echo "<tr><th>Name</th><th>Responder Link</th><th>CPR Expiration Date</th><th>Title</th></tr>";

// Loop through each row of the CSV file
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    // Safely retrieve name parts, ensuring they exist
    $lastname_csv = $data[1] ?? '';
    $firstname_csv = $data[2] ?? '';
    
    // Skip if essential data is missing
    if (empty($lastname_csv) || empty($firstname_csv)) {
        continue;
    }

    // Escape strings for SQL query
    $lastname_safe = escMe($lastname_csv);
    $firstname_safe = escMe($firstname_csv);

    // 1. Find Responder ID (res)
    $res = db_query_first_cell("SELECT responderid FROM responders_esi WHERE lastname = '{$lastname_safe}' AND firstname = '{$firstname_safe}' AND deleted = 0");
    
    if ($res) {
        $responder_id = (int)$res;
        
        // 2. Calculate Expiration Date and get Title
        // Use DATE_ADD in SQL for reliable date manipulation. Original used INTERVAL 2 YEAR + date.
        // We will stick close to the original for minimal change and assume MySQL syntax.
        $exp_sql = "SELECT DATE_ADD(trainingdate, INTERVAL 2 YEAR) FROM responder_training_dates WHERE responderid = {$responder_id} ORDER BY trainingdate DESC LIMIT 1";
        $exp_db = db_query_first_cell($exp_sql);
        
        $title = db_query_first_cell("SELECT title FROM responders_esi WHERE responderid = {$responder_id}");
        
        // Format the expiration date
        $exp_formatted = $exp_db ? date("m/d/Y", strtotime($exp_db)) : "N/A";
        
        // Output Found Match Row
        $name_output = htmlspecialchars($lastname_csv) . ", " . htmlspecialchars($firstname_csv);
        $title_output = htmlspecialchars($title ?? '');
        
        echo "<tr>";
        echo "<td>{$name_output}</td>";
        echo "<td><a href='editresponder.php?responderid={$responder_id}'>View</a></td>";
        echo "<td>{$exp_formatted}</td>";
        echo "<td>{$title_output}</td>";
        echo "</tr>";
        
    } else {
        // Output No Match Row
        $name_output = htmlspecialchars($lastname_csv) . ", " . htmlspecialchars($firstname_csv);
        
        echo "<tr>";
        echo "<td>{$name_output}</td>";
        echo "<td>No Match</td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "</tr>";
    }
}

fclose($handle);
echo "</table>";
?>