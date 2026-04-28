<?php
include "mysql.php";

$sql = " SELECT 
            id, 
            companyname, 
            SUM(d2.completed) AS cnt2, 
            SUM(drill.completed) AS cnt 
         FROM 
            company_esi 
         LEFT JOIN 
            drill ON drill.companyid = company_esi.id 
         LEFT JOIN 
            drill_to_companyid ON drill_to_companyid.companyid = company_esi.id 
         LEFT JOIN 
            drill d2 ON d2.drillid = drill_to_companyid.drillid 
         WHERE 
            deleted = 0 AND iscorp = 0 
         GROUP BY 
            company_esi.id 
         HAVING 
            cnt2 IS NULL AND cnt IS NULL 
         ORDER BY 
            companyname";

$res = db_query_rows( $sql, "id" );

foreach( $res as $row )
{
    // PHP 8.2 Compliance: Array keys are quoted, and output is secured with htmlspecialchars().
    $company_id = htmlspecialchars($row['id'] ?? '');
    $company_name = htmlspecialchars($row['companyname'] ?? '');

    echo( "<a href='viewcompany.php?id=" . $company_id . "'>" . $company_name . "</a><br>" );
}
?>