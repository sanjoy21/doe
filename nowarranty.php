<?php

include "mysql.php";

$sql = "SELECT 
            c.*, 
            COUNT(aed_esi.clientid) AS cnt, 
            SUM(aed_esi.outofwarranty) AS w 
        FROM company_esi c 
        LEFT JOIN aed_esi ON aed_esi.clientid = c.id 
        WHERE 
            c.iscorp = 0 
            AND c.deleted = 0 
            AND aed_esi.deleted = 0 
        GROUP BY c.id 
        HAVING cnt = w AND cnt > 0";

$schools = db_query_rows($sql);
?>

<?php 
$i = 1;
echo ("<table border=1>");

echo ("<tr>");
echo ("<td></td><td>School Code</td><td>School Name</td><td>Address</td><td>City</td><td>State</td><td>Zip</td><td>Region</td><td>AED Count</td>");
echo ("</tr>");

foreach ($schools as $crow) {

    $id = htmlspecialchars($crow['id']);
    $schoolcode = htmlspecialchars($crow['schoolcode']);
    $companyname = htmlspecialchars($crow['companyname']);
    $address = htmlspecialchars($crow['address']);
    $city = htmlspecialchars($crow['city']);
    $state = htmlspecialchars($crow['state']);
    $zip = htmlspecialchars($crow['zip']);
    $region = htmlspecialchars($crow['region']);
    $cnt = htmlspecialchars($crow['cnt']);
    // $w = htmlspecialchars($crow['w']); // 'w' is equal to 'cnt' due to the HAVING clause

    echo ("<tr>");
    // ID and School Code with links to viewcompany.php
    echo ("<td><a target='_blank' href='viewcompany.php?id={$id}'>{$id}</a></td>");
    echo ("<td><a target='_blank' href='viewcompany.php?id={$id}'>{$schoolcode}&nbsp;</a></td>");
    
    // Company/Location Details
    echo ("<td>{$companyname}</td>");
    echo ("<td>{$address}</td>");
    echo ("<td>{$city}</td>");
    echo ("<td>{$state}</td>");
    echo ("<td>{$zip}</td>");
    echo ("<td>{$region}</td>");
    
    // AED Count
    echo ("<td>{$cnt}</td>");
    // The original script commented out the 'w' (out of warranty) and 'cnt-w' (in warranty) columns
    // echo ("<td>{$w}</td>");
    // echo ("<td>" . ($cnt - $w) . "</td>");
    
    echo ("</tr>");
    $i++;
} 
echo ("</table>"); 
?>