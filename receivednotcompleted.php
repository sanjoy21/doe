<?php
include "mysql.php";

$sql = "SELECT 
            id, 
            companyname, 
            schoolcode, 
            campusid, 
            drillid 
        FROM 
            company_esi c, drill a 
        WHERE 
            a.companyid = c.id 
            AND c.iscorp = '" . $session_iscorp . "' 
            AND a.completed = -1 
            AND c.deleted = 0 
        ORDER BY 
            companyname";

$result = db_query_rows( $sql );

echo( "<table>" );

// Add a header row for clarity
echo( "<tr><th>School Code</th><th>Company Name</th><th>Drill ID</th></tr>" ); 

foreach( $result as $row )
{
    // PHP 8.2 Compliance and Security: Quoting array keys and using htmlspecialchars()
    $drill_id = htmlspecialchars($row['drillid'] ?? '');
    $school_code = htmlspecialchars($row['schoolcode'] ?? 'N/A');
    $company_name = htmlspecialchars($row['companyname'] ?? 'N/A');

    echo ("<tr>
              <td><a target='_blank' href='editdrill.php?drillid=" . $drill_id . "'>" . $school_code . "</a></td>
              <td>" . $company_name . "</td>
              <td>" . $drill_id . "</td>
          </tr>" );
}
echo( "</table>" );
?>