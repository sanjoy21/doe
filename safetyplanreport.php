<?php
// Initialize external variables safely
$nologinrequired = true;
// Default date set in original code is "2016-12-01"
$datetorun = $_GET['datetorun'] ?? "2016-12-01";
$schoolcode = $_GET['schoolcode'] ?? '';
$go = $_GET['go'] ?? null;

include "mysql.php";
// Assumed external functions: db_query_rows, db_query_array, db_query_first_cell, getCompanyAddress
?>

<form method='get'>
Date: <input type='text' name='datetorun' value='<?php echo htmlspecialchars($datetorun); ?>'><br>
Specific Schoolcode: <input type='text' name='schoolcode' value='<?php echo htmlspecialchars($schoolcode); ?>'> (optional)<br>
<input type='submit' name='go' value='Go'>
</form>

<?php
if ($go) {
    // Safety: Escape inputs for use in SQL queries
    $schoolcode_safe = addslashes($schoolcode);
    $datetorun_safe = addslashes($datetorun);
    $sc = !empty($schoolcode) ? " AND schoolcode = '{$schoolcode_safe}' " : "";

    // --- 1. Fetch Schools ---
    $sql_schools = "SELECT * FROM company_esi 
                    WHERE iscorp = 0 AND deleted = 0 AND retired = 0 
                    AND showsondrillreports = 1 {$sc}";
    $schools = db_query_rows($sql_schools);

    // --- 2. Get Upcoming Class IDs ---
    // Note: The original SQL uses STARTDATE > NOW(), which might be inefficient for a complex report.
    $sql_classes = "SELECT class.id 
                    FROM class, company_esi 
                    WHERE company_esi.deleted = 0 
                    AND class.deleted = 0 
                    AND companyid = company_esi.id 
                    AND iscorp = 0 
                    AND startdate > NOW() 
                    AND showsondrillreports = 1 
                    AND accepted = 1 
                    AND canceldate IS NULL";
    $classes_array = db_query_array($sql_classes, "id", "id");
    
    // Ensure the list is never empty to prevent SQL errors (add a dummy ID)
    if (empty($classes_array)) {
        $classes_array[] = -1;
    }
    $classes_list = implode(",", $classes_array);

    // --- 3. Setup Output Files and Table ---
    $h = fopen("safetyplanreport.csv", "w");
    echo "<table border=1 cellspacing=0 cellpadding=2><tr><th>Name</th><th>School Code</th><th>Address</th><th>Building Code</th><th>Num current</th><th>Num Upcoming</th><th>Latest training Date</th><th>Next Training Date</th></tr>";
    
    // Write header to CSV
    fputcsv($h, ["Name", "School Code", "Address", "Building Code", "Num current", "Num Upcoming", "Last Exp Date", "Next Training Date"]);

    // --- 4. Process Schools and Buildings ---
    foreach ($schools as $r) {
        $company_id = $r['id'] ?? 0;
        $location_code = $r['locationcode'] ?? '';
        $company_name = htmlspecialchars($r['companyname'] ?? 'N/A');
        $school_code = htmlspecialchars($r['schoolcode'] ?? 'N/A');

        // Get building codes associated with the school's location code
        $buildingcodes = db_query_array("SELECT buildingcode FROM location_to_building WHERE locationcode = '" . addslashes($location_code) . "'", "buildingcode", "buildingcode");
        
        $addr = getCompanyAddress($company_id, $r); // Assumed external function

        // --- A. Process by Building Code ---
        if (!empty($buildingcodes)) {
            foreach ($buildingcodes as $b) {
                $b_safe = addslashes($b);
                
                // Get all responders associated with this specific building code
                $responders_array = db_query_array("SELECT responderid FROM responders_esi WHERE buildingcode = '{$b_safe}'", "responderid", "responderid");
                
                // Add dummy ID to prevent SQL error if list is empty
                $responders_array[] = -1;
                $responders_list = implode(",", $responders_array);

                // Count current responders trained since $datetorun
                $count = db_query_first_cell("SELECT COUNT(*) FROM responder_training_dates WHERE responderid IN ({$responders_list}) AND trainingdate >= '{$datetorun_safe}'");

                if ($count < 2) {
                    // Count responders enrolled in upcoming classes
                    $count2 = db_query_first_cell("SELECT COUNT(*) FROM responder_to_class WHERE responderid IN ({$responders_list}) AND classid IN ({$classes_list})");
                    
                    if (($count2 + $count) < 2) {
                        // Get latest training date
                        $max = db_query_first_cell("SELECT MAX(trainingdate) FROM responder_training_dates WHERE responderid IN ({$responders_list})");
                        
                        // Get next scheduled class date for the company
                        $nexttd = db_query_first_cell("SELECT MAX(startdate) FROM class WHERE companyid = '{$company_id}' AND startdate > NOW()");
                        
                        $b_html = htmlspecialchars($b);
                        $max_display = htmlspecialchars($max ?? '');
                        $nexttd_display = htmlspecialchars($nexttd ?? '');
                        $addr_html = htmlspecialchars($addr);
                        
                        // HTML Output
                        echo "<tr><td><a href='viewcompany.php?id={$company_id}#resps'>{$company_name}</a></td><td>{$school_code}</td><td>{$addr_html}</td><td>{$b_html}</td><td>{$count}</td><td>{$count2}</td><td>{$max_display}</td><td>{$nexttd_display}</td></tr>";
                        
                        // CSV Output (Note: CSV cannot handle live HTML links directly)
                        $arr = [
                            $company_name, 
                            $school_code, 
                            $addr_html, 
                            $b_html, 
                            $count, 
                            $count2, 
                            $max_display, 
                            $nexttd_display
                        ];
                        fputcsv($h, $arr);
                    }
                }
            }
        } 
        // --- B. Process by Company ID (No Building Code Found) ---
        elseif (empty($buildingcodes)) {
            // Get all responders linked directly to the company ID
            $responders_array = db_query_array("SELECT responderid FROM responders_esi WHERE clientid = '{$company_id}'", "responderid", "responderid");
            
            $responders_array[] = -1;
            $responders_list = implode(",", $responders_array);
            
            // Count current responders trained since $datetorun
            $count = db_query_first_cell("SELECT COUNT(*) FROM responder_training_dates WHERE responderid IN ({$responders_list}) AND trainingdate >= '{$datetorun_safe}'");
            
            if ($count < 2) {
                // Count responders enrolled in upcoming classes
                $count2 = db_query_first_cell("SELECT COUNT(*) FROM responder_to_class WHERE responderid IN ({$responders_list}) AND classid IN ({$classes_list})");
                
                if (($count2 + $count) < 2) {
                    // Get latest training date
                    $max = db_query_first_cell("SELECT MAX(trainingdate) FROM responder_training_dates WHERE responderid IN ({$responders_list})");
                    
                    // Get next scheduled class date for the company
                    $nexttd = db_query_first_cell("SELECT MAX(startdate) FROM class WHERE companyid = '{$company_id}' AND startdate > NOW()");

                    $max_display = htmlspecialchars($max ?? '');
                    $nexttd_display = htmlspecialchars($nexttd ?? '');
                    $addr_html = htmlspecialchars($addr);
                    
                    // HTML Output
                    echo "<tr><td><a href='viewcompany.php?id={$company_id}#resps'>{$company_name}</a></td><td>{$school_code}</td><td>{$addr_html}</td><td>no bc</td><td>{$count}</td><td>{$count2}</td><td>{$max_display}</td><td>{$nexttd_display}</td></tr>";
                    
                    // CSV Output
                    $arr = [
                        $company_name, 
                        $school_code, 
                        $addr_html, 
                        "no bc", 
                        $count, 
                        $count2, 
                        $max_display, 
                        $nexttd_display
                    ];
                    fputcsv($h, $arr);
                }
            }
        }
    }

    echo "</table>";
    echo "<a href='safetyplanreport.csv'>Download Here</a>";
    fclose($h);
    exit;
}
?>