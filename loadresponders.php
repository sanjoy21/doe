<?php
include "mysql.php";

// Initialize assumed database link for escaping functions
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. File Handling ---
$file_path = "/tmp/resp.csv";
$handle = fopen($file_path, "r");

if ($handle === false) {
    die("Error: Could not open file '{$file_path}' for reading.");
}

// --- HTML Table Header Output ---
echo "<table border='1' cellspacing='0'>
        <tr>
            <th>ID</th>
            <th>First</th>
            <th>Last</th>
            <th>PMS ID</th>
            <th>Title (DB vs File)</th>
            <th>Deleted</th>
        </tr>";

// --- 2. CSV Processing Loop ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Skip header row based on the first column value
    if (($data[0] ?? '') === "TRID") {
        continue;
    }

    // Safely retrieve and trim data fields
    $id_raw = trim( $data[0] ?? '' );
    $id_safe = mysqli_real_escape_string($db_link, $id_raw);

    // Skip if ID is empty
    if (empty($id_safe)) continue; 

    // --- 3. Fetch Responder Record ---
    $sql_fetch = "SELECT * FROM responders_esi WHERE responderid = '{$id_safe}'";
    $resp = db_query_first( $sql_fetch ); // Assumed function that returns an associative array
    
    $is_deleted = $resp['deleted'] ?? 0;
    $responder_id = $resp['responderid'] ?? null;

    if( $is_deleted ) continue; // Skip if the database record is deleted
    
    // --- 4. Process Match ---
    if( $responder_id )
    {
        $new_title_raw = trim( $data[6] ?? '' ); // Data column 6 for Title in original script logic
        $new_first_raw = trim( $data[1] ?? '' );
        $new_last_raw = trim( $data[2] ?? '' );
        $new_pmsid_raw = trim( $data[3] ?? '' );
        
        $db_first = $resp['firstname'] ?? '';
        $db_last = $resp['lastname'] ?? '';
        $db_pmsid = $resp['pmsid'] ?? '';
        $db_title = $resp['title'] ?? '';

        echo "<tr>";
        // Compare and output for each field
        compareme( $data[0], $responder_id ); // ID
        compareme( $new_first_raw, $db_first ); // First Name
        compareme( $new_last_raw, $db_last ); // Last Name
        compareme( $new_pmsid_raw, $db_pmsid ); // PMS ID
        compareme( $new_title_raw, $db_title ); // Title
        
        // Deleted Status
        echo "<td>" . ($is_deleted ? "Yes" : "No") . "</td>"; 
        
        // --- Merge Logic (Commented out in original, replaced with placeholder) ---
        $other = null; // Assumed result of finding another active user with the same PMS ID

        if( !$is_deleted && $other )
        {
            echo "<td>why are you still here?</td>";
        }
        
        echo "</tr>";
        
        // --- 5. Title Update Logic ---
        if( $db_title !== $new_title_raw )
        {
            $new_title_safe = mysqli_real_escape_string($db_link, $new_title_raw);
            $sql_update_title = "UPDATE responders_esi SET title = '{$new_title_safe}' WHERE responderid = {$id_safe}";
            
            echo htmlspecialchars($sql_update_title) . "<br>";
            db_query( $sql_update_title ); // Assumed function
        }
    }
    else
    {
        // --- Record Not Found ---
        $data1_safe = htmlspecialchars($data[1] ?? 'N/A');
        $data0_safe = htmlspecialchars($data[0] ?? 'N/A');
        echo "didn't find a match for {$data1_safe}, {$data0_safe}<br> ";
    }
}
echo "</table>";

// --- 6. Close File Handle ---
fclose( $handle );

// --- 7. compareme function definition ---
function compareme( $a, $b )
{
    $b_trimmed = trim( $b );
    $a_trimmed = trim( $a );
    
    // HTML-escape values for safe output
    $a_safe = htmlspecialchars($a_trimmed);
    $b_safe = htmlspecialchars($b_trimmed);
    
    if( strtoupper( $a_trimmed ) === strtoupper( $b_trimmed ) )
    {
        echo "<td>{$a_safe}</td>";
    }
    else
    {
        echo "<td>was '{$b_safe}', now '{$a_safe}'</td>";
    }
}
?>