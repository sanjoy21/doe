<?php
include "mysql.php";

echo( "<table border='1' cellpadding='2' cellspacing='0'>
        <thead>
            <tr>
                <th>Name</th>
                <th>School Code</th>
                <th># Buildings</th>
                <th>Buildings</th>
            </tr>
        </thead>
        <tbody>" );

// Query to find companies that have more than one building associated with their location code.
$sql = "SELECT 
            id, 
            companyname, 
            schoolcode, 
            count( buildingcode ) AS cnt, 
            group_concat( buildingcode ) AS bldgs 
        FROM 
            company_esi, 
            location_to_building 
        WHERE 
            company_esi.locationcode IS NOT NULL 
            AND company_esi.locationcode = location_to_building.locationcode 
        GROUP BY 
            id, companyname, schoolcode 
        HAVING 
            cnt > 1";

$res = db_query_rows( $sql );

foreach( $res as $r )
{
    $company_id = htmlspecialchars($r['id'] ?? '');
    $company_name = htmlspecialchars($r['companyname'] ?? '');
    $schoolcode = htmlspecialchars($r['schoolcode'] ?? '');
    $count = htmlspecialchars($r['cnt'] ?? 0);
    $buildings = htmlspecialchars($r['bldgs'] ?? '');

    echo( "<tr>" );
    echo( "<td><a href='viewcompany.php?companyid=" . $company_id . "'><nobr>" . $company_name . "</nobr></a></td>" );
    echo( "<td>" . $schoolcode . "</td>" );
    echo( "<td>" . $count . "</td>" );
    echo( "<td>" . $buildings . "</td>" );
    echo( "</tr>" );
}
echo( "</tbody></table>" );
?>