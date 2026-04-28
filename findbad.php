<?php
$nologinrequired = 1; // Used to bypass login check in included files
include "mysql.php";
require_once('services.php'); // Assumes this contains getResponderRow and updateResponder

// --- 1. File Handling Setup ---
$loc = "/tmp/bad.csv";
$handle = fopen($loc, "r");

if ($handle === false) {
    die("Error: Could not open CSV file at {$loc}");
}

// Safely retrieve the $fix flag for optional update
$fix = $_REQUEST['fix'] ?? null;

// --- 2. HTML Table Output Start ---
echo "<table border='1' cellpadding='2' cellspacing='0'><tr>";
echo "<th>District ID</th>";
echo "<th>Building Code</th>";
echo "<th>First</th>";
echo "<th>Last</th>";
echo "<th>TRID</th>";
echo "<th>Exp Date</th>";
echo "<th>ESI RESULTS</th>";
echo "</tr>";

// --- 3. Process CSV Rows ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
    
    // Skip header row based on expected header value
    if (($data[8] ?? null) === "TRID") continue;

    // Sanitize and cast TRID (which is $data[8])
    $trid = (int)($data[8] ?? 0);

    // --- Optional Fix Logic ---
    if( $fix && $trid > 0 )
    {
        // Assumes getResponderRow and updateResponder are defined in services.php
        $arow = getResponderRow( $trid );
        updateResponder( $arow );
    }
    
    // --- Database Query: Find Responder by TRID ---
    // The query is structured to find a unique responder using the TRID (responderid)
    if ($trid > 0) {
        $sql = "SELECT responderid, firstname, lastname, clientid, pmsid, pmsidvalidated, lastupdateresult, lastupdatedate 
                FROM responders_esi 
                WHERE responderid = {$trid} AND deleted = 0 
                ORDER BY lastupdateresult DESC LIMIT 1";
        $r = db_query_first( $sql );
    } else {
        $r = null;
    }

    // --- Output CSV data row ---
    echo "<tr>";
    echo "<td valign='top'>" . htmlspecialchars($data[0] ?? '') . "</td>"; // District ID
    echo "<td valign='top'>" . htmlspecialchars($data[1] ?? '') . "</td>"; // Building Code
    echo "<td valign='top'>" . htmlspecialchars($data[4] ?? '') . "</td>"; // First
    echo "<td valign='top'>" . htmlspecialchars($data[5] ?? '') . "</td>"; // Last
    echo "<td valign='top'>" . htmlspecialchars($data[8] ?? '') . "</td>"; // TRID
    echo "<td valign='top'>" . htmlspecialchars($data[7] ?? '') . "</td>"; // Exp Date
    echo "<td><table>";
    
    // --- Output ESI Results (Sub-table) ---
    $f = "";
    // Check if a result was found
    if( is_array($r) && ($r['lastupdateresult'] ?? '') === "Success" ) {
        $f = "bgcolor='#befac5'";
    }

    echo "<tr {$f}>";
    
    $pmsid = htmlspecialchars($r['pmsid'] ?? 'N/A');
    $firstname_db = htmlspecialchars($r['firstname'] ?? 'N/A');
    $lastname_db = htmlspecialchars($r['lastname'] ?? 'N/A');
    $lastupdateresult = htmlspecialchars($r['lastupdateresult'] ?? 'Not Found');
    $lastupdatedate = htmlspecialchars($r['lastupdatedate'] ?? 'N/A');

    echo "<td width='50'>{$pmsid}</td>";
    echo "<td width='150'>{$firstname_db}</td>";
    echo "<td width='150'>{$lastname_db}</td>";
    echo "<td>{$lastupdateresult}</td>";
    echo "<td>{$lastupdatedate}</td>";
    echo "</tr>";
    
    echo "</table></td>";
    echo "</tr>";
}
// --- 4. Cleanup and Close Table ---
fclose($handle);
echo "</table>";
?>