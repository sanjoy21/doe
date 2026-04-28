<?php
include "mysql.php";

// The query selects corporate companies (iscorp = 1, not deleted) that have no class records.
// It uses LEFT JOIN and HAVING COUNT = 0 to achieve this filtering.
$sql = "SELECT 
            c.id, 
            count(class.id) AS cnt 
        FROM 
            company_esi c 
        LEFT JOIN 
            class ON class.companyid = c.id 
        WHERE 
            c.iscorp = 1 
            AND c.deleted = 0 
        GROUP BY 
            c.id 
        HAVING 
            cnt = 0";

// db_query_array returns an array where both key and value are the company ID.
$results = db_query_array( $sql, "id", "id" );

// Output the list of companies
foreach( $results as $company_id )
{
    // PHP 8.2 Compliance and Security:
    // htmlspecialchars() is used to secure the output link parameter.
    $safe_id = htmlspecialchars($company_id);
    
    echo( "<A href='viewcompany.php?id=$safe_id'>" . getCompanyName( $company_id ) . "</a><br>" );
}

?>