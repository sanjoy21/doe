<?php 
include "mysql.php";

$res = db_query_rows( "select company_esi.zip, company_esi.id, schoolcode, companyname from company_esi where deleted = 0 and schoolphone = '' and company_esi.iscorp=0", "id" );

$already = array();
foreach( $res as $row )
{
    $id_safe = $row["id"] ?? '';
    $companyname_safe = $row["companyname"] ?? 'N/A';
    $schoolcode_safe = $row["schoolcode"] ?? 'N/A';
    $zip_safe = $row["zip"] ?? 'N/A';
    
    echo( "no phone for: <A href='viewcompany.php?id=$id_safe'>" . $companyname_safe . "</a> " . $schoolcode_safe . " " . $zip_safe . "<br>" );
}

?>