<?php
include "mysql.php";

// Assume $xls and $session_iscorp are defined elsewhere. Defaulting $session_iscorp to '0' 
// (non-corporate) for safety, based on the original query's likely intent.
$xls = $xls ?? null;
$session_iscorp = $session_iscorp ?? '0';

// Excel Download Headers
if( $xls )
{
    // Sets headers to trigger an Excel download
    Header( "Content-type: application/vnd.ms-excel" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Disposition: attachment; filename=\"classes.xls\"");
    // Note: No Excel content is generated here, only the headers are sent.
}

$arr = array();

// Query to find companies that are not deleted, match the session's corporate status, 
// and have no associated user records (userid IS NULL).
$sql = "SELECT 
            company_esi.* FROM 
            company_esi 
        LEFT JOIN 
            user ON companyid = company_esi.id 
        WHERE 
            iscorp = '$session_iscorp' 
            AND company_esi.deleted = 0 
            AND userid IS NULL 
        GROUP BY 
            company_esi.id 
        ORDER BY 
            company_esi.companyname";

$rows = db_query_rows( $sql );

// Start HTML table output
echo( "<table border=1>
        <thead>
            <tr>
                <th>schoolcode</th>
                <th>name</th>
                <th>id</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>" ) ;

foreach( $rows as $r )
{
    // PHP 8.2 Compliance and Security: Quoting array keys and securing output with htmlspecialchars()
    $schoolcode = htmlspecialchars($r['schoolcode'] ?? '');
    $companyname = htmlspecialchars($r['companyname'] ?? '');
    $company_id = htmlspecialchars($r['id'] ?? '');

    echo( "<tr>
        <td>" . $schoolcode . "</td>
        <td>" . $companyname . "</td>
        <td>" . $company_id . "</td>" );
    echo( "<td><a href='viewcompany.php?id=" . $company_id . "'>View</a></td>" );
    echo( "</tr>" );
}

echo( "</tbody></table>" ) ;
?>