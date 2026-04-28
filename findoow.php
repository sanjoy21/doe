<?php
$nologinrequired = 1;
include "mysql.php";

// --- 1. Submission and Processing Logic ---
$go = $_POST['go'] ?? null;

if( $go )
{
    $db_link = $GLOBALS['link'] ?? null; // Get database link for escaping
    
    // Determine file location: uploaded file or default 'oow.csv'
    $loc = ($_FILES["thefile"]["tmp_name"] ?? null) ? $_FILES["thefile"]["tmp_name"] : "oow.csv";
    $handle = fopen($loc, "r");
    
    if ($handle === false) {
        die("Error: Could not open CSV file at {$loc}");
    }
    
    $schooltoserial = array();

    // --- Process CSV and Map Serials to Client IDs ---
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
        $serial_raw = trim($data[0] ?? '');
        if( !$serial_raw ) {
            continue;
        }

        // Sanitize serial number for the query
        $serial_safe = mysqli_real_escape_string($db_link, $serial_raw);
        $serial_zero_safe = mysqli_real_escape_string($db_link, '0' . $serial_raw);
        
        // Query to find AED by serial number
        $sql = "SELECT * FROM aed_esi WHERE (serial = '{$serial_safe}' OR serial = '{$serial_zero_safe}') AND deleted = 0";
        $res = db_query_first( $sql );
        
        $clientid = $res['clientid'] ?? 0;
        
        // Map serials to client IDs (schools)
        $schooltoserial[$clientid][] = $serial_raw;
    }
    fclose($handle);
    
    // --- 2. CSV Report Generation Setup ---
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="oow.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Header Row
    $headers = array(
        "Serial(s)",
        "Num AED(s)",
        "The schools the AEDs are assigned to",
        "The number of non-OOW units at these schools",
        "The # of OOW units listed as stolen",
        "When the school is due to renew their certifications",
        "Total number of trained responders",
        "Responder Exp Dates"
    );
    fputcsv($output, $headers);

    // --- 3. Iterate Through Schools and Write Data ---
    foreach( $schooltoserial as $clientid => $serials )
    {
        $clientid_safe = (int)$clientid; // Safe for database queries
        
        // Start row data
        $row_data = array();
        
        // 1. Serial(s) - join with newlines (Excel will display in separate lines within cell)
        $row_data[] = implode("\n", $serials);
        
        // 2. Num AED(s)
        $row_data[] = count($serials);
        
        if( $clientid_safe === 0 ) {
            // If clientid is null/0, fill remaining columns with empty values
            $row_data[] = ''; // School name
            $row_data[] = ''; // Non-OOW units
            $row_data[] = ''; // OOW stolen units
            $row_data[] = ''; // Renewal date
            $row_data[] = ''; // Trained responders count
            $row_data[] = ''; // Responder expiration dates
        } else {
            // 3. School Name
            $row_data[] = getCompanyName($clientid_safe) ?? '';
            
            // 4. Num Non-OOW Units at School
            $sql_non_oow = "SELECT COUNT(*) FROM aed_esi WHERE clientid = {$clientid_safe} AND outofwarranty = 0";
            $non_oow = db_query_first_cell($sql_non_oow) ?? 0;
            $row_data[] = $non_oow;
            
            // 5. Num OOW Units Listed as Stolen
            $sql_oow_stolen = "SELECT COUNT(*) FROM aed_esi WHERE clientid = {$clientid_safe} AND outofwarranty = 1 AND aedstolen = 1";
            $oow_stolen = db_query_first_cell($sql_oow_stolen) ?? 0;
            $row_data[] = $oow_stolen;
            
            // 6. Renewal Date (Placeholder in original script)
            $row_data[] = '';
            
            // 7 & 8. Responder Training Data
            $tr = getNonExpiredResponders($clientid_safe) ?? array();
            $row_data[] = count($tr);
            
            $dts = array();
            if (is_array($tr) && !empty($tr)) {
                foreach( $tr as $trow )
                {
                    // Calculate expiration date (2 years after training date)
                    $training_date = $trow['trainingdate'] ?? date('Y-m-d');
                    $td = date("Y-m-d", strtotime($training_date . " + 2 years"));
                    $dts[$td] = ($dts[$td] ?? 0) + 1;
                }
            }
            
            // Group and format expiration dates
            ksort($dts);
            $str_array = array();
            foreach( $dts as $d => $v )
            {
                $str_array[] = "{$d} : {$v}";
            }
            $row_data[] = implode("\n", $str_array);
        }
        
        fputcsv($output, $row_data);
    }

    // --- 4. Finalize Report and Exit ---
    fclose($output);
    exit;
}
?>

<form method='post' enctype="multipart/form-data">
    <input type='file' name='thefile'> <input type='submit' name='go' value='Go'>
</form>