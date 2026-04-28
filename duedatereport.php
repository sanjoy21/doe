<?php
// NOTE: This code uses manual sanitation (db_escape_string) for all user inputs 
// to mitigate SQL Injection. The function db_escape_string() (or similar) 
// and database helper functions (e.g., db_query_rows, db_query_first_cell) 
// MUST be defined and functional in 'mysql.php'.

require_once('mysql.php');

// Define a placeholder for the escaping function, required for security.
if (!function_exists('db_escape_string')) {
    function db_escape_string($str) {
        // !!! REPLACE THIS WITH YOUR ACTUAL ESCAPING FUNCTION !!!
        global $link; // Assuming $link is your mysqli connection object
        return mysqli_real_escape_string($link, (string) $str);
    }
}

// Check if the 'go' flag is set from the form submission
$go = $_POST['go'] ?? null;
$fdate = $_POST['fdate'] ?? null;
$tdate = $_POST['tdate'] ?? null;

if ($go) {
    
    $ext = "";
    
    // --- 1. Sanitize and Build Date Filter (CRITICAL SECURITY AREA) ---
    
    // Filter by start date ($fdate)
    if ($fdate) {
        // SECURE: Use date() on the output of strtotime() for safe formatting, 
        // after escaping the user input to prevent injection in strtotime() if it were possible.
        // If strtotime is considered safe on its own, only the final value needs escaping for SQL.
        $start_date_sql = date("Y-m-d", strtotime(db_escape_string($fdate)));
        $ext .= " AND nextservicecalldate >= '{$start_date_sql}' ";
    }
    
    // Filter by end date ($tdate)
    if ($tdate) {
        $end_date_sql = date("Y-m-d", strtotime(db_escape_string($tdate)));
        $ext .= " AND nextservicecalldate <= '{$end_date_sql}' ";
    }
    
    // --- 2. Construct and Execute Main Query ---
    // Fetch all relevant service calls for corporate companies within the date range.
    $sql = "SELECT 
                sc.nextservicecalldate, 
                c.companyname, 
                sc.companyid, 
                CONCAT(c.address, ', ', c.floor, ',', c.city, ', ', c.state, ' ', c.zip) AS address, 
                c.contactphone, 
                c.contactname 
            FROM 
                servicecall sc, 
                company_esi c 
            WHERE 
                c.id = sc.companyid 
                AND c.iscorp = 1 
                {$ext}
            ORDER BY
                sc.companyid, sc.nextservicecalldate DESC"; 
                
    // Assuming db_query_rows safely executes the query and returns an array of results
    $rids = db_query_rows($sql);

    // --- 3. CSV Setup ---
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="servicecalls.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Header Row
    $headers = array(
        "Company",
        "Address",
        "Contact",
        "Phone",
        "Due Date"
    );
    fputcsv($output, $headers);

    // --- 4. Process Data Rows (Filtering for Latest Call) ---
    foreach ($rids as $trow) {
        // This inner check finds if there is *any* service call date for the same company 
        // that is *after* the current service call's date. If there is, it means the current 
        // service call is NOT the latest one, and it is skipped.
        // NOTE: This logic means only the latest 'nextservicecalldate' for a company in the result set is included.
        
        // SECURE: $trow[companyid] is from the database and should be safe (intval), 
        // $trow[nextservicecalldate] must be escaped for SQL safety.
        $esc_nextdate = db_escape_string($trow['nextservicecalldate']);
        $company_id = intval($trow['companyid']);
        
        $anyafter = db_query_first_cell("SELECT 
                                            servicecallid 
                                        FROM 
                                            servicecall 
                                        WHERE 
                                            companyid = {$company_id} 
                                            AND nextservicecalldate > '{$esc_nextdate}'
                                            LIMIT 1");

        if ($anyafter) {
            continue; // Skip if a later service call exists for this company
        }
        
        // Prepare row data with null safety
        $row_data = array(
            $trow["companyname"] ?? '',
            $trow["address"] ?? '',
            $trow["contactname"] ?? '',
            $trow["contactphone"] ?? '',
            $trow["nextservicecalldate"] ?? ''
        );
        
        // Write the row to CSV
        fputcsv($output, $row_data);
    }

    fclose($output);
    exit; // Stop execution after CSV file generation
}

// --- 5. HTML Form Display ---
?>
<?php include "ssi/top.php"; ?>
<form method='post'>
    From: <input type='text' name='fdate' value="<?=htmlspecialchars(date("Y-m-d"))?>"><br>
    To: <input type='text' name='tdate' value="<?=htmlspecialchars($tdate)?>"><br>
    <input type='submit' name='go' value='Go'>
</form>
<?php include "ssi/footer.php" ; ?>
</body>
</html>