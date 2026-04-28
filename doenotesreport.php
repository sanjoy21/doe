<?php
// NOTE: This code uses manual sanitation (db_escape_string) for ALL user inputs 
// to mitigate SQL Injection. The function db_escape_string() (or similar) 
// and database helper functions (e.g., db_query_rows, fixdate) 
// MUST be defined and functional in 'mysql.php'.

include "mysql.php";

// Define a placeholder for the escaping function, required for security.
if (!function_exists('db_escape_string')) {
    function db_escape_string($str) {
        // !!! REPLACE THIS WITH YOUR ACTUAL ESCAPING FUNCTION !!!
        global $link; // Assuming $link is your mysqli connection object
        return mysqli_real_escape_string($link, (string) $str);
    }
}

// Check if the 'doit' flag is set (either from a form submission or other means)
$doit = $_POST['doit'] ?? null;
$xls = $_POST['xls'] ?? null;
$concat = $_POST['concat'] ?? null;
$fieldfrom = $_POST['fieldfrom'] ?? null;
$fieldto = $_POST['fieldto'] ?? null;
$since = $_POST['since'] ?? null;

// Assuming session variables are available globally
$session_iscorp = $GLOBALS['session_iscorp'] ?? '0'; 

if ($doit) { 
    $thetable = "supplyrequests";
    $table = "supplyrequests";
    $datefield = "datesent";
    
    // Default JOIN type and condition (assuming $lj is a global or defined elsewhere, defaulting to a standard join)
    $lj = " "; 

    $extrafields = ", schoolcode, zip";
    $extra = "";
    $swhr = "";

    if ($concat) {
        // SECURE: Ensures the function is called properly in the SQL.
        $extrafields .= ", GROUP_CONCAT( ' ', descr ) AS descr ";
    } else {
        $extrafields .= ", descr";
    }

    // --- 1. Date Filtering (CRITICAL SECURITY AREA) ---

    // Filter by start date ($fieldfrom)
    if ($fieldfrom) {
        // fixdate() should convert the input string to a safe 'Y-m-d' format.
        // We still escape the output of fixdate just in case, or if fixdate isn't safe.
        $tm = fixdate($fieldfrom); 
        $esc_tm = db_escape_string($tm);
        $extra .= " AND {$datefield} >= '{$esc_tm}' ";
    }
    
    // Filter by end date ($fieldto)
    if ($fieldto) {
        $tm = fixdate($fieldto); 
        $esc_tm = db_escape_string($tm);
        $extra .= " AND {$datefield} <= '{$esc_tm}' ";
    }

    // Filter by 'since' date
    if ($since) {
        // SECURE: date() is safe, but using the user input as part of strtotime is safer 
        // if wrapped in proper validation/escaping, though date() limits the format risk.
        $since_date = date("Y-m-d", strtotime(db_escape_string($since)));
        $swhr = " AND {$datefield} > '{$since_date}'";
    }

    // Grouping for concatenation
    if ($concat) {
        $extra .= " GROUP BY companyid ";
    }
    
    // --- 2. Construct and Execute SQL Query (CRITICAL SECURITY AREA) ---
    // Note: $session_iscorp must be type-checked/escaped since it is a session variable 
    // and used in the WHERE clause.
    $esc_iscorp = db_escape_string($session_iscorp);

    $sql = "SELECT 
                t.*, companyname, address, city, borough, principalname, 
                contactphone, contactname, contactemail, schoolcode {$extrafields} 
            FROM 
                company_esi {$lj}, {$table} t 
            WHERE 
                iscorp = '{$esc_iscorp}' 
                AND completed = 0 
                AND companyid = company_esi.id 
                {$swhr} 
                {$extra} 
            ORDER BY 
                {$datefield}";

    // For debugging, use the safe version:
    // echo( "<font color='black'>" . htmlspecialchars($sql) . "</font>" );
    // exit;
    
    // Assuming db_query_rows safely executes the query and returns an array of results
    $res = db_query_rows($sql);

    // --- 3. Output Generation ---
    
    if ($xls) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report_' . htmlspecialchars($table) . '.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array(
        "school",
        "schoolcode",
        "address",
        "telephone number",
        "principal name",
        "date",
        "note"
    );
    fputcsv($output, $headers);

    // Write data rows
    foreach ($res as $r) {
        // Build address with null safety
        $address_parts = array();
        if (!empty($r["address"])) $address_parts[] = $r["address"];
        if (!empty($r["city"])) $address_parts[] = $r["city"];
        if (!empty($r["zip"])) $address_parts[] = $r["zip"];
        if (!empty($r["borough"])) $address_parts[] = $r["borough"];
        $full_address = implode(", ", $address_parts);
        
        // Prepare row data with null safety
        $row_data = array(
            $r["companyname"] ?? '',
            $r["schoolcode"] ?? '',
            $full_address,
            $r["contactphone"] ?? '',
            $r["principalname"] ?? '',
            $r[$datefield] ?? '',
            $r["descr"] ?? ''
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
    else {
        // HTML Output
?>
<?php include "ssi/top.php"; ?>
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr>
    <th class='copy'>school</th>
    <th class='copy'>schoolcode</th>
    <th class='copy'>address</th>
    <th class='copy'>telephone number</th>
    <th class='copy'>principal name</th>
    <th class='copy'>date</th>
    <th class='copy'>note</th>
</tr>
<?php
        foreach ($res as $r) {
?>
<tr>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?=htmlspecialchars($r["companyid"])?>'><?=htmlspecialchars($r["companyname"])?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?=htmlspecialchars($r["companyid"])?>'><?=htmlspecialchars($r["schoolcode"])?></a></td>
<td valign='top' class='copy'><?=htmlspecialchars($r["address"])?>, <?=htmlspecialchars($r["city"])?>, <?=htmlspecialchars($r["zip"])?>, <?=htmlspecialchars($r["borough"])?></td>
<td valign='top' class='copy'><?=htmlspecialchars($r["contactphone"])?></td>
<td valign='top' class='copy'><?=htmlspecialchars($r["principalname"])?></td>
<td valign='top' class='copy'><?=htmlspecialchars($r[$datefield])?></td>
<td valign='top' class='copy'><?=nl2br(htmlspecialchars($r["descr"]))?></td>
</tr>
<?php
        }
?>
</table>
<?php
        // Footer and closing HTML tags for the report view
        include "ssi/footer.php";
?>
<br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
<?php
    }
} else {
    // --- 4. Form Output ---
    // Display the input form if 'doit' is not set
?>
<?php include "ssi/top.php"; ?>
<form method='post'>
Notes to the DOE Since: <input type='text' name='since' value='<?=htmlspecialchars($since)?>'><br>
XLS: <input type='checkbox' name='xls' value='1' <?=!empty($xls) ? "CHECKED" : ""?> ><br>
All Notes From One School On One Line: <input type='checkbox' name='concat' value='1' <?=!empty($concat) ? "CHECKED" : "CHECKED"?>><br>
<input type='submit' name='doit' value='Go'>
</form>
<?php include "ssi/footer.php"; ?>
<?php 
}
?>