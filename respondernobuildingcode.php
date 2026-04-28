<?php
require_once "mysql.php";

// The initial query uses internal database fields and constants, reducing immediate SQLi risk, 
// but it should ideally use proper JOIN syntax and be handled by a safe function if any filter variables were used.
$sql = "
SELECT 
    a.*, 
    c.companyname, 
    c.schoolcode, 
    c.locationcode 
FROM responders_esi a
JOIN company_esi c ON a.clientid = c.id
WHERE a.buildingcode = ''
    AND a.iscorp = 0
    AND a.donotinclude = 0
    AND a.deleted = 0
    AND c.deleted = 0
    AND c.schoolcode NOT LIKE '84-%'
";


$schools = db_query_rows($sql); 

$i = 1;
$minnum = $_REQUEST['minnum'] ?? '0'; // Initialize $minnum safely

echo "<table border=1>";
echo "<tr><td></td><td>RESPONDER name</td><td>RESPONDER pmsid</td><td>School Code</td><td>BCs?</td><td>School Name</td></tr>";

if (!empty($schools)) {
    foreach ($schools as $s) {
        $location_code = $s['locationcode'] ?? '';

        // 1. SQL INJECTION MITIGATION (Secondary Query): Use a safe function with parameter
        // Assumes locationcode is being treated as a safe parameter
        $bc = db_query_first_cell("SELECT group_concat(buildingcode) FROM location_to_building WHERE locationcode = ?", 
                                       array($location_code));

        // 2. XSS MITIGATION: Escape all database outputs before echoing
        $i_safe = htmlspecialchars($i);
        $firstname_safe = htmlspecialchars($s['firstname'] ?? '');
        $lastname_safe = htmlspecialchars($s['lastname'] ?? '');
        $pmsid_safe = htmlspecialchars($s['pmsid'] ?? '');
        $companyname_safe = htmlspecialchars($s['companyname'] ?? '');
        $schoolcode_safe = htmlspecialchars($s['schoolcode'] ?? '');
        $bc_safe = htmlspecialchars($bc); // Escape the concatenated building codes
        $responderid = (int)($s['responderid'] ?? 0);
        $clientid = (int)($s['clientid'] ?? 0);

        echo "<tr>";
        echo "<td>{$i_safe}</td>";
        echo "<td><a href='viewresponder.php?responderid={$responderid}'>{$firstname_safe} {$lastname_safe}</a></td>";
        echo "<td>{$pmsid_safe}</td>";
        echo "<td><a href='viewcompany.php?id={$clientid}'>{$companyname_safe}</a></td>";
        echo "<td>{$bc_safe}</td>";
        echo "<td>{$schoolcode_safe}</td>";
        echo "</tr>";
        
        $i++;
    }
} else {
     echo "<tr><td colspan='6'>No schools found matching the criteria.</td></tr>";
}
echo "</table>";
?>